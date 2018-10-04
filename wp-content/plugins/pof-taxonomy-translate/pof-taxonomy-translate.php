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



	$languages = pof_settings_get_all_languages();

	$ret = array();

	foreach ($languages as $lang) {
		if ($lang->is_active || $lang->is_default) {
			$ret[$lang->lang_code] = $lang->lang_title;
		}
	}


/*
		$ret['fi'] = "Suomi";
	$ret['sv'] = "Ruotsi";
	$ret['en'] = "Englanti";
*/

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
	add_menu_page('POF Taxonomy Translate', 'K&auml;&auml;nn&ouml;kset', 'pof_manage_translations', 'pof_taxonomy_translate_frontpage-handle', 'pof_taxonomy_translate_frontpage', 'dashicons-translation');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Suorituspaikat', 'Suorituspaikat', 'pof_manage_translations', 'pof_taxonomy_translate_places-handle', 'pof_taxonomy_translate_places');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Ryhm&auml;koko', 'Ryhm&auml;koot', 'pof_manage_translations', 'pof_taxonomy_translate_groupsizes-handle', 'pof_taxonomy_translate_groupsizes');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Pakollisuus', 'Pakollisuus', 'pof_manage_translations', 'pof_taxonomy_translate_mandatory-handle', 'pof_taxonomy_translate_mandatory');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Aktiviteetin kestot', 'Aktiviteetin kestot', 'pof_manage_translations', 'pof_taxonomy_translate_taskduration-handle', 'pof_taxonomy_translate_taskduration');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Aktiviteetin valmistelun kestot', 'Aktiviteetin valmistelun kestot', 'pof_manage_translations', 'pof_taxonomy_translate_taskpreparationduration-handle', 'pof_taxonomy_translate_taskpreparationduration');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Tarvikkeet', 'Tarvikkeet', 'pof_manage_translations', 'pof_taxonomy_translate_equpments-handle', 'pof_taxonomy_translate_equpments');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Taitoalueet', 'Taitoalueet', 'pof_manage_translations', 'pof_taxonomy_translate_skillareas-handle', 'pof_taxonomy_translate_skillareas');
  add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Kasvatustavoitteen avainsanat', 'Kasvatustavoitteen avainsana', 'pof_manage_translations', 'pof_taxonomy_translate_growthtarget-handle', 'pof_taxonomy_translate_growthtarget');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Johtamistaidot', 'Johtamistaidot', 'pof_manage_translations', 'pof_taxonomy_translate_leaderships-handle', 'pof_taxonomy_translate_leaderships');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Aktiviteettipaketin yl&auml;k&auml;site', 'Aktiviteettipaketin yl&auml;k&auml;site', 'pof_manage_translations', 'pof_taxonomy_translate_taskgroupterm-handle', 'pof_taxonomy_translate_taskgroupterm');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Aktiviteetin yl&auml;k&auml;site', 'Aktiviteetin yl&auml;k&auml;site', 'pof_manage_translations', 'pof_taxonomy_translate_taskterm-handle', 'pof_taxonomy_translate_taskterm');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Yleiset', 'Yleiset', 'pof_manage_translations', 'pof_taxonomy_translate_common-handle', 'pof_taxonomy_translate_common');

	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Hakusivu', 'Hakusivu', 'pof_manage_translations', 'pof_taxonomy_translate_search-handle', 'pof_taxonomy_translate_search');
	add_submenu_page( 'pof_taxonomy_translate_frontpage-handle', 'Api Type', 'Api Type', 'pof_manage_translations', 'pof_taxonomy_translate_api_type-handle', 'pof_taxonomy_translate_api_type');

}

