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
		'post_type' => 'pof_post_agegroup',
		'orderby' => 'title',
		'order' => 'ASC'
	);

	$the_query = new WP_Query( $args );

	$tmp = new stdClass();
	$tmp->id = 0;
	$tmp->title = "Default";

	array_push($agegroups, $tmp);

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			$tmp = new stdClass();
			$tmp->id = $the_query->post->ID;
			$tmp->title = $the_query->post->post_title;

			array_push($agegroups, $tmp);
		}
	}

	return $agegroups;
}


function pof_taxonomy_icons_menu() {
	add_menu_page('POF Taxonomy icons', 'Ikonit', 'manage_options', 'pof_taxonomy_icons_frontpage-handle', 'pof_taxonomy_icons_frontpage');
	add_submenu_page( 'pof_taxonomy_icons_frontpage-handle', 'Suorituspaikat', 'Suorituspaikat', 'manage_options', 'pof_taxonomy_icons_places-handle', 'pof_taxonomy_icons_places');
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

function pof_taxonomy_icons_get_icon($icon_key, $agegroup_id) {
	global $wpdb;
	$icon_res = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . pof_taxonomy_icons_get_table_name() . "
		WHERE taxonomy_slug = '" . $icon_key . "' 
			AND agegroup_id = ".$agegroup_id."
		"
	);

	return $icon_res;
}

function pof_taxonomy_icons_get_places() {
	$ret = array();

	$ret['meeting_place'] = 'Kolo';
	$ret['hike'] = 'Retki';
	$ret['camp'] = 'Leiri';
	$ret['boat'] = 'Vene';
	$ret['other'] = 'Muu';
	
	return $ret;

}

function pof_taxonomy_icons_places() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	global $wpdb;
	$table_name = pof_taxonomy_icons_get_table_name();

	if(isset($_POST['Submit'])) {
		echo 'uploading';
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
		// Let WordPress handle the upload.
		// Remember, 'my_image_upload' is the name of our file input in our form above.
		$attachment_id = media_handle_upload( 'taxonomy_icon', 0);
	
		if ( is_wp_error( $attachment_id ) ) {
			// There was an error uploading the image.
			echo '<h1>ERROR</h1>';
			echo '<pre>';
			print_r($attachment_id);
			print_r($_POST);
			print_r($_FILES);
			echo '</pre>';

		} else {
			$icon = pof_taxonomy_icons_get_icon($_POST['taxonomy_key'], $_POST['agegroup_id']);
			if (empty($icon)) {
				$tmp = $wpdb->insert( 
					$table_name, 
					array( 
						'taxonomy_slug' => $_POST['taxonomy_key'], 
						'agegroup_id' => (int) $_POST['agegroup_id'],
						'attachment_id' => $attachment_id
					), 
					array( 
						'%s', 
						'%d', 
						'%d'
					) 
				);

				echo $tmp;

			}
		}		


	} else if (isset($_POST['Delete'])) {
		echo 'deleting';
	}

	$agegroups = pof_taxonomy_icons_get_agegroups();
	$places = pof_taxonomy_icons_get_places();

	echo '<div class="wrap">';
	echo '<h1>Suorituspaikat</h1>';

	echo '<table cellpadding="2" cellspacing="2" border="2">';
	echo '<thead>';
	echo '<tr>';
	echo '<th><h2>Suorituspaikka</h2></th>';
	foreach ($agegroups as $agegroup) {
		echo '<th><h2>'.$agegroup->title.'</h2></th>';
	}
	echo '<tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($places as $tmp_key => $tmp_title) {
		echo '<tr>';
		echo '<th>'.$tmp_title.'</th>';
		foreach ($agegroups as $agegroup) {

			echo '<td>';
			$icon = pof_taxonomy_icons_get_icon($tmp_key, $agegroup->id);

			if (empty($icon)) {
				echo "ei kuvaa<br />";
			} else {
				echo wp_get_attachment_image($icon[0]->attachment_id);
			}

			echo '<form id="featured_upload" method="post" action="" enctype="multipart/form-data">';
			echo '<input type="file" name="taxonomy_icon" id="taxonomy_icon"  multiple="false" />';
			echo '<input type="hidden" name="agegroup_id" id="agegroup_id" value="'.$agegroup->id.'" />';
			echo '<input type="hidden" name="taxonomy_key" id="taxonomy_key" value="'.$tmp_key.'" />';
			echo '<br /><input type="submit" name="Submit" value="Upload new" />';
			echo '<br /><input type="submit" name="Delete" value="Delete" />';
			echo '</form>';
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';

	echo '</div>';	
}
