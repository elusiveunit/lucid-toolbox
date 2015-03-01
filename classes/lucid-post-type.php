<?php
/**
 * Custom post type class definition.
 *
 * @package Lucid\Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Handles custom post types; registering, update messages etc.
 *
 * The format is very similar to the standard way of registering, with some
 * additional arguments:
 *
 *     $post_type_name = new Lucid_Post_Type( 'NAME', array(
 *        'post_type_args' => array(
 *           [...]
 *        ),
 *        'update_messages' => array(
 *           [...]
 *        ),
 *        'columns' => array(
 *           [...]
 *        )
 *     ) );
 *
 * The post_type_args array contains standard register_post_type arguments, see
 * {@link http://codex.wordpress.org/Function_Reference/register_post_type#Arguments the codex}.
 *
 * The labels argument in post_type_args accepts:
 *
 *     'labels' => array(
 *
 *        // menu_name default, use plural
 *        'name' =>               _x( 'Movies', 'post type general name', 'TEXTDOMAIN' ),
 *        'singular_name' =>      _x( 'Movie', 'post type singular name', 'TEXTDOMAIN' ),
 *        'all_items' =>          __( 'All movies', 'TEXTDOMAIN' ),
 *        'add_new' =>            __( 'Add new', 'TEXTDOMAIN' ),
 *        'add_new_item' =>       __( 'Add new movie', 'TEXTDOMAIN' ),
 *        'edit_item' =>          __( 'Edit movie', 'TEXTDOMAIN' ),
 *        'new_item' =>           __( 'New movie', 'TEXTDOMAIN' ),
 *        'view_item' =>          __( 'View movie', 'TEXTDOMAIN' ),
 *        'search_items' =>       __( 'Search movies', 'TEXTDOMAIN' ),
 *        'not_found' =>          __( 'No movies found', 'TEXTDOMAIN' ),
 *        'not_found_in_trash' => __( 'No movies found in trash', 'TEXTDOMAIN' ),
 *
 *        // Hierarchical only
 *        'parent_item_colon' =>  __( 'Parent movie:', 'TEXTDOMAIN' )
 *     )
 *
 * The columns argument makes use of Lucid_Admin_Column, see that class for
 * the data to pass.
 *
 * @package Lucid\Toolbox
 * @version 1.3.0
 */
class Lucid_Post_Type {

	/**
	 * The post type name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $name;

	/**
	 * Additional post type data; the $args param from the constructor.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $args = array();

	/**
	 * The column class object, if the columns argument was used.
	 *
	 * @since 1.3.0
	 * @var Lucid_Admin_Column
	 */
	public $columns = null;

	/**
	 * Constructor, pass post type.
	 *
	 * @since 1.0.0
	 * @param string $post_type The unique post type name. Maximum 20
	 *   characters, can not contain capital letters or spaces.
	 * @param array $args Additional post type data.
	 * @param array $args {
	 *    Additional post type data.
	 *
	 *    @type string $small_menu_icon_url DEPRECATED. Absolute url to to a
	 *       16x40 pixels sprite image to use as admin menu icon for the post
	 *       type. The hover state should be on top of the regular state in the
	 *       image.*
	 *    @type string $large_menu_icon_url DEPRECATED. Absolute url to a 32x32
	 *       image to use as the icon beside the heading in the post edit screen.
	 *    @type string $icon DEPRECATED. An icon from the included icon font
	 *       can be used. Pass the hexadecimal/unicode code for the icon, like
	 *       'f120' for the WordPress logo. See the Dashicons link.
	 *    @type array $post_type_args The arguments for register_post_type, like
	 *       'hierarchical', 'labels', 'supports' etc. See WordPress Codex.
	 *    @type array $update_messages Update messages to display instead of the
	 *       standard 'post updated' etc. See _update_messages() for examples.
	 *    @type array $update_messages_no_links Same as update_messages, but
	 *       without show/preview links to the post. This can be appropriate if
	 *       the post isn't supposed to be viewed in itself (probably has
	 *       'public' set to false), like a post type for gallery images. See
	 *       _update_messages() for examples.
	 *    @type array $columns Custom columns for the admin post list. See
	 *       Lucid_Admin_Column for the arguments to use.
	 * }
	 * @see _update_messages() For message array structure.
	 * @see Lucid_Admin_Column For the 'columns' argument structure.
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 * @link https://developer.wordpress.org/resource/dashicons/ The Dashicons
	 *    icon font
	 */
	public function __construct( $post_type, array $args = array() ) {
		$this->name = (string) $post_type;

		if ( ! $this->_is_valid_post_type_name() )
			return;

		$defaults = array(
			'small_menu_icon_url' => '',
			'large_menu_icon_url' => '',
			'icon' => '',
			'post_type_args' => array(),
			'update_messages' => array(),
			'update_messages_no_links' => array(),
			'columns' => array()
		);
		$this->args = array_merge( $defaults, $args );

		$this->_add_post_type();
		$this->_add_hooks();

		if ( $this->args['columns'] )
			$this->_add_columns();
	}

