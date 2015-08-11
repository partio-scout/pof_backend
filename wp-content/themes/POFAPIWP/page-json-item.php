<?php
/*
Template Name: JSON item
*/

header('Content-type: application/json');

$post_guid = $_GET["postGUID"];

$args = array(
	'numberposts' => -1,
	'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	'meta_key' => 'post_guid',
	'meta_value' => $post_guid
);

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$mypost = $the_query->post;
	}
}

$classProgram = "POFITEM\\program";
$classAgegroup = "POFITEM\\agegroup";
$classTaskGroup = "POFITEM\\taskgroup";
$classTask = "POFITEM\\task";


$post_type = str_replace('pof_post_', '', $mypost->post_type);

$post_class = $classTask;

$agegroup = null;

$tree_array = array();
$tree_array = array_reverse(pof_get_parent_tree($mypost, $tree_array));

switch ($post_type) {
	case "program":
		$post_class = $classProgram;
	break;
	case "agegroup":
		$post_class = $classAgegroup;
		$agegroup = $my_post;
	break;
	case "taskgroup":
		$post_class = $classTaskGroup;
		$agegroup = pof_get_agegroup_from_tree_arr($tree_array);
	break;
	case "task":
		$post_class = $classTask;
		$agegroup = pof_get_agegroup_from_tree_arr($tree_array);
	break;
}

$jsonItem = new $post_class;
$jsonItem->type = $post_type;

if (empty($tree_array)) {
	$jsonItem->parents = array();
} else {
	$jsonItem->parents = pof_output_parents_arr_json($tree_array);
}

$jsonItem = getJsonItemBaseDetailsItem($jsonItem, $mypost);


$lang = "FI";

if (!empty($_GET["lang"])) {
	switch (strtolower($_GET["lang"])) {
		case "fi":
			$lang = "FI";
		break;
		case "sv":
			$lang = "SV";
		break;
		case "en":
			$lang = "EN";
		break;
	}
}

$title = $mypost->post_title;
$ingress = get_field("ingress");
$content = $mypost->post_content;

if ($lang != "FI") {
	$title = get_field("title_".strtolower($lang));
	$ingress = get_field("ingress_".strtolower($lang));
	$content = get_field("content_".strtolower($lang));
}

switch ($post_type) {
	case "program":
		$jsonItem = getJsonItemDetailsProgram($jsonItem, $mypost);
	break;
	case "agegroup":
		$jsonItem = getJsonItemDetailsAgegroup($jsonItem, $mypost);
	break;
	case "taskgroup":
		$jsonItem = getJsonItemDetailsTaskgroup($jsonItem, $mypost);


		$mandatory_tasks = getMandatoryTasksForTaskGroup($mypost->ID);

		$jsonItem->mandatory_task_hashes = implode(",", $mandatory_tasks->hashes);

	break;
	case "task":
		$jsonItem = getJsonItemDetailsTask($jsonItem, $mypost);
	break;
}

$agegroup_id = 0;
if (!empty($agegroup)) {
	$agegroup_id = $agegroup->ID;
}

$jsonItem->title = $title;
$jsonItem->ingress = $ingress;
$jsonItem->content = $content;
$jsonItem->lang = $lang;
$jsonItem->tags = get_post_tags_JSON($mypost->ID, $agegroup_id, strtolower($lang));
$jsonItem->images = get_post_images_JSON($mypost->ID);
$jsonItem->additional_content = get_post_additional_content_JSON($mypost->ID);

echo json_encode($jsonItem);



?>