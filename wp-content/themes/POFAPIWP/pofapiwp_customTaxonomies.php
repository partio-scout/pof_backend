<?php

add_action( 'init', 'AddPofApiCustomTaxonomies' );

function AddPofApiCustomTaxonomies() {
	register_taxonomy('pof_tax_skillarea', array('pof_post_task'), array(
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

    register_taxonomy('pof_tax_growth_target', array('pof_post_task'), array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Kasvatustavoitteen avainsanat', 'taxonomy general name' ),
			'singular_name' => _x( 'Kasvatustavoitteen avainsana', 'taxonomy singular name' ),
			'menu_name' => __( 'Kasvatustavoitteen avainsanat' )
		),
		'rewrite' => array(
			'slug' => 'growth_target',
			'with_front' => false,
			'hierarchical' => false
		)
	));

    register_taxonomy('pof_tax_leadership', array('pof_post_task'), array(
                'hierarchical' => false,
                'labels' => array(
                    'name' => _x( 'Johtamistaito', 'taxonomy general name' ),
                    'singular_name' => _x( 'Johtamistaito', 'taxonomy singular name' ),
                    'menu_name' => __( 'Johtamistaidot' )
                ),
                'rewrite' => array(
                    'slug' => 'leadership',
                    'with_front' => false,
			'hierarchical' => false
		)
	));
}