	/**
	 * Check if the post type name is valid and show errors if not.
	 *
	 * @since 1.1.2
	 * @return bool
	 */
	protected function _is_valid_post_type_name() {
		$name_valid = true;

		// Maximum 20 characters long
		if ( strlen( $this->name ) > 20 ) :
			trigger_error( sprintf( "Post type name '%s' can be no more than 20 characters long", $this->name ), E_USER_WARNING );
			$name_valid = false;
		endif;

		// No capital letters or spaces
		if ( preg_match( '/[A-Z\s]/', $this->name ) ) :
			trigger_error( sprintf( "Post type name '%s' can not contain capital letters or spaces", $this->name ), E_USER_WARNING );
			$name_valid = false;
		endif;

		return $name_valid;
	}

	/**
	 * Add relevant hooks for post type functions.
	 *
	 * @since 1.0.0
	 */
	protected function _add_hooks() {
		if ( $this->args['small_menu_icon_url']
		  || $this->args['large_menu_icon_url']
		  || $this->args['icon'] ) :
			add_action( 'admin_head', array( $this, '_admin_icons' ) );
			add_action( 'admin_notices', array( $this, '_admin_icons_notice' ) );
		endif;

		add_action( 'post_updated_messages', array( $this, '_update_messages' ) );
	}

	/**
	 * Register the custom post type.
	 *
	 * @since 1.0.0
	 */
	protected function _add_post_type() {
		register_post_type(
			$this->name,
			$this->args['post_type_args']
		);
	}

