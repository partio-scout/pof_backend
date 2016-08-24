<?php
/*
Template Name: JSON vinkit
*/

header('Content-type: application/json');

$lastModified = strtotime('2010-01-01');
$lastModifiedBy = 0;
$lastModifiedByName = "";


$post_guid = $_GET["postGUID"];

$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	'meta_key' => 'post_guid',
	'meta_value' => $post_guid
);

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$task_post = $the_query->post;
	}
}

pof_checkDatetime($task_post);


$suggestions = pof_get_suggestions($post);

$lang = "fi";

if (!empty($_GET["lang"])) {
	$lang = strtolower($_GET["lang"]);
}

$jsonItem = new stdClass();

$jsonItem->items = array();

$jsonItem->lang = strtoupper($lang);

$jsonItem->post = new stdClass();
$jsonItem->post->id = $task_post->ID;
$jsonItem->post->title = $task_post->post_title;
$jsonItem->post->guid = get_post_meta( $task_post->ID, "post_guid", true );

foreach ($suggestions as $suggestion) {
	$suggestiong_lang = get_post_meta( $suggestion->ID, "pof_suggestion_lang", true );
	if (strtolower($suggestiong_lang) == $lang || ($lang == 'fi' && $suggestiong_lang == '')) {
		$suggestiong_writer = get_post_meta( $suggestion->ID, "pof_suggestion_writer", true );
		$item = new stdClass();
		$item->title = $suggestion->post_title;
        $item->guid = get_post_meta( $suggestion->ID, "post_guid", true );
		$item->content = $suggestion->post_content;
		$item->publisher = new stdClass();
		$item->publisher->nickname = $suggestiong_writer;
		$item->published = $suggestion->post_date;
		$item->modified = $suggestion->post_modified;


        //		$suggestiong_file_user_id = get_post_meta( $suggestion->ID, "pof_suggestion_file_user", true );
		$suggestiong_file_id = get_post_meta( $suggestion->ID, "pof_suggestion_file", true );
        /*
        if ($suggestiong_file_user_id != "") {
        $path = wp_get_attachment_url( $suggestiong_file_user_id );
        $item->file_user = $path;
        }
         */
        if ($suggestiong_file_id != "") {
            $path = wp_get_attachment_url( $suggestiong_file_id );
            $item->file = $path;
        }

        $item->additional_content = get_post_additional_content_JSON($suggestion->ID);

		pof_checkDatetime($suggestion);

		array_push($jsonItem->items, $item);
	}
}

$jsonItem->lastModified = date("Y-m-d H:i:s",$lastModified);

echo json_encode($jsonItem);



?>