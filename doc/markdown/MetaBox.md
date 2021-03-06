# Lucid\_WPAlchemy

A class for handling custom meta boxes, forked from the original `WPAlchemy_MetaBox` by Dimas Begunoff. The usage is mostly the same, but some differences exist.

* `area` argument for adding fields inline `after_title` or `after_editor`.
* The display options from the original are removed, as they didn't use standard WordPress ways of doing stuff.
* Various code style and execution tweaks.

A note outside the documentation that I tend to forget myself: prefix metabox ID with underscore to hide it in the custom fields view.

* [Documentation](http://www.farinspace.com/wpalchemy-metabox/)
* [Github](https://github.com/farinspace/wpalchemy)

## Fork changelog

### 1.6.1: Feb 18, 2018

* Fix: Don't count `null`, fixes PHP 7.2 warning.

### 1.6.0: Jan 08, 2017

Start adhering to semantic versioning.

* New: Handle cloning of multiple 'roots' for repeatable groups, instead of just the first one.
* New: Add a second parameter to `[get_]the_group_open` for adding extra classes to the group warpping element.

### 1.5.2.lucid-2, now Lucid\_WPAlchemy: Feb 23, 2015

With this version it's a complete fork and will not fully match the original class.

* New: Add defaults to checkbox, select and radio from upstream.
* Tweak: Change the class name to `Lucid_WPAlchemy`. If no `WPAlchemy_MetaBox` class exists, it will be defined and extend `Lucid_WPAlchemy` to keep backwards compatibility. This may be removed eventually.
* Tweak/fix: Improve the JavaScript for repeatable field's copy/delete and make it work when using the `area` argument.
* Tweak: Some general code style changes and cleanup.
* Remove: All display options have been removed: `hide_title`, `hide_editor`, `lock`, `view` and `hide_screen_option`. They were relying on JavaScript and CSS to accomplish their purpose and didn't feel future proof or maintainable. Removing the editor can be done on the post type itself and the `area` argument can handle different placements. The title should always be editable, even if it's not used publicly.

### 1.5.2.lucid-1: Dec 09, 2013

* New: Add `area` argument (string). Defaults to `metabox`, which doesn't do anything different. Can also be set to `after_title` or `after_editor`, which will add the metabox template content directly to the page in those places. Since it's not a meta 'box' in those cases, arguments like title, context etc. won't have any effect.
* Tweak: Move the htmlentities filtering from `get_the_value` to `the_value`, so that the pure data can be passed to a wp_editor instance.