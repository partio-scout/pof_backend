<?php
/**
 * @package POF Importer
 */
/*
Plugin Name: POF Importer
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/* Helper functions */
include( plugin_dir_path( __FILE__ ) . 'pof-importer-helpers.php');

add_action( 'admin_menu', 'pof_importer_menu' );

function pof_importer_menu() {
	add_menu_page('POF Importer', 'Importteri', 'manage_options', 'pof_importer_frontpage-handle', 'pof_importer_frontpage');
	add_submenu_page( 'pof_importer_frontpage-handle', 'Suoritepaketit', 'Suoritepaketit', 'manage_options', 'pof_importer_taskgroups-handle', 'pof_importer_taskgroups');
	add_submenu_page( 'pof_importer_frontpage-handle', 'Suoritukset export', 'Suoritukset export', 'manage_options', 'pof_importer_tasksexport-handle', 'pof_importer_tasksexport');
	add_submenu_page( 'pof_importer_frontpage-handle', 'Suoritukset drive import', 'Suoritukset drive import', 'manage_options', 'pof_importer_tasksdriveimport-handle', 'pof_importer_tasksdriveimport');
}

function pof_importer_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Importer</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat importtaa.</p>';
	echo '</div>';
}

function pof_importer_taskgroups() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap">';
	echo '<h1>POF Importer, suorituspaketit</h1>';
	
	
	if(isset($_POST['Submit'])) {
		
		$row = 1;
		$added = 0;
		$updated = 0;
		if (($handle = fopen($_FILES['csv_content']['tmp_name'], "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",", '"')) !== FALSE) {
				if ($row == 1) {
					$row++;
					continue;
				}
				$num = count($data);
				$row++;
				
				if ($data[2] == "") {
					continue;
				}
				
				if ($data[1] == "" || intval($data[1]) == 0) {
					$post = array('post_title' => $data[2], 'post_status' => 'publish', 'post_type' => 'pof_post_taskgroup');
					$post_id = wp_insert_post($post);
					update_post_meta($post_id, 'title_sv', $data[2]);
					$parent_post = get_post(intval($data[0]));
					if ($parent_post->post_type == 'pof_post_agegroup') {
						update_post_meta($post_id, 'ikakausi', $data[0]);
					} else {
						update_post_meta($post_id, 'suoritepaketti', $data[0]);
					}
					$added++;
					
				} else {
					$post_id = $data[1];
					$post = get_post(intval($data[1]));
					$post->post_title = $data[2];
					wp_update_post($post);
					update_post_meta($post->ID, 'title_sv', $data[3]);
					$updated++;
				}
				for ($c=0; $c < $num; $c++) {
					echo $data[$c] . "<br />\n";
				}
				echo '"'.$data[0].'","'.$post_id.'","'.$data[2].'","'.$data[3].'"';
				echo "<br />";
			}
			
			echo "<br />";
			echo "<br />";
			echo "<br />";
			echo "<br />";
			echo "ADDED: " . $added;
			echo "<br />";
			echo "UPDATED: " . $updated;
			echo "<br />";
			fclose($handle);
		}
		
		
		echo "<h2></h2>";
	} else {
		echo '<form method="POST" enctype="multipart/form-data">';
		echo '<p>Sy&ouml;t&auml; alla olevaan kentt&auml;&auml;n csv seuraavassa formaatissa:<br />"&lt;parent_id&gt;","&lt;id&gt;","&lt;suorituspaketti_title&gt;","&lt;suorituspaketti_title_sv&gt;<br />Ensimm&auml;inen rivi on header-rivi, jota ei lueta sis&auml;&auml;n</p>';
		echo '<input name="csv_content" type="file" /><br />';
		echo '<input type="submit" name="Submit" value="Lue sis&auml;&auml;n" />';
		echo '</form>';
		
		echo "<br /><br /><br /><br />";
		echo '"parent","id","title_fi","title_sv"<br />';
		
		$args = array(
			'nopaging' => true,
			'post_type' => 'pof_post_taskgroup'
		);

		$the_query = new WP_Query( $args );
	
		if( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$ikakausi_tmp = get_post_meta($the_query->post->ID, 'ikakausi', true);
				if ($ikakausi_tmp) {
					echo '"'.$ikakausi_tmp;
				} else {
					echo '"'.get_post_meta($the_query->post->ID, 'suoritepaketti', true);
				}
				echo '","'.$the_query->post->ID.'","'.$the_query->post->post_title.'","'.get_field('title_sv');
				echo '"';
				echo "<br />";
			}
		}
		
	}
	
	

	echo '</div>';
}


