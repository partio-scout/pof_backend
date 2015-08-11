<?php
/*
Template Name: JSON Tags
*/

header('Content-type: application/json');

$ret = new stdClass();

$languages = pof_taxonomy_translate_get_languages();

function pof_pages_get_tags($languages, $items, $item_tax_key) 
{
	$items_arr = array();
	foreach ($languages as $lang_key => $lang) {
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


// Places
$items = pof_taxonomy_translate_get_places();
$item_tax_key = 'place_of_performance';
$ret->paikka = pof_pages_get_tags($languages, $items, $item_tax_key);

// Groupsizes
$items = pof_taxonomy_translate_get_groupsizes();
$item_tax_key = 'groupsize';
$ret->ryhmakoko = pof_pages_get_tags($languages, $items, $item_tax_key);

// Mandatory
$items = pof_taxonomy_translate_get_mandatory();
$item_tax_key = 'mandatory';
$ret->pakollisuus = pof_pages_get_tags($languages, $items, $item_tax_key);

//TaskDuration
$items = pof_taxonomy_translate_get_taskduration();
$item_tax_key = 'taskduration';
$ret->suoritus_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);

//TaskPreparationDuration
$items = pof_taxonomy_translate_get_taskpreparationduration();
$item_tax_key = 'taskpreparationduration';
$ret->suoritus_valmistelu_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);

//Equipments
$items = pof_taxonomy_translate_get_equpments();
$item_tax_key = 'equpments';
$ret->tarvikkeet = pof_pages_get_tags($languages, $items, $item_tax_key);

//Taitoalueet
$items = pof_taxonomy_translate_get_skillareas();
$item_tax_key = 'skillarea';
$ret->taitoalueet = pof_pages_get_tags($languages, $items, $item_tax_key);


echo json_encode($ret);

?>