<?php
/*
Template Name: JSON Searchpage
*/

header('Content-type: application/json');

$filter_tags = "all";
$post_guid = "";
$program = 0;

if (isset($_GET["tags"]) && trim($_GET["tags"]) != ''){
   $filter_tags = $_GET["tags"];
}

if (!empty($_GET["postGUID"])) {
	$post_guid = $_GET["postGUID"];
} else if (!empty($_POST["postGUID"])) {
	$post_guid = $_POST["postGUID"];
}

$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_program' ),
);

if (strlen($post_guid) > 0) {
  $args['meta_key'] = 'post_guid';
  $args['meta_value'] = $post_guid;
}

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$program = $the_query->post->ID;
	}
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
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->paikka = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "ryhmakoko")) {
	// Groupsizes
	$item_tax_key = 'groupsize';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->ryhmakoko = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_groupsizes_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "pakollisuus")) {
	// Mandatory
	$item_tax_key = 'mandatory';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->pakollisuus = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_kesto")) {
	//TaskDuration
	$item_tax_key = 'taskduration';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->suoritus_kesto = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "suoritus_valmistelu_kesto")) {
	//TaskPreparationDuration
	$item_tax_key = 'taskpreparationduration';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->suoritus_valmistelu_kesto = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "tarvikkeet")) {
	//Equipments
	$item_tax_key = 'equpment';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->tarvikkeet = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "taitoalueet")) {
	//Taitoalueet
	$item_tax_key = 'skillarea';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->taitoalueet = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "kasvatustavoitteet")) {
	//Kasvatustavoitteet
	$item_tax_key = 'growth_target';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->kasvatustavoitteet = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

if ($filter_tags == "all" || strstr($filter_tags, "johtamistaito")) {
	//Johtamistaito
	$item_tax_key = 'leadership';
	$items = pof_taxonomy_searchpage_get_items_by_taxonomy_base_key($item_tax_key, false, $program);
	$ret->johtamistaito = array(
    "fields" => pof_pages_get_tags_searchpage($items, $item_tax_key),
    "type" => get_option('taxonomy_searchoptions_' . $item_tax_key . '_' . $program)
  );
}

echo json_encode($ret);

?>