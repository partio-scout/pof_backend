<?php
/**
 * @package POF Taxonomy searchpage
 */
/*
Plugin Name: POF Taxonomy searchpage
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

add_action( 'admin_menu', 'pof_taxonomy_searchpage_menu' );

register_activation_hook( __FILE__, 'pof_taxonomy_searchpage_install' );

global $pof_taxonomy_searchpage_db_version;
$pof_taxonomy_searchpage_db_version = '1.0';


function pof_taxonomy_searchpage_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'pof_taxonomy_searchpage';
}

function pof_taxonomy_searchpage_install() {
	global $wpdb;
	global $pof_taxonomy_searchpage_db_version;

	$table_name = pof_taxonomy_searchpage_get_table_name();
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		taxonomy_base varchar(255) DEFAULT '' NOT NULL,
		taxonomy_slug varchar(255) DEFAULT '' NOT NULL,
		UNIQUE KEY taxonomy_slug (`taxonomy_slug` (191)),
		KEY taxonomy_base (taxonomy_base)
        ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'pof_taxonomy_searchpage_db_version', $pof_taxonomy_searchpage_db_version );
}

function pof_taxonomy_searchpage_menu() {
	add_menu_page('POF Taxonomy searchpage', 'Hakusivu', 'manage_options', 'pof_taxonomy_searchpage_frontpage-handle', 'pof_taxonomy_searchpage_frontpage', 'dashicons-search');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Suorituspaikat', 'Suorituspaikat', 'manage_options', 'pof_taxonomy_searchpage_places-handle', 'pof_taxonomy_searchpage_places');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Ryhm&auml;koko', 'Ryhm&auml;koot', 'manage_options', 'pof_taxonomy_searchpage_groupsizes-handle', 'pof_taxonomy_searchpage_groupsizes');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Pakollisuus', 'Pakollisuus', 'manage_options', 'pof_taxonomy_searchpage_mandatory-handle', 'pof_taxonomy_searchpage_mandatory');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Suorituksen kestot', 'Suorituksen kestot', 'manage_options', 'pof_taxonomy_searchpage_taskduration-handle', 'pof_taxonomy_searchpage_taskduration');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Suorituksen valmistelun kestot', 'Suorituksen valmistelun kestot', 'manage_options', 'pof_taxonomy_searchpage_taskpreparationduration-handle', 'pof_taxonomy_searchpage_taskpreparationduration');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Tarvikkeet', 'Tarvikkeet', 'manage_options', 'pof_taxonomy_searchpage_equpments-handle', 'pof_taxonomy_searchpage_equpments');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Taitoalueet', 'Taitoalueet', 'manage_options', 'pof_taxonomy_searchpage_skillareas-handle', 'pof_taxonomy_searchpage_skillareas');
    add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Kasvatustavoitteen avainsanat', 'Kasvatustavoitteen avainsana', 'manage_options', 'pof_taxonomy_searchpage_growthtarget-handle', 'pof_taxonomy_searchpage_growthtarget');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Suoritepaketin yl&auml;k&auml;site', 'Suoritepaketin yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_searchpage_taskgroupterm-handle', 'pof_taxonomy_searchpage_taskgroupterm');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Suoritteen yl&auml;k&auml;site', 'Suoritteen yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_searchpage_taskterm-handle', 'pof_taxonomy_searchpage_taskterm');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Yleiset', 'Yleiset', 'manage_options', 'pof_taxonomy_searchpage_common-handle', 'pof_taxonomy_searchpage_common');

}

function pof_taxonomy_searchpage_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<h1>POF Taxonomy searchpage</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat muokata.</p>';
	echo '</div>';
}


function pof_taxonomy_searchpage_parser_taxonomy_key($tmpkey) {
	$key = str_replace("taxonomy_searchpage_", "", $tmpkey);

    $ret = array();
	$ret["key"] = $key;

	return $ret;
}



function pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2, $additional_text = "") {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	global $wpdb;
	$table_name = pof_taxonomy_searchpage_get_table_name();

	if(isset($_POST['Submit'])) {

        $items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);

        $wpdb->delete(
			$table_name,
			array(
				'taxonomy_base' => $taxonomy_base_key
			),
			array( '%s' )
		);

        echo "Deleted all";

		foreach ($_POST as $key => $item) {
			if (substr( $key, 0, 20 ) == "taxonomy_searchpage_") {
				$tmp = pof_taxonomy_searchpage_parser_taxonomy_key($key);

				$taxonomy_key = $tmp["key"];
				$taxonomy_full_key = $taxonomy_base_key . "::" . $tmp["key"];

				$tmp = $wpdb->insert( 
					$table_name, 
					array( 
						'taxonomy_slug' => $taxonomy_full_key,
						'taxonomy_base' => $taxonomy_base_key
					), 
					array( 
						'%s', 
						'%s'
					) 
				);
				echo "<br />Added " . $key . "";
			}
		}
        echo "<br />";
		
		// reload items:
		$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	}

	echo '<div class="wrap">';
	echo '<h1>'.$title.'</h1>';
	echo '<form id="featured_upload" method="post" action="">';
	echo '<table cellpadding="2" cellspacing="2" border="2">';
	echo '<thead>';
	echo '<tr>';
	echo '<th><h2>'.$title2.'</h2></th>';
	echo '<th><h2>N&auml;yt&auml; lomakkeella</h2></th>';
	echo '<tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($items as $tmp_key => $tmp_item) {
		echo '<tr>';
		echo '<th>'.$tmp_item->title.'<br /> ('.$tmp_key.')</th>';
		echo '<th>';
        if ($tmp_item->enabled) {
            echo '<input type="checkbox" checked="checked" name="taxonomy_searchpage_'.$tmp_key.'" id="taxonomy_searchpage_'.$tmp_key.'" />';
        }
        else {
            echo '<input type="checkbox" name="taxonomy_searchpage_'.$tmp_key.'" id="taxonomy_searchpage_'.$tmp_key.'" />';
        }
        echo '</th>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '<br /><input type="submit" name="Submit" value="Submit" />';
	echo '</form>';
	echo '</div>';	
}


function pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower = false) {
	$ret = array();

	global $wpdb;

	$table_name = pof_taxonomy_searchpage_get_table_name();

    if ($taxonomy_base_key == 'equipment' || $taxonomy_base_key == 'skillarea') {
        $all_items = array();
        foreach (get_terms('pof_tax_'.$taxonomy_base_key) as $term) {
		    $all_items[$term->slug] = $term->name;
	    }

    } else {
        $all_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower);
    }
    

	$searchpage_res = $wpdb->get_results( 
		"
		SELECT taxonomy_slug
		FROM " . pof_taxonomy_searchpage_get_table_name() . "
		WHERE taxonomy_base = '".$taxonomy_base_key."'
		"
	, ARRAY_N);

    $enabled_keys = array();

    foreach($searchpage_res as $search_result) {
        array_push($enabled_keys, $search_result[0]);
    }

    foreach ($all_items as $item_key => $item) {
        $ret[$item_key] = new stdClass();
        $ret[$item_key]->title = $item;
        $ret[$item_key]->enabled = false;
        if (in_array($taxonomy_base_key."::".$item_key, $enabled_keys) ) {
            $ret[$item_key]->enabled = true;
        }

    }

    return $ret;
}


function pof_taxonomy_searchpage_places() {
	$taxonomy_base_key = "place_of_performance";
	
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituspaikat";
	$title2 = "Suorituspaikka";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_groupsizes() {
	$taxonomy_base_key = "groupsize";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Ryhm&auml;koot";
	$title2 = "Ryhm&auml;koko";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_searchpage_mandatory() {
	$taxonomy_base_key = "mandatory";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Pakollisuus";
	$title2 = "Pakollisuus";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_searchpage_taskduration() {
	$taxonomy_base_key = "taskduration";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen kestot";
	$title2 = "Kesto";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}



function pof_taxonomy_searchpage_taskpreparationduration() {
	$taxonomy_base_key = "taskpreaparationduration";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Suorituksen valmistelun kestot";
	$title2 = "Kesto";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_get_equpments($taxonomy_base_key) {
	$all_items = array();

	foreach (get_terms('pof_tax_equipment') as $term) {
		$all_items[$term->slug] = $term->name;
	}
	
    $ret = array();

	global $wpdb;

	$table_name = pof_taxonomy_searchpage_get_table_name();

//    $all_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower);

	$searchpage_res = $wpdb->get_results( 
		"
		SELECT taxonomy_slug
		FROM " . pof_taxonomy_searchpage_get_table_name() . "
		WHERE taxonomy_base = '".$taxonomy_base_key."'
		"
	, ARRAY_N);

    $enabled_keys = array();

    foreach($searchpage_res as $search_result) {
        array_push($enabled_keys, $search_result[0]);
    }

    foreach ($all_items as $item_key => $item) {
        $ret[$item_key] = new stdClass();
        $ret[$item_key]->title = $item;
        $ret[$item_key]->enabled = false;
        if (in_array($taxonomy_base_key."::".$item_key, $enabled_keys) ) {
            $ret[$item_key]->enabled = true;
        }

    }
	
	return $ret;

}


function pof_taxonomy_searchpage_equpments() {
	$taxonomy_base_key = "equpment";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Tarvikkeet";
	$title2 = "Tarvike";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_get_skillareas($taxonomy_base_key) {
	$all_items = array();

	foreach (get_terms('pof_tax_skillarea') as $term) {
		$all_items[$term->slug] = $term->name;
	}

    $ret = array();

	global $wpdb;

	$table_name = pof_taxonomy_searchpage_get_table_name();

//    $all_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower);

	$searchpage_res = $wpdb->get_results( 
		"
		SELECT taxonomy_slug
		FROM " . pof_taxonomy_searchpage_get_table_name() . "
		WHERE taxonomy_base = '".$taxonomy_base_key."'
		"
	, ARRAY_N);

    $enabled_keys = array();

    foreach($searchpage_res as $search_result) {
        array_push($enabled_keys, $search_result[0]);
    }

    foreach ($all_items as $item_key => $item) {
        $ret[$item_key] = new stdClass();
        $ret[$item_key]->title = $item;
        $ret[$item_key]->enabled = false;
        if (in_array($taxonomy_base_key."::".$item_key, $enabled_keys) ) {
            $ret[$item_key]->enabled = true;
        }

    }
	
	return $ret;

}


function pof_taxonomy_searchpage_skillareas($taxonomy_base_key) {
	$taxonomy_base_key = "skillarea";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Taitoalueet";
	$title2 = "Taitoalue";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_growthtarget($taxonomy_base_key) {
	$taxonomy_base_key = "growth_target";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Kasvatustavoitteen avainsanat";
	$title2 = "Kasvatustavoitteen avainsana";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_get_taskgroupterms() {
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


function pof_taxonomy_searchpage_taskgroupterm() {
	$taxonomy_base_key = "taskgroup_term";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$items2 = pof_taxonomy_searchpage_get_taskgroupterms();

	foreach ($items2 as $item2key => $item2) {
		if (!array_key_exists ($item2key, $items)) {
			$items[$item2key] = $item2;
		}
	}

//	$items = pof_taxonomy_searchpage_get_taskgroupterms();
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";
	$additional_text = "Kun lis&auml;&auml;t, lis&auml;&auml; aina kaksi. Yksikk&ouml;muodon per&auml;ss&auml; oltava _single, monikkomuodon per&auml;ss&auml; _plural";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2, $additional_text);
}

function pof_taxonomy_searchpage_get_taskterms() {
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


function pof_taxonomy_searchpage_taskterm() {
	$taxonomy_base_key = "task_term";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$items2 = pof_taxonomy_searchpage_get_taskterms();

	foreach ($items2 as $item2key => $item2) {
		if (!array_key_exists ($item2key, $items)) {
			$items[$item2key] = $item2;
		}
	}

//	$items = pof_taxonomy_searchpage_get_taskterms();
	$title = "Suoritepaketin yl&auml;k&auml;site";
	$title2 = "Termi";
	$additional_text = "Kun lis&auml;&auml;t, lis&auml;&auml; aina kaksi. Yksikk&ouml;muodon per&auml;ss&auml; oltava _single, monikkomuodon per&auml;ss&auml; _plural";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2, $additional_text);
}

function pof_taxonomy_searchpage_common() {
	$taxonomy_base_key = "common";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);

	$title = "Yleiset";
	$title2 = "Termi";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}