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
		),
		'columns' => array(
			[...]
		)
	) );

The `post_type_args` array contains standard `register_post_type` arguments, see [the codex](http://codex.wordpress.org/Function_Reference/register_post_type#Arguments).

The `init` hook is a good one to use for registering.

## Additional arguments

In addition to the standard `register_post_type` arguments, there are some additional customizations.

* `'post_type_args'` **(array)** The standard arguments for `register_post_type`, like `'hierarchical'`, `'labels'`, `'supports'` etc. See WordPress Codex link above.
* `'update_messages'` **(array)** Update messages to display instead of the standard `'post updated'` etc. See below for examples.
* `'update_messages_no_links'` **(array)** Same as `update_messages`, but without show/preview links to the post. This can be appropriate if the post isn't supposed to be viewed in itself (probably has `'public'` set to false), like a post type for gallery images. See below for examples.
* `'columns'` **(array)** Custom columns for the admin post list. See `Lucid_Admin_Column` for the arguments to use.

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
			'menu_icon' => 'dashicons-palmtree',
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

### 1.3.0: Mar 01, 2015

* New: Add `'columns'` argument for adding custom admin columns to the post type, using `Lucid_Admin_Column`.

### 1.2.1: Feb 23, 2015

* Tweak: Deprecate the custom icon arguments, use the built-in `menu_icon` instead.

### 1.2.0: Dec 09, 2013

* New: Add 'icon' argument for the new Dashicons in WordPress 3.8.

### 1.1.2: Aug 25, 2013

* Tweak: Check the post type name before registering and trigger errors if it's invalid.

### 1.1.1: May 22, 2013

* Tweak: Register post type in the constructor, allowing the user to choose the hook.

### 1.1.0: Mar 27, 2013

* Initial public release.
* New/tweak: Change the way update messages are handled.
* Tweak: 'Minify' icon CSS by removing unnecessary whitespace.

### 1.0.0

* Initial version.