<?php
/*
Template Name: Linktree
*/

get_header(); ?>


<link rel="stylesheet" href="/wp-content/themes/POFAPIWP/jstree/themes/default/style.min.css" />
<script src="/wp-content/themes/POFAPIWP/jstree/jstree.min.js"></script>

<script>
jQuery(document).ready(function( $ ) {
	
	$('#jstree-content')
	.on('changed.jstree', function (e, data) {
		var i, j, r = [];
		for(i = 0, j = data.selected.length; i < j; i++) {
			var text = $('#'+data.selected).attr('data-jstree');
			var obj = JSON.parse(text);
			if (typeof obj.postid != 'undefined') {
				window.open('/wp-admin/post.php?post='+obj.postid+'&action=edit', '_blank');
			}
		}
  }).jstree();
	
});

</script>

	<div id="primary" class="content-area content-area-wide">
		<main id="main" class="site-main site-main-wide" role="main">
			<header class="entry-header">
				<h1 class="entry-title">POF API Demo</h1>
			</header>
			<div class="entry-content" id="jstree-content">
			

<?php

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

$root_post = get_post($post_id);


?>
<ul>
	<li class="jstree-open" data-jstree='{"icon":"http://jstree.com/tree.png", "postid":"<?php echo $root_post->ID; ?>"}'><?php echo $root_post->post_title; 

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'post_type' => 'pof_post_agegroup',
		'meta_key' => 'suoritusohjelma',
		'meta_value' => $root_post->ID
	);

	$the_query2 = new WP_Query( $args );

	if( $the_query2->have_posts() ) {
		?><ul><?php
		while ( $the_query2->have_posts() ) {
			$the_query2->the_post();
			?><li class="jstree-open" data-jstree='{"icon":"/wp-content/themes/POFAPIWP/inc/group.png", "postid":<?php echo $the_query2->post->ID; ?>}'><?php 
			echo $the_query2->post->post_title;
			echo ' (' . get_field('agegroup_min_age', $the_query2->post->ID); 
			echo '-' . get_field('agegroup_max_age', $the_query2->post->ID) . ')'; 

			echo linkTreeGetTaskgroups($the_query2->post->ID);

		?></li><?php
		}
		?></ul><?php
	}

?>
	</li>
</ul>




</div>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>

<?php

function linkTreeGetTaskgroups($parent_id) {

	$ret = "";

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'post_type' => 'pof_post_taskgroup',
		'meta_key' => 'ikakausi',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		$ret .= '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$ret .= "<li class=\"jstree-open\" data-jstree='{\"postid\":\"".$the_query->post->ID."\"}'>";
			$ret .= $the_query->post->post_title;

			$ret .= linkTreeGetTaskgroups2($the_query->post->ID);
			$ret .= linkTreeGetTasks($the_query->post->ID);

		$ret .= '</li>';
		}
		$ret .= '</ul>';
	}

	return $ret;
}

function linkTreeGetTaskgroups2($parent_id) {

	$ret = "";
	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		$ret .= '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$ret .= "<li class=\"jstree-open\" data-jstree='{\"postid\":\"".$the_query->post->ID."\"}'>";
			$ret .= $the_query->post->post_title; 

			$ret .= linkTreeGetTaskgroups2($the_query->post->ID);
			$ret .= linkTreeGetTasks($the_query->post->ID);

		$ret .= '</li>';
		}
		$ret .= '</ul>';
	}

	return $ret;
}

function linkTreeGetTasks($parent_id) {
	$ret = "";
	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_task',
		'orderby' => 'title',
		'order' => 'ASC',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		$ret .= '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$ret .= "<li class=\"jstree-open\" data-jstree='{\"icon\":\"/wp-content/themes/POFAPIWP/inc/task.png\", \"postid\":\"".$the_query->post->ID."\"}'>";
			$ret .= $the_query->post->post_title; 
			$ret .= '</li>';
		}
		$ret .= '</ul>';
	}
	return $ret;
}

?>