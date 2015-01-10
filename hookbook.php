<?php

/**
 * Plugin Name: HookBook
 * Plugin URI: http://patchedupcreative.com/hookbook
 * Version: 1.0
 * Author: Casey Patrick Driscoll
 * Author URI: http://caseypatrickdriscoll.com
 * Description: A plugin for finding hooks in WordPress and generating docs
 */

include 'cpt/action-hook.php';
include 'cpt/filter-hook.php';

class HookBook {

    function __construct() {
        new ActionHook();
        new FilterHook();

        add_action( 'admin_menu', array( $this, 'register_menu' ) );

    }


    /**
     * Registers the top level 'HookBook' menu
     *
     * @author  caseypatrickdriscoll
     *
     * @created 2015-01-10 16:26:23
     *
     * @return  void
     */
    function register_menu() {

        add_menu_page(
            'HookBook',
            'HookBook',
            'manage_options',
            'hookbook',
            array( $this, 'render_settings' ),
            'dashicons-book-alt'
        );

    }


    /**
     * Renders the generate and settings page
     *
     * @author caseypatrickdriscoll
     *
     * @created 2015-01-10 16:45:21
     *
     * @return void
     */
    function render_settings() { ?>
        <div class="wrap">
            <h2>HookBook</h2>
        </div>
        <?php
    }

}

new HookBook();