<?php
/*
Template Name: JSON Tags
*/

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

$filter_langs = "all";
$filter_tags = "all";


if (isset($_GET["langs"]) && trim($_GET["langs"]) != ''){
   $filter_langs = $_GET["langs"];
}
if (isset($_GET["tags"]) && trim($_GET["tags"]) != ''){
   $filter_tags = $_GET["tags"];
}

$program_id = 0;

if (isset($_GET["program"]) && trim($_GET["program"]) != ''){
  global $wpdb;
  $program = $_GET["program"];
  $program_id = $wpdb->get_var(
   $wpdb->prepare(
     "SELECT post_id
     FROM $wpdb->postmeta
     WHERE meta_key = 'post_guid' AND meta_value = %s",
     $program
   )
  );
}

$ret = new stdClass();

$languages = pof_taxonomy_translate_get_languages();

function pof_pages_get_tags($languages, $items, $item_tax_key, $program_id)
{
	global $filter_langs;
	$items_arr = array();

	foreach ($languages as $lang_key => $lang) {
		if ($filter_langs != "all" && !strstr($filter_langs, $lang_key)) {
			continue;
		}
		$tmp = new stdClass();
		$tmp->lang = $lang_key;
    $tmp->lastModified = "0000-00-00 00:00:00";
		$tmp->items = array();
    $lastModified = "0000-00-00 00:00:00";
		foreach ($items as $item_key => $item) {
			$tmp_item = new stdClass();
			$tmp_item->key = trim($item_key);

			$tmp_name = pof_taxonomy_translate_get_translation($item_tax_key, $item_key, 0, $lang_key, false, $program_id);

      $item_timestamp = $tmp_name[0]->time;
      if(strtotime($item_timestamp) > strtotime($lastModified)) {
        $lastModified = $item_timestamp;
      }

			if (!empty($tmp_name)) {
				$tmp_item->value = trim($tmp_name[0]->content);
			} else {
				$tmp_item->value = trim($item);
			}
			array_push($tmp->items, $tmp_item);
		}
    $tmp->lastModified = $lastModified;
		array_push($items_arr, $tmp);
	}
	return $items_arr;
}

function merge_items($items1, $items2) {
  $items = array();
  foreach ($items1 as $item1) {
    foreach($items2 as $item2) {
      if($item1->lang == $item2->lang) {
        $translation_array = array(
          "lang" => $item1->lang,
          "lastModified" => $item1->lastModified > $item2->lastModified ? $item1->lastModified : $item2->lastModified,
          "items" => array_merge((array)$item1->items, (array)$item2->items)
        );
        $items[] = $translation_array;
      }
    }
  }

  return $items;
}

if ($filter_tags == "all" || strstr($filter_tags, "paikka")) {
	// Places
	$item_tax_key = 'place_of_performance';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);

	$ret->paikka = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "ryhmakoko")) {
	// Groupsizes
	$item_tax_key = 'groupsize';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->ryhmakoko = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "pakollisuus")) {
	// Mandatory
	$item_tax_key = 'mandatory';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->pakollisuus = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_kesto")) {
	//TaskDuration
	$item_tax_key = 'taskduration';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->suoritus_kesto = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_valmistelu_kesto")) {
	//TaskPreparationDuration
	$item_tax_key = 'taskpreparationduration';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->suoritus_valmistelu_kesto = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "tarvikkeet")) {
	//Equipments
	$item_tax_key = 'equpment';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $equipment_tags = [];
  foreach(get_terms('pof_tax_equipment', array('hide_empty' => false)) as $item) {
    $equipment_tags[$item->slug] = $item->name;
  }

  foreach($items as $item_key => $item_name) {
    if(!array_key_exists($item_key, $equipment_tags)) {
      unset($items[$item_key]);
    }
  }

	$ret->tarvikkeet = pof_pages_get_tags($languages, $items, $item_tax_key, $program_id);
}

if ($filter_tags == "all" || strstr($filter_tags, "taitoalueet")) {
	//Taitoalueet
	$item_tax_key = 'skillarea';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $skillarea_tags = [];
  foreach(get_terms('pof_tax_skillarea', array('hide_empty' => false)) as $item) {
    $skillarea_tags[$item->slug] = $item->name;
  }

  foreach($items as $item_key => $item_name) {
    if(!array_key_exists($item_key, $skillarea_tags)) {
      unset($items[$item_key]);
    }
  }
	$ret->taitoalueet = pof_pages_get_tags($languages, $items, $item_tax_key, $program_id);
}

if ($filter_tags == "all" || strstr($filter_tags, "kasvatustavoitteet")) {
	//Kasvatustavoitteet
	$item_tax_key = 'growth_target';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $growth_target_tags = [];
  foreach(get_terms('pof_tax_growth_target', array('hide_empty' => false)) as $item) {
    $growth_target_tags[$item->slug] = $item->name;
  }

  foreach($items as $item_key => $item_name) {
    if(!array_key_exists($item_key, $growth_target_tags)) {
      unset($items[$item_key]);
    }
  }
	$ret->kasvatustavoitteet = pof_pages_get_tags($languages, $items, $item_tax_key, $program_id);
}

if ($filter_tags == "all" || strstr($filter_tags, "johtamistaidot")) {
	//Kasvatustavoitteet
	$item_tax_key = 'leadership';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $leadership_tags = [];
  foreach(get_terms('pof_tax_leadership', array('hide_empty' => false)) as $item) {
    $leadership_tags[$item->slug] = $item->name;
  }

  foreach($items as $item_key => $item_name) {
    if(!array_key_exists($item_key, $leadership_tags)) {
      unset($items[$item_key]);
    }
  }
	$ret->johtamistaidot = pof_pages_get_tags($languages, $items, $item_tax_key, $program_id);
}

if ($filter_tags == "all" || strstr($filter_tags, "aktiviteettipaketin_ylakasite")) {
	//Aktiviteettipaketin ylakasite
	$item_tax_key = 'taskgroup_term';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->aktiviteettipaketin_ylakasite = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "aktiviteetin_ylakasite")) {
	//Aktiviteetin yläkäsite
	$item_tax_key = 'task_term';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->aktiviteetin_ylakasite = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "yleiset")) {
	//Yleiset
	$item_tax_key = 'common';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->yleiset = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "haku")) {
	//Haku
	$item_tax_key = 'search';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->haku = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "api_type")) {
	//Api type
	$item_tax_key = 'apitype';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $common_items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, 0);
	$ret->api_type = merge_items(
    pof_pages_get_tags($languages, $items, $item_tax_key, $program_id),
    pof_pages_get_tags($languages, $common_items, $item_tax_key, 0)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "teemat")) {
	//Teemat
	$item_tax_key = 'theme';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key, false, $program_id);
  $theme_tags = [];
  foreach(get_terms('pof_tax_theme', array('hide_empty' => false)) as $item) {
    $theme_tags[$item->slug] = $item->name;
  }

  foreach($items as $item_key => $item_name) {
    if(!array_key_exists($item_key, $theme_tags)) {
      unset($items[$item_key]);
    }
  }

	$ret->teemat = pof_pages_get_tags($languages, $items, $item_tax_key, $program_id);
}

echo json_encode($ret);

?>
