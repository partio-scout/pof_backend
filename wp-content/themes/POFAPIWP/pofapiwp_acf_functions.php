<?php


function acf_load_pof_language_choices( $field ) {
	$languages = pof_settings_get_all_languages();

	if (count($languages) > 1) {
		$field['choices'] = array();

		foreach ($languages as $lang) {
			$field['choices'][ $lang->lang_code ] = $lang->lang_title;
		}
	}

	return $field;
}

add_filter('acf/load_field/name=pof_suggestion_lang', 'acf_load_pof_language_choices');
add_filter('acf/load_field/name=program_lang', 'acf_load_pof_language_choices');


function acf_load_pof_taskgroup_terms( $field ) {
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key("taskgroup_term");
	
	if (count($items) > 1) {
		$field['choices'] = array();

		foreach ($items as $item_key => $item) {
			if (strstr($item_key, "_single")) {
				$field['choices'][ str_replace("_single", "", $item_key) ] = $item;
			}
		}
	}

	return $field;
}

add_filter('acf/load_field/name=agegroup_subtaskgroup_term', 'acf_load_pof_taskgroup_terms');
add_filter('acf/load_field/name=taskgroup_subtaskgroup_term', 'acf_load_pof_taskgroup_terms');
add_filter('acf/load_field/name=taskgroup_taskgroup_term', 'acf_load_pof_taskgroup_terms');


function acf_load_pof_task_terms( $field ) {
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key("task_term");
	
	if (count($items) > 1) {
		$field['choices'] = array();

		foreach ($items as $item_key => $item) {
			if (strstr($item_key, "_single")) {
				$field['choices'][ str_replace("_single", "", $item_key) ] = $item;
			}
		}
	}

	return $field;
}

add_filter('acf/load_field/name=taskgroup_subtask_term', 'acf_load_pof_task_terms');
add_filter('acf/load_field/name=task_task_term', 'acf_load_pof_task_terms');

function acf_load_pof_task_placesofperformance( $field ) {
	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key("place_of_performance");
	
	if (count($items) > 1) {
		$field['choices'] = array();

		foreach ($items as $item_key => $item) {
			$field['choices'][ str_replace("place_of_performance::", "", $item_key) ] = $item;
		}
	}

	return $field;
}

add_filter('acf/load_field/name=task_place_of_performance', 'acf_load_pof_task_placesofperformance');

function acf_load_pof_task_duration( $field ) {

	$items = pof_taxonomy_translate_get_items_by_taxonomy_base_key("taskduration");

	if (count($items) > 1) {
		$field['choices'] = array();

		foreach ($items as $item_key => $item) {
			$field['choices'][ str_replace("taskduration::", "", $item_key) ] = $item;
		}
	}

	return $field;
}

add_filter('acf/load_field/name=task_duration', 'acf_load_pof_task_duration');
