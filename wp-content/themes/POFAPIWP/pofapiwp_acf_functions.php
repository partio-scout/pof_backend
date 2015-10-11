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

add_filter('acf/load_field/name=pof_suggestion_language', 'acf_load_pof_language_choices');