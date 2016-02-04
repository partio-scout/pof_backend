<?php
/*
Template Name: JSON Searchpage
*/

header('Content-type: application/json');

$filter_tags = "all";

if (isset($_GET["tags"]) && trim($_GET["tags"]) != ''){
   $filter_tags = $_GET["tags"];
}

$ret = new stdClass();

$languages = pof_taxonomy_translate_get_languages();

function pof_pages_get_tags_searchpage($items, $item_tax_key) 
{
	$items_arr = array();
	foreach ($items as $item_key => $item) {
        if ($item->enabled) {
            array_push($items_arr, $item_key);
        }
    }
	return $items_arr;
}

if ($filter_tags == "all" || strstr($filter_tags, "paikka")) {
	// Places
	$item_tax_key = 'place_of_performance';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->paikka = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "ryhmakoko")) {
	// Groupsizes
	$item_tax_key = 'groupsize';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->ryhmakoko = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "pakollisuus")) {
	// Mandatory
	$item_tax_key = 'mandatory';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->pakollisuus = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_kesto")) {
	//TaskDuration
	$item_tax_key = 'taskduration';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->suoritus_kesto = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_valmistelu_kesto")) {
	//TaskPreparationDuration
	$item_tax_key = 'taskpreparationduration';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->suoritus_valmistelu_kesto = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "tarvikkeet")) {
	//Equipments
	$item_tax_key = 'equpment';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->tarvikkeet = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "taitoalueet")) {
	//Taitoalueet
	$item_tax_key = 'skillarea';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->taitoalueet = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

if ($filter_tags == "all" || strstr($filter_tags, "yleiset")) {
	//Yleiset
	$item_tax_key = 'common';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key);
	$ret->yleiset = pof_pages_get_tags_searchpage($items, $item_tax_key);
}

echo json_encode($ret);

?>