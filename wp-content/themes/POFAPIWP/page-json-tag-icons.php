<?php
/*
Template Name: JSON Tag icons
*/

header('Content-type: application/json');

$ret = new stdClass();

$agegroups = pof_taxonomy_icons_get_agegroups();

function pof_pages_get_tag_icons($agegroups, $items, $item_tax_key) 
{
	$items_arr = array();
	foreach ($agegroups as $agegroup_key => $agegroup) {
		$tmp = new stdClass();
		$tmp->agegroup = $agegroup->title;
		$tmp->items = array();
		$agegroup_id = $agegroup->id;
		foreach ($items as $item_key => $item) {
			$tmp_item = new stdClass();
			$tmp_item->key = $item_key;

			$icon = pof_taxonomy_icons_get_icon($item_tax_key, $item_key, $agegroup_id, false);

			if (!empty($icon)) {
				$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
				if (!empty($icon_src)) {
					$tmp_item->icon = $icon_src[0];
				}
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
$ret->paikka = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

// Groupsizes
$items = pof_taxonomy_translate_get_groupsizes();
$item_tax_key = 'groupsize';
$ret->ryhmakoko = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

// Mandatory
$items = pof_taxonomy_translate_get_mandatory();
$item_tax_key = 'mandatory';
$ret->pakollisuus = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

//TaskDuration
$items = pof_taxonomy_translate_get_taskduration();
$item_tax_key = 'taskduration';
$ret->suoritus_kesto = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

//TaskPreparationDuration
$items = pof_taxonomy_translate_get_taskpreparationduration();
$item_tax_key = 'taskpreparationduration';
$ret->suoritus_valmistelu_kesto = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

//Equipments
$items = pof_taxonomy_translate_get_equpments();
$item_tax_key = 'equpments';
$ret->tarvikkeet = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);

//Taitoalueet
$items = pof_taxonomy_translate_get_skillareas();
$item_tax_key = 'skillarea';
$ret->taitoalueet = pof_pages_get_tag_icons($agegroups, $items, $item_tax_key);


echo json_encode($ret);

?>