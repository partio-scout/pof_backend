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

global $pof_taxonomy_searchpage_db_version, $searchpage_selected_program;
$pof_taxonomy_searchpage_db_version = '1.0';

$args = array(
  'numberposts' => -1,
  'posts_per_page' => -1,
  'post_type' => 'pof_post_program'
);

$programs = get_posts( $args );

$searchpage_selected_program = $programs[0]->ID;
if (isset($_POST['Change_program']) && isset($_POST['program'])) {
  $searchpage_selected_program = $_POST['program'];
}

function pof_taxonomy_searchpage_get_programs() {
  $args = array(
    'numberposts' => -1,
    'posts_per_page' => -1,
    'post_type' => 'pof_post_program'
  );

  return get_posts( $args );
}

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
    program bigint(20) DEFAULT 0 NOT NULL,
		UNIQUE KEY taxonomy_slug (`taxonomy_slug` (191)),
		KEY taxonomy_base (taxonomy_base)
    KEY program (program)
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
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Aktiviteetin kestot', 'Aktiviteetin kestot', 'manage_options', 'pof_taxonomy_searchpage_taskduration-handle', 'pof_taxonomy_searchpage_taskduration');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Aktiviteetin valmistelun kestot', 'Aktiviteetin valmistelun kestot', 'manage_options', 'pof_taxonomy_searchpage_taskpreparationduration-handle', 'pof_taxonomy_searchpage_taskpreparationduration');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Tarvikkeet', 'Tarvikkeet', 'manage_options', 'pof_taxonomy_searchpage_equpments-handle', 'pof_taxonomy_searchpage_equpments');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Taitoalueet', 'Taitoalueet', 'manage_options', 'pof_taxonomy_searchpage_skillareas-handle', 'pof_taxonomy_searchpage_skillareas');
  add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Kasvatustavoitteen avainsanat', 'Kasvatustavoitteen avainsana', 'manage_options', 'pof_taxonomy_searchpage_growthtarget-handle', 'pof_taxonomy_searchpage_growthtarget');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Johtamistaidot', 'Johtamistaidot', 'manage_options', 'pof_taxonomy_searchpage_leaderships-handle', 'pof_taxonomy_searchpage_leaderships');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Aktiviteettipaketin yl&auml;k&auml;site', 'Aktiviteettipaketin yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_searchpage_taskgroupterm-handle', 'pof_taxonomy_searchpage_taskgroupterm');
	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Aktiviteetin yl&auml;k&auml;site', 'Aktiviteetin yl&auml;k&auml;site', 'manage_options', 'pof_taxonomy_searchpage_taskterm-handle', 'pof_taxonomy_searchpage_taskterm');

	add_submenu_page( 'pof_taxonomy_searchpage_frontpage-handle', 'Yleiset', 'Yleiset', 'manage_options', 'pof_taxonomy_searchpage_common-handle', 'pof_taxonomy_searchpage_common');

}

