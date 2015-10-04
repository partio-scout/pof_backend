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


function pof_importer_tasksdriveimport_run($fileId, $saveToDataBase = false) {
	$service = pof_importer_get_google_service();

	try {
		$file = $service->files->get($fileId);

		echo '<table cellpadding="2" cellspacing="2" border="2">';
		echo '<tr>';
		echo '<th>Otsikko</th>';
		echo '<td>'.$file->getTitle().'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Kuvaus</th>';
		echo '<td>'.$file->getDescription().'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Viimeksi muokattu</th>';
		$fileLastModified = strtotime($file->getModifiedDate());
		echo '<td>'.date('d.m.Y', $fileLastModified).'</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th>Muokkaaja</th>';
		echo '<td>'.$file->getLastModifyingUserName().'</td>';
		echo '</tr>';

		echo '</table>';


		$filepath = pof_importer_download_drive_file($service, $file);
		
		$objReader = pof_imported_get_phpExcel_objReader();

		$objPHPExcel = $objReader->load($filepath);

		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

/*
		echo "<pre>";
		print_r($sheetData);

		echo "\n\n\n\n\n\n\n\n\n\n\n";

		var_dump($sheetData);

		echo "</pre>";
*/

		if (pof_importer_tasksdriveimport_checkThatHeadersMatchTasks($sheetData[1])) {

			$headers = $sheetData[1];
			$i = 0;
			foreach($sheetData as $sheetDataRow) {
				$i++;
				if ($i < 2) {
					continue;
				}

				pof_importer_tasksdriveimport_importRow($sheetDataRow, $i, $saveToDataBase);
				
			}

		} else {
			echo "<h1>Unvalid excel, or failed to read excel at all.</h1>";
		}

	} catch (Exception $e) {
		echo "An error occurred: " . $e->getMessage();
	}

}

function pof_importer_tasksdriveimport_checkThatHeadersMatchTasks($headerRow) {

	if (   !is_array($headerRow)
		|| count($headerRow) < 7) {
		echo "<h1>Not enough fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	if (   $headerRow['D'] != "Otsikko fi"
		|| $headerRow['E'] != "Ingressi fi"
		|| $headerRow['I'] != "Taitoalueet") {
		echo "<h1>Wrong fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	return true;
}


function pof_importer_tasksdriveimport_importRow($row, $row_index, $saveToDataBase = false) {
	$agegroups_and_taskgroups = pof_importer_get_agegroups_and_taskgroups();
	if (   empty($row['A'])
		|| substr($row['A'], 0, 2) != 'KY'
		|| empty($row['D'])) {

		if (!empty($row['D'])) {
			echo "Not importing, because row not setted to be imported: " . $row['D'];
		} else {
			echo "Not importing, because row not setted to be imported, empty title. Row " . $row_index;
		}
		
		echo '<br />';

		return;
	}
	echo "<br />";
	echo "to be imported:" . $row['D'];
	echo "<br />";

/*
	echo "<pre>";
	print_r($row);
	echo "</pre>";
*/

	$taskgroup_obj = new stdClass();

	$agegroup_title = pof_importer_normalize_title($row['B']);
	$taskgroup_title = pof_importer_normalize_title($row['C']);

	if (!empty($agegroups_and_taskgroups[$agegroup_title])
		&& !empty($agegroups_and_taskgroups[$agegroup_title]->taskgroups[$taskgroup_title])) {
		$taskgroup_obj = $agegroups_and_taskgroups[$agegroup_title]->taskgroups[$taskgroup_title];

	}

	if (empty($taskgroup_obj->id)) {
		echo "<h2>Couldn't find taskgroup '" . $row['C'] . "' for task '".$row['D']."'</h2>";
		return;
	}

	$wp_error = false;

	$post = get_page_by_title(trim($row['D']), "OBJECT", 'pof_post_task');

	if (empty($post)) {
		echo "POST NOT FOUND; TO BE CREATED<br />";
		if ($saveToDataBase) {
			$post = array(
				'post_title'    => trim($row['D']),
				'post_content'  => $row['F'],
				'post_type' => 'pof_post_task',
				'post_status'   => 'publish',
				'post_author'   => get_current_user_id()
			);
			$post_id = wp_insert_post( $post, $wp_error );

			echo "imported, post_id: " . $post_id;
			echo "<br />";

			$post = get_post($post_id);
		}

	} else {
		echo "POST FOUND; TO BE UPDATED<br />";
		$post_id = $post->ID;
	}



	if ($saveToDataBase) {
		update_post_meta($post_id, "suoritepaketti", $taskgroup_obj->id);


		update_field("ingress", $row['E'], $post_id);
		$post->post_title = trim($row['D']);
		$post->post_content = $row['F'];
		$post->post_author = get_current_user_id();

		update_post_meta($post_id, "task_duration", trim(str_replace(" min", "", $row['M'])));

		if (substr(strtolower($row['K']), 0, 2) == 'ky') {
			update_field("task_mandatory", 1, $post_id);
		} else {
			update_field("task_mandatory", 0, $post_id);
		}

		update_field("task_place_of_performance", pof_importer_get_places($row['L']), $post_id);

		wp_update_post($post, $wp_error);
	}
}


function pof_importer_get_places($places_str) {
	$ret = array();

	$parts = explode(',', $places_str);

	foreach ($parts as $part) {

		if (strtolower($part) == 'kolo') {
			array_push($ret, 'meeting_place');
		} elseif (strtolower($part) == 'vene') {
			array_push($ret, 'boat');
		} elseif (strtolower($part) == 'retki') {
			array_push($ret, 'hike');
		} elseif (strtolower($part) == 'leiri') {
			array_push($ret, 'camp');
		} else {
			array_push($ret, 'other');
			array_push($ret, $part);
		}
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

function pof_importer_normalize_title($title) {

	$title = mb_convert_encoding($title,"UTF-8", "auto");

	$title = strtolower($title);
	$title = str_replace( ' ', '_', $title);
/*
	$title = str_replace( "ä", 'a', $title);
	$title = str_replace( 'ö', 'o', $title);
	$title = str_replace( 'å', 'a', $title);
*/
	return $title;
}