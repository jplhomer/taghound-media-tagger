<?php

function image_tag_init() {
	register_taxonomy( 'image_tag', array( 'attachment' ), array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts'
		),
		'labels'            => array(
			'name'                       => __( 'Tags', 'image-auto-description' ),
			'singular_name'              => _x( 'Tag', 'taxonomy general name', 'image-auto-description' ),
			'search_items'               => __( 'Search Tags', 'image-auto-description' ),
			'popular_items'              => __( 'Popular Tags', 'image-auto-description' ),
			'all_items'                  => __( 'All Tags', 'image-auto-description' ),
			'parent_item'                => __( 'Parent Tag', 'image-auto-description' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'image-auto-description' ),
			'edit_item'                  => __( 'Edit Tag', 'image-auto-description' ),
			'update_item'                => __( 'Update Tag', 'image-auto-description' ),
			'add_new_item'               => __( 'New Tag', 'image-auto-description' ),
			'new_item_name'              => __( 'New Tag', 'image-auto-description' ),
			'separate_items_with_commas' => __( 'Tags separated by comma', 'image-auto-description' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'image-auto-description' ),
			'choose_from_most_used'      => __( 'Choose from the most used Tags', 'image-auto-description' ),
			'not_found'                  => __( 'No Tags found.', 'image-auto-description' ),
			'menu_name'                  => __( 'Tags', 'image-auto-description' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'image_tag',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'image_tag_init' );
