<?php
/**
 * Custom taxonomy class definition.
 *
 * @package Lucid\Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Handles custom taxonomies; registering, admin columns etc.
 *
 * The format is very similar to the standard way of registering, with some
 * additional arguments:
 *
 *     $taxonomy_name = new Lucid_Taxonomy(
 *        'NAME',
 *        array( 'POST_TYPE_1', 'POST_TYPE_2' ),
 *        array(
 *           'taxonomy_args' => array(
 *              [...]
 *           )
 *        )
 *     );
 *
 * The taxonomy_args array contains standard register_taxonomy arguments, see
 * {@link http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments the codex}.
 *
 * The labels argument in taxonomy_args accepts:
 *
 *     'labels' => array(
 *
 *        // menu_name default, use plural
 *        'name' =>            _x( 'Genres', 'taxonomy general name', 'TEXTDOMAIN' ),
 *        'singular_name' =>   _x( 'Genre', 'taxonomy singular name', 'TEXTDOMAIN' ),
 *        'all_items' =>       __( 'All genres', 'TEXTDOMAIN' ),
 *        'edit_item' =>       __( 'Edit genre', 'TEXTDOMAIN' ),
 *        'view_item' =>       __( 'View genre', 'TEXTDOMAIN' ),
 *        'update_item' =>     __( 'Update genre', 'TEXTDOMAIN' ),
 *        'add_new_item' =>    __( 'Add new genre', 'TEXTDOMAIN' ),
 *        'new_item_name' =>   __( 'New genre name', 'TEXTDOMAIN' ),
 *        'search_items' =>    __( 'Search genres', 'TEXTDOMAIN' ),
 *
 *        // Hierarchical only
 *        'parent_item' =>       __( 'Parent genre', 'TEXTDOMAIN' ),
 *        'parent_item_colon' => __( 'Parent genre:', 'TEXTDOMAIN' ),
 *
 *        // Non-hierarchical only
 *        'popular_items' =>              __( 'Popular genres', 'TEXTDOMAIN' ),
 *        'add_or_remove_items' =>        __( 'Add or remove genres', 'TEXTDOMAIN' ),
 *        'separate_items_with_commas' => __( 'Separate genres with commas', 'TEXTDOMAIN' ),
 *        'choose_from_most_used' =>      __( 'Choose from the most used genres', 'TEXTDOMAIN' ),
 *        'not_found' =>                  __( 'No genres found', 'TEXTDOMAIN' )
 *     )
 *
 * @package Lucid\Toolbox
 * @version 1.1.3
 */
class Lucid_Taxonomy {

	/**
	 * The taxonomy name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $name;

	/**
	 * The post type(s) that get the taxonomy.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $to_post_types;

	/**
	 * The taxonomy.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $taxonomy_data = array();

	/**
	 * Constructor, pass taxonomy.
	 *
	 * Arguments through the $args array:
	 *
	 * - 'default_terms' (array) Default terms to set if none is chosen, just
	 *   like 'uncategorized' for categories. Leave out to not set defaults.
	 *   Hierarchical terms must always pass the ID rather than the term name
	 *   to avoid confusion where there may be another child with the same name.
	 * - 'taxonomy_args' (array) The standard arguments for register_taxonomy,
	 *   like 'hierarchical', 'labels', 'rewrite' etc. See WordPress Codex.
	 *
	 * @since 1.0.0
	 * @param string $taxonomy The unique taxonomy name. Maximum 32 characters
	 *   and can not contain capital letters or spaces. See WordPress Codex for
	 *   a list of reserved terms.
	 * @param array $to_post_types Post types to attach the taxonomy to. Can be
	 *   built-in ones or any custom post type.
	 * @param array $args Additional taxonomy data.
	 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
	 */
	public function __construct( $taxonomy, $to_post_types, array $args = array() ) {
		$this->name = (string) $taxonomy;

		if ( ! $this->_is_valid_taxonomy_name() )
			return;

		$this->to_post_types = (array) $to_post_types;
		$this->taxonomy_data = $args;

		$this->_add_taxonomy();
		$this->_add_hooks();
	}

	/**
	 * Check if the taxonomy name is valid and show errors if not.
	 *
	 * @since 1.1.3
	 * @return bool
	 */
	protected function _is_valid_taxonomy_name() {
		$name_valid = true;

		// Maximum 32 characters long
		if ( strlen( $this->name ) > 32 ) :
			trigger_error( sprintf( "Taxonomy name '%s' can be no more than 32 characters long", $this->name ), E_USER_WARNING );
			$name_valid = false;
		endif;

		// No capital letters or spaces
		if ( preg_match( '/[A-Z\s]/', $this->name ) ) :
			trigger_error( sprintf( "Taxonomy name '%s' can not contain capital letters or spaces", $this->name ), E_USER_WARNING );
			$name_valid = false;
		endif;

		return $name_valid;
	}

