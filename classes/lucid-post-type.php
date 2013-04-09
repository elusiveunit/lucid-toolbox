<?php
/**
 * Custom post type class definition.
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Handles custom post types; registering, update messages etc.
 *
 * The format is very similar to the standard way of registering, with some
 * additional arguments:
 *
 * <code>
 * $post_type_name = new Lucid_Post_Type( 'NAME', array(
 * 	'small_menu_icon_url' => [path_to_image_directory]/16x40_sprite.png',
 * 	'large_menu_icon_url' => [path_to_image_directory]/32x32.png',
 * 	'post_type_args' => array(
 * 		[...]
 * 	),
 * 	'update_messages' => array(
 * 		[...]
 * 	)
 * ) );
 * </code>
 *
 * The post_type_args array contains standard register_post_type arguments, see
 * {@link http://codex.wordpress.org/Function_Reference/register_post_type#Arguments the codex}.
 *
 * The labels argument in post_type_args accepts:
 * <code>
 * 'labels' => array(
 *
 * 	// menu_name default, use plural
 * 	'name' =>               _x( 'Movies', 'post type general name', 'TEXTDOMAIN' ),
 * 	'singular_name' =>      _x( 'Movie', 'post type singular name', 'TEXTDOMAIN' ),
 * 	'all_items' =>          __( 'All movies', 'TEXTDOMAIN' ),
 * 	'add_new' =>            __( 'Add new', 'TEXTDOMAIN' ),
 * 	'add_new_item' =>       __( 'Add new movie', 'TEXTDOMAIN' ),
 * 	'edit_item' =>          __( 'Edit movie', 'TEXTDOMAIN' ),
 * 	'new_item' =>           __( 'New movie', 'TEXTDOMAIN' ),
 * 	'view_item' =>          __( 'View movie', 'TEXTDOMAIN' ),
 * 	'search_items' =>       __( 'Search movies', 'TEXTDOMAIN' ),
 * 	'not_found' =>          __( 'No movies found', 'TEXTDOMAIN' ),
 * 	'not_found_in_trash' => __( 'No movies found in trash', 'TEXTDOMAIN' ),

 * 	// Hierarchical only
 * 	'parent_item_colon' =>  __( 'Parent movie:', 'TEXTDOMAIN' )
 * )
 * </code>
 *
 * @package Lucid
 * @subpackage Toolbox
 * @version 1.1.0
 */
class Lucid_Post_Type {

	/**
	 * The post type name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Additional post type data.
	 *
	 * @var array
	 */
	public $post_type_data = array();

	/**
	 * Constructor, pass post type.
	 *
	 * Arguments through the $args array:
	 *
	 * - 'small_menu_icon_url' (string) Absolute url to to a 16x40 pixels sprite
	 *   image to use as admin menu icon for the post type. The hover state
	 *   should be on top of the regular state in the image.
	 * - 'large_menu_icon_url' (string) Absolute url to a 32x32 image to use as
	 *   the icon beside the heading in the post edit screen.
	 * - 'post_type_args' (array) The standard arguments for register_post_type,
	 *   like 'hierarchical', 'labels', 'supports' etc. See WordPress Codex.
	 * - 'update_messages' (array) Update messages to display instead of the
	 *   standard 'post updated' etc. See _update_messages() for examples.
	 * - 'update_messages_no_links' (array) Same as update_messages, but without
	 *   show/preview links to the post. This can be appropriate if the post
	 *   isn't supposed to be viewed in itself (probably has 'public' set to
	 *   false), like a post type for gallery images. See _update_messages()
	 *   for examples.
	 *
	 * @param string $post_type The unique post type name. Maximum 20
	 *   characters, can not contain capital letters or spaces.
	 * @param array $args Additional post type data.
	 * @see _update_messages() For message array structure.
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public function __construct( $post_type, array $args = array() ) {
		$this->name = (string) $post_type;
		$this->post_type_data = $args;
		$this->_add_hooks();
	}

	/**
	 * Add relevant hooks for post type functions.
	 */
	protected function _add_hooks() {
		add_action( 'wp_loaded', array( $this, '_add_post_type' ), 0 );
		add_action( 'admin_head', array( $this, '_admin_icons' ) );
		add_action( 'post_updated_messages', array( $this, '_update_messages' ) );
	}

