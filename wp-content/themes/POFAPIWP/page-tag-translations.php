<?php
/*
Template Name: Tags view
*/




function pof_taxonomy_translate_form_view() {

    $taxonomies = array();

    $taxonomies['place_of_performance'] = "Suorituspaikat";
    $taxonomies['groupsize'] = "Ryhm&auml;koot";
    $taxonomies['mandatory'] = "Pakollisuus";
    $taxonomies['taskduration'] = "Aktiviteetin kestot";
    $taxonomies['taskpreaparationduration'] = "Aktiviteetin valmistelun kestot";
    $taxonomies['equpment'] = "Tarvikkeet";
    $taxonomies['skillarea'] = "Taitoalueet";
    $taxonomies['growth_target'] = "Kasvatustavoitteen avainsanat";
    $taxonomies['taskgroup_term'] = "Aktiviteettipaketin yl&auml;k&auml;site";



    global $wpdb;
	$table_name = pof_taxonomy_translate_get_table_name();

	$languages = pof_taxonomy_translate_get_languages();
	$agegroups = pof_taxonomy_translate_get_agegroups();

	$selected_lang = 'fi';

    $taxonomy_base_key = "place_of_performance";

	if (isset($_POST['language'])) {
		$selected_lang = $_POST['language'];
	}

    if (isset($_POST['taxonomy'])) {
		$taxonomy_base_key = $_POST['taxonomy'];
	}

    $items = pof_taxonomy_translate_get_items_by_taxonomy_base_key($taxonomy_base_key);

    $title = $taxonomies[$taxonomy_base_key];

	echo '<div class="wrap">';
	echo '<h1>'.$title.'</h1>';
	echo '<form method="post" action="">';
    echo 'Valitse taksonomia:';
	echo '<select name="taxonomy">';
	foreach ($taxonomies as $taxonomy_key => $taxonomy_title) {
		if ($taxonomy_key == $taxonomy_base_key) {
			echo '<option selected="selected" value="'.$taxonomy_key.'">'.$taxonomy_title.'</option>';
		} else {
			echo '<option value="'.$taxonomy_key.'">'.$taxonomy_title.'</option>';
		}
	}
	echo '</select>';
	echo '<br />';
	echo 'Valitse kieli:';
	echo '<select name="language">';
	foreach ($languages as $lang_key => $lang) {
		if ($lang_key == $selected_lang) {
			echo '<option selected="selected" value="'.$lang_key.'">'.$lang.'</option>';
		} else {
			echo '<option value="'.$lang_key.'">'.$lang.'</option>';
		}
	}
	echo '</select>';
	echo '<br />';
	echo '<input type="submit" value="Vaihda" />';
	echo '</form>';
	echo '<br /><br /><br />';
	echo '<h2>Kieli: '.$languages[$selected_lang].' ('.$selected_lang.')</h2>';
	echo '<table cellpadding="2" cellspacing="2" border="2">';
	echo '<thead>';
	echo '<tr>';
    echo '<th></th>';
	foreach ($agegroups as $agegroup) {
		echo '<th><h2>'.$agegroup->title.'</h2></th>';
	}
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($items as $tmp_key => $tmp_title) {
		echo '<tr>';
		echo '<td>'.$tmp_title.'<br /> ('.$tmp_key.')</td>';
		foreach ($agegroups as $agegroup) {

			echo '<td>';
			$translation = pof_taxonomy_translate_get_translation($taxonomy_base_key,$tmp_key, $agegroup->id, $selected_lang, false);

			$translation_content = "";
			if (!empty($translation)) {
				$translation_content = $translation[0]->content;
			}

			echo $translation_content;
			echo '</td>';
		}
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
}

get_header(); ?>

<div id="primary" class="content-area content-area-wide">
    <main id="main" class="site-main site-main-wide" role="main">
        <header class="entry-header">
            <h1 class="entry-title">Tagit</h1>
        </header>
        <?php pof_taxonomy_translate_form_view(); ?>
        </main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
