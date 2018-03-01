<?php
/*
Template Name: JSON Full
*/

header('Content-type: application/json');

$lastModified = strtotime('2010-01-01');
$lastModifiedBy = 0;
$lastModifiedByName = "";

$root = get_field("suoritusohjelma");

$pof_settings_lastupdate_overwrite = pof_settings_get_lastupdate_overwrite();

$pof_settings_fulljson_cache_ttl = pof_settings_get_fulljson_cache_ttl();

$post_id = $root->ID;

$post_guid = "";


if (!empty($_GET["postGUID"])) {
	$post_guid = $_GET["postGUID"];
} else if (!empty($_POST["postGUID"])) {
	$post_guid = $_POST["postGUID"];
}

$forceRun = false;
if (   (!empty($_POST["forceRun"]) && $_POST["forceRun"] == "1")
    || (!empty($_GET["forceRun"]) && $_GET["forceRun"] == "1")) {
    $forceRun = true;
}

if (strlen($post_guid) >0) {

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_program' ),
		'meta_key' => 'post_guid',
		'meta_value' => $post_guid
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_id = $the_query->post->ID;
		}
	}
}

$filepath = get_home_path() . "wp-content/cache/pof/pof-full-json-".$post_id.".json";
if (!file_exists($filepath)) {
    $forceRun = true;
}

$cache_last_run = (int)get_post_meta( $post_id, 'full_json_last_save', true);
$cache_run_started = (int)get_post_meta( $post_id, 'full_json_cache_run_started', true);

if(!$forceRun) {


    readfile($filepath);
    flush();

    if (($cache_last_run + $pof_settings_fulljson_cache_ttl) < time()) {
        $time = new DateTime();
        $time->modify('-15 minutes');

        // if cache is older than ttl and cache is not running now (or has taken over 10 minutes), start cache run again
        if ($cache_last_run > $cache_run_started
            || $cache_run_started < $time->format('U')) {

            $params = array(
                "postGUID" => $post_guid,
                "forceRun" => "1"
            );

            $absolute_url = pof_full_url( $_SERVER );

            update_post_meta($post_id, 'full_json_cache_run_started', time());
            pof_curl_post_async($absolute_url, $params);
        }
    }

} else {

    update_post_meta($post_id, 'full_json_cache_run_started', time());
    $tree = getJsonTree($post_id);

    $tree_hash = hash("md5", serialize($tree));

    $tree->program[0]->treeDetails = new stdClass();

    if ($pof_settings_lastupdate_overwrite == null) {
        $tree->program[0]->treeDetails->lastModified = date("Y-m-d H:i:s",$lastModified);
    } else {
        $tree->program[0]->treeDetails->lastModified = $pof_settings_lastupdate_overwrite;
    }

    $tree->program[0]->treeDetails->lastModifiedBy = getLastModifiedBy($lastModifiedBy);
    $tree->program[0]->treeDetails->hash = $tree_hash;

    $jsconContent = json_encode($tree);

    $cache_last_run2 = (int)get_post_meta( $post_id, 'full_json_last_save', true);

    //make sure that there were no other processes that touched the file while this proces was doing it
    if ($cache_last_run == $cache_last_run2) {

        if (file_exists($filepath)) {
	        unlink($filepath);
        }

        $file2 = fopen($filepath, "w+");
        fputs($file2, $jsconContent);
        fclose($file2);
        echo $jsconContent;
    }

    update_post_meta($post_id, 'full_json_last_save', time());
}



function getJsonTree($root_id) {

	$classPrograms = "POFTREE\\programs";
	$classProgram = "POFTREE\\program";

	$root_post = get_post($root_id);

	pof_checkDatetime($root_post);


	$program = new $classProgram;

	$program = getJsonItemDetailsProgram($program, $root_post);

	$program->title = $root_post->post_title;

	$program = getJsonItemBaseDetails($program, $root_post);

	$program->agegroups = getJsonAgeGroups($root_post->ID);

	$programs = new $classPrograms;
	$programs->program[0] = $program;

	return $programs;
}


