<?php
/**
 * Core functionality.
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Currently only loads translation.
 *
 * @package Lucid
 * @subpackage Toolbox
 */
class Lucid_Toolbox {

	/**
	 * Full path to plugin main file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $plugin_file;

	/**
	 * Constructor, add hooks.
	 *
	 * @since 1.0.0
	 * @param string $file Full path to plugin main file.
	 */
	public function __construct( $file ) {
		self::$plugin_file = $file;

		add_action( 'init', array( $this, 'load_translation' ), 1 );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );
	}

	/**
	 * Load translation.
	 *
	 * @since 1.0.0
	 */
	public function load_translation() {
		load_plugin_textdomain( 'lucid-toolbox', false, trailingslashit( dirname( plugin_basename( self::$plugin_file ) ) ) . 'lang/' );
	}

	/**
	 * Add a documentation link to the plugin meta data.
	 *
	 * @since 1.1.0
	 * @param array $links Default meta links.
	 * @param string $basename Basename of plugin currently processing.
	 * @return array
	 */
	public function add_meta_links( $links, $basename ) {
		if ( plugin_basename( self::$plugin_file ) == $basename ) :
			$url = esc_attr( LUCID_TOOLBOX_URL . 'doc' );

			// Generally bad practice to rely on core strings, but I feel it's
			// unlikely this is ever untranslated. If it happens, it's a simple
			// update.
			$text = __( 'Documentation' );

			$links['documentation'] = "<a href=\"{$url}\">{$text}</a>";
		endif;

		return $links;
	}
}