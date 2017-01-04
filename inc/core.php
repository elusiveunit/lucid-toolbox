<?php
/**
 * Core functionality.
 *
 * @package Lucid\Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Currently only loads translation and adds some links.
 *
 * @package Lucid\Toolbox
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

		add_action( 'init', array( $this, 'load_translation' ) );
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

			$text = __( 'Documentation', 'lucid-toolbox' );

			$links['documentation'] = "<a href=\"{$url}\">{$text}</a>";
		endif;

		return $links;
	}
}