function pof_taxonomy_searchpage_frontpage() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

  $searchItems = [
    'place_of_performance' => 'Suorituspaikat',
    'groupsizes' => 'Ryhm&auml;koko',
    'mandatory' => 'Pakollisuus',
    'taskduration' => 'Aktiviteetin kestot',
    'taskpreparationduration' => 'Aktiviteetin valmistelun kestot',
    'equpment' => 'Tarvikkeet',
    'skillarea' => 'Taitoalueet',
    'growth_target' => 'Kasvatustavoitteen avainsanat',
    'leadership' => 'Johtamistaidot'
  ];

  $args = array(
    'numberposts' => -1,
    'posts_per_page' => -1,
    'post_type' => 'pof_post_program'
  );

  $programs = get_posts( $args );

	echo '<div class="wrap">';

  if(isset($_POST['update-fields'])) {

    foreach ($searchItems as $key => $item) {
      foreach($programs as $program) {
        $field_key = 'taxonomy_searchoptions_' . $key . '_' . $program->ID;
        if(isset($_POST[$field_key])) {
          update_option( $field_key, $_POST[$field_key]);
        }
      }
    }

    echo "<div class=\"updated notice notice-success is-dismissible\">
        <p>Hakukenttien tyypit päivitetty</p>
        </div>";
  }

  if(isset($_POST['update-types'])) {
      if(isset($_POST['taxonomy_searchoptions_types'])) {
        update_option('taxonomy_searchoptions_types', $_POST['taxonomy_searchoptions_types']);
      }
      echo "<div class=\"updated notice notice-success is-dismissible\">
          <p>Datatyypit päivitetty</p>
          </div>";
  }

  $searchTypes = explode("\n", get_option('taxonomy_searchoptions_types'));

	echo '<h1>POF Taxonomy searchpage</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat muokata.</p>'; ?>
  <h2>Muokkaa kenttiä</h2>
  <form id="featured_upload" method="post" action="">
  <table cellpadding="2" cellspacing="2" border="2">
    <thead>
      <tr>
        <th><h2>Kenttä</h2></th>
        <!-- <th><h2>Tyyppi</h2></th> -->
        <?php foreach($programs as $program): ?>
            <th><h2><?php echo $program->post_title; ?><h2></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($searchItems as $item_key => $item): ?>
      <tr>
        <td><?php echo $item; ?></td>
        <?php foreach($programs as $program): ?>
          <?php
          $select_name = 'taxonomy_searchoptions_' . $item_key . '_' . $program->ID;
          $selected_value = get_option($select_name);
          ?>
          <td>
            <select name="<?php echo $select_name; ?>">
              <?php foreach ($searchTypes as $key => $item): ?>
                <option value="<?php echo sanitize_title($item); ?>" <?php echo $selected_value == sanitize_title($item) ? "selected": ""?>><?php echo $item; ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>

  </table>
  <br>
  <input type="submit" name="update-fields" value="Päivitä kentät" />
  <br>
  <br>
  <h2>Muokkaa kenttätyyppejä</h2>
  <p class="description" id="tagline-description">Yksi kenttätyyppi per rivi</p>
  <textarea id="taxonomy_searchoptions_types" rows="10" cols="80" autocomplete="off" name="taxonomy_searchoptions_types"><?php echo esc_attr( get_option('taxonomy_searchoptions_types')); ?></textarea>
  <br>
  <input type="submit" name="update-types" value="Päivitä kenttätyypit" />
  </form>
	<?php echo '</div>';
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

	global $wpdb, $searchpage_selected_program;
	$table_name = pof_taxonomy_searchpage_get_table_name();

  $args = array(
    'numberposts' => -1,
    'posts_per_page' => -1,
    'post_type' => 'pof_post_program'
  );

  $programs = get_posts( $args );

  $searchpage_selected_program = $programs[0]->ID;
  if (isset($_POST['program'])) {
    $searchpage_selected_program = $_POST['program'];
  }

	if(isset($_POST['Submit'])) {
        $items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, $searchpage_selected_program);

        $wpdb->delete(
			$table_name,
			array(
				'taxonomy_base' => $taxonomy_base_key,
        'program'       => $searchpage_selected_program
			),
			array( '%s', '%d' )
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
						'taxonomy_base' => $taxonomy_base_key,
            'program'       => $searchpage_selected_program
					),
					array(
						'%s',
						'%s',
            '%d'
					)
				);
				echo "<br />Added " . $key . "";
        print_r(array(
          'taxonomy_slug' => $taxonomy_full_key,
          'taxonomy_base' => $taxonomy_base_key,
          'program'       => $searchpage_selected_program
        ));
			}
		}
        echo "<br />";

		// reload items:
		$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, $searchpage_selected_program);
	}

	echo '<div class="wrap">';
	echo '<h1>'.$title.'</h1>';
	echo '<form id="program_form" method="post" action="">';
  echo 'Valitse ohjelma:';
  echo '<select name="program">';
  foreach ($programs as $program) {
    if ($program->ID == $searchpage_selected_program) {
      echo '<option selected="selected" value="'.$program->ID.'">'.$program->post_title.'</option>';
    } else {
      echo '<option value="'.$program->ID.'">'.$program->post_title.'</option>';
    }
  }
  echo '</select>';
  echo '<input type="submit" name="Change_program" id="Change_program" value="Vaihda" />';
  echo '</form>';
  echo '<form id="featured_upload" method="post" action="">';
  echo '<input type="hidden" name="program" value="'.$searchpage_selected_program.'" />';
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


function pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower = false, $program = 0) {
	$ret = array();

	global $wpdb, $searchpage_selected_program;

  if(isset($searchpage_selected_program)) {
    $program = $searchpage_selected_program;
  }

	$table_name = pof_taxonomy_searchpage_get_table_name();

    if ($taxonomy_base_key == 'equipment' || $taxonomy_base_key == 'skillarea') {
        $all_items = array();
        foreach (get_terms('pof_tax_'.$taxonomy_base_key) as $term) {
		    $all_items[$term->slug] = $term->name;
	    }

    } else {
        $all_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower, $program);
        $all_items = array_merge($all_items, pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key, $tolower, 0));
    }


	$searchpage_res = $wpdb->get_results(
		"
		SELECT taxonomy_slug
		FROM " . pof_taxonomy_searchpage_get_table_name() . "
		WHERE taxonomy_base = '".$taxonomy_base_key."'
    AND program = '".$program."'
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

  global $searchpage_selected_program;
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, false, $searchpage_selected_program);
	$title = "Suorituspaikat";
	$title2 = "Suorituspaikka";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_groupsizes() {
	$taxonomy_base_key = "groupsize";

  global $searchpage_selected_program;
  if(!$searchpage_selected_program) {
    $searchpage_selected_program = pof_taxonomy_searchpage_get_programs()[0]->ID;
  }
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, false, $searchpage_selected_program);
	$title = "Ryhm&auml;koot";
	$title2 = "Ryhm&auml;koko";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_searchpage_mandatory() {
	$taxonomy_base_key = "mandatory";

  global $searchpage_selected_program;
  if(!$searchpage_selected_program) {
    $searchpage_selected_program = pof_taxonomy_searchpage_get_programs()[0]->ID;
  }
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, false, $searchpage_selected_program);
	$title = "Pakollisuus";
	$title2 = "Pakollisuus";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_searchpage_taskduration() {
	$taxonomy_base_key = "taskduration";

  global $searchpage_selected_program;
  if(!$searchpage_selected_program) {
    $searchpage_selected_program = pof_taxonomy_searchpage_get_programs()[0]->ID;
  }
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, false, $searchpage_selected_program);
	$title = "Aktiviteetin kestot";
	$title2 = "Kesto";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}



function pof_taxonomy_searchpage_taskpreparationduration() {
	$taxonomy_base_key = "taskpreaparationduration";

  global $searchpage_selected_program;
  if(!$searchpage_selected_program) {
    $searchpage_selected_program = pof_taxonomy_searchpage_get_programs()[0]->ID;
  }
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, false, $searchpage_selected_program);
	$title = "Aktiviteetin valmistelun kestot";
	$title2 = "Kesto";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_get_equpments($taxonomy_base_key, $program) {
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
    AND program = '".$program."'
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
  global $searchpage_selected_program;
  if(!$searchpage_selected_program) {
    $searchpage_selected_program = pof_taxonomy_searchpage_get_programs()[0]->ID;
  }
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key, $searchpage_selected_program);
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


function pof_taxonomy_searchpage_skillareas() {
	$taxonomy_base_key = "skillarea";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Taitoalueet";
	$title2 = "Taitoalue";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}


function pof_taxonomy_searchpage_leaderships() {
	$taxonomy_base_key = "leadership";
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($taxonomy_base_key);
	$title = "Johtamistaidot";
	$title2 = "Johtamistaito";

	pof_taxonomy_searchpage_form($taxonomy_base_key, $items, $title, $title2);
}

function pof_taxonomy_searchpage_growthtarget() {
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
	$title = "Aktiviteettipaketin yl&auml;k&auml;site";
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
	$title = "Aktiviteettipaketin yl&auml;k&auml;site";
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