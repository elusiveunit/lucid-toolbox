<?php
/**
 * Lucid Toolbox plugin definition.
 *
 * Plugin Name: Lucid Toolbox
 * Plugin URI: https://github.com/elusiveunit/lucid-toolbox
 * Description: A small collection of tools for speeding up development.
 * Author: Jens Lindberg
 * Version: 1.1.10
 * License: GPL-2.0+
 * Text Domain: lucid-toolbox
 * Domain Path: /lang
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

// Symlink workaround, see http://core.trac.wordpress.org/ticket/16953
$lucid_toolbox_plugin_file = __FILE__;
if ( isset( $plugin ) )
	$lucid_toolbox_plugin_file = $plugin;
elseif ( isset( $network_plugin ) )
	$lucid_toolbox_plugin_file = $network_plugin;

// Plugin constants
if ( ! defined( 'LUCID_TOOLBOX_VERSION' ) )
	define( 'LUCID_TOOLBOX_VERSION', '1.1.10' );

if ( ! defined( 'LUCID_TOOLBOX_URL' ) )
	define( 'LUCID_TOOLBOX_URL', trailingslashit( plugin_dir_url( $lucid_toolbox_plugin_file ) ) );

if ( ! defined( 'LUCID_TOOLBOX_PATH' ) )
	define( 'LUCID_TOOLBOX_PATH', trailingslashit( plugin_dir_path( $lucid_toolbox_plugin_file ) ) );

if ( ! defined( 'LUCID_TOOLBOX_CLASS' ) )
	define( 'LUCID_TOOLBOX_CLASS', LUCID_TOOLBOX_PATH . 'classes/' );

// Load and initialize the plugin parts
require LUCID_TOOLBOX_PATH . 'inc/core.php';
$lucid_toolbox = new Lucid_Toolbox( $lucid_toolbox_plugin_file );