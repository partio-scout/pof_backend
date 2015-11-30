<?php

function pof_importer_suggestionsdriveimport_run2($fileId, $agegroup_id, $saveToDataBase = false) {
	$service = pof_importer_get_google_service();

    $agegroups = pof_taxonomy_translate_get_agegroups();

    $agegroup = new stdClass();
			
    foreach ($agegroups as $agegroup_tmp) {
        if ($agegroup_tmp->id == $agegroup_id) {
            $agegroup = $agegroup_tmp;
            break;
        }
    }

	try {
		$file = $service->files->get($fileId);

        $lang = substr($file->getTitle(), -2, 2);

		echo '<table cellpadding="2" cellspacing="2" border="2">';
		echo '<tr>';
		echo '<th>Otsikko</th>';
		echo '<td>'.$file->getTitle().'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Kieli</th>';
		echo '<td>'.$lang.'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Ik&auml;kausi</th>';
		echo '<td>'.$agegroup->title.'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Kirjoittaja</th>';
		echo '<td>Suomen partiolaiset</td>';
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


		if (pof_importer_tasksdriveimport_checkThatHeadersMatchSuggestions2($sheetData[1])) {

			$headers = $sheetData[1];
			$i = 0;
			foreach($sheetData as $sheetDataRow) {
				$i++;
				if ($i < 2) {
					continue;
				}

                if (trim($sheetDataRow['A'])=='') {
                    continue;
                }

				pof_importer_suggestionssdriveimport_importRow2($sheetDataRow, $i, $agegroup_id, $lang, $saveToDataBase);
			}

		} else {
			echo "<h1>Unvalid excel, or failed to read excel at all.</h1>";
		}

	} catch (Exception $e) {
		echo "An error occurred: " . $e->getMessage();
	}

}

function pof_importer_tasksdriveimport_checkThatHeadersMatchSuggestions2($headerRow) {

	if (   !is_array($headerRow)
		|| count($headerRow) < 7) {
		echo "<h1>Not enough fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	if (   strpos($headerRow['A'], "Aktiviteetin nimi") == -1
		|| strpos($headerRow['B'], "Toteutusvinkki 1") == -1) {
		echo "<h1>Wrong fields</h1>";
		echo "<pre>";
		print_r($headerRow);
		echo "</pre>";
		return false;
	}

	return true;
}

function pof_importer_suggestionssdriveimport_importRow2($row, $row_index, $agegroup_id, $lang, $saveToDataBase = false) {

	$wp_error = false;
	$post = null;

    $agegroups_and_tasks = pof_importer_get_agegroups_and_tasks($agegroup_id);
    
    $tasks = $agegroups_and_tasks[$agegroup_id];

    $task_title = pof_importer_normalize_title($row['A']);

    if (array_key_exists($task_title, $tasks)) {
        $task = $tasks[$task_title];
        echo "<br />Task found '" . $row['A'] . "'</br>";

        $title_counter = 0;

        foreach ($row as $row_key => $row_value) {
            
            if ($row_key == 'A') {
                continue;
            }
            if ($row_key == 'H') {
                break;
            }

            $title_counter++;
            $title = "Toteutusvinkki " . strval($title_counter);

            if (strlen(trim($row_value)) < 5) {
                continue;
            }



            $args = array(
		        'numberposts' => -1,
		        'posts_per_page' => -1,
		        'post_type' => array('pof_post_suggestion' ),
		        'meta_key' => 'pof_suggestion_task',
		        'meta_value' => $task->id,
		        'search_post_title' => $title
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
		        echo "POST NOT FOUND; TO BE CREATED, ".$title."<br />";
		        if ($saveToDataBase) {
			        $post = array(
				        'post_title'    => $title,
				        'post_content'  => $row_value,
				        'post_type' => 'pof_post_suggestion',
				        'post_status'   => 'publish',
				        'post_author'   => get_current_user_id()
			        );
			        $post_id = wp_insert_post( $post, $wp_error );

			        echo "imported, post_id: " . $post_id;
			        echo "<br />";

			        $post = get_post($post_id);

                    update_post_meta($post_id, "pof_suggestion_task", $task->id);
                    update_post_meta($post_id, "pof_suggestion_lang", $lang);
	        	    update_post_meta($post_id, "pof_suggestion_writer", "Suomen partiolaiset");
		        }

	        } else {
		        echo "SUGGESTION ".$title." FOUND; SKIPPING<br />";
	        }

            

        }

    } else {
		echo "<h2>Couldn't find task '" . $row['A'] . "'</h2>";
    }
}