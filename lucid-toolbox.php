<?php
/**
 * Lucid Toolbox loading.
 *
 * @package Lucid
 * @subpackage Toolbox
 */

/*
Plugin Name: Lucid Toolbox
Description: A small library of functionality common to Lucid plugins.
Author: Jens Lindberg
Author URI: http://profiles.wordpress.org/elusiveunit/
Version: 1.0.0
*/

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

// Plugin constants
if ( ! defined( 'LUCID_TOOLBOX_VERSION' ) )
	define( 'LUCID_TOOLBOX_VERSION', '1.0.0' );

if ( ! defined( 'LUCID_TOOLBOX_URL' ) )
	define( 'LUCID_TOOLBOX_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

if ( ! defined( 'LUCID_TOOLBOX_PATH' ) )
	define( 'LUCID_TOOLBOX_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! defined( 'LUCID_TOOLBOX_CLASS' ) )
	define( 'LUCID_TOOLBOX_CLASS', LUCID_TOOLBOX_PATH . 'classes/' );

// Load and initialize the plugin parts
require LUCID_TOOLBOX_PATH . 'inc/core.php';
$lucid_toolbox = new Lucid_Toolbox( __FILE__ );