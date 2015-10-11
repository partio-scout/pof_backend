<?php
/**
 * @package POF Taxonomy icons
 */
/*
Plugin Name: POF Taxonomy icons
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

add_action( 'admin_menu', 'pof_taxonomy_icons_menu' );

register_activation_hook( __FILE__, 'pof_taxonomy_icons_install' );

global $pof_taxonomy_icons_db_version;
$pof_taxonomy_icons_db_version = '1.1';


function pof_taxonomy_icons_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'pof_taxonomy_icons';
}

function pof_taxonomy_icons_install() {
	global $wpdb;
	global $pof_taxonomy_icons_db_version;

	$table_name = pof_taxonomy_icons_get_table_name();
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		taxonomy_slug varchar(255) DEFAULT '' NOT NULL,
		agegroup_id bigint(20) NOT NULL,
		attachment_id bigint(20) NOT NULL,
		UNIQUE KEY id (id),
		KEY taxonomy_slug (taxonomy_slug),
		KEY agegroup_id (agegroup_id),
		KEY attachment_id (attachment_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'pof_taxonomy_icons_db_version', $pof_taxonomy_icons_db_version );
}

function pof_taxonomy_icons_get_agegroups() {

	$agegroups = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_agegroup',
		'orderby' => 'title',
		'order' => 'ASC'
	);

	$the_query = new WP_Query( $args );

	$tmp = new stdClass();
	$tmp->id = 0;
	$tmp->title = "Default";

	$agegroups[0] = $tmp;

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			$tmp = new stdClass();
			$tmp->id = $the_query->post->ID;
			$tmp->title = $the_query->post->post_title;
			$min_age = get_field("agegroup_min_age");
			
			$agegroups[$min_age] = $tmp;
		}
	}

	ksort($agegroups);

	return $agegroups;
}


function pof_taxonomy_icons_menu() {
	add_menu_page('POF Taxonomy icons', 'Ikonit', 'manage_options', 'pof_taxonomy_icons_frontpage-handle', 'pof_taxonomy_icons_frontpage');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suorituspaikat', 'Suorituspaikat', 'manage_options', 'pof_taxonomy_icons_places-handle', 'pof_taxonomy_icons_places');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Ryhm&auml;koko', 'Ryhm&auml;koot', 'manage_options', 'pof_taxonomy_icons_groupsizes-handle', 'pof_taxonomy_icons_groupsizes');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Pakollisuus', 'Pakollisuus', 'manage_options', 'pof_taxonomy_icons_mandatory-handle', 'pof_taxonomy_icons_mandatory');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suorituksen kestot', 'Suorituksen kestot', 'manage_options', 'pof_taxonomy_icons_taskduration-handle', 'pof_taxonomy_icons_taskduration');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suorituksen valmistelun kestot', 'Suorituksen valmistelun kestot', 'manage_options', 'pof_taxonomy_icons_taskpreparationduration-handle', 'pof_taxonomy_icons_taskpreparationduration');

	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Tarvikkeet', 'Tarvikkeet', 'manage_options', 'pof_taxonomy_icons_equpments-handle', 'pof_taxonomy_icons_equpments');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Taitoalueet', 'Taitoalueet', 'manage_options', 'pof_taxonomy_icons_skillareas-handle', 'pof_taxonomy_icons_skillareas');

	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suoritepaketin yl&auml;k&auml;site', 'Suoritepaketin yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_icons_taskgroupterm-handle', 'pof_taxonomy_icons_taskgroupterm');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suoritteen yl&auml;k&auml;site', 'Suoritteen yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_icons_taskterm-handle', 'pof_taxonomy_icons_taskterm');

}

function pof_taxonomy_icons_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>POF Taxonomy icons</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat muokata.</p>';
	echo '</div>';
}

function pof_taxonomy_icons_get_icon($taxonomy, $icon_key, $agegroup_id, $fallback = false) {
	global $wpdb;
	$icon_res = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . pof_taxonomy_icons_get_table_name() . "
		WHERE taxonomy_slug = '" . $taxonomy . '::' . $icon_key . "' 
			AND agegroup_id = ".$agegroup_id."
		"
	);

	if (   $fallback 
		&& $agegroup_id != 0
		&& empty($icon_res)) {
		$icon_res = $wpdb->get_results( 
			"
			SELECT * 
			FROM " . pof_taxonomy_icons_get_table_name() . "
			WHERE taxonomy_slug = '" . $taxonomy . '::' . $icon_key . "' 
				AND agegroup_id = 0
			"
		);
	}

	return $icon_res;
}

function pof_taxonomy_icons_parser_taxonomy_key($tmpkey) {
	$tmpkey = str_replace("delete_", "", $tmpkey);
	$tmpkey = str_replace("taxonomy_icon_", "", $tmpkey);
	$tmp = explode("_", $tmpkey);

	$agegroup_id = $tmp[count($tmp)-1];
	
	$key = str_replace("_".$agegroup_id, "", $tmpkey);

	$ret = array();
	$ret["key"] = $key;
	$ret["agegroup_id"] = $agegroup_id;

	return $ret;
}


function pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2) {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	global $wpdb;
	$table_name = pof_taxonomy_icons_get_table_name();
	$agegroups = pof_taxonomy_icons_get_agegroups();

	if(isset($_POST['Submit'])) {
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		foreach ($_FILES as $key => $file) {
			$tmp = pof_taxonomy_icons_parser_taxonomy_key($key);

			$taxonomy_key = $tmp["key"];
			$taxonomy_full_key = $taxonomy_base_key . "::" . $tmp["key"];
			$agegroup_id = $tmp["agegroup_id"];


			if (   array_key_exists("delete_".$key, $_POST)
				&& $_POST["delete_".$key] == "delete") {
				$icon = pof_taxonomy_icons_get_icon($taxonomy_base_key, $taxonomy_key, $agegroup_id);

				if (!empty($icon)) {
					wp_delete_attachment( $icon[0]->attachment_id, false );
				}

				$wpdb->delete( 
					$table_name, 
					array( 
						'taxonomy_slug' => $taxonomy_full_key, 
						'agegroup_id' => (int) $tmp["agegroup_id"] 
					), 
					array( '%s', '%d' )	
				);

				echo "<br />Deleted " . $key . "";

			}
			else if (!empty($file['name'])) {
			
				$attachment_id = media_handle_upload( $key, 0);

				if ( is_wp_error( $attachment_id ) ) {
					// There was an error uploading the image.
					echo '<h1>ERROR '.$key.'</h1>';
	/*				echo '<pre>';
					print_r($_POST);
					print_r($_FILES);
					echo '</pre>';*/

				} else {
					$icon = pof_taxonomy_icons_get_icon($taxonomy_base_key, $taxonomy_key, $agegroup_id);
					if (empty($icon)) {
						$tmp = $wpdb->insert( 
							$table_name, 
							array( 
								'taxonomy_slug' => $taxonomy_full_key, 
								'agegroup_id' => (int) $agegroup_id,
								'attachment_id' => $attachment_id
							), 
							array( 
								'%s', 
								'%d', 
								'%d'
							) 
						);
						echo "<br />Added " . $key . "";
					} else {
						if (empty($icon)) {
							wp_delete_attachment( $icon[0]->attachment_id, false );
						}

						$tmp = $wpdb->update( 
							$table_name, 
							array( 

								'attachment_id' => $attachment_id
							), 
							array(
								'taxonomy_slug' => $taxonomy_full_key, 
								'agegroup_id' => (int) $agegroup_id
							),
							array( 
								'%d'
							),
							array( 
								'%s', 
								'%d'
							) 
						);
						echo "<br />Updated" . $key . "";
					}
				}				
			}
	
		}


	}

	echo '<div class="wrap">';
	echo '<h1>'.$title.'</h1>';
	echo '<form id="featured_upload" method="post" action="" enctype="multipart/form-data">';

	echo '<table cellpadding="2" cellspacing="2" border="2">';
	echo '<thead>';
	echo '<tr>';
	echo '<th><h2>'.$title2.'</h2></th>';
	foreach ($agegroups as $agegroup) {
		echo '<th><h2>'.$agegroup->title.'</h2></th>';
	}
	echo '<tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($items as $tmp_key => $tmp_title) {
		echo '<tr>';
		echo '<th>'.$tmp_title.'<br /> ('.$tmp_key.')</th>';
		foreach ($agegroups as $agegroup) {

			echo '<td>';
			$icon = pof_taxonomy_icons_get_icon($taxonomy_base_key,$tmp_key, $agegroup->id);

			if (empty($icon)) {
				echo "ei kuvaa<br />";
			} else {
				echo wp_get_attachment_image($icon[0]->attachment_id);
			}


			echo '<input type="file" name="taxonomy_icon_'.$tmp_key.'_'.$agegroup->id.'" id="taxonomy_icon_'.$tmp_key.'_'.$agegroup->id.'"  multiple="false" />';
			echo '<br /><input type="checkbox" name="delete_taxonomy_icon_'.$tmp_key.'_'.$agegroup->id.'" value="delete" /> Delete';
			echo '</td>';
		}
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '<br /><input type="submit" name="Submit" value="Submit" />';
	echo '</form>';
	echo '</div>';	
}

