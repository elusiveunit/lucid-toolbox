# Lucid\_Post\_Type

Handles custom post types; registering, update messages etc.

The format is very similar to the standard way of registering, with some additional arguments:

	$post_type_name = new Lucid_Post_Type( 'NAME', array(
		'small_menu_icon_url' => '[path_to_image_directory]/16x40_sprite.png',
		'large_menu_icon_url' => '[path_to_image_directory]/32x32.png',
		'post_type_args' => array(
			[...]
		),
		'update_messages' => array(
			[...]
		)
	) );

The `post_type_args` array contains standard `register_post_type` arguments, see [the codex](http://codex.wordpress.org/Function_Reference/register_post_type#Arguments).

## Additional arguments

In addition to the standard `register_post_type` arguments, there are some additional customizations.

* `'small_menu_icon_url'` **(string)** Absolute url to to a 16x40 pixels sprite image to use as admin menu icon for the post type. The hover state should be on top of the regular state in the image.
* `'large_menu_icon_url'` **(string)** Absolute url to a 32x32 image to use as the icon beside the heading in the post edit screen.
* `'post_type_args'` **(array)** The standard arguments for `register_post_type`, like `'hierarchical'`, `'labels'`, `'supports'` etc. See WordPress Codex link above.
* `'update_messages'` **(array)** Update messages to display instead of the standard `'post updated'` etc. See below for examples.
* `'update_messages_no_links'` **(array)** Same as `update_messages`, but without show/preview links to the post. This can be appropriate if the post isn't supposed to be viewed in itself (probably has `'public'` set to false), like a post type for gallery images. See below for examples.

## Labels

The labels argument in `post_type_args` accepts:

	'labels' => array(

		// menu_name default, use plural
		'name' =>               _x( 'Movies', 'post type general name', 'TEXTDOMAIN' ),
		'singular_name' =>      _x( 'Movie', 'post type singular name', 'TEXTDOMAIN' ),
		'all_items' =>          __( 'All movies', 'TEXTDOMAIN' ),
		'add_new' =>            __( 'Add new', 'TEXTDOMAIN' ),
		'add_new_item' =>       __( 'Add new movie', 'TEXTDOMAIN' ),
		'edit_item' =>          __( 'Edit movie', 'TEXTDOMAIN' ),
		'new_item' =>           __( 'New movie', 'TEXTDOMAIN' ),
		'view_item' =>          __( 'View movie', 'TEXTDOMAIN' ),
		'search_items' =>       __( 'Search movies', 'TEXTDOMAIN' ),
		'not_found' =>          __( 'No movies found', 'TEXTDOMAIN' ),
		'not_found_in_trash' => __( 'No movies found in trash', 'TEXTDOMAIN' ),

		// Hierarchical only
		'parent_item_colon' =>  __( 'Parent movie:', 'TEXTDOMAIN' )
	)

## Update messages

There are two possible variants for update messages. The regular `'update_messages'` takes precedence, should both be provided.

Regular messages:

	'update_messages' => array(
		'updated_view' => __( 'Movie updated. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
		'updated'      => __( 'Movie updated.', 'TEXTDOMAIN' ),
		'revision'     => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
		'published'    => __( 'Movie published. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
		'saved'        => __( 'Movie saved.', 'TEXTDOMAIN' ),
		'submitted'    => __( 'Movie submitted. <a target="_blank" href="%s">Preview movie</a>', 'TEXTDOMAIN' ),
		'scheduled'    => __( 'Movie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview movie</a>', 'TEXTDOMAIN' ),
		'draft'        => __( 'Movie draft updated. <a href="%s">Preview movie</a>', 'TEXTDOMAIN' )
	)

Messages without links:

	'update_messages_no_links' => array(
		'updated'   => __( 'Movie updated.', 'TEXTDOMAIN' ),
		'revision'  => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
		'published' => __( 'Movie published.', 'TEXTDOMAIN' ),
		'saved'     => __( 'Movie saved.', 'TEXTDOMAIN' ),
		'submitted' => __( 'Movie submitted.', 'TEXTDOMAIN' ),
		'scheduled' => __( 'Movie scheduled for: <strong>%1$s</strong>.', 'TEXTDOMAIN' ),
		'draft'     => __( 'Movie draft updated.', 'TEXTDOMAIN' )
	)

## Complete example

To get an easier overview, here's a complete example.

	$my_movie_post_type = new Lucid_Post_Type( 'movie', array(
		'small_menu_icon_url' => $my_plugin_url . 'img/icon-movie.png',
		'post_type_args' => array(
			'hierarchical' => true,
			'labels' => array(
				// menu_name default, use plural
				'name' =>               _x( 'Movies', 'post type general name', 'TEXTDOMAIN' ),
				'singular_name' =>      _x( 'Movie', 'post type singular name', 'TEXTDOMAIN' ),
				'all_items' =>          __( 'All movies', 'TEXTDOMAIN' ),
				'add_new' =>            __( 'Add new', 'TEXTDOMAIN' ),
				'add_new_item' =>       __( 'Add new movie', 'TEXTDOMAIN' ),
				'edit_item' =>          __( 'Edit movie', 'TEXTDOMAIN' ),
				'new_item' =>           __( 'New movie', 'TEXTDOMAIN' ),
				'view_item' =>          __( 'View movie', 'TEXTDOMAIN' ),
				'search_items' =>       __( 'Search movies', 'TEXTDOMAIN' ),
				'not_found' =>          __( 'No movies found', 'TEXTDOMAIN' ),
				'not_found_in_trash' => __( 'No movies found in trash', 'TEXTDOMAIN' ),
				'parent_item_colon' =>  __( 'Parent movie:', 'TEXTDOMAIN' )
			),
			'show_in_nav_menus' => false,
			'menu_position' => 5,
			'supports' => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'revisions',
				'page-attributes'
			),
			'taxonomies' => array( 'category', 'post_tag' )
		),
		'update_messages' => array(
			'updated_view' => __( 'Movie updated. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
			'updated'      => __( 'Movie updated.', 'TEXTDOMAIN' ),
			'revision'     => __( 'Movie restored to revision from %s.', 'TEXTDOMAIN' ),
			'published'    => __( 'Movie published. <a href="%s">View movie</a>', 'TEXTDOMAIN' ),
			'saved'        => __( 'Movie saved.', 'TEXTDOMAIN' ),
			'submitted'    => __( 'Movie submitted. <a target="_blank" href="%s">Preview movie</a>', 'TEXTDOMAIN' ),
			'scheduled'    => __( 'Movie scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview movie</a>', 'TEXTDOMAIN' ),
			'draft'        => __( 'Movie draft updated. <a href="%s">Preview movie</a>', 'TEXTDOMAIN' )
		)
	) );

## Changelog

### 1.1.0: Mar 27, 2013

* Initial public release.
* New/tweak: Change the way update messages are handled.
* Tweak: 'Minify' icon CSS by removing unnecessary whitespace.

### 1.0.0

* Initial version.