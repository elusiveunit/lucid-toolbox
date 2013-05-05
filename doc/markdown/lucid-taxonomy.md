# Lucid\_Taxonomy

Handles custom taxonomies; registering, admin columns etc.

The format is very similar to the standard way of registering, with some additional arguments:

	$taxonomy_name = new Lucid_Taxonomy(
		'NAME',
		array( 'POST_TYPE_1', 'POST_TYPE_2' ),
		array(
			'taxonomy_args' => array(
				[...]
			)
		)
	);

The `taxonomy_args` array contains standard `register_taxonomy` arguments, see [the codex](http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments).

## Additional arguments

In addition to the standard `register_taxonomy` arguments, there are some additional customizations.

* `'default_terms'` **(array)** Default terms to set if none is chosen, just like 'uncategorized' for categories. Leave out to not set defaults. Hierarchical terms must always pass the ID rather than the term name to avoid confusion where there may be another child with the same name.
* `'taxonomy_args'` **(array)** The standard arguments for `register_taxonomy`, like `'hierarchical'`, `'labels'`, `'rewrite'` etc. See WordPress Codex link above.

## Labels

The labels argument in `taxonomy_args` accepts:

	'labels' => array(

		// menu_name default, use plural
		'name' =>            _x( 'Genres', 'taxonomy general name', 'TEXTDOMAIN' ),
		'singular_name' =>   _x( 'Genre', 'taxonomy singular name', 'TEXTDOMAIN' ),
		'all_items' =>       __( 'All genres', 'TEXTDOMAIN' ),
		'edit_item' =>       __( 'Edit genre', 'TEXTDOMAIN' ),
		'view_item' =>       __( 'View genre', 'TEXTDOMAIN' ),
		'update_item' =>     __( 'Update genre', 'TEXTDOMAIN' ),
		'add_new_item' =>    __( 'Add new genre', 'TEXTDOMAIN' ),
		'new_item_name' =>   __( 'New genre name', 'TEXTDOMAIN' ),
		'search_items' =>    __( 'Search genres', 'TEXTDOMAIN' ),

		// Hierarchical only
		'parent_item' =>       __( 'Parent genre', 'TEXTDOMAIN' ),
		'parent_item_colon' => __( 'Parent genre:', 'TEXTDOMAIN' ),

		// Non-hierarchical only
		'popular_items' =>              __( 'Popular genres', 'TEXTDOMAIN' ),
		'add_or_remove_items' =>        __( 'Add or remove genres', 'TEXTDOMAIN' ),
		'separate_items_with_commas' => __( 'Separate genres with commas', 'TEXTDOMAIN' ),
		'choose_from_most_used' =>      __( 'Choose from the most used genres', 'TEXTDOMAIN' ),
		'not_found' =>                  __( 'No genres found', 'TEXTDOMAIN' )
	)

## Complete example

To get an easier overview, here's a complete example.

	$my_genre_taxonomy = new Lucid_Taxonomy(
		'genre',
		array( 'movie' ),
		array(
			'taxonomy_args' => array(
				'hierarchical' => false,
				'labels' => array(

					// menu_name default, use plural
					'name' =>            _x( 'Genres', 'taxonomy general name', 'TEXTDOMAIN' ),
					'singular_name' =>   _x( 'Genre', 'taxonomy singular name', 'TEXTDOMAIN' ),
					'all_items' =>       __( 'All genres', 'TEXTDOMAIN' ),
					'edit_item' =>       __( 'Edit genre', 'TEXTDOMAIN' ),
					'view_item' =>       __( 'View genre', 'TEXTDOMAIN' ),
					'update_item' =>     __( 'Update genre', 'TEXTDOMAIN' ),
					'add_new_item' =>    __( 'Add new genre', 'TEXTDOMAIN' ),
					'new_item_name' =>   __( 'New genre name', 'TEXTDOMAIN' ),
					'search_items' =>    __( 'Search genres', 'TEXTDOMAIN' ),

					// Non-hierarchical only
					'popular_items' =>              __( 'Popular genres', 'TEXTDOMAIN' ),
					'add_or_remove_items' =>        __( 'Add or remove genres', 'TEXTDOMAIN' ),
					'separate_items_with_commas' => __( 'Separate genres with commas', 'TEXTDOMAIN' ),
					'choose_from_most_used' =>      __( 'Choose from the most used genres', 'TEXTDOMAIN' ),
					'not_found' =>                  __( 'No genres found', 'TEXTDOMAIN' )
				),
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count'
			)
		)
	);

## Changelog

### 1.1.1: Apr 14, 2013

* Tweak: Change the select list width 'algorithm' and prevent double digit result.

### 1.1.0: Mar 27, 2013

* Initial public release.