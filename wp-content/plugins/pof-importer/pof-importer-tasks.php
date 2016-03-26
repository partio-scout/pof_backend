<?php

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
			if (empty($row['A'])) {
				return;
			}
			echo "Not importing, because row not setted to be imported, empty title. Row " . $row_index;
		}
		
		echo '<br />';

		return;
	}
	echo "<br />";
	echo "to be imported: " . $row['D'];

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
	$post = null;

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_task' ),
		'meta_key' => 'suoritepaketti',
		'meta_value' => $taskgroup_obj->id,
		'search_post_title' => trim($row['D'])
	);

	add_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	$the_query_task = new WP_Query( $args );
	remove_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );

	if( $the_query_task->have_posts() ) {
		while ( $the_query_task->have_posts() ) {

			$item = new stdClass();
			$the_query_task->the_post();
			$post = $the_query_task->post;
		}
	}


	if (empty($post)) {
		echo "  POST NOT FOUND; TO BE CREATED";
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

			$post = get_post($post_id);
		}

	} else {
		echo "  POST FOUND; TO BE UPDATED";
		$post_id = $post->ID;
	}



	if ($saveToDataBase) {
		update_post_meta($post_id, "suoritepaketti", $taskgroup_obj->id);


		update_field("ingress", $row['E'], $post_id);
		update_field("leader_tasks_fi", $row['G'], $post_id);
		
		$post->post_title = trim($row['D']);
		$post->post_content = $row['F'];
		$post->post_author = get_current_user_id();

		update_post_meta($post_id, "task_duration", trim(str_replace(" min", "", $row['M'])));

		if (substr(strtolower($row['K']), 0, 2) == 'ky') {
			update_field("task_mandatory", 1, $post_id);
		} else {
			update_field("task_mandatory", 0, $post_id);
		}

		$places = pof_importer_get_places($row['L']);

		update_field("task_place_of_performance", $places, $post_id);

		update_field("task_level", pof_importer_get_level($row['N']), $post_id);

		echo " updated places, ".count($places)." items,";

		$skillareas = pof_importer_get_skillareas(trim($row['I']));

		wp_set_post_terms( $post_id, $skillareas, "pof_tax_skillarea", false );

        $growth_targets = pof_importer_get_growth_targets(trim($row['H']));

		wp_set_post_terms( $post_id, $growth_targets, "pof_tax_growth_target", false );

//        update_field("growth_target_fi", $row['H'], $post_id);

		wp_update_post($post, $wp_error);
	}
	echo "<br />";
}



function pof_importer_tasksdrivelocalizationtitles_run($fileId, $saveToDataBase = false) {
	$service = pof_importer_get_google_service();

	try {
		$file = $service->files->get($fileId);

        $lang = substr($file->getTitle(), 0, 2);

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
		echo '<th>Kieli</th>';
		echo '<td>'.$lang.'</td>';
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

		if (pof_importer_tasksdrivelocalizationtitles_checkThatHeadersMatchTasks($sheetData[1], $lang)) {

			$headers = $sheetData[1];
			$i = 0;
			foreach($sheetData as $sheetDataRow) {
				$i++;
				if ($i < 2) {
					continue;
				}

				pof_importer_tasksdrivelocalizationtitles_importRow($sheetDataRow, $i, $saveToDataBase, $lang);

			}

		} else {
			echo "<h1>Unvalid excel, or failed to read excel at all.</h1>";
		}

	}
    catch (Exception $e) {
		echo "An error occurred: " . $e->getMessage();
	}

}

function pof_importer_tasksdrivelocalizationtitles_checkThatHeadersMatchTasks($headerRow, $lang) {

	if (   !is_array($headerRow)
		|| count($headerRow) < 3) {
		echo "<h1>Not enough fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	if (   $headerRow['B'] != "Otsikko fi"
		|| $headerRow['C'] != "Otsikko " . $lang) {
		echo "<h1>Wrong fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	return true;
}


function pof_importer_tasksdrivelocalizationtitles_importRow($row, $row_index, $saveToDataBase = false, $lang = 'sv') {

	if (   empty($row['A'])
		|| substr($row['A'], 0, 2) != 'KY'
		|| empty($row['B'])
        || empty($row['C'])) {

		if (substr($row['A'], 0, 2) != 'KY') {
			echo "Not importing, because row not setted to be imported: " . $row['A'];
		} else {
            echo "Not importing, because empty row. Row " . $row_index;
        }

		echo '<br />';

		return;
	}
	echo "<br />";
	echo "to be imported: " . $row['B'];


	$wp_error = false;
	$post = null;

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_task' ),
		'search_post_title' => trim($row['B'])
	);

	add_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );
	$the_query_task = new WP_Query( $args );
	remove_filter( 'posts_where', 'pof_importer_title_filter', 10, 2 );

    $posts_found = 0;

	if( $the_query_task->have_posts() ) {
		while ( $the_query_task->have_posts() ) {

			$item = new stdClass();
			$the_query_task->the_post();
			$post = $the_query_task->post;

            $posts_found = $posts_found + 1;
            echo "<br />";
            echo "POST FOUND ";
            echo "<a href=\"/wp-admin/post.php?post=" . $post->ID . "&action=edit\" target=\"_blank\">" . $post->post_title . "</a>";

            if ($saveToDataBase) {
                echo " Updating, title_".$lang . " = \"".trim($row['C'])."\"";
                update_post_meta($post->ID, "title_".$lang, trim($row['C']));
            }

		}
	}
    if ($posts_found == 0) {
        echo "<h2> POST NOT FOUND: ".$row['B']." ==> RETURNING</h2>";
        echo "<br />";
		return;
    }


	echo "<br />";
    echo "<br />";
}
