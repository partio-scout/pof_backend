<?php

function pof_importer_get_google_service() {
	set_include_path(get_include_path() . PATH_SEPARATOR . '/wp-content/plugins/pof-importer/google-api-php-client/src');
	require 'google-api-php-client/src/Google/autoload.php';

	$client_email = pof_settings_get_google_api_user();
	$private_key = pof_settings_get_google_api_certificate();
	$password = pof_settings_get_google_api_password();
	$scopes = array(
				'https://www.googleapis.com/auth/drive',
				'https://www.googleapis.com/auth/drive.file',
				'https://www.googleapis.com/auth/drive.metadata',
				'https://www.googleapis.com/auth/drive.metadata.readonly'
			);
	$credentials = new Google_Auth_AssertionCredentials(
		$client_email,
		$scopes,
		$private_key,
		$password
	);


	$client = new Google_Client();
	$client->setAssertionCredentials($credentials);
	if ($client->getAuth()->isAccessTokenExpired()) {
		$client->getAuth()->refreshTokenWithAssertion();
	}


	$service = new Google_Service_Drive($client);

	return $service;
}

function pof_imported_get_phpExcel_objReader() {
	require_once plugin_dir_path( __FILE__ ) . '/phpexcel/PHPExcel.php';
		
	$objReader = new PHPExcel_Reader_Excel2007();

	return $objReader;
}

function pof_importer_download_drive_file($service, $file) {

	$filefolder = plugin_dir_path( __FILE__ ) . "googleDriveDownloads";

	$filename =  str_replace("-", "", $file->id) . ".xlsx";

	$downloadUrl = $file->exportLinks["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];

	$filecontents = null;

	if ($downloadUrl) {
		$request = new Google_Http_Request($downloadUrl, 'GET', null, null);
		$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
		if ($httpRequest->getResponseHttpCode() == 200) {
			$filecontents = $httpRequest->getResponseBody();
		} else {
			// An error occurred.
			return null;
		}
	} else {
		// The file doesn't have any content stored on Drive.
		return null;
	}
	

	if (!empty($filecontents)) {
		if (!file_exists($filefolder)) {
			mkdir($filefolder);
		}

		$destination = $filefolder . "/" . $filename;

		if (file_exists($destination)) {
			unlink($destination);
		}

		$file2 = fopen($destination, "w+");
		fputs($file2, $filecontents);
		fclose($file2);

		return ($destination);
	}

	return null;
}


function pof_importer_get_level($string) {
	$level_num = "0";

	$string = strtolower(trim($string));
	$string = str_replace("taso", "", $string);
	$string = trim($string);

	if ($string != "") {
		$level_num = (int) $string;
	}

	return (string) $level_num;
}

function pof_importer_normalize_key($string) {
	$string = strtolower($string);
	$string = mb_convert_encoding($string, "UTF-8", "auto");
	$string = str_replace(" ", "_", $string);
	$string = str_replace(mb_convert_encoding("ä", "UTF-8", "auto"), "_", $string);
//	$string = str_replace(mb_convert_encoding("ö", "UTF-8", "auto"), "_", $string);
//	$string = str_replace(mb_convert_encoding("å", "UTF-8", "auto"), "_", $string);

	return $string;
}


$pof_importer_driveimport_skillareas = array();

function pof_importer_get_skillareas($areas_str) {

	global $wpdb;

	global $pof_importer_driveimport_skillareas;

	if (count($pof_importer_driveimport_skillareas) == 0)
	{
		$pof_importer_driveimport_skillareas = pof_taxonomy_translate_get_skillareas();
	}

	$ret = array();

	$parts = explode(',', $areas_str);	

	foreach ($parts as $part) 
	{
		$part = trim($part);

		$sanitized_part = sanitize_title_with_dashes(pof_importer_normalize_key($part));

		$part_key = array_search(strtolower($part), $pof_importer_driveimport_skillareas);

		if ($part_key == false) {
			wp_insert_term($part, "pof_tax_skillarea", array("slug" => $sanitized_part));
			array_push($ret, $sanitized_part);
			$pof_importer_driveimport_skillareas = pof_taxonomy_translate_get_skillareas();

		} else {
			array_push($ret, $part_key);
		}

	}

	return $ret;
	
}


$pof_importer_driveimporter_places = array();
function pof_importer_get_places($places_str) {

	global $wpdb;

	$taxonomy_base_key = "place_of_performance";

	global $pof_importer_driverimporter_places;

	if (count($pof_importer_driverimporter_places) == 0)
	{
		$pof_importer_driverimporter_places = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, true);
	}


	$ret = array();

	$parts = explode(',', $places_str);

	foreach ($parts as $part) 
	{
		$part = trim($part);

		$part_key = array_search(strtolower($part), $pof_importer_driverimporter_places);

		if ($part_key == false) {

			$part_tmp = str_replace(" ", "_", strtolower($part));
		
			$taxonomy_full_key = $taxonomy_base_key . "::" . sanitize_title_with_dashes(pof_importer_normalize_key($part_tmp));

			$tmp = $wpdb->insert( 
				pof_taxonomy_translate_get_table_name(), 
				array( 
					'taxonomy_slug' => $taxonomy_full_key, 
					'agegroup_id' => 0,
					'lang' => 'fi',
					'content' => $part
				), 
				array( 
					'%s', 
					'%d', 
					'%s',
					'%s',
				) 
			);

			$pof_importer_driverimporter_places = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, true);
		}
	
		$part_key = str_replace($taxonomy_base_key . '::', "", $part_key);
		array_push($ret, $part_key);
	}
	
	return $ret;

}

