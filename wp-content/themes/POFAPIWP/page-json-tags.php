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
$item_tax_key = 'place_of_performance';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->paikka = pof_pages_get_tags($languages, $items, $item_tax_key);

// Groupsizes
$item_tax_key = 'groupsize';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->ryhmakoko = pof_pages_get_tags($languages, $items, $item_tax_key);

// Mandatory
$item_tax_key = 'mandatory';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->pakollisuus = pof_pages_get_tags($languages, $items, $item_tax_key);

//TaskDuration
$item_tax_key = 'taskduration';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->suoritus_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);

//TaskPreparationDuration
$item_tax_key = 'taskpreparationduration';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->suoritus_valmistelu_kesto = pof_pages_get_tags($languages, $items, $item_tax_key);

//Equipments
$item_tax_key = 'equpments';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->tarvikkeet = pof_pages_get_tags($languages, $items, $item_tax_key);

//Taitoalueet
$item_tax_key = 'skillarea';
$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($item_tax_key);
$ret->taitoalueet = pof_pages_get_tags($languages, $items, $item_tax_key);


echo json_encode($ret);

?>