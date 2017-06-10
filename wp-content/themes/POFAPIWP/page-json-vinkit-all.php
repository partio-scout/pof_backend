<?php
/*
Template Name: JSON vinkit kaikki
*/

header('Content-type: application/json');

$pof_settings_fulljson_cache_ttl = pof_settings_get_fulljson_cache_ttl();

$post_id = $post->ID;

$forceRun = false;
if (   (!empty($_POST["forceRun"]) && $_POST["forceRun"] == "1")
    || (!empty($_GET["forceRun"]) && $_GET["forceRun"] == "1")) {
    $forceRun = true;
}

$filepath = get_home_path() . "wp-content/cache/pof/pof-vinkit-json-".$post_id.".json";
if (!file_exists($filepath)) {
    $forceRun = true;
}

$cache_last_run = (int)get_post_meta( $post_id, 'vinkit_json_last_save', true);
$cache_run_started = (int)get_post_meta( $post_id, 'vinkit_json_cache_run_started', true);


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
                "forceRun" => "1"
            );

            $absolute_url = pof_full_url( $_SERVER );

            update_post_meta($post_id, 'vinkit_json_cache_run_started', time());
            pof_curl_post_async($absolute_url, $params);
        }
    }

}
else {
    update_post_meta($post_id, 'vinkit_json_cache_run_started', time());

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

            $item->additional_content = get_post_additional_content_JSON($suggestion->ID, $suggestiong_lang);

		    array_push($posts, $item);
	    }
    }

    $jsconContent = json_encode($posts);

    $cache_last_run2 = (int)get_post_meta( $post_id, 'vinkit_json_last_save', true);

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

    update_post_meta($post_id, 'vinkit_json_last_save', time());
}



?>