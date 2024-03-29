<?php

/*
 * Plugin Name: NIF Field for Gravity Forms
 * Plugin URI: http://github.com
 * Description: Allow adding a NIF field to Gravity Forms
 * Version: 0.2
 * Author: Mikel
 * Author URI: http://zhenit.com
 * Text Domain: gravityforms-nif
 * Domain Path: /languages
------------------------------------------------------------------------
Copyright 2022 ZhenIT Sofware.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/
define('GF_NIF_ADDON_VERSION', '0.2');

add_action('gform_loaded', array('GF_NIF_AddOn_Bootstrap', 'load'), 5);

class GF_NIF_AddOn_Bootstrap
{
    public static function load()
    {
        if (!method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain( 'gravityforms-nif', false, $plugin_rel_path );

        add_action('plugins_loaded', 'myplugin_init');
        require_once('class-gf-field-nif.php');
        GF_Fields::register(GF_Field_NIF::get_instance());
    }
}