function pof_taxonomy_translate_frontpage() {
	if ( !current_user_can( 'pof_manage_translations' ) )  {
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

function pof_taxonomy_translate_get_translation_content($taxonomy_base_key, $tmp_key, $agegroup_id = '0', $selected_lang = 'fi') {
    $item = pof_taxonomy_translate_get_translation($taxonomy_base_key, $tmp_key, $agegroup_id, $selected_lang, true);

    if (   count($item)>0
        && array_key_exists('content', $item[0])) {
        return $item[0]->content;
    }
    return "";
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
		&& (empty($translate_res) || count($translate_res) == 0)) {
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
		&& (empty($translate_res) || count($translate_res) == 0)) {
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
		&& (empty($translate_res) || count($translate_res) == 0)) {
		$translate_res = $wpdb->get_results(
			"
			SELECT *
			FROM " . pof_taxonomy_translate_get_table_name() . "
			WHERE taxonomy_slug = '" . $taxonomy_slug . "'
				AND lang = 'fi'
				AND agegroup_id = 0
			"
		);


        // if all have failed, then create the item so that it can be translated
        if (empty($translate_res) || count($translate_res) == 0) {
            $tmp = $wpdb->insert(
				pof_taxonomy_translate_get_table_name(),
				array(
					'taxonomy_slug' => $taxonomy_slug,
					'agegroup_id' => 0,
					'lang' => 'fi',
          'content' => $tmp_key,
          'time' => current_time( 'mysql' )
				),
				array(
					'%s',
					'%d',
					'%s',
          '%s',
          '%s'
				)
			);


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
	}


	return $translate_res;
}


function pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2, $additional_text = "") {
	if ( !current_user_can( 'pof_manage_translations' ) )  {
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

				$taxonomy_key = trim($tmp["key"]);

        if (strlen($taxonomy_key) == 0) {
            continue;
        }

				$taxonomy_full_key = $taxonomy_base_key . "::" . $taxonomy_key;
				$agegroup_id = $tmp["agegroup_id"];

				$translation = pof_taxonomy_translate_get_translation($taxonomy_base_key, $taxonomy_key, $agegroup_id, $selected_lang, false);
        $taxonomy_deletion_key = 'taxonomy_delete_' . $taxonomy_key;



        if(isset($_POST[$taxonomy_deletion_key])) {
          $table_name = pof_taxonomy_translate_get_table_name();
					$wpdb->delete(
						$table_name,
						array(
							'id' => $translation[0]->id
						),
						array( '%d' )
					);
					echo "<br />Deleted " . $key . "";
          continue;
				}

				if (empty($translation)
					&& $item != "") {
					$tmp = $wpdb->insert(
						$table_name,
						array(
							'taxonomy_slug' => $taxonomy_full_key,
							'agegroup_id' => (int) $agegroup_id,
							'lang' => $selected_lang,
							'content' => $item,
              'time' => current_time( 'mysql' )
						),
						array(
							'%s',
							'%d',
							'%s',
							'%s',
              '%s'
						)
					);
					echo "<br />Added " . $key . "";
				} else if (!empty($translation) && $item != "") {
					if ($translation[0]->content != $item) {
						$tmp = $wpdb->update(
							$table_name,
							array(
								'content' => $item,
                'time' => current_time( 'mysql' )
							),
							array(
								'id' => $translation[0]->id
							),
							array(
								'%s',
                '%s'
							),
							array(
								'%d'
							)
						);
						echo "<br />Updated" . $key . "";
					}
				} else if (!empty($translation) && $item == "") {
          $wpdb->update(
            $table_name,
            array(
              'content' => ""
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
						'content' => $_POST['add_taxonomy_translate_key_0'],
            'time' => current_time( 'mysql' )
					),
					array(
						'%s',
						'%d',
						'%s',
						'%s',
            '%s'
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
							'content' => $_POST['add_taxonomy_translate_key_'.$agegroup->id],
              'time' => current_time( 'mysql' )
						),
						array(
							'%s',
							'%d',
							'%s',
							'%s',
              '%s'
						)
					);
				}
			}
		}
		// reload items:
		switch ($taxonomy_base_key) {
	    	case 'skillarea':
						$items = pof_taxonomy_translate_get_skillareas();
						break;
				case 'equpment':
						$items = pof_taxonomy_translate_get_equpments();
						break;
				case 'growth_target':
						$items = pof_taxonomy_translate_get_growthtargets();
						break;
				case 'leadership':
						$items = pof_taxonomy_translate_get_leaderships();
						break;
		    default:
		        $items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
		}
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
  echo '<th><h2>Poista</h2></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($items as $tmp_key => $tmp_title) {
        $tmp_key = trim($tmp_key);
        if (strlen($tmp_key) == 0) {
            continue;
        }
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
    echo '<td style="text-align:center;">';
    if(!pof_is_tag_used($taxonomy_base_key, $tmp_key)) {
      echo '<input name="taxonomy_delete_' .$tmp_key.'" type="checkbox">';
    }
    echo '</td>';

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
		SELECT taxonomy_slug, content, id
		FROM " . pof_taxonomy_translate_get_table_name() . "
		WHERE lang = 'fi'
			AND agegroup_id = 0
			AND taxonomy_slug LIKE '".$taxonomy_base_key."::%'

        ORDER BY taxonomy_slug, content
		"
	);

	foreach ($translate_res as $item) {
        $key = str_replace($taxonomy_base_key.'::', "", $item->taxonomy_slug);

        if (trim($key) == "") {
            $wpdb->delete(
				pof_taxonomy_translate_get_table_name(),
				array(
					'id' => $item->id
				),
				array( '%d' )
			);
        }

		if ($tolower) {
			$ret[$key] = strtolower($item->content);
		} else {
			$ret[$key] = $item->content;
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
	$title = "Aktiviteetin kestot";
	$title2 = "Kesto";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}



function pof_taxonomy_translate_taskpreparationduration() {
	$taxonomy_base_key = "taskpreaparationduration";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Aktiviteetin valmistelun kestot";
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

function pof_taxonomy_translate_get_leaderships() {
	$ret = array();

	foreach (get_terms('pof_tax_leadership') as $term) {
		$ret[$term->slug] = $term->name;
	}

	return $ret;

}


function pof_taxonomy_translate_leaderships() {
	$taxonomy_base_key = "leadership";
	$items = pof_taxonomy_translate_get_leaderships();
	$title = "Johtamistaidot";
	$title2 = "Johtamistaito";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_get_growthtargets() {
	$ret = array();

	foreach (get_terms('pof_tax_growth_target') as $term) {
		$ret[$term->slug] = $term->name;
	}

	return $ret;

}


function pof_taxonomy_translate_growthtarget() {
	$taxonomy_base_key = "growth_target";
	$items = pof_taxonomy_translate_get_growthtargets();
	$title = "Kasvatustavoitteen avainsanat";
	$title2 = "Kasvatustavoitteen avainsana";

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
	$title = "Aktiviteettipaketin yl&auml;k&auml;site";
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
	$title = "Aktiviteettipaketin yl&auml;k&auml;site";
	$title2 = "Termi";
	$additional_text = "Kun lis&auml;&auml;t, lis&auml;&auml; aina kaksi. Yksikk&ouml;muodon per&auml;ss&auml; oltava _single, monikkomuodon per&auml;ss&auml; _plural";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2, $additional_text);
}

function pof_taxonomy_translate_common() {
	$taxonomy_base_key = "common";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$title = "Yleiset";
	$title2 = "Termi";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_search() {
	$taxonomy_base_key = "search";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$title = "Hakusivun yläkäsitteet";
	$title2 = "Termi";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_translate_api_type() {
	$taxonomy_base_key = "apitype";
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$title = "Api Type";
	$title2 = "Termi";

	pof_taxonomy_translate_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_is_tag_used($taxonomy, $tag) {
  // Right now this uses function defined in pof-content-status plugin. Consider rewriting function in case content status plugin is deactivated
  $options = pof_content_status_tags_get_tag_options();

  $option = $options[$taxonomy];

  $meta_key = $taxonomy;
  if (!empty($option->meta_key)) {
      $meta_key = $option->meta_key;
  }

  global $wpdb;

  /*
   * Check if translation is custom taxonomy term before performing
   */
  $activity_taxonomies = get_object_taxonomies( 'pof_post_task' );
  $terms = $wpdb->get_results( $wpdb->prepare(
  	"
  		SELECT term_id
  		FROM $wpdb->terms
  		WHERE slug = %s
  	",
  	$tag
  ), ARRAY_A );

  if(!empty($terms)) {
    foreach($terms as $term) {
      $term_id = $term['term_id'];
      $term_count = $wpdb->get_var( $wpdb->prepare(
        "
        SELECT count
        FROM $wpdb->term_taxonomy
        WHERE term_id = %d",
        $term_id
      ) );
      if($term_count > 0) {
        return true;
      }
    }
  }

  $res = array();

  if ($option->type == 'boolean') {
      $query = "
  SELECT posts.ID, posts.post_title, posts.post_status, posts.post_type
      FROM wp_posts posts
          JOIN wp_postmeta meta ON posts.ID = meta.post_id
      WHERE posts.post_status NOT IN ('trash', 'inherit')";

      $query_types = "";
      $query_boolean = "";

      if (strstr($tag, 'NON_')) {
          $query_boolean .=  "
              AND meta.meta_value = '0'
      ";
      }
      else {
          $query_boolean .=  "
          AND meta.meta_value = '1'
  ";
      }


      foreach ($option->types as $type) {
          if ($query_types == "") {
              $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
          } else {
              $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$query_boolean.")";
          }
      }


      $query .= "AND (".$query_types.") ";



      $query .=  " GROUP BY posts.ID, posts.post_title, posts.post_status, posts.post_type ORDER BY posts.post_type, posts.post_title";

  }
  else {
      $query = "
  SELECT posts.ID, posts.post_title, posts.post_status, posts.post_type
      FROM wp_posts posts
          JOIN wp_postmeta meta ON posts.ID = meta.post_id
      WHERE posts.post_status NOT IN ('trash', 'inherit')";

      $query_types = "";

      $meta_value_query = "";
      if ($option->type == "multiselect") {
          $meta_value_query = "AND meta.meta_value LIKE '%\"".$tag."\"%'";
      } else {
          $meta_value_query = "AND meta.meta_value = '".$tag."'";
      }

      foreach ($option->types as $type) {
          if ($query_types == "") {
              $query_types = "(meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
          } else {
              $query_types .= " OR (meta.meta_key = '".$type."_".$meta_key."' AND posts.post_type = 'pof_post_".$type."' ".$meta_value_query.")";
          }
      }

      $query .= " AND (".$query_types.") ";


      $query .=  " GROUP BY posts.ID, posts.post_title, posts.post_status, posts.post_type ORDER BY posts.post_type, posts.post_title";
  }

  $res = $wpdb->get_results($query);

  return count($res) > 0;
}
