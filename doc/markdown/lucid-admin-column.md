# Lucid\_Admin\_Column

Add custom columns to admin post lists.

Can be used both as a standalone class and as an argument for `Lucid_Post_Type`. This covers the standalone use case; for the post type, just pass the same below `column_data` to the `columns` argument.

## Arguments

The constructor takes two arguments:

* `post_type` **(string|array)** Which post type(s) to add the columns to. Can be a single one like `'page'`, an array like `array( 'post', 'page' )` or the special `'all'` to include all post types that has a screen (the type's `show_ui` is true).
* `column_data` **(array)** Each item is an array with data for that column.

### Column item (`column_data`) arguments

* `'id'` **(string)** A unique column ID.
* `'title'` **(string)** The column title.
* `'position'` **(int)** Optional. Zero-based position of the column relative to the existing WordPress ones; for example 1 to place it before the title column. Note that the position will depend on the current 'state'; adding multiple columns with the same position will effectively add them in reverse order of the `column_data` array. Defaults to 2.
* `'width'` **(string)** Optional. CSS width value, like '5em'.
* `'sorting'` **(string|callable)** Optional. Turn on sorting for the column, either by passing one of the built-in orderby parameters, like `'ID'` or `'date'`, or by passing a callback. The callback will be passed the main `WP_Query` object on which custom parameters can be set. See examples below.
* `'output'` **(callable)** Function that outputs the content for each post's cell in the column. The function is passed the $post_id.

## Example

This simple example adds a column to pages with the ID.

	$my_columns = new Lucid_Admin_Column( 'page', array(
		array(
			'id' => 'my_id_column',
			'title' => __( 'Page ID', 'TEXTDOMAIN' ),
			'position' => 1,
			'width' => '4em',
			'output' => 'my_id_column_output'
		),
		[...]
	) );

	function my_id_column_output( $post_id ) {
		echo $post_id;
	}

### Sorting

Sorting in the above example can easily be achieved with the built-in `orderby` value `'ID'`, which would be enabled by passing just that in the column array:

	[...]
	'sorting' => 'ID',
	'output' => 'my_id_column_output'
	[...]

See the [Codex entry on WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query) for the available options.

Sorting something more complex like a custom field value requires passing your own callback instead of the strings. The callback is called inside the `pre_get_posts` action and is passed the current `WP_Query` object. Below is an example with a custom date field on an event post type:

	$date_column = new Lucid_Admin_Column( 'event', array(
		array(
			'id' => 'my_event_date',
			'title' => __( 'Event date', 'TEXTDOMAIN' ),
			'output' => 'my_event_date_output',
			'sorting' => 'my_event_date_sort'
		)
	) );

	function my_event_date_output( $post_id ) {
		$date = get_post_meta( $post_id, '_my_event_date', true );
		echo ( $date ) ? $date : __( 'No date', 'TEXTDOMAIN' );
	}

	function my_event_date_sort( $query ) {
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', '_my_event_date' );
		$query->set( 'meta_type', 'DATE' );
	}

A downside with sorting on custom fields is that the `meta_key` parameter will cause posts without the targeted field to be skipped from the result, instead of just appearing last like one would expect in the admin list. In the above example, posts without a date set would simply not be shown. Including those posts will require filtering the generated SQL with filters like `get_meta_sql` and `posts_orderby`.

## Changelog

### 1.0.0: Mar 01, 2015

* Initial version.