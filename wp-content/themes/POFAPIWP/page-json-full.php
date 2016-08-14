<?php
/*
Template Name: JSON Full
*/

header('Content-type: application/json');

$lastModified = strtotime('2010-01-01');
$lastModifiedBy = 0;
$lastModifiedByName = "";

$root = get_field("suoritusohjelma");

$post_id = $root->ID;

if (!empty($_GET["postGUID"])) {
	$post_guid = $_GET["postGUID"];

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

$tree = getJsonTree($post_id);

$tree_hash = hash("md5", serialize($tree));

$tree->program[0]->treeDetails = new stdClass();
$tree->program[0]->treeDetails->lastModified = date("Y-m-d H:i:s",$lastModified);
$tree->program[0]->treeDetails->lastModifiedBy = getLastModifiedBy($lastModifiedBy);
$tree->program[0]->treeDetails->hash = $tree_hash;

echo json_encode($tree);


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

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			$child = new $classAgeGroup;
			$child = getJsonItemBaseDetails($child, $the_query->post);
			$child = getJsonItemDetailsAgegroup($child, $the_query->post, 'fi');
			$child->title = $the_query->post->post_title;
			$child->taskgroups = getJsonTaskGroups($the_query->post->ID);

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
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'ikakausi',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

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
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_querysub = new WP_Query( $args );

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
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();


			$child = new $classTask;
			$child = getJsonItemBaseDetails($child, $the_query->post);
			$child = getJsonItemDetailsTask($child, $the_query->post);

			$child->title = $the_query->post->post_title;

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