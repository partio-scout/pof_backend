<?php

function pof_importer_suggestionsdriveimport_run($fileId, $saveToDataBase = false) {
	$service = pof_importer_get_google_service();

	try {
		$file = $service->files->get($fileId);

		echo '<table cellpadding="2" cellspacing="2" border="2">';
		echo '<tr>';
		echo '<th>Otsikko</th>';
		echo '<td>'.$file->getTitle().'</td>';
		echo '</tr>';
        echo '<tr>';
		echo '<th>Avaa drivess&auml;</th>';
		echo '<td><a href="'.$file->getAlternateLink().'" target="_blank">'.$file->getAlternateLink().'</a></td>';
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


		if (pof_importer_tasksdriveimport_checkThatHeadersMatchSuggestions($sheetData[1])) {

			$headers = $sheetData[1];
			$i = 0;
			foreach($sheetData as $sheetDataRow) {
				$i++;
				if ($i < 2) {
					continue;
				}

				pof_importer_suggestionssdriveimport_importRow($sheetDataRow, $i, $saveToDataBase);
				
			}

		} else {
			echo "<h1>Unvalid excel, or failed to read excel at all.</h1>";
		}

	} catch (Exception $e) {
		echo "An error occurred: " . $e->getMessage();
	}

}

function pof_importer_tasksdriveimport_checkThatHeadersMatchSuggestions($headerRow) {

	if (   !is_array($headerRow)
		|| count($headerRow) < 7) {
		echo "<h1>Not enough fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	if (   $headerRow['B'] != "Aktiviteetti"
		|| $headerRow['C'] != "Vinkin kieli"
		|| $headerRow['D'] != "Kirjoittaja") {
		echo "<h1>Wrong fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	return true;
}

function pof_importer_suggestionssdriveimport_importRow($row, $row_index, $saveToDataBase = false) {
	$agegroups_and_taskgroups = pof_importer_get_agegroups_and_taskgroups();
	if (   empty($row['A'])
		|| substr($row['A'], 0, 2) != 'KY'
		|| empty($row['D'])) {

		if (empty($row['A'])) {
			return;
		}
		if (!empty($row['D'])) {
			echo "Not importing, because row not setted to be imported: " . $row['D'];
		} else {
			echo "Not importing, because row not setted to be imported, empty title. Row " . $row_index;
		}
		
		echo '<br />';

		return;
	}
	echo "<br />";
	echo "to be imported:" . $row['F'];
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
		echo "<h2>Couldn't find taskgroup '" . $row['C'] . "' for suggestion '".$row['F']."'</h2>";
		return;
	}

	$wp_error = false;
	$post = null;

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_task' ),
		'meta_key' => 'suoritepaketti',
		'meta_value' => $taskgroup_obj->id,
		'search_post_title' => $row['D']
	);

	add_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	$the_query_task = new WP_Query( $args );
	remove_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	

	if( $the_query_task->have_posts() ) {
		while ( $the_query_task->have_posts() ) {

			$the_query_task->the_post();
			$task = $the_query_task->post;
		}
	}

	if (empty($task)) {
		echo "<h2>Couldn't find task '" . $row['D'] . "' for suggestion '".$row['F']."'</h2>";
		return;

	}

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_suggestion' ),
		'meta_key' => 'pof_suggestion_task',
		'meta_value' => $task->ID,
		'search_post_title' => $row['F']
	);

	add_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	$the_query_suggestion = new WP_Query( $args );
	remove_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	

	if( $the_query_suggestion->have_posts() ) {
		while ( $the_query_suggestion->have_posts() ) {

			$the_query_suggestion->the_post();
			$post = $the_query_suggestion->post;
		}
	}

	if (empty($post)) {
		echo "POST NOT FOUND; TO BE CREATED<br />";
		if ($saveToDataBase) {
			$post = array(
				'post_title'    => trim($row['B']),
				'post_content'  => $row['F'],
				'post_type' => 'pof_post_suggestion',
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
		update_post_meta($post_id, "pof_suggestion_task", $task->ID);


		update_post_meta($post_id, "pof_suggestion_lang", pof_importer_suggestions_get_language($row['C']));
		update_post_meta($post_id, "pof_suggestion_writer", $row['D']);
		$post->post_title = trim($row['F']);
		$post->post_content = $row['I'];
		$post->post_author = get_current_user_id();

		// TODO: read publish time

		wp_update_post($post, $wp_error);
	}

}
