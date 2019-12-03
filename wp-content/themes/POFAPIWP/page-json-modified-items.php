<?php
/*
Template Name: JSON Modified items
*/

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

$ret = array();

$classProgram = "POFITEM\\program";
$classAgegroup = "POFITEM\\agegroup";
$classTaskGroup = "POFITEM\\taskgroup";
$classTask = "POFITEM\\task";

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


$startDate = null;

if (array_key_exists('startDate', $_GET)) {
    $startDate = $_GET["startDate"];
}

if (empty($startDate)) {
    $startDate = '1 day ago';
}

$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	'date_query' => array(
        array(
            'column' => 'post_modified',
            'after' => $startDate,
        ),
    )
);

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$mypost = $the_query->post;

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

        if (empty($tree_array)) {
            $jsonItem->parents = array();
        } else {
            $jsonItem->parents = pof_output_parents_arr_json($tree_array);
        }

        $jsonItem = getJsonItemBaseDetailsItem($jsonItem, $mypost);




        $title = $mypost->post_title;
        $ingress = get_post_meta($mypost->ID, "ingress", true);
        $content = $mypost->post_content;
        $content = $mypost->post_content;

        if ($lang != "FI") {
            $title = get_post_meta($mypost->ID, "title_".strtolower($lang), true);
            $ingress = get_post_meta($mypost->ID, "ingress_".strtolower($lang), true);
            $content = get_post_meta($mypost->ID, "content_".strtolower($lang), true);
        }

        switch ($post_type) {
            case "program":
                $jsonItem = getJsonItemDetailsProgram($jsonItem, $mypost);
                break;
            case "agegroup":
                $jsonItem = getJsonItemDetailsAgegroup($jsonItem, $mypost, strtolower($lang));
                break;
            case "taskgroup":
                $jsonItem = getJsonItemDetailsTaskgroup($jsonItem, $mypost, strtolower($lang));


                $mandatory_tasks = getMandatoryTasksForTaskGroup($mypost->ID);

                $jsonItem->mandatory_task_hashes = implode(",", $mandatory_tasks->hashes);


                $subtask_term = getJsonTaskTerm(get_post_meta($mypost->ID, "taskgroup_subtask_term", true));
                if (empty($task_term)) {
                    foreach ($tree_array_orig as $tree_item) {
                        $task_term = getJsonTaskTerm(get_post_meta($tree_item->ID, "taskgroup_subtask_term", true));

                        if ($task_term) {
                            $jsonItem->subtask_term = $task_term;
                            break;
                        }
                    }
                } else {
                    $jsonItem->subtask_term = $task_term;
                }

                $subtaskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($post->ID, "taskgroup_subtaskgroup_term", true), strtolower($lang));
                if (empty($subtaskgroup_term)) {
                    foreach ($tree_array_orig as $tree_item) {
                        $subtaskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($tree_item->ID, "taskgroup_subtaskgroup_term", true), strtolower($lang));

                        if ($task_term) {
                            $jsonItem->subtaskgroup_term = $subtaskgroup_term;
                            break;
                        }
                    }
                } else {
                    $jsonItem->subtaskgroup_term = $subtaskgroup_term;
                }

                $taskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($post->ID, "taskgroup_taskgroup_term", true), strtolower($lang));
                if (empty($taskgroup_term)) {
                    foreach ($tree_array_orig as $tree_item) {
                        $taskgroup_term = getJsonSubtaskgroupTerm(get_post_meta($tree_item->ID, "taskgroup_taskgroup_term", true), strtolower($lang));

                        if ($task_term) {
                            $jsonItem->taskgroup_term = $taskgroup_term;
                            break;
                        }
                    }
                } else {
                    $jsonItem->taskgroup_term = $taskgroup_term;
                }

                break;
            case "task":
                $jsonItem = getJsonItemDetailsTask($jsonItem, $mypost, strtolower($lang));

                $task_term = getJsonTaskTerm(get_post_meta($mypost->ID, "task_task_term", true), strtolower($lang));
                if (empty($task_term)) {
                    foreach ($tree_array_orig as $tree_item) {
                        $task_term = getJsonTaskTerm(get_post_meta($tree_item->ID, "taskgroup_subtask_term", true), strtolower($lang));

                        if ($task_term) {
                            $jsonItem->task_term = $task_term;
                            break;
                        }
                    }
                } else {
                    $jsonItem->task_term = $task_term;
                }


                $jsonItem->level = pof_normalize_task_level(get_post_meta($mypost->ID, "task_level", true));
                $jsonItem->leader_tasks = get_post_meta($mypost->ID, "leader_tasks_".strtolower($lang), true);


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
            $jsonItem->tags = get_post_tags_JSON($mypost->ID, $agegroup_id, strtolower($lang));
        }

        $jsonItem->images = get_post_images_JSON($mypost->ID);
        $jsonItem->additional_content = get_post_additional_content_JSON($mypost->ID, strtolower($lang));

        array_push($ret, $jsonItem);

	}
}






echo json_encode($ret);



?>