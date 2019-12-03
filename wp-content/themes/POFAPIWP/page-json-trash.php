<?php
/*
Template Name: JSON Trash
*/

header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

$ret = array();

$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_program', 'pof_post_agegroup', 'pof_post_taskgroup', 'pof_post_task', 'pof_post_suggestion' ),
	'post_status' => 'trash'
);

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$tmp = new stdClass();
		$the_query->the_post();
		$post_id = $the_query->post->ID;
		$tmp->guid = get_post_meta( $post_id, 'post_guid', true);
		$tmp->title = $the_query->post->post_title;
		$tmp->type = $the_query->post->post_type;
		$tmp->modified = $the_query->post->post_modified;
		array_push($ret, $tmp);
	}
}


echo json_encode($ret);

?>