<?php
/*
Template Name: JSON vinkit kaikki
*/

header('Content-type: application/json');


$args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_suggestion')
);

if (!empty($_GET["lang"])) {
	$lang = strtolower($_GET["lang"]);

    $args = array(
	'numberposts' => -1,
	'posts_per_page' => -1,
	'post_type' => array('pof_post_suggestion'),
    'meta_query' => array(
            array(
                'key'			=> 'pof_suggestion_lang',
                'compare'		=> '=',
                'value'         => $lang
            )
        ),
    );

}

$pof_settings_lastupdate_overwrite = pof_settings_get_lastupdate_overwrite();

$the_query = new WP_Query( $args );

$posts = array();

$task_posts = array();


if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();

        $suggestion =  $the_query->post;

        $suggestiong_writer = get_post_meta( $suggestion->ID, "pof_suggestion_writer", true );
        $suggestiong_lang = get_post_meta( $suggestion->ID, "pof_suggestion_lang", true );
		$item = new stdClass();
        $item->lang = $suggestiong_lang;
		$item->title = $suggestion->post_title;
        $item->guid = get_post_meta( $suggestion->ID, "post_guid", true );
		$item->content = $suggestion->post_content;

        $item->publisher = new stdClass();
		$item->publisher->nickname = $suggestiong_writer;
		$item->published = $suggestion->post_date;
		if ($pof_settings_lastupdate_overwrite == null) {
            $item->modified = $suggestion->post_modified;
        } else {
            $item->modified = $pof_settings_lastupdate_overwrite;
        }


        $task_post_id = get_post_meta( $suggestion->ID, "pof_suggestion_task", true );
        if (!empty($task_post_id)) {
            if (array_key_exists($task_post_id, $task_posts)) {
                $task_post = $task_posts[$task_post_id];
            } else {
                $task_post = get_post($task_post_id);
                $task_posts[$task_post_id] = $task_post;
            }

            if (!empty($task_post) && $task_post->ID != null) {
                $item->post = new stdClass();
                $item->post->id = $task_post->ID;
                $item->post->title = $task_post->post_title;
                $item->post->guid = get_post_meta( $task_post->ID, "post_guid", true );

            }

        }

        $item->additional_content = get_post_additional_content_JSON($suggestion->ID);

		array_push($posts, $item);
	}
}


echo json_encode($posts);





?>