	/**
	 * Update messages for custom post types.
	 *
	 * By default, custom post types use the standard post message, i.e. 'post
	 * published' etc. This sets more appropriate messages. Requires params
	 * update_messages or update_messages_no_links to be set when constructing
	 * the post type.
	 *
	 * The array passed in the constructor must follow a specific format.
	 *
	 * Regular format:
	 *
	 *     'update_messages' => array(
	 *        'updated_view' => __( 'Movie updated. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
	 *        'updated'      => __( 'Movie updated.', 'TEXTDOMAIN' ),
	 *        'revision'     => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
	 *        'published'    => __( 'Movie published. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
	 *        'saved'        => __( 'Movie saved.', 'TEXTDOMAIN' ),
	 *        'submitted'    => __( 'Movie submitted. <a target="_blank" href="%s">Preview movie</a>', 'TEXTDOMAIN' ),
	 *        'scheduled'    => __( 'Movie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview movie</a>', 'TEXTDOMAIN' ),
	 *        'draft'        => __( 'Movie draft updated. <a href="%s">Preview movie</a>', 'TEXTDOMAIN' )
	 *     )
	 *
	 * No links format:
	 *
	 *     'update_messages_no_links' => array(
	 *        'updated'   => __( 'Movie updated.', 'TEXTDOMAIN' ),
	 *        'revision'  => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
	 *        'published' => __( 'Movie published.', 'TEXTDOMAIN' ),
	 *        'saved'     => __( 'Movie saved.', 'TEXTDOMAIN' ),
	 *        'submitted' => __( 'Movie submitted.', 'TEXTDOMAIN' ),
	 *        'scheduled' => __( 'Movie scheduled for: <strong>%1$s</strong>.', 'TEXTDOMAIN' ),
	 *        'draft'     => __( 'Movie draft updated.', 'TEXTDOMAIN' )
	 *     )
	 *
	 * @since 1.0.0
	 * @global WP_Post $post Used to get data about the current post.
	 * @param array $messages Default messages.
	 * @return array Message array with custom messages added.
	 */
	public function _update_messages( $messages ) {
		global $post;

		// Regular messages
		if ( ! empty( $this->args['update_messages'] ) ) :
			$msg = $this->args['update_messages'];

			$messages[$this->name] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( $msg['updated_view'], esc_url( get_permalink( $post->ID ) ) ),
				2 => $messages['post'][2],
				3 => $messages['post'][3],
				4 => $msg['updated'],
				5 => ( isset( $_GET['revision'] ) ) ? sprintf( $msg['updated_view'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( $msg['published'], esc_url( get_permalink( $post->ID ) ) ),
				7 => $msg['saved'],
				8 => sprintf( $msg['submitted'], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
				/* translators: Publish box date format, see http://php.net/date */
				9 => sprintf( $msg['scheduled'], date_i18n( __( 'M j, Y @ G:i', 'lucid-toolbox' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
				10 => sprintf( $msg['draft'], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) )
			);

		// Messages with no links
		elseif ( ! empty( $this->args['update_messages_no_links'] ) ) :
			$msg = $this->args['update_messages_no_links'];

			$messages[$this->name] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => $msg['updated'],
				2 => $messages['post'][2],
				3 => $messages['post'][3],
				4 => $msg['updated'],
				5 => isset( $_GET['revision'] ) ? sprintf( $msg['revision'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => $msg['published'],
				7 => $msg['saved'],
				8 => $msg['submitted'],
				9 => sprintf( $msg['scheduled'], date_i18n( __( 'M j, Y @ G:i', 'lucid-toolbox' ), strtotime( $post->post_date ) ) ),
				10 => $msg['draft']
			);

		endif;

		return $messages;
	}

	/**
	 * Add custom admin columns.
	 *
	 * @since 1.3.0
	 * @see Lucid_Admin_Column
	 */
	protected function _add_columns() {
		if ( ! class_exists( 'Lucid_Admin_Column' ) )
			require LUCID_TOOLBOX_CLASS . 'lucid-admin-column.php';

		$this->columns = new Lucid_Admin_Column( $this->name, $this->args['columns'] );
	}

	/**
	 * Custom icons for custom post types.
	 *
	 * The small image should be a 16x40 pixels sprite image, with the hover
	 * state on top of the regular state. The large icon should be 32x32 pixels.
	 *
	 * @since 1.0.0
	 */
	public function _admin_icons() {
		$post_type = $this->name;

		$small_icon = ( $this->args['small_menu_icon_url'] )
			? $this->args['small_menu_icon_url']
			: '';

		$large_icon = ( $this->args['large_menu_icon_url'] )
			? $this->args['large_menu_icon_url']
			: '';

		$font_icon = ( $this->args['icon'] ) ? $this->args['icon'] : '';

		$css = '';

		// Dashicon
		if ( $font_icon && version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ) :
			$css .= "#menu-posts-{$post_type} .wp-menu-image:before{content:'\\{$font_icon}'!important;}";

		// Custom sprite
		else :
			if ( $small_icon ) :
				$css .= "menu-posts-{$post_type} .wp-menu-image:before{content:''!important;}
				#menu-posts-{$post_type} .wp-menu-image{
					background:url('{$small_icon}') no-repeat 6px -17px!important;
				}
				#menu-posts-{$post_type}:hover .wp-menu-image,
				#menu-posts-{$post_type}.wp-has-current-submenu .wp-menu-image{
					background-position:6px 7px !important;
				}";
			endif;

			if ( $large_icon ) :
				$css .= ".icon32-posts-{$post_type}{
					background:url('{$large_icon}') no-repeat!important;
				}";
			endif;
		endif;

		// Don't print an empty style tag
		if ( ! $css ) return;

		// Remove newlines and tabs
		$output = str_replace( array( "\n", "\t" ), '', $css );

		echo "<style>{$output}</style>\n";
	}

	/**
	 * Show a notice about the deprecated icon arguments.
	 *
	 * Added to admin_notice to prevent the dashboard menu from breaking.
	 */
	public function _admin_icons_notice() {
		trigger_error( sprintf( 'The custom icon arguments are deprecated, use the core menu_icon in post_type_args instead (post type %s).', $this->name ), E_USER_NOTICE );
	}
}