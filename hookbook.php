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

            <style>
                html, body {
                    height: 100%;
                }
                .wrap {
                    display: block; position: relative;
                    height: 100%;
                }
                h2.nav-tab-wrapper {
                    margin-bottom: 20px;
                }
                h2.plugins {
                    float: left;
                    width: 20%;
                }
                h2.nav-tab-wrapper.vertical {
                    display: block; position: relative;
                    height: 100%;
                    padding: 10px 0 12px 0; margin-bottom: 0; margin-left: -1px;
                    border-right: 1px solid #ccc; border-bottom: none;
                    /*box-sizing: border-box;*/
                }
                .vertical .nav-tab {
                    display: block;
                    width: auto;
                    padding-right: 0; margin-top: 10px; margin-right: -1px;
                    border-bottom: 1px solid #ccc;
                    }
                    .vertical .nav-tab-active {
                        border-right: 1px solid #f1f1f1;
                    }

                .hook-list {
                    display: block; float: left;
                    width: 80%;
                    padding: 10px; margin: 0;
                    border: 1px solid #ccc; border-left: none;
                    box-sizing: border-box;
                    }
                    .hook-list li {
                        display: inline-block; position: relative;
                        padding: 15px; margin: 2px;
                        border: 1px solid #ccc;
                        border-left: 5px solid #ccc;
                        background: #e4e4e4;
                        }
                        .hook-list li.action_hook {
                            border-left-color: red;
                        }
                        .hook-list li.filter_hook {
                            border-left-color: blue;
                        }
                        .hook-list li:hover {
                            background: #fff;
                            cursor: pointer;
                        }
                        .hook-list li span {
                            display: none; position: absolute;
                            left: 5px; top: -8px;
                            padding: 0px 6px;
                            border-radius: 3px;
                            font-size: 6px;
                            color: #fff;
                            }
                            .hook-list li:hover span {
                                display: block;
                            }
                            .hook-list li span.action_hook {
                                background: red;
                            }
                            .hook-list li span.filter_hook {
                                background: blue;
                            }
                .clearfix:after {
                    visibility: hidden;
                    display: block;
                    font-size: 0;
                    content: " ";
                    clear: both;
                    height: 0;
                    }
                * html .clearfix             { zoom: 1; } /* IE6 */
                *:first-child+html .clearfix { zoom: 1; } /* IE7 */
            </style>

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

            if ( in_array( $file, $not_file ) ) continue;

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

                    $out .= '<li class="' . $type . '_hook"><span class="' . $type . '_hook">' . $type . '</span>' . htmlspecialchars( $hook ) . '</li>';
                }
            }

        }

        if ( $out == '' ) $out = '<h3>No hooks in ' . ucwords( $current['plugin'] ) . '</h3>';

        $out .= '<br style="clear:both;" />';

        return $out;
    }

}

new HookBook();