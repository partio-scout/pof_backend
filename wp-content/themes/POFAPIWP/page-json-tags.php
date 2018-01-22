<?php
/*
Template Name: JSON Tags
*/

header('Content-type: application/json');

$filter_langs = "all";
$filter_tags = "all";


if (isset($_GET["langs"]) && trim($_GET["langs"]) != ''){
   $filter_langs = $_GET["langs"];
}
if (isset($_GET["tags"]) && trim($_GET["tags"]) != ''){
   $filter_tags = $_GET["tags"];
}

$ret = new stdClass();

$languages = pof_taxonomy_translate_get_languages();

function pof_pages_get_tags($languages, $items, $item_tax_key)
{
	global $filter_langs;
	$items_arr = array();
	foreach ($languages as $lang_key => $lang) {
		if ($filter_langs != "all" && !strstr($filter_langs, $lang_key)) {
			continue;
		}
		$tmp = new stdClass();
		$tmp->lang = $lang_key;
		$tmp->items = array();
		foreach ($items as $item_key => $item) {
			$tmp_item = new stdClass();
			$tmp_item->key = $item_key;

			$tmp_name = pof_taxonomy_translate_get_translation($item_tax_key, $item_key, 0, $lang_key, false);

			if (!empty($tmp_name)) {
				$tmp_item->value = $tmp_name[0]->content;
			} else {
				$tmp_item->value = $item;
			}
			array_push($tmp->items, $tmp_item);
		}
		array_push($items_arr, $tmp);
	}
	return $items_arr;
}

if ($filter_tags == "all" || strstr($filter_tags, "paikka")) {
	// Places
	$item_tax_key = 'place_of_performance';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->paikka = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "ryhmakoko")) {
	// Groupsizes
	$item_tax_key = 'groupsize';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->ryhmakoko = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "pakollisuus")) {
	// Mandatory
	$item_tax_key = 'mandatory';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->pakollisuus = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_kesto")) {
	//TaskDuration
	$item_tax_key = 'taskduration';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->suoritus_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_valmistelu_kesto")) {
	//TaskPreparationDuration
	$item_tax_key = 'taskpreparationduration';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->suoritus_valmistelu_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "tarvikkeet")) {
	//Equipments
	$item_tax_key = 'equpments';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->tarvikkeet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "taitoalueet")) {
	//Taitoalueet
	$item_tax_key = 'skillarea';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->taitoalueet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "kasvatustavoitteet")) {
	//Kasvatustavoitteet
	$item_tax_key = 'growth_target';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->kasvatustavoitteet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "aktiviteettipaketin_ylakasite")) {
	//Kasvatustavoitteet
	$item_tax_key = 'taskgroup_term';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->kasvatustavoitteet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "aktiviteettipaketin_ylakasite")) {
	//aktiviteettipaketin yläkäsite
	$item_tax_key = 'taskgroup_term';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->kasvatustavoitteet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "aktiviteetin_ylakasite")) {
	//Aktiviteetin yläkäsite
	$item_tax_key = 'task_term';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->kasvatustavoitteet = pof_pages_get_tags($languages, $items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "yleiset")) {
	//Yleiset
	$item_tax_key = 'common';
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->yleiset = pof_pages_get_tags($languages, $items, $item_tax_key);
}

echo json_encode($ret);

?>