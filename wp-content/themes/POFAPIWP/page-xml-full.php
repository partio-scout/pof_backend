<?php
/*
Template Name: XML Full
*/
header('Content-type: application/xml');

$lastModified = strtotime('2010-01-01');
$lastModifiedBy = 0;
$lastModifiedByName = "";

$root = get_field("suoritusohjelma");

$tree = getTree($root->ID);

$tree_hash = hash("md5", serialize($tree));

$data = array(
	'name' => 'programs',
	array(
		'name' => 'program',
		'attributes' => array(
			'lang' => 'FI',
			'guid' => wp_hash($root->ID),
			'id' => $root->ID
		),
		array(
			'name' => 'title',
			'value' => $root->post_title,
		),
		array(
			'name' => 'owner',
			'value' => "Suomen Partiolaiset ry",
		),
		array(
			'name' => 'languages',
			array(
				'name' => 'lang',
				'value' => "FI",
			),
			array(
				'name' => 'lang',
				'value' => "SV",
			),
		),
		array(
			'name'=> 'rootNode',
			array(
				'name' => 'lastModified',
				'value' => $root->post_modified,
			),
			array(
				'name' => 'lastModifiedBy',
				'attributes' => array(
					'userId' => $root->post_author
				),
				'value' => get_userdata($root->post_author)->display_name,
			),
		),
		array(
			'name'=> 'treeDetails',
			array(
				'name' => 'lastModified',
				'value' => date("Y-m-d H:i:s",$lastModified),
			),
			array(
				'name' => 'hash',
				'value' => $tree_hash,
			),
		),
		$tree
	),
);

function getTree($root_id) {
	$root_post = get_post($root_id);
	
	pof_checkDatetime($root_post);
	
	$ret = array(
		'name' => 'tree',
	);

	$rootNode = array(
		'name' => 'node',
		'attributes' => array(
			'type' => 'root'
		),
		array(
			'name' => 'title',
			'value' => $root_post->post_title,
		),
		array(
			'name' => 'guid',
			'value' => wp_hash($root_post->id),
		),
		array(
			'name' => 'id',
			'value' => $root_post->ID,
		),
		array(
			'name' => 'details',
			'value' => get_site_url() . "/item-xml/?postID=".$root_id,
		),

		get_post_tags_XML($root_post->ID),
		get_post_images_XML($root_post->ID),
		get_post_custom_attributes($root_post),
		get_childs_for_suoritusohjelma($root_id)
	);
	array_push($ret, $rootNode);

	return $ret;
}


function get_childs_for_suoritusohjelma($parent_id) {
	$ret = array(
		'name'=>'childs'
	);

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_agegroup',
		'meta_key' => 'suoritusohjelma',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			
			pof_checkDatetime($the_query->post);
			
			$tmp = array(
				'name' => 'node',
					'attributes' => array(
					'type' => "agegroup"
				),
				array(
					'name'=>'title',
					'value' => $the_query->post->post_title
				),
				array(
					'name' => 'guid',
					'value' => wp_hash($the_query->post->id),
				),
				array(
					'name' => 'id',
					'value' => $the_query->post->ID,
				),
				array(
					'name' => 'details',
					'value' => get_site_url() . "/item-xml/?postID=".$the_query->post->ID,
				),
				array(
					'name'=>'leaf',
					array(
						'name'=>'minAge',
						'attributes' => array(
							'value' => get_field("agegroup_min_age")
						),
					),
					array(
						'name'=>'maxAge',
						'attributes' => array(
							'value' => get_field("agegroup_max_age")
						),
					),
				),
				get_post_tags_XML($the_query->post->ID),
				get_post_images_XML($the_query->post->ID),
				get_childs_for_ikakausi($the_query->post->ID)
			);
			
			array_push($ret, $tmp);
		}
	}


	// TODO: get all other types that have this suoritusohjelma as parent

	wp_reset_query();

	return $ret;
}

function get_childs_for_ikakausi($parent_id) {
$ret = array(
	'name'=>'childs'
);

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'meta_key' => 'ikakausi',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			pof_checkDatetime($the_query->post);
			$tmp = array(
				'name' => 'node',
					'attributes' => array(
					'type' => "taskgroup"
				),
				array(
					'name'=>'title',
					'value' => $the_query->post->post_title
				),
				
				array(
					'name' => 'guid',
					'value' => wp_hash($the_query->post->id),
				),
				array(
					'name' => 'id',
					'value' => $the_query->post->ID,
				),
				array(
					'name' => 'details',
					'value' => get_site_url() . "/item-xml/?postID=".$the_query->post->ID,
				),
				get_post_tags_XML($the_query->post->ID),
				get_post_images_XML($the_query->post->ID),
				get_childs_for_suoritepaketti($the_query->post->ID)
			);
			array_push($ret, $tmp);
		}
	}

	wp_reset_query();
	
	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_task',
		'meta_key' => 'ikakausi',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			pof_checkDatetime($the_query->post);
			$tmp = array(
				'name' => 'node',
					'attributes' => array(
					'type' => "task"
				),
				array(
					'name'=>'title',
					'value' => $the_query->post->post_title
				),
				array(
					'name' => 'guid',
					'value' => wp_hash($the_query->post->id),
				),
				array(
					'name' => 'id',
					'value' => $the_query->post->ID,
				),
				array(
					'name' => 'details',
					'value' => get_site_url() . "/item-xml/?postID=".$the_query->post->ID,
				),
				get_post_tags_XML($the_query->post->ID),
				get_post_images_XML($the_query->post->ID)
			);
			array_push($ret, $tmp);
		}
	}

	wp_reset_query();

	return $ret;
}

function get_childs_for_suoritepaketti($parent_id) {
	$ret = array(
		'name'=>'childs'
	);

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_taskgroup',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			pof_checkDatetime($the_query->post);
			$tmp = array(
				'name' => 'node',
					'attributes' => array(
					'type' => "taskgroup"
				),
				array(
					'name'=>'title',
					'value' => $the_query->post->post_title
				),
				array(
					'name' => 'guid',
					'value' => wp_hash($the_query->post->id),
				),
				array(
					'name' => 'id',
					'value' => $the_query->post->ID,
				),
				array(
					'name' => 'details',
					'value' => get_site_url() . "/item-xml/?postID=".$the_query->post->ID,
				),
				get_post_tags_XML($the_query->post->ID),
				get_post_images_XML($the_query->post->ID),
				get_childs_for_suoritepaketti($the_query->post->ID)
			);
			array_push($ret, $tmp);
		}
	}

	wp_reset_query();
	
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
			pof_checkDatetime($the_query->post);
			$tmp = array(
				'name' => 'node',
					'attributes' => array(
					'type' => "task"
				),
				array(
					'name'=>'title',
					'value' => $the_query->post->post_title
				),
				array(
					'name' => 'guid',
					'value' => wp_hash($the_query->post->id),
				),
				array(
					'name' => 'id',
					'value' => $the_query->post->ID,
				),
				array(
					'name' => 'details',
					'value' => get_site_url() . "/item-xml/?postID=".$the_query->post->ID,
				),
				get_post_tags_XML($the_query->post->ID),
				get_post_images_XML($the_query->post->ID)
			);
			array_push($ret, $tmp);
		}
	}

	wp_reset_query();

	return $ret;
}



echo getXML($data);

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