function pof_importer_tasksexport() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap">';
	echo '<h1>POF Importer, suoritukset export</h1>';

	$separator = ";";
	$linebreak = "\n";

	echo '<textarea style="width: 100%; height: 100%;" cols="40" rows="50">';
	echo '"ikakausiID";"ikakausiTitle";"taskgroupID";"taskgroupTitle";"taskgroupID";"taskgroupTitle";"taskId";"TaksTitle"';
	echo $linebreak;

	$separator = ";";

		$args = array(
			'nopaging' => true,
			'post_type' => 'pof_post_agegroup'
		);

		$the_query = new WP_Query( $args );
	
		if( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$args2 = array(
					'nopaging' => true,
					'post_type' => 'pof_post_taskgroup',
					'meta_key' => 'ikakausi',
					'meta_value' => $the_query->post->ID
				);

				$the_query2 = new WP_Query( $args2 );
				if( $the_query2->have_posts() ) {
					while ( $the_query2->have_posts() ) {
						$the_query2->the_post();

						$args3 = array(
							'nopaging' => true,
							'post_type' => 'pof_post_taskgroup',
							'meta_key' => 'suoritepaketti',
							'meta_value' => $the_query2->post->ID
						);

						$the_query3 = new WP_Query( $args3 );
						if( $the_query3->have_posts() ) {
							while ( $the_query3->have_posts() ) {
								$the_query3->the_post();

								$args4 = array(
									'nopaging' => true,
									'post_type' => 'pof_post_task',
									'meta_key' => 'suoritepaketti',
									'meta_value' => $the_query3->post->ID
								);

								$the_query4 = new WP_Query( $args4 );
								if( $the_query4->have_posts() ) {
									while ( $the_query4->have_posts() ) {
										$the_query4->the_post();

										echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
										echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
										echo $the_query3->post->ID.'"'.$separator.'"'.$the_query3->post->post_title.'"'.$separator.'"';
										echo $the_query4->post->ID.'"'.$separator.'"'.$the_query4->post->post_title.'"';
										echo $linebreak;
									}
									echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
									echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
									echo $the_query3->post->ID.'"'.$separator.'"'.$the_query3->post->post_title.'"'.$separator.'"';
									echo '"'.$separator.'""';
									echo $linebreak;
								} else {
										echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
										echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
										echo $the_query3->post->ID.'"'.$separator.'"'.$the_query3->post->post_title.'"'.$separator.'"';
										echo '"'.$separator.'""';
										echo $linebreak;
								}
							}
						} else {
							$args4 = array(
								'nopaging' => true,
								'post_type' => 'pof_post_task',
								'meta_key' => 'suoritepaketti',
								'meta_value' => $the_query2->post->ID
							);

							$the_query4 = new WP_Query( $args4 );
							if( $the_query4->have_posts() ) {
								while ( $the_query4->have_posts() ) {
									$the_query4->the_post();

									echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
									echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
									echo '"'.$separator.'""'.$separator.'"';
									echo $the_query4->post->ID.'"'.$separator.'"'.$the_query4->post->post_title.'"';
									echo $linebreak;

								}
								echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
								echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
								echo '"'.$separator.'""'.$separator.'"';
								echo '"'.$separator.'""';
								echo $linebreak;
							} else {
								echo '"'.$the_query->post->ID.'"'.$separator.'"'.$the_query->post->post_title.'"'.$separator.'"';
								echo $the_query2->post->ID.'"'.$separator.'"'.$the_query2->post->post_title.'"'.$separator.'"';
								echo '"'.$separator.'""'.$separator.'"';
								echo '"'.$separator.'""';
								echo $linebreak;
							}

						}
					}
				}

			}
		}
	echo "</textarea>";


	echo '<div>';
}


function pof_importer_tasksdriveimport() {


	echo '<div class="wrap">';
	echo '<h1>POF Importer, suoritukset google drivest&auml;</h1>';

/*
echo "<pre>";
print_r(pof_importer_get_agegroups_and_taskgroups());
echo "</pre>";
*/


	if (   !isset($_POST)
		|| !isset($_POST["drive_file_id"])) {

		$service = pof_importer_get_google_service();

		echo '<form method="post" action="">';

		// Print the names and IDs for up to 10 files.
		$optParams = array(
			'maxResults' => 999,
			'q' => "mimeType = 'application/vnd.google-apps.spreadsheet'",
			'orderBy' => 'folder,modifiedDate desc,title'
		);
		$results = $service->files->listFiles($optParams);

		if (count($results->getItems()) == 0) {
			print "No files found.\n";
		} else {

			print "Valitse importoitava tiedosto:<br />";
			echo '<select name="drive_file_id">';
			foreach ($results->getItems() as $file) {
			
				$fileLastModified = strtotime($file->getModifiedDate());

				printf("<option value=\"%s\">%s (%s)</option>\n", $file->getId(), $file->getTitle(), date('d.m.Y', $fileLastModified));

				echo "<br />";
			}
			echo "</select>";

		echo '<input type="submit" name="Submit" value="Valitse tiedosto" />';
		echo '</form>';
		}
	} else {
		if (!isset($_POST["SaveToDatabase"])) {
			echo '<form method="post" action="">';
			echo '<input type="hidden" name="drive_file_id" value="'.$_POST["drive_file_id"].'" />';
			echo '<input type="submit" name="SaveToDatabase" value="Tallenna tietokantaan" />';
			echo '<br /><br />';
			echo '<input type="submit" name="RunAgain" value="Aja uudestaan" />';
			echo '</form>';
			pof_importer_tasksdriveimport_run($_POST["drive_file_id"]);
		} else {
			echo '<form method="post" action="">';
			echo '<input type="hidden" name="drive_file_id" value="'.$_POST["drive_file_id"].'" />';
			echo '<input type="submit" name="SaveToDatabase" value="Tallenna tietokantaan" />';
			echo '<br /><br />';
			echo '<input type="submit" name="RunAgain" value="Aja uudestaan" />';
			echo '</form>';
			pof_importer_tasksdriveimport_run($_POST["drive_file_id"], true);
		}
	}
	echo "</div>";
}