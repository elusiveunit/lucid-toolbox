<?php
/**
 * Lucid Toolbox plugin definition.
 *
 * Plugin Name: Lucid Toolbox
 * Plugin URI: https://github.com/elusiveunit/lucid-toolbox
 * Description: A small library of commonly needed functionality.
 * Author: Jens Lindberg
 * Version: 1.1.7
 * License: GPL-2.0+
 * Text Domain: lucid-toolbox
 * Domain Path: /lang
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

// Plugin constants
if ( ! defined( 'LUCID_TOOLBOX_VERSION' ) )
	define( 'LUCID_TOOLBOX_VERSION', '1.1.7' );

if ( ! defined( 'LUCID_TOOLBOX_URL' ) )
	define( 'LUCID_TOOLBOX_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

if ( ! defined( 'LUCID_TOOLBOX_PATH' ) )
	define( 'LUCID_TOOLBOX_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! defined( 'LUCID_TOOLBOX_CLASS' ) )
	define( 'LUCID_TOOLBOX_CLASS', LUCID_TOOLBOX_PATH . 'classes/' );

// Load and initialize the plugin parts
require LUCID_TOOLBOX_PATH . 'inc/core.php';
$lucid_toolbox = new Lucid_Toolbox( __FILE__ );