$pof_imported_agegeroups_and_taskgroups_arr = array();

function pof_importer_get_agegroups_and_taskgroups() {
	global $pof_imported_agegeroups_and_taskgroups_arr;

	if (count($pof_imported_agegeroups_and_taskgroups_arr)) {
		return $pof_imported_agegeroups_and_taskgroups_arr;
	}


	$items = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_agegroup' )
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {

			$item = new stdClass();
			$the_query->the_post();
			$item->id = $the_query->post->ID;
			$item->title = $the_query->post->post_title;

			$item->taskgroups = pof_importer_get_agegroups_and_taskgroups_get_taskgroups($the_query->post->ID);
			
			$items[pof_importer_normalize_title($the_query->post->post_title)] = $item;
		}
	}
	$pof_imported_agegeroups_and_taskgroups_arr = $items;

	return $items;
}

function pof_importer_get_agegroups_and_taskgroups_get_taskgroups($agegroup_id) {
	
	$items = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_taskgroup' ),
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'ikakausi',
		'meta_value' => $agegroup_id
	);

	$the_query2 = new WP_Query( $args );

	if( $the_query2->have_posts() ) {
		while ( $the_query2->have_posts() ) {

			$item = new stdClass();
			$the_query2->the_post();
			$item->id = $the_query2->post->ID;
			$item->title = $the_query2->post->post_title;

			$items[pof_importer_normalize_title($the_query2->post->post_title)] = $item;

			$subitems = pof_importer_get_agegroups_and_taskgroups_get_subtaskgroups($the_query2->post->ID);

			if (count($subitems) > 0 ) {
				foreach ($subitems as $subitem_key => $subitem) {
					$items[$subitem_key] = $subitem;
				}
			}

		}
	}


	return $items;
}

function pof_importer_get_agegroups_and_taskgroups_get_subtaskgroups($parent_id) {
	$items = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_taskgroup' ),
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_query3 = new WP_Query( $args );

	if( $the_query3->have_posts() ) {
		while ( $the_query3->have_posts() ) {

			$item = new stdClass();
			$the_query3->the_post();
			$item->id = $the_query3->post->ID;
			$item->title = $the_query3->post->post_title;

			$items[pof_importer_normalize_title($the_query3->post->post_title)] = $item;

			$subitems = pof_importer_get_agegroups_and_taskgroups_get_subtaskgroups($the_query3->post->ID);

			foreach ($subitems as $subitem_key => $subitem) {
				$items[$subitem_key] = $subitem;
			}
		}
	}

	return $items;
}

$pof_imported_agegeroups_and_tasks_arr = array();

function pof_importer_get_agegroups_and_tasks($agegroup_id) {
    global $pof_imported_agegeroups_and_tasks_arr;

	if (array_key_exists($agegroup_id, $pof_imported_agegeroups_and_tasks_arr)) {
		return $pof_imported_agegeroups_and_tasks_arr;
	}

    $taskgroups = pof_importer_get_agegroups_and_taskgroups_get_taskgroups($agegroup_id);

    $items = array();

    $taskgroup_ids = array();

    foreach ($taskgroups as $taskgroup) {
        array_push($taskgroup_ids, $taskgroup->id);
    }

    $items = pof_importer_get_agegroups_and_tasks_get_tasks($taskgroup_ids);

    $pof_imported_agegeroups_and_tasks_arr[$agegroup_id] = $items;

    return $pof_imported_agegeroups_and_tasks_arr;
}

function pof_importer_get_agegroups_and_tasks_get_tasks($taskgroup_ids) {

    $items = array();

    $args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_task' ),
		'orderby' => 'title',
		'order' => 'ASC',
//		'meta_key' => 'suoritepaketti',
//		'meta_value' => $taskgroup_id
        'meta_query' => array (
		    array (
			  'key' => 'suoritepaketti',
			  'value' => $taskgroup_ids,
              'compare' => 'IN'
		    )
		)
	);

	$the_query4 = new WP_Query( $args );

    if( $the_query4->have_posts() ) {
		while ( $the_query4->have_posts() ) {

			$item = new stdClass();
			$the_query4->the_post();
			$item->id = $the_query4->post->ID;
			$item->title = $the_query4->post->post_title;
			$item->guid = get_post_meta( $the_query4->post->ID, "post_guid", true );

			$items[pof_importer_normalize_title($the_query4->post->post_title)] = $item;
		}
	}

	return $items;
}


function pof_importer_normalize_title($title) {

	$title = mb_convert_encoding($title,"UTF-8", "auto");

	$title = strtolower(trim($title));
	$title = str_replace( ' ', '_', $title);
/*
	$title = str_replace( "ä", 'a', $title);
	$title = str_replace( 'ö', 'o', $title);
	$title = str_replace( 'å', 'a', $title);
*/
	return $title;
}



function pof_importer_suggestions_get_language($language) {

	$lang = 'fi';

// TODO: use lang table for this

	switch (strtolower($language)) {
		case "suomi":
			$lang = "fi";
		break;
		case "ruotsi":
			$lang = "sv";
		break;
		case "englanti":
			$lang = "en";
		break;
		case "somalia":
			$lang = "so";
		break;
	}
	return $lang;
}


function pof_importer_title_filter( $where, &$wp_query )
{
	global $wpdb;
	if ( $search_term = $wp_query->get( 'search_post_title' ) ) {
		$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $search_term ) ) . '%\'';
	}
	return $where;
}