	/**
	 * Register the custom post type.
	 */
	public function _add_post_type() {
		register_post_type(
			$this->name,
			$this->post_type_data['post_type_args']
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
	 * <code>
	 * 'update_messages' => array(
	 * 	'updated_view' => __( 'Movie updated. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
	 * 	'updated'      => __( 'Movie updated.', 'TEXTDOMAIN' ),
	 * 	'revision'     => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
	 * 	'published'    => __( 'Movie published. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
	 * 	'saved'        => __( 'Movie saved.', 'TEXTDOMAIN' ),
	 * 	'submitted'    => __( 'Movie submitted. <a target="_blank" href="%s">Preview movie</a>', 'TEXTDOMAIN' ),
	 * 	'scheduled'    => __( 'Movie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview movie</a>', 'TEXTDOMAIN' ),
	 * 	'draft'        => __( 'Movie draft updated. <a href="%s">Preview movie</a>', 'TEXTDOMAIN' )
	 * )
	 * </code>
	 *
	 * No links format:
	 * <code>
	 * 'update_messages_no_links' => array(
	 * 	'updated'   => __( 'Movie updated.', 'TEXTDOMAIN' ),
	 * 	'revision'  => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
	 * 	'published' => __( 'Movie published.', 'TEXTDOMAIN' ),
	 * 	'saved'     => __( 'Movie saved.', 'TEXTDOMAIN' ),
	 * 	'submitted' => __( 'Movie submitted.', 'TEXTDOMAIN' ),
	 * 	'scheduled' => __( 'Movie scheduled for: <strong>%1$s</strong>.', 'TEXTDOMAIN' ),
	 * 	'draft'     => __( 'Movie draft updated.', 'TEXTDOMAIN' )
	 * )
	 * </code>
	 *
	 * @param array $messages Default messages.
	 * @return array Message array with custom messages added.
	 */
	public function _update_messages( $messages ) {
		global $post;

		// Regular messages
		if ( ! empty( $this->post_type_data['update_messages'] ) ) :
			$msg = $this->post_type_data['update_messages'];

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
		elseif ( ! empty( $this->post_type_data['update_messages_no_links'] ) ) :
			$msg = $this->post_type_data['update_messages_no_links'];

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
	 * Custom icons for custom post types.
	 *
	 * The small image should be a 16x40 pixels sprite image, with the hover
	 * state on top of the regular state. The large icon should be 32x32 pixels.
	 */
	public function _admin_icons() {
		$post_type = $this->name;

		$small_icon = ( isset( $this->post_type_data['small_menu_icon_url'] ) )
			? $this->post_type_data['small_menu_icon_url']
			: '';

		$large_icon = ( isset( $this->post_type_data['large_menu_icon_url'] ) )
			? $this->post_type_data['large_menu_icon_url']
			: '';

		$css = '';

		// Small icon CSS
		if ( ! empty( $small_icon ) ) :
			$css .= "#menu-posts-{$post_type} .wp-menu-image {
				background: url('{$small_icon}') no-repeat 6px -17px !important;
			}
			#menu-posts-{$post_type}:hover .wp-menu-image,
			#menu-posts-{$post_type}.wp-has-current-submenu .wp-menu-image {
				background-position: 6px 7px !important;
			}";
		endif;

		// Large icon CSS
		if ( ! empty( $large_icon ) ) :
			$css .= ".icon32-posts-{$post_type} {
				background: url('{$large_icon}') no-repeat !important;
			}";
		endif;

		// Don't print an empty style tag
		if ( empty( $css ) ) return;

		// Remove newlines and tabs
		$output = str_replace( array( "\n", "\t" ), '', $css );

		echo "<style>{$output}</style>\n";
	}
}