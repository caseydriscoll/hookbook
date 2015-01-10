<?php

class FilterHook {

    function __construct() {
        add_action( 'init', array( $this, 'custom_post_type' ), 0 );
    }


    /**
     * Registers the "Filter Hook" custom post type
     *
     * @author caseypatrickdriscoll
     *
     * @created 2015-01-10 16:17:45
     * 
     * @return void
     */
    function custom_post_type() {

        $labels = array(
            'name'                => _x( 'Filter Hooks', 'Post Type General Name', 'hookbook' ),
            'singular_name'       => _x( 'Filter Hook', 'Post Type Singular Name', 'hookbook' ),
            'menu_name'           => __( 'Filter Hooks', 'hookbook' ),
            'parent_item_colon'   => __( 'Parent Item:', 'hookbook' ),
            'all_items'           => __( 'All Filter Hooks', 'hookbook' ),
            'view_item'           => __( 'View Filter Hook', 'hookbook' ),
            'add_new_item'        => __( 'Add New Filter Hook', 'hookbook' ),
            'add_new'             => __( 'Add New', 'hookbook' ),
            'edit_item'           => __( 'Edit Filter Hook', 'hookbook' ),
            'update_item'         => __( 'Update Filter Hook', 'hookbook' ),
            'search_items'        => __( 'Search Filter Hook', 'hookbook' ),
            'not_found'           => __( 'Not found', 'hookbook' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'hookbook' ),
        );

        $args = array(
            'label'               => __( 'filter_hook', 'hookbook' ),
            'description'         => __( 'A WordPress Filter Hook', 'hookbook' ),
            'labels'              => $labels,
            'supports'            => array( ),
            'taxonomies'          => array( 'category', 'post_tag' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );

        register_post_type( 'filter_hook', $args );

    }

}