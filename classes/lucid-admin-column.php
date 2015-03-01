<?php
/**
 * Admin columns class definition.
 *
 * @package Lucid\Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Add custom columns to admin post lists.
 *
 *     $my_columns = new Lucid_Admin_Column( 'page', array(
 *        array(
 *           'id' => 'my_id_column',
 *           'title' => __( 'Page ID', 'TEXTDOMAIN' ),
 *           'position' => 1,
 *           'width' => '5em',
 *           'output' => 'my_id_column_output'
 *        ),
 *        [...]
 *     ) );
 *
 *     function my_id_column_output( $post_id ) {
 *        echo $post_id;
 *     }
 *
 * @package Lucid\Toolbox
 * @version 1.0.0
 */
class Lucid_Admin_Column {

	/**
	 * Post types to add the columns to.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $_post_types = array();

	/**
	 * Column data.
	 *
	 * @since 1.0.0
	 * @var array
	 * @see __constructor
	 */
	protected $_data = array();

	/**
	 * Constructor, add hooks.
	 *
	 * @since 1.0.0
	 * @param string|array $post_type Post type(s) to add columns to. Use 'all'
	 *    as a shortcut for adding to every post type.
	 * @param array $column_data {
	 *    Data for each column. Each item should be an array with the following
	 *    keys:
	 *
	 *    @type string $id A unique column ID.
	 *    @type string $title The column title.
	 *    @type int $position Optional. Zero-based position of the column
	 *       relative to the existing WordPress ones; for example 1 to place it
	 *       before the title column. Note that the position will depend on the
	 *       current 'state'; adding multiple columns with the same position will
	 *       effectively add them in reverse order of the $column_data array.
	 *       Defaults to 2.
	 *    @type string $width Optional. CSS width value, like '5em'.
	 *    @type string|callable $sorting Optional. Turn on sorting for the
	 *       column, either by passing one of the built-in orderby parameters,
	 *       like 'ID' or 'date', or by passing a callback. The callback will
	 *       be passed the main WP_Query object on which custom parameters can
	 *       be set.
	 *    @type callable $output Function that outputs the content for each
	 *       post's cell in the column. The function is passed the $post_id.
	 * }
	 */
	public function __construct( $post_type, array $column_data ) {
		$this->_post_types = (array) $post_type;
		$this->_data = $column_data;

		add_action( 'load-edit.php', array( $this, '_init' ) );
	}

	/**
	 * Set default column values and run necessary functionality.
	 *
	 * @since 1.0.0
	 */
	public function _init() {

		// Set default values. Null values are required and validated in
		// _validate_data().
		$column_defaults = array(
			'id' => null,
			'title' => null,
			'position' => 2,
			'width' => '',
			'sorting' => false,
			'output' => null
		);
		foreach ( $this->_data as $i => $column ) :
			if ( ! is_array( $column ) ) :
				trigger_error( 'Each $column_data item must be an array', E_USER_WARNING );
				return;
			else :
				$this->_data[$i] = array_merge( $column_defaults, $column );
			endif;
		endforeach;

		$this->_validate_data();
		$this->_add_hooks();
	}

	/**
	 * Validate the column data, warn if there are errors.
	 *
	 * @since 1.0.0
	 */
	protected function _validate_data() {
		$required = array(
			'id',
			'title',
			'output'
		);

		foreach ( $this->_data as $i => $column ) :
			$is_valid = true;

			if ( ! $column['id'] ) :
				trigger_error( 'A column must have an ID', E_USER_WARNING );
				$is_valid = false;
			else :
				foreach ( $required as $key ) :
					if ( ! $column[$key] ) :
						trigger_error( sprintf( 'Column "%s" is missing key "%s"', $column['id'], $key ), E_USER_WARNING );
						$is_valid = false;
					elseif ( 'output' == $key && ! is_callable( $column[$key] ) ) :
						trigger_error( sprintf( '"output" in column "%s" must be a callable function', $column['id'] ), E_USER_WARNING );
						$is_valid = false;
					endif;
				endforeach;
			endif;

			$this->_data[$i]['is_valid'] = $is_valid;
		endforeach;
	}

