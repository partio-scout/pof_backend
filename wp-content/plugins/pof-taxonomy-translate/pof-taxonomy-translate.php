<?php
/**
 * @package POF Taxonomy translate
 */
/*
Plugin Name: POF Taxonomy translate
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

add_action( 'admin_menu', 'pof_taxonomy_translate_menu' );

register_activation_hook( __FILE__, 'pof_taxonomy_translate_install' );

global $pof_taxonomy_translate_db_version;
$pof_taxonomy_translate_db_version = '1.0';


function pof_taxonomy_translate_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'pof_taxonomy_translate';
}

function pof_taxonomy_translate_install() {
	global $wpdb;
	global $pof_taxonomy_translate_db_version;

	$table_name = pof_taxonomy_translate_get_table_name();
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		taxonomy_slug varchar(255) DEFAULT '' NOT NULL,
		agegroup_id bigint(20) NOT NULL,
		content varchar(255) NOT NULL,
		lang varchar(10) NOT NULL,
		UNIQUE KEY id (id),
		KEY taxonomy_slug (taxonomy_slug),
		KEY agegroup_id (agegroup_id),
		KEY content (content),
		KEY lang (lang)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'pof_taxonomy_translate_db_version', $pof_taxonomy_translate_db_version );
}

function pof_taxonomy_translate_get_languages() {
	$ret = array();
	$ret['fi'] = "Suomi";
	$ret['sv'] = "Ruotsi";
	$ret['en'] = "Englanti";

	return $ret;
}

function pof_taxonomy_translate_get_agegroups() {

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


function pof_taxonomy_translate_menu() {
	add_menu_page('POF Taxonomy Translate', 'K&auml;&auml;nn&ouml;kset', 'manage_options', 'pof_taxonomy_translate_frontpage-handle', 'pof_taxonomy_translate_frontpage');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suorituspaikat', 'Suorituspaikat', 'manage_options', 'pof_taxonomy_translate_places-handle', 'pof_taxonomy_translate_places');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Ryhm&auml;koko', 'Ryhm&auml;koot', 'manage_options', 'pof_taxonomy_translate_groupsizes-handle', 'pof_taxonomy_translate_groupsizes');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Pakollisuus', 'Pakollisuus', 'manage_options', 'pof_taxonomy_translate_mandatory-handle', 'pof_taxonomy_translate_mandatory');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suorituksen kestot', 'Suorituksen kestot', 'manage_options', 'pof_taxonomy_translate_taskduration-handle', 'pof_taxonomy_translate_taskduration');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suorituksen valmistelun kestot', 'Suorituksen valmistelun kestot', 'manage_options', 'pof_taxonomy_translate_taskpreparationduration-handle', 'pof_taxonomy_translate_taskpreparationduration');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Tarvikkeet', 'Tarvikkeet', 'manage_options', 'pof_taxonomy_translate_equpments-handle', 'pof_taxonomy_translate_equpments');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Taitoalueet', 'Taitoalueet', 'manage_options', 'pof_taxonomy_translate_skillareas-handle', 'pof_taxonomy_translate_skillareas');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suoritepaketin yl&auml;k&auml;site', 'Suoritepaketin yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_translate_taskgroupterm-handle', 'pof_taxonomy_translate_taskgroupterm');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suoritteen yl&auml;k&auml;site', 'Suoritteen yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_translate_taskterm-handle', 'pof_taxonomy_translate_taskterm');
}

function pof_taxonomy_translate_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>POF Taxonomy translate</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat muokata.</p>';
	echo '</div>';
}


function pof_taxonomy_translate_parser_taxonomy_key($tmpkey) {
	$tmpkey = str_replace("delete_", "", $tmpkey);
	$tmpkey = str_replace("taxonomy_translate_", "", $tmpkey);
	$tmp = explode("_", $tmpkey);

	$agegroup_id = $tmp[count($tmp)-1];
	
	$key = str_replace("_".$agegroup_id, "", $tmpkey);

	$ret = array();
	$ret["key"] = $key;
	$ret["agegroup_id"] = $agegroup_id;

	return $ret;
}

function pof_taxonomy_translate_get_translation($taxonomy_base_key, $tmp_key, $agegroup_id, $selected_lang, $fallback = false) {
	$taxonomy_slug = $taxonomy_base_key . '::' . $tmp_key;

	global $wpdb;
	$translate_res = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . pof_taxonomy_translate_get_table_name() . "
		WHERE taxonomy_slug = '" . $taxonomy_slug . "' 
			AND lang = '".$selected_lang."'
			AND agegroup_id = ".$agegroup_id."
		"
	);

	if (   $fallback 
		&& $agegroup_id != 0
		&& empty($translate_res)) {
		$translate_res = $wpdb->get_results( 
			"
			SELECT * 
			FROM " . pof_taxonomy_translate_get_table_name() . "
			WHERE taxonomy_slug = '" . $taxonomy_slug . "' 
				AND lang = '".$selected_lang."'
				AND agegroup_id = 0
			"
		);
	}

	if (   $fallback 
		&& $agegroup_id != 0
		&& $selected_lang != 'fi'
		&& empty($translate_res)) {
		$translate_res = $wpdb->get_results( 
			"
			SELECT * 
			FROM " . pof_taxonomy_translate_get_table_name() . "
			WHERE taxonomy_slug = '" . $taxonomy_slug . "' 
				AND lang = 'fi'
				AND agegroup_id = ".$agegroup_id."
			"
		);
	}

	if (   $fallback 
		&& $selected_lang != 'fi'
		&& empty($translate_res)) {
		$translate_res = $wpdb->get_results( 
			"
			SELECT * 
			FROM " . pof_taxonomy_translate_get_table_name() . "
			WHERE taxonomy_slug = '" . $taxonomy_slug . "' 
				AND lang = 'fi'
				AND agegroup_id = 0
			"
		);
	}

	return $translate_res;
}


function pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2, $additional_text = "") {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	global $wpdb;
	$table_name = pof_taxonomy_translate_get_table_name();

	$languages = pof_taxonomy_translate_get_languages();
	$agegroups = pof_taxonomy_translate_get_agegroups();
	
	$selected_lang = 'fi';
	if (isset($_POST['language'])) {
		$selected_lang = $_POST['language'];
	}

	if (isset($_POST['Change_lang'])) {
		$selected_lang = $_POST['language'];
	}

	if(isset($_POST['Submit'])) {

		foreach ($_POST as $key => $item) {
			if (substr( $key, 0, 19 ) == "taxonomy_translate_") {
				$tmp = pof_taxonomy_translate_parser_taxonomy_key($key);

				$taxonomy_key = $tmp["key"];
				$taxonomy_full_key = $taxonomy_base_key . "::" . $tmp["key"];
				$agegroup_id = $tmp["agegroup_id"];

				$translation = pof_taxonomy_translate_get_translation($taxonomy_base_key, $taxonomy_key, $agegroup_id, $selected_lang, false);

				if (empty($translation)
					&& $item != "") {
					$tmp = $wpdb->insert( 
						$table_name, 
						array( 
							'taxonomy_slug' => $taxonomy_full_key, 
							'agegroup_id' => (int) $agegroup_id,
							'lang' => $selected_lang,
							'content' => $item
						), 
						array( 
							'%s', 
							'%d', 
							'%s',
							'%s',
						) 
					);
					echo "<br />Added " . $key . "";
				} else if (!empty($translation) && $item != "") {
					if ($translation[0]->content != $item) {
						$tmp = $wpdb->update( 
							$table_name, 
							array( 
								'content' => $item
							), 
							array(
								'id' => $translation[0]->id
							),
							array( 
								'%s'
							),
							array(  
								'%d'
							) 
						);
						echo "<br />Updated" . $key . "";
					}
				} else if (!empty($translation) && $item == "") {
					$wpdb->delete( 
						$table_name, 
						array( 
							'id' => $translation[0]->id
						), 
						array( '%d' )	
					);
					echo "<br />Deleted " . $key . "";
				}
				
			}
		}
		if (   isset($_POST['add_taxonomy_translate_key'])
			&& !empty($_POST['add_taxonomy_translate_key'])
			&& strlen($_POST['add_taxonomy_translate_key']) > 0) {

			$taxonomy_key = trim($_POST['add_taxonomy_translate_key']);
			$taxonomy_key = str_replace(" ", "_", $taxonomy_key);
			
			$default = pof_taxonomy_translate_get_translation($taxonomy_base_key, $taxonomy_key, 0, 0, false);

			if (count($default) == 0) {

				$taxonomy_full_key = $taxonomy_base_key . "::" . $taxonomy_key;

				$tmp = $wpdb->insert( 
					$table_name, 
					array( 
						'taxonomy_slug' => $taxonomy_full_key, 
						'agegroup_id' => 0,
						'lang' => 'fi',
						'content' => $_POST['add_taxonomy_translate_key_0']
					), 
					array( 
						'%s', 
						'%d', 
						'%s',
						'%s',
					) 
				);

				foreach ($agegroups as $agegroup) {
					if ($agegroup->id == 0) {
						continue;
					}
					$tmp = $wpdb->insert( 
						$table_name, 
						array( 
							'taxonomy_slug' => $taxonomy_full_key, 
							'agegroup_id' => $agegroup->id,
							'lang' => 'fi',
							'content' => $_POST['add_taxonomy_translate_key_'.$agegroup->id]
						), 
						array( 
							'%s', 
							'%d', 
							'%s',
							'%s',
						) 
					);
				}
			}
		}
		// reload items:
		$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	}

	echo '<div class="wrap">';
	echo '<h1>'.$title.'</h1>';
	echo '<form id="featured_upload" method="post" action="">';
	echo 'Valitse kieli:';
	echo '<select name="language">';
	foreach ($languages as $lang_key => $lang) {
		if ($lang_key == $selected_lang) {
			echo '<option selected="selected" value="'.$lang_key.'">'.$lang.'</option>';
		} else {
			echo '<option value="'.$lang_key.'">'.$lang.'</option>';
		}
	}
	echo '</select>';
	echo '<br />';
	echo '<input type="submit" name="Change_lang" id="Change_lang" value="Vaihda kieli" />';
	echo '</form>';
	echo '<br /><br /><br />';
	echo '<form id="featured_upload" method="post" action="">';
	echo '<input type="hidden" name="language" value="'.$selected_lang.'" />';
	echo '<h2>Kieli: '.$languages[$selected_lang].' ('.$selected_lang.')</h2>';
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
			$translation = pof_taxonomy_translate_get_translation($taxonomy_base_key,$tmp_key, $agegroup->id, $selected_lang, false);

			$translation_content = "";
			if (!empty($translation)) {
				$translation_content = $translation[0]->content;
			}

			echo '<input type="text" name="taxonomy_translate_'.$tmp_key.'_'.$agegroup->id.'" id="taxonomy_translate_'.$tmp_key.'_'.$agegroup->id.'" value="'.$translation_content.'" />';
			echo '</td>';
		}
		echo '</tr>';
	}

	echo '<tr>';
	echo '<th align="left" colspan="'.(count($agegroups) + 1).'"><h3>Lis&auml;&auml; uusi</h3></th>';
	echo '</tr>';

	echo '<tr>';
	echo '<th valign="bottom">Koodi:<br /><input type="text" name="add_taxonomy_translate_key" id="add_taxonomy_translate_key" /></th>';
	foreach ($agegroups as $agegroup) {
		echo '<td valign="bottom">';
		echo '<input type="text" name="add_taxonomy_translate_key_'.$agegroup->id.'" id="add_taxonomy_translate_key_'.$agegroup->id.'" />';
		echo '</td>';
	}
	echo '</tr>';

	if ($additional_text != "") {
		echo '<tr>';
		echo '<th align="left" colspan="'.(count($agegroups) + 1).'"><strong>'.$additional_text.'</strong></th>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '<br /><input type="submit" name="Submit" value="Submit" />';
	echo '</form>';
	echo '</div>';	
}


function pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower = false) {
	$ret = array();

	global $wpdb;

	$table_name = pof_taxonomy_translate_get_table_name();


	$translate_res = $wpdb->get_results( 
		"
		SELECT taxonomy_slug, content
		FROM " . pof_taxonomy_translate_get_table_name() . "
		WHERE lang = 'fi'
			AND agegroup_id = 0
			AND taxonomy_slug LIKE '".$taxonomy_base_key."::%'
		"
	);

	foreach ($translate_res as $item) {
		if ($tolower) {
			$ret[str_replace($taxonomy_base_key.'::', "", $item->taxonomy_slug)] = strtolower($item->content);
		} else {
			$ret[str_replace($taxonomy_base_key.'::', "", $item->taxonomy_slug)] = $item->content;
		}
	}

	return $ret;
}


function pof_taxonomy_translate_places() {
	$taxonomy_base_key = "place_of_performance";
	
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituspaikat";
	$title2 = "Suorituspaikka";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_groupsizes() {
	$taxonomy_base_key = "groupsize";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Ryhm&auml;koot";
	$title2 = "Ryhm&auml;koko";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_translate_mandatory() {
	$taxonomy_base_key = "mandatory";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Pakollisuus";
	$title2 = "Pakollisuus";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_translate_taskduration() {
	$taxonomy_base_key = "taskduration";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen kestot";
	$title2 = "Kesto";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}



function pof_taxonomy_translate_taskpreparationduration() {
	$taxonomy_base_key = "taskpreaparationduration";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen valmistelun kestot";
	$title2 = "Kesto";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_get_equpments() {
	$ret = array();

	foreach (get_terms('pof_tax_equipment') as $term) {
		$ret[$term->slug] = $term->name;
	}
	
	return $ret;

}


function pof_taxonomy_translate_equpments() {
	$taxonomy_base_key = "equpment";
	$items = pof_taxonomy_translate_get_equpments();
	$title = "Tarvikkeet";
	$title2 = "Tarvike";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_get_skillareas() {
	$ret = array();

	foreach (get_terms('pof_tax_skillarea') as $term) {
		$ret[$term->slug] = $term->name;
	}
	
	return $ret;

}


function pof_taxonomy_translate_skillareas() {
	$taxonomy_base_key = "skillarea";
	$items = pof_taxonomy_translate_get_skillareas();
	$title = "Taitoalueet";
	$title2 = "Taitoalue";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_get_taskgroupterms() {
	$ret = array();

	$ret["jalki_single"] = mb_convert_encoding("J&auml;lki","UTF-8", "auto");
	$ret["jalki_plural"] = mb_convert_encoding("J&auml;ljet","UTF-8", "auto");

	$ret["kasvatusosio_single"] = "Kasvatusosio";
	$ret["kasvatusosio_plural"] = "Kasvatusosiot";

	$ret["ilmansuunta_single"] = "Ilmansuunta";
	$ret["ilmansuunta_plural"] = "Ilmansuunnat";

	$ret["taitomerkki_single"] = "Taitomerkki";
	$ret["taitomerkki_plural"] = "Taitomerkit";

	$ret["tarppo_single"] = "Tarppo";
	$ret["tarppo_plural"] = "Tarpot";

	$ret["ryhma_single"] = mb_convert_encoding("Ryhm&auml;","UTF-8", "auto");
	$ret["ryhma_plural"] = mb_convert_encoding("Ryhm&auml;t","UTF-8", "auto");

	$ret["aktiviteetti_single"] = "Aktiviteetti";
	$ret["aktiviteetti_plural"] = "Aktiviteetit";

	$ret["aihe_single"] = "Aihe";
	$ret["aihe_plural"] = "Aiheet";

	$ret["tasku_single"] = "Tasku";
	$ret["tasku_plural"] = "Taskut";

	$ret["rasti_single"] = "Rasti";
	$ret["rasti_plural"] = "Rastit";
	
	return $ret;

}


function pof_taxonomy_translate_taskgroupterm() {
	$taxonomy_base_key = "taskgroup_term";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$items2 = pof_taxonomy_translate_get_taskgroupterms();

	foreach ($items2 as $item2key => $item2) {
		if (!array_key_exists ($item2key, $items)) {
			$items[$item2key] = $item2;
		}
	}

//	$items = pof_taxonomy_translate_get_taskgroupterms();
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";
	$additional_text = "Kun lis&auml;&auml;t, lis&auml;&auml; aina kaksi. Yksikk&ouml;muodon per&auml;ss&auml; oltava _single, monikkomuodon per&auml;ss&auml; _plural";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2, $additional_text);
}

function pof_taxonomy_translate_get_taskterms() {
	$ret = array();

	$ret["askel_single"] = "Askel";
	$ret["askel_plural"] = "Askeleet";

	$ret["aktiviteetti_single"] = "Aktiviteetti";
	$ret["aktiviteetti_plural"] = "Aktiviteetit";

	$ret["aktiviteettitaso_single"] = "Aktiviteettitaso";
	$ret["aktiviteettitaso_plural"] = "Aktiviteettitasot";

	$ret["suoritus_single"] = "Suoritus";
	$ret["suoritus_plural"] = "Suoritukset";

	$ret["paussi_single"] = "Paussi";
	$ret["paussi_plural"] = "Paussit";
	
	return $ret;

}


function pof_taxonomy_translate_taskterm() {
	$taxonomy_base_key = "task_term";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$items2 = pof_taxonomy_translate_get_taskterms();

	foreach ($items2 as $item2key => $item2) {
		if (!array_key_exists ($item2key, $items)) {
			$items[$item2key] = $item2;
		}
	}

//	$items = pof_taxonomy_translate_get_taskterms();
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";
	$additional_text = "Kun lis&auml;&auml;t, lis&auml;&auml; aina kaksi. Yksikk&ouml;muodon per&auml;ss&auml; oltava _single, monikkomuodon per&auml;ss&auml; _plural";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2, $additional_text);
}