function pof_taxonomy_icons_places() {
	$taxonomy_base_key = "place_of_performance";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituspaikat";
	$title2 = "Suorituspaikka";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_icons_groupsizes() {
	$taxonomy_base_key = "groupsize";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Ryhm&auml;koot";
	$title2 = "Ryhm&auml;koko";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_icons_mandatory() {
	$taxonomy_base_key = "mandatory";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Pakollisuus";
	$title2 = "Pakollisuus";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_icons_taskduration() {
	$taxonomy_base_key = "taskduration";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen kestot";
	$title2 = "Kesto";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_icons_taskpreparationduration() {
	$taxonomy_base_key = "taskpreaparationduration";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen valmistelun kestot";
	$title2 = "Kesto";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_icons_get_equpments() {
	$ret = array();

	foreach (get_terms('pof_tax_equipment') as $term) {
		$ret[$term->slug] = $term->name;
	}
	
	return $ret;

}


function pof_taxonomy_icons_equpments() {
	$taxonomy_base_key = "equpment";
	$items = pof_taxonomy_icons_get_equpments();
	$title = "Tarvikkeet";
	$title2 = "Tarvike";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_icons_get_skillareas() {
	$ret = array();

	foreach (get_terms('pof_tax_skillarea') as $term) {
		$ret[$term->slug] = $term->name;
	}
	
	return $ret;

}


function pof_taxonomy_icons_skillareas() {
	$taxonomy_base_key = "skillarea";
	$items = pof_taxonomy_icons_get_skillareas();
	$title = "Taitoalueet";
	$title2 = "Taitoalue";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_icons_taskgroupterm() {
	$taxonomy_base_key = "taskgroup_term";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_icons_taskterm() {
	$taxonomy_base_key = "task_term";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";

	pof_taxonomy_icons_form($taxonomy_base_key, $items, $title, $title2);
}