	/**
	 * Add the required hooks for the columns.
	 *
	 * @since 1.0.0
	 * @link https://developer.wordpress.org/reference/hooks/manage_post_type_posts_columns/
	 * @link https://developer.wordpress.org/reference/hooks/manage_posts_custom_column/
	 * @link https://developer.wordpress.org/reference/hooks/manage_pages_custom_column/
	 */
	protected function _add_hooks() {
		$sort_query = false;

		foreach ( $this->_post_types as $type ) :
			if ( 'all' == $type ) :

				// Skip built-in ones here and add post and page manually, to avoid
				// the 'hidden' ones like nav menu items.
				$all_types = get_post_types( array(
					'_builtin' => false,
					'show_ui' => true
				) );
				$all_types[] = 'post';
				$all_types[] = 'page';

				foreach ( $all_types as $type )
					add_filter( "manage_{$type}_posts_columns", array( $this, '_add_columns' ) );

				add_action( 'manage_posts_custom_column', array( $this, '_populate_columns' ), 10, 2 );
				add_action( 'manage_pages_custom_column', array( $this, '_populate_columns' ), 10, 2 );
			else :
				add_filter( "manage_{$type}_posts_columns", array( $this, '_add_columns' ) );
				add_action( "manage_{$type}_posts_custom_column", array( $this, '_populate_columns' ), 10, 2 );
			endif;

			// Only add one filter for the post type, not one for each column
			$add_sorting = false;
			foreach ( $this->_data as $column ) :
				$sort = $column['sorting'];

				if ( $column['is_valid'] && $sort ) :
					$add_sorting = true;

					if ( $this->_is_callback_sort( $sort ) ) :
						$sort_query = true;
					endif;
				endif;
			endforeach;

			if ( $add_sorting )
				add_filter( "manage_edit-{$type}_sortable_columns", array( $this, '_add_column_sorting' ) );
		endforeach;

		if ( $sort_query )
			add_action( 'pre_get_posts', array( $this, '_sort_query' ) );

		add_action( 'admin_head', array( $this, '_column_styles' ) );
	}

	/**
	 * Insert the custom columns.
	 *
	 * @since 1.0.0
	 * @param array $columns Default columns.
	 */
	public function _add_columns( $columns ) {
		foreach ( $this->_data as $column ) :
			if ( $column['is_valid'] ) :
				$new = array( $column['id'] => $column['title'] );
				$columns = $this->_array_insert( $columns, $new, $column['position'] );
			endif;
		endforeach;

		return $columns;
	}

	/**
	 * Call column output functions.
	 *
	 * @since 1.0.0
	 * @param string $current_column ID of the current column.
	 * @param int $post_id Post ID for the current column cell.
	 */
	public function _populate_columns( $current_column, $post_id ) {
		foreach ( $this->_data as $column ) :
			if ( $column['is_valid'] && $column['id'] == $current_column ) :
				$column['output']( $post_id );
			endif;
		endforeach;
	}

	/**
	 * Add sorting to columns with the 'sorting' parameter.
	 *
	 * @since 1.0.0
	 * @param array $sortable_columns Columns that should be sortable. The key
	 *    id the column ID and the value is the query 'orderby' value.
	 */
	public function _add_column_sorting( $sortable_columns ) {
		foreach ( $this->_data as $column ) :
			$sort = $column['sorting'];

			if ( $sort ) :
				if ( $this->_is_callback_sort( $sort ) ) :
					$sort = 'lucid_' . $column['id'];
					$sortable_columns[$column['id']] = $sort;
				elseif ( is_string( $sort ) ) :
					$sortable_columns[$column['id']] = $sort;
				endif;
			endif;
		endforeach;

		return $sortable_columns;
	}

	/**
	 * Call the 'sorting' param callback for columns that have it.
	 *
	 * @since 1.0.0
	 * @param WP_Query $query The current query object.
	 */
	public function _sort_query( $query ) {
		$orderby = $query->get( 'orderby' );

		if ( is_admin() && $query->is_main_query() && 0 === strpos( $orderby, 'lucid_' ) ) :
			foreach ( $this->_data as $column ) :
				if ( 'lucid_' . $column['id'] == $orderby ) :
					$column['sorting']( $query );
				endif;
			endforeach;
		endif;
	}

	/**
	 * Add column width styles.
	 *
	 * @since 1.0.0
	 */
	public function _column_styles() {
		$output = '';

		foreach ( $this->_data as $column ) :
			if ( $column['is_valid'] && $column['width'] ) :
				$output .= sprintf( '.column-%s{width:%s;}', $column['id'], $column['width'] );
			endif;
		endforeach;

		if ( $output )
			echo "<style>{$output}</style>\n";
	}

	/**
	 * Check if a column's 'sorting' parameter is a custom callback.
	 *
	 * Passing 'date' for exmaple will make is_callable return true due to the
	 * PHP date function, so check for the built-in orderby params before
	 * checking if it's a callable.
	 *
	 * @since 1.0.0
	 * @param mixed $sort Value to check.
	 * @return bool
	 */
	protected function _is_callback_sort( $sort ) {
		$built_in_sorting = array(
			'none',
			'ID',
			'author',
			'title',
			'name',
			'type',
			'date',
			'modified',
			'parent',
			'rand',
			'comment_count',
			'menu_order',
			'meta_value',
			'meta_value_num',
			'meta_type',
			'post__in'
		);

		return ( ! in_array( $sort, $built_in_sorting ) && is_callable( $sort ) );
	}

	/**
	 * Insert an array into another at the specified position.
	 *
	 * @since 1.0.0
	 * @param array $source Original array.
	 * @param array $new Array to insert.
	 * @param int $pos Position to insert at.
	 * @return array The combined array.
	 */
	protected function _array_insert( $source, $new, $pos ) {
		$left = array_slice( $source, 0, $pos, true );
		$right = array_slice( $source, $pos, null, true );

		return array_merge( $left, $new, $right );
	}
}