function getJsonAgeGroups($parent_id) {
	$classAgeGroup = "POFTREE\\agegroup";

	$childs = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_agegroup',
        'meta_query' => array(
            array(
                'key'			=> 'suoritusohjelma',
                'compare'		=> '=',
                'value'         => $parent_id
            )
        ),
        'order'				=> 'ASC',
        'orderby'			=> 'meta_value',
        'meta_key'			=> 'agegroup_min_age',
        'meta_type'			=> 'NUMERIC'
	);

	$the_query = new WP_Query( $args );

  $order_number = 0;

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			$child = new $classAgeGroup;
			$child = getJsonItemBaseDetails($child, $the_query->post);
			$child = getJsonItemDetailsAgegroup($child, $the_query->post, 'fi');
			$child->title = $the_query->post->post_title;
			$child->taskgroups = getJsonTaskGroups($the_query->post->ID);
      $child->order = $order_number;
      $order_number++;

			array_push($childs, $child);

		}
	}

	return $childs;

}

function getJsonTaskGroups($parent_id) {
	global $mandatory_task_guids;
	$classTaskGroup = "POFTREE\\taskgroup";

	$childs = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'orderby' => 'menu_order',
		'meta_key' => 'ikakausi',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

  $order_number = 0;

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			// try to avoid infinite loops
			if ($the_query->post->ID == $parent_id) {
				continue;
			}

			$child = new $classTaskGroup;
			$child = getJsonItemBaseDetails($child, $the_query->post);
			$child = getJsonItemDetailsTaskgroup($child, $the_query->post, 'fi');
			$child->title = $the_query->post->post_title;
      $child->order = $order_number;
      $order_number++;

			$child->taskgroups = getJsonTaskGroupsForTaskGroup($the_query->post->ID);


			$mandatory_task_guids = array();

			$child->tasks = getJsonTasks($the_query->post->ID);

			$child->mandatory_tasks = implode(",", $mandatory_task_guids);
			$mandatory_task_guids = array();
			array_push($childs, $child);

		}
	}

	return $childs;

}

function getJsonTaskGroupsForTaskGroup($parent_id) {
	global $mandatory_task_guids;
	$classTaskGroup = "POFTREE\\taskgroup";

	$childs = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'orderby' => 'menu_order',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_querysub = new WP_Query( $args );

  $order_number = 0;

	if( $the_querysub->have_posts() ) {
		while ( $the_querysub->have_posts() ) {
			$the_querysub->the_post();

			// try to avoid infinite loops
			if ($the_querysub->post->ID == $parent_id) {
				continue;
			}

			$child = new $classTaskGroup;
			$child = getJsonItemBaseDetails($child, $the_querysub->post);
			$child = getJsonItemDetailsTaskgroup($child, $the_querysub->post, 'fi');
			$child->title = $the_querysub->post->post_title;
      $child->order = $order_number;
      $order_number++;


			$child->taskgroups = getJsonTaskGroupsForTaskGroup($the_querysub->post->ID, 'fi');


			$mandatory_task_guids = array();

			$child->tasks = getJsonTasks($the_querysub->post->ID);

			$child->mandatory_tasks = implode(",", $mandatory_task_guids);
			$mandatory_task_guids = array();
			array_push($childs, $child);

		}
	}

	return $childs;

}

function getJsonTasks($parent_id) {
	$classTask = "POFTREE\\task";

	$childs = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_task',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id,
    'orderby' => 'menu_order'
	);

	$the_query = new WP_Query( $args );

  $order_number = 0;

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();


			$child = new $classTask;
			$child = getJsonItemBaseDetails($child, $the_query->post);
			$child = getJsonItemDetailsTask($child, $the_query->post);

			$child->title = $the_query->post->post_title;
      $child->order = $order_number;
      $order_number++;

			array_push($childs, $child);

		}
	}

	return $childs;

}



/*
echo "<!--";
$terms = wp_get_post_terms(21, 'tarvike');
print_r($terms);

print_r(get_post_tags_XML(21));

echo "-->"
*/


/*
echo "<!--";

echo json_encode($data);

echo "-->"
*/
?>