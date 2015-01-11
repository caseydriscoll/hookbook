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

        add_action( 'admin_enqueue_scripts', array( $this, 'register_styles_and_scripts' ) );

        add_action( 'admin_menu', array( $this, 'register_menu' ) );

        add_action( 'wp_ajax_generate_hook_post', array( $this, 'generate_hook_post' ) );
    }


    /**
     * Registers the needed styles and scripts
     *
     * @author caseypatrickdriscoll
     *
     * 
     */
    function register_styles_and_scripts() {
        if ( $_GET['page'] !== 'hookbook' ) return;

        wp_register_style( 'hookbook', plugin_dir_url( __FILE__ ) . 'css/style.css' );

        wp_register_script( 'hookbook', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ) );
    }


    /**
     * Registers the top level 'HookBook' menu and subsequent pages
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
            array( $this, 'render' ),
            'dashicons-book-alt'
        );

        add_submenu_page( 
            'hookbook', 
            'Find Hooks', 
            'Find Hooks', 
            'edit_others_posts', 
            'hookbook',
            array( $this, 'render' )
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
    function render() { 
        wp_enqueue_style( 'hookbook' );
        wp_enqueue_script( 'hookbook' );
        
        $current['tab'] = isset( $_GET['tab'] ) ? $_GET['tab'] : 'plugins';

        $tabs = array( 
                  'plugins'  => 'Plugins', 
                  'themes'   => 'Themes', 
                  'core'     => 'Core', 
                  'settings' => 'Settings' 
                );

        $not_plugins = array( '.', '..', 'index.php' );

        $plugins = get_plugins();

        ?>

        <div class="wrap">
            <h2>HookBook</h2>
            <h2 class="nav-tab-wrapper">
            <?php
                foreach( $tabs as $tab => $name ) {
                    $class = ( $tab == $current['tab'] ) ? ' nav-tab-active' : '';
                    echo "<a class='nav-tab$class' href='?page=hookbook&tab=$tab'>$name</a>";
                }
            ?>
            </h2>

            <?php 
            switch( $current['tab'] ) {

                case 'plugins': ?>

                    <h2 class="plugins nav-tab-wrapper vertical">
                    <?php

                        $current['plugin'] = isset( $_GET['plugin'] ) ? $_GET['plugin'] : 'plugins';
                        $url = '?page=hookbook&tab=plugins';


                        foreach( $plugins as $plugin_file => $plugin ) {
                            if ( in_array( $plugin, $not_plugins ) ) continue;
                            
                            $class = '';

                            if ( strtolower( $plugin['Name'] ) == $current['plugin'] ) {
                                $class = ' nav-tab-active';
                                $current['full_file'] = trailingslashit( WP_PLUGIN_DIR ) . $plugin_file;
                                $current['hooklist'] = $this->generate_hook_list( $current );
                            }
                            
                            echo "<a class='nav-tab$class' href='" . $url . "&plugin=" . strtolower( $plugin['Name'] ) . "'>" . $plugin['Name'] . "</a>";
                        } ?>

                    </h2>
                    <ul class="hook-list clearfix">
                        <h2 class="nav-tab-wrapper">
                            <a id="generate-posts" class="nav-tab">Generate All Posts</a>
                            <a id="actions-only" class="nav-tab">Actions Only</a>
                            <a id="filters-only" class="nav-tab">Filters Only</a>
                            <a id="generate-progress" class="nav-tab">Generating <i class="spinner"></i><span class="total"></span></a>
                        </h2>
                        <?php echo $current['hooklist']; ?>
                    </ul>

                    <?php 
                    break;

                case 'themes':
                    break;

            } ?>

        </div>
        <?php
    }


    /**
     * Generates the output, a list of all hooks, for the given current plugin
     *
     * @author  caseypatrickdriscoll
     *
     * @created 2015-01-11 10:20:49
     * 
     * @param  Array   $current   An array of information for the current plugin
     * 
     * @return string  $out       A list of hooks to render
     */
    function generate_hook_list( $current ) {

        if ( basename( dirname( $current['full_file'] ) ) == 'plugins' ) {
            $regex = array( array( $current['full_file'] ) );
        } else {
            $directory = new RecursiveDirectoryIterator( dirname( $current['full_file'] ) );
            $iterator = new RecursiveIteratorIterator( $directory );
            $regex = new RegexIterator( $iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
        }

        $out = '';
        foreach( $regex as $file ) {
            $not_file = array( '.', '..', '.git', '.htaccess' );

            if ( in_array( $file[0], $not_file ) ) continue;

            // echo '<pre>' . $file[0] . '</pre>';

            foreach( file( $file[0] ) as $number => $line ) {
                if ( $position = strpos( $line, 'do_action(' ) !== false ) {
                    $type = 'action';  
                    $hook = explode( '\'', trim( substr( $line, $position + strlen( 'do_action(' ) ) ) )[1];
                } elseif ( $position = strpos( $line, 'apply_filters(' ) !== false ) {
                    $type = 'filter';
                    $hook = explode( '\'', trim( substr( $line, $position + strlen( 'apply_filters(' ) ) ) )[1];
                }

                if ( $position !== false) {
                    if ( $hook == '' ) continue;

                    if ( substr( $hook, -1 ) == "_" )
                        $hook .= '{$var}';

                    if ( get_page_by_title( $hook, ARRAY_N, $type . '_hook' ) !== null ) // it already exists
                        $complete = 'complete ';
                    else
                        $complete = '';

                    $out .= '<li class="' . $complete . $type . '-hook" data-hook="' . $hook . '">
                                <span class="' . $type . '-hook">' . $type . '</span>' . htmlspecialchars( $hook ) . 
                            '</li>';
                }
            }

        }

        if ( $out == '' ) $out = '<h3>No hooks in ' . ucwords( $current['plugin'] ) . '</h3>';

        return $out;
    }


    /**
     * Inserts or updates a new 'action' or 'filter' custom post type
     *    'Returns' a success reponse
     *
     * @author  caseypatrickdriscoll
     *
     * @return  void   
     */
    function generate_hook_post() {
        
        $hook = array(
            'post_title'  => $_POST['hook'],
            'post_type'   => $_POST['type'] . '_hook',
            'post_status' => 'publish'
        );

        wp_insert_post( $hook );

        wp_send_json_success( array( 'i' => $_POST['i'] ) );
    }

}

new HookBook();