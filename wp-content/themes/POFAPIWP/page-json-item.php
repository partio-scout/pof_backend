<?php
/*
Template Name: JSON item
*/

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

$post_guid = $_GET["postGUID"];

$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	'meta_key' => 'post_guid',
	'meta_value' => $post_guid,
  'orderby' => 'menu_order'
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
$tree_array_orig = pof_get_parent_tree($mypost, $tree_array);

$tree_array = array_reverse($tree_array_orig);


switch ($post_type) {
	case "program":
		$post_class = $classProgram;
	break;
	case "agegroup":
		$post_class = $classAgegroup;
		$agegroup = $mypost;
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

if($post_type == 'task') {
	$jsonItem->secondary_taskgroups = pof_get_additional_taskgroups($mypost->ID);
}

if (empty($tree_array)) {
	$jsonItem->parents = array();
} else {
	$jsonItem->parents = pof_output_parents_arr_json($tree_array);
}

$jsonItem = getJsonItemBaseDetailsItem($jsonItem, $mypost);

$lang = "FI";
$lang_lowercase = "fi";

if (!empty($_GET["lang"])) {
    $langs = pof_settings_get_active_lang_codes();

    if (in_array(strtolower($_GET["lang"]), $langs)) {
        $lang = strtoupper($_GET["lang"]);
        $lang_lowercase = strtolower($_GET["lang"]);
    }
}

$title = $mypost->post_title;
$ingress = get_post_meta($mypost->ID, "ingress", true);
$content = $mypost->post_content;

if ($lang != "FI") {
	$title = get_post_meta($mypost->ID, "title_".$lang_lowercase, true);
	$ingress = get_post_meta($mypost->ID, "ingress_".$lang_lowercase, true);
	$content = get_post_meta($mypost->ID, "content_".$lang_lowercase, true);
}

switch ($post_type) {
	case "program":
		$jsonItem = getJsonItemDetailsProgram($jsonItem, $mypost);
	break;
	case "agegroup":
		$jsonItem = getJsonItemDetailsAgegroup($jsonItem, $mypost, $lang_lowercase);
	break;
	case "taskgroup":
		$jsonItem = getJsonItemDetailsTaskgroup($jsonItem, $mypost, $lang_lowercase);
		$mandatory_tasks = getMandatoryTasksForTaskGroup($mypost->ID);
		$jsonItem->mandatory_task_hashes = implode(",", $mandatory_tasks->hashes);

		$subtask_term = getJsonTaskTerm(get_post_meta($mypost->ID, "taskgroup_subtask_term", true), $lang_lowercase);
		if (empty($subtask_term)) {
			foreach ($tree_array_orig as $tree_item) {
				$subtask_term = getJsonTaskTerm(get_post_meta($tree_item->ID, "taskgroup_subtask_term", true), $lang_lowercase);

				if ($subtask_term) {
					$jsonItem->subtask_term = $subtask_term;
					break;
				}
			}
		} else {
			$jsonItem->subtask_term = $subtask_term;
		}

		$subtaskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($mypost->ID, "taskgroup_subtaskgroup_term", true), $lang_lowercase);
		if (empty($subtaskgroup_term)) {
			foreach ($tree_array_orig as $tree_item) {
				$subtaskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($tree_item->ID, "taskgroup_subtaskgroup_term", true), $lang_lowercase);

				if ($subtaskgroup_term) {
					$jsonItem->subtaskgroup_term = $subtaskgroup_term;
					break;
				}
			}
		} else {
			$jsonItem->subtaskgroup_term = $subtaskgroup_term;
		}

		$taskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($mypost->ID, "taskgroup_taskgroup_term", true), $lang_lowercase);
		if (empty($taskgroup_term)) {
			foreach ($tree_array_orig as $tree_item) {
				$taskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($tree_item->ID, "taskgroup_subtaskgroup_term", true), $lang_lowercase);

				if ($taskgroup_term) {
					$jsonItem->taskgroup_term = $taskgroup_term;
					break;
				}
			}
		} else {
			$jsonItem->taskgroup_term = $taskgroup_term;
		}

    $execution_order = get_post_meta($mypost->ID, "execution_order", true);

    $jsonItem->execution_order = $execution_order != 'empty' ? $execution_order : '';
    $jsonItem->execution_order_code = $execution_order != 'empty' ? get_post_meta($mypost->ID, "execution_order_code", true) : '';

	break;
	case "task":
		$jsonItem = getJsonItemDetailsTask($jsonItem, $mypost, $lang_lowercase);

		$task_term = getJsonTaskTerm(get_post_meta($mypost->ID, "task_task_term", true), $lang_lowercase);
		if (empty($task_term)) {
			foreach ($tree_array_orig as $tree_item) {
				$task_term = getJsonTaskTerm(get_post_meta($tree_item->ID, "taskgroup_subtask_term", true), $lang_lowercase);

				if ($task_term) {
					$jsonItem->task_term = $task_term;
					break;
				}
			}
		} else {
			$jsonItem->task_term = $task_term;
		}

		$jsonItem->level = pof_normalize_task_level(get_post_meta($mypost->ID, "task_level", true));
		$jsonItem->leader_tasks = get_post_meta($mypost->ID, "leader_tasks_".$lang_lowercase, true);

    $execution_order = get_post_meta($mypost->ID, "execution_order", true);

    $jsonItem->execution_order = $execution_order != 'empty' ? $execution_order : '';
    $jsonItem->execution_error = $execution_order != 'empty' ? get_post_meta($mypost->ID, "execution_error", true) : '';

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

if ($mypost->post_type == 'pof_post_task') {
    $jsonItem->tags = get_post_tags_JSON($mypost->ID, $agegroup_id, $lang_lowercase);
}

if ($mypost->post_type == 'pof_post_taskgroup') {
    $jsonItem->tags = get_post_tags_taskgroup_JSON($mypost->ID, $agegroup_id, $lang_lowercase);
}

$jsonItem->images = get_post_images_JSON($mypost->ID);
$jsonItem->additional_content = get_post_additional_content_JSON($mypost->ID, $lang_lowercase);

/*
 * Check the menu_order of siblings to determine what order number this specific item should have
 */
$siblings = pof_get_siblings($mypost);
$sibling_order = array();

for($i=0; $i < count($siblings); $i++) {
  array_push($sibling_order, array('ID' => $siblings[$i]->ID, 'menu_order' => $siblings[$i]->menu_order));
}

array_push($sibling_order, array('ID' => $mypost->ID, 'menu_order' => $mypost->menu_order));

usort($sibling_order, function ($item1, $item2) {
    if ($item1['menu_order'] == $item2['menu_order']) return 0;
    return $item1['menu_order'] > $item2['menu_order'] ? -1 : 1;
});

foreach ($sibling_order as $key => $val) {
   if ($val['ID'] === $mypost->ID) {
       $order = $key;
   }
}

$jsonItem->order = $order;

echo json_encode($jsonItem);
?>