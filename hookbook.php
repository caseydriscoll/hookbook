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
    }

}

new HookBook();