<?php

add_action( 'init', 'AddPofApiCustomTaxonomies' );

function AddPofApiCustomTaxonomies() {
	register_taxonomy('pof_tax_skillarea', array('pof_post_agegroup', 'pof_post_taskgroup', 'pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Taitoalueet', 'taxonomy general name' ),
			'singular_name' => _x( 'Taitoalue', 'taxonomy singular name' ),
			'menu_name' => __( 'Taitoalueet' )
		),
		'rewrite' => array(
			'slug' => 'skillarea',
			'with_front' => false,
			'hierarchical' => false
		)
	));
	
	register_taxonomy('pof_tax_equipment', array('pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Tarvikkeet', 'taxonomy general name' ),
			'singular_name' => _x( 'Tarvike', 'taxonomy singular name' ),
			'menu_name' => __( 'Tarvikkeet' )
		),
		'rewrite' => array(
			'slug' => 'equipment',
			'with_front' => false,
			'hierarchical' => false
		)
	));
	
	register_taxonomy('pof_tax_taskduration', array('pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Suorituksen kesto', 'taxonomy general name' ),
			'singular_name' => _x( 'Suorituksen kestot', 'taxonomy singular name' ),
			'menu_name' => __( 'Suorituksen kestot' )
		),
		'rewrite' => array(
			'slug' => 'taskduration',
			'with_front' => false,
			'hierarchical' => false
		)
	));
	
	register_taxonomy('pof_tax_taskpreparationduration', array('pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Suorituksen valmistelun kesto', 'taxonomy general name' ),
			'singular_name' => _x( 'Suorituksen valmistelun kestot', 'taxonomy singular name' ),
			'menu_name' => __( 'Suorituksen valmistelun kestot' )
		),
		'rewrite' => array(
			'slug' => 'taskpreparationduration',
			'with_front' => false,
			'hierarchical' => false
		)
	));
/*
	register_taxonomy('pof_tax_taskplace', array('pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Paikka', 'taxonomy general name' ),
			'singular_name' => _x( 'Paikat', 'taxonomy singular name' ),
			'menu_name' => __( 'Paikat' )
		),
		'rewrite' => array(
			'slug' => 'taskplace',
			'with_front' => false,
			'hierarchical' => false
		)
	));
*/
}