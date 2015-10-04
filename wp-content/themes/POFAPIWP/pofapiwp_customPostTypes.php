<?php

/**
 * POFAPIWP custom post types
 */

add_action( 'init', 'AddPofApiCustomPostTypes' );

function AddPofApiCustomPostTypes() {
	register_post_type( 'pof_post_program',
		array(
			'labels' => array(
				'name' => __( 'Suoritusohjelmat' ),
				'singular_name' => __( 'Suoritusohjelma' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-editor-customchar'
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
			'menu_icon' => 'dashicons-backup'
		)
	);
	
	register_post_type( 'pof_post_taskgroup',
		array(
			'labels' => array(
				'name' => __( 'Suoritepaketit' ),
				'singular_name' => __( 'Suoritepaketti' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-cart'
		)
	);
	
	register_post_type( 'pof_post_task',
		array(
			'labels' => array(
				'name' => __( 'Suoritukset' ),
				'singular_name' => __( 'Suoritus' ),
				),
			'public' => true,
			'has_archive' => true,
			'menu_icon' => 'dashicons-star-filled'
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
			'menu_icon' => 'dashicons-smiley'
		)
	);
}
 
