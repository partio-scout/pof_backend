<?php

/**
 * POFAPIWP custom post types
 */

add_action( 'init', 'AddPofApiCustomPostTypes' );

function AddPofApiCustomPostTypes() {
	register_post_type( 'pof_post_program',
		array(
			'labels' => array(
				'name' => __( 'Ohjelmat' ),
				'singular_name' => __( 'Ohjelma' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-editor-customchar',
            'supports' => array('title', 'editor', 'revisions', 'page-attributes')
		)
	);

	register_post_type( 'pof_post_agegroup',
		array(
			'labels' => array(
				'name' => __( 'Ik&auml;kaudet' ),
				'singular_name' => __( 'Ik&auml;kausi' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-backup',
            'supports' => array('title', 'editor', 'revisions', 'page-attributes')
		)
	);

	register_post_type( 'pof_post_taskgroup',
		array(
			'labels' => array(
				'name' => __( 'Aktiviteettipaketit' ),
				'singular_name' => __( 'Aktiviteettipaketti' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor', 'revisions', 'page-attributes')
		)
	);

	register_post_type( 'pof_post_task',
		array(
			'labels' => array(
				'name' => __( 'Aktiviteetit' ),
				'singular_name' => __( 'Aktiviteetti' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-star-filled',
            'supports' => array('title', 'editor', 'revisions', 'page-attributes')
		)
	);

	register_post_type( 'pof_post_suggestion',
		array(
			'labels' => array(
				'name' => __( 'Vinkit' ),
				'singular_name' => __( 'Vinkki' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-smiley',
            'supports' => array('title', 'editor', 'revisions', 'page-attributes')
		)
	);
}