	/**
	 * Add relevant hooks for taxonomy functions.
	 *
	 * @since 1.0.0
	 */
	private function _add_hooks() {
		add_action( 'restrict_manage_posts', array( $this, '_restrict_posts_by_taxonomy' ) );
		add_filter( 'parse_query', array( $this, '_convert_taxonomy_id_to_term_in_query' ) );
		add_action( 'admin_head', array( $this, '_admin_css' ) );
		add_action( 'save_post', array( $this, '_default_terms' ), 100, 2 );
	}

	/**
	 * Register custom taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function _add_taxonomy() {
		register_taxonomy(
			$this->name,
			$this->to_post_types,
			$this->taxonomy_data['taxonomy_args']
		);
	}

	/**
	 * Generate a drop down for custom taxonomy filtering above the list of
	 * posts.
	 *
	 * @since 1.0.0
	 * @global string $typenow Current post type.
	 * @global WP_Query $wp_query Current query object.
	 */
	public function _restrict_posts_by_taxonomy() {
		global $typenow;
		global $wp_query;
		$taxonomy = $this->name;

		if ( in_array( $typenow, $this->to_post_types ) ) {
			$current_taxonomy = get_taxonomy( $taxonomy );
			$term = isset( $wp_query->query[$taxonomy] ) ? $wp_query->query[$taxonomy] : '';

			wp_dropdown_categories( array(
				'show_option_all' => sprintf( __( 'Show all %s', 'lucid-toolbox' ), strtolower( $current_taxonomy->label ) ),
				'taxonomy'        => $taxonomy,
				'name'            => $current_taxonomy->name,
				'orderby'         => 'name',
				'selected'        => $term,
				'hierarchical'    => true,
				'depth'           => 3,
				'show_count'      => false, // Show post count, i.e. (3)
				'hide_empty'      => true, // Hide taxonomy with no posts
				'hide_if_empty'   => true // Hide dropdown if no terms returned
			) );
		}
	}

	/**
	 * Filter post list based on the selected taxonomy.
	 *
	 * @since 1.0.0
	 * @global string $pagenow Current admin page.
	 * @param object $query The current WP_Query object.
	 */
	public function _convert_taxonomy_id_to_term_in_query( $query ) {
		global $pagenow;

		$taxonomy = $this->name;
		$vars = &$query->query_vars;

		if ( $pagenow == 'edit.php'
		  && isset( $vars[$taxonomy] )
		  && is_numeric( $vars[$taxonomy] ) ) :
			$term = get_term_by( 'id', $vars[$taxonomy], $taxonomy );
			$vars[$taxonomy] = $term ? $term->slug : '';
		endif;
	}

	/**
	 * Style the filter select box.
	 *
	 * By default the right edge of the select box is right against the text.
	 * This sets a min-width depending on character count, minus a totally trial
	 * and error number, so it's not too wide.
	 *
	 * @since 1.0.0
	 */
	public function _admin_css() {
		$tax = $this->name;
		$len = strlen( $tax );
		$count = $len - round( ( sqrt( $len ) ) * 2.25 );
		if ( $count > 9 ) $count = 9;

		$css = ".tablenav select#{$tax} {min-width: 1{$count}em !important;}";

		echo "<style>{$css}</style>\n";
	}

	/**
	 * Define default terms for custom taxonomies.
	 *
	 * Unlike categories, custom taxonomies don't have a default term if none is
	 * chosen.
	 *
	 * @since 1.1.0
	 * @param int $post_id ID of current post being saved.
	 * @param object $post Current post object.
	 * @link wordpress.mfields.org/?p=311
	 * @link http://codex.wordpress.org/Function_Reference/wp_set_post_terms
	 */
	public function _default_terms( $post_id, $post ) {
		if ( 'publish' === $post->post_status
		  && isset( $this->taxonomy_data['default_terms'] ) ) :

			$terms = wp_get_post_terms( $post_id, $this->name );

			if ( empty( $terms ) ) :
				wp_set_post_terms(
					$post_id,
					(array) $this->taxonomy_data['default_terms'],
					$this->name
				);
			endif;

		endif;
	}
}