<?php


/* Helpers */
function pof_settings_get_google_api_certificate() {
	$filename = esc_attr( get_option('pof_settings_google_api_certificate_name') );
	return file_get_contents(plugin_dir_path( __FILE__ ) . 'certificates/' . $filename);
}

function pof_settings_get_google_api_user() {
	return esc_attr( get_option('pof_settings_google_api_user') );
}

function pof_settings_get_google_api_password() {
	return esc_attr( get_option('pof_settings_google_api_password') );
}

function pof_settings_get_suggestions_emails() {
	return esc_attr( get_option('pof_settings_suggestions_emails') );
}

function pof_settings_get_all_languages($use_cache = true) {
	global $pof_settings_langs;

	if (   $use_cache
		&& isset($pof_settings_langs)
		&& is_array($pof_settings_langs)
		&& !empty($pof_settings_langs)
		&& count($pof_settings_langs) < 1) {

		return $pof_settings_langs;
	}



	global $wpdb;
	$languages_res = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . pof_settings_get_table_name_languages() . "
		ORDER BY id
		"
	);

	$pof_settings_langs = $languages_res;


	return $languages_res;
}

function pof_settigs_get_active_lang_codes() {
	global $pof_settings_langs;

	$toret = array();

	if (   !isset($pof_settings_langs)
		|| empty($pof_settings_langs)
		|| count($pof_settings_langs) < 1) {
		$pof_settings_langs = pof_settings_get_all_languages(false);
	}

	foreach ($pof_settings_langs as $lang) {
		if ($lang->is_active || $lang->is_default) {
			array_push($toret, $lang->lang_code);
		}
	}

	return $toret;
}