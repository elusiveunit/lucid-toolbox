# Lucid\_Settings

Simple settings page generation, using the Settings API.

Setup is simple, create a new settings object with a unique ID and a heading:

	$example_settings = new Lucid_Settings( 'example_settings_id' );
	$example_settings->page_heading = __( 'My settings', 'TEXTDOMAIN' );

Then add each part with:

* `$example_settings->submenu( [...] );`
* `$example_settings->section( [...] );`
* `$example_settings->field( [...] );`

Finally initialize the page with `$example_settings->init();`.

Settings are accessed with the ID (`get_option( 'example_settings_id' )`) when using a single page and tab ID when using tabs. To use a tabbed settings page, simply add a 'tabs' array to `submenu()`, see below.

## Properties

Some options are controlled through properties: `$instance->prop = 'value'`.

* `capability` **(string)** Capability required to edit the settings. Defaults to `'manage_options'`.
* `pass_settings_errors_id` **(bool)** *Should no longer be needed as of 1.5.1*. Whether to pass setting ID to `settings_errors`. This is sometimes needed to avoid multiple update messages, other times it causes update messages to not be displayed at all. I have yet to find the reason for the issue. Defaults to false.

## Submenu

The `submenu` method requires a menu label text, and accepts some optional arguments through an array:

* `'add_to'` **(string)** Slug for the parent menu, or the file name of a standard WordPress admin page (wp-admin/<file_name>). Includes .php extension and defaults to `'options-general.php'`.
* `'title'` **(string)** HTML `<title>` text, defaults to the menu label.
* `'tabs'` **(array)** Tabs to add, format `'unique_id' => 'Tab label'`.
* `'capability'` **(string)** Capability needed to edit the settings. If not set, the $capability property is used, which defaults to `manage_options`.

Example:

	$example_settings->submenu( __( 'Menu label', 'TEXTDOMAIN' ), array(
		'title' => __( 'HTML title element text', 'TEXTDOMAIN' ),
		'add_to' => 'themes.php',
		'tabs' => array(
			'my_general_settings' => __( 'General', 'TEXTDOMAIN' ),
			'my_advanced_settings' => __( 'Advanced', 'TEXTDOMAIN' )
		)
	) );

When using tabs, each tab is saved as a separate option (`get_option( 'my_advanced_settings' )`).

## Section

Fields are added to sections, so at least one section must be added. The `section` method requires an ID, and accepts some optional arguments through an array:

* `'heading'` **(string)** Section heading.
* `'tab'` **(string)** Tab to add section to. Tabs are defined with `submenu()`. Defaults to first tab if there are any.
* `'output'` **(string)** HTML to display at the top of the section, below the heading.

## Field

The `field` method requires an ID and a label, and accepts additional arguments through an array (see the pattern?):

* `'type'` **(string)** Type of field. Fields without specific 'support' will fall back to just an input with the specified type. Supported types:
  * `'text'`
  * `'text_monospace'`
  * `'textarea'`
  * `'textarea_large'`
  * `'textarea_monospace'`
  * `'textarea_large_monospace'`
  * `'editor'`
  * `'checkbox'`
  * `'checklist'` (List of checkboxes)
  * `'radios'`
  * `'select'`
  * `'post_select'`
  * `'page_select'`
  * `'color_picker'`
  * `'button_field'` (Text field with a button beside it)
  * `'button_field_monospace'`
* `'section'` **(string)** Section to add the field to, defined with `section()`.
* `'default'` **(mixed)** Default field value. Is only set if options don't exist, so will probably only run on theme/plugin activation.
* `'description'` **(string)** A help text to show under the field. Prints unfiltered, so beware if user input is somehow involved.
* `'inline_label'` **(string)** Field label for checkbox and radio button.
* `'options'` **(array)** Options for types `'select'`, `'radios'`, and `'checklist'`, format: `value => text`.
* `'button_text'` **(string)** Text for the button when using button_field.
* `'select_post_type'` (string) Post type to use when using `post_select` or `page_select`. Defaults to `'post'` for `post_select` and `'page'` for `page_select`.
* `'validate'` **(string)** Validate value against predefined functions, see below.
* `'must_match'` **(regex)** A regular expression that is matched against the value, i.e. `'/^\d{3}$/'` to require exactly three digits.
* `'must_not_match'` **(regex)** A regular expression that is matched against the value, where the result is reversed. So something like `'/\d{3}/'` would mean the value can not contain three digits in a row.
* `'error_message'` **(string)** Message for when validation fails.
* `'sanitize'` **(string)** Sanitize value against predefined functions, see below. Defaults to `'checkbox'` for checkboxes.
* `'sanitize_custom'` **(regex)** Sanitize value with a regular expression. Value will go through preg_replace.
* `'output_callback'` **(callback)** Custom method for the field output, see _Custom output callback_ below.

When the data is passed through the required checks, an explicitly defined sanitize value of `'none'` is required to save unfiltered data. Any sanitize or validate values take precedence. If no sanitation or validation is defined, the default action is stripping illegal tags with [wp_kses_post](http://codex.wordpress.org/Function_Reference/wp_kses_post).

Wrapping field registration with a `is_on_settings_page` if statement is a good idea, to limit unnecessary function calls on other admin pages.

### Predefined validation

There are a few predefined validation options:

* `'email'` Email address, uses the PHP `FILTER_VALIDATE_EMAIL`
* `'url'` URL, uses a modified regex by [John Gruber](http://daringfireball.net/2010/07/improved_regex_for_matching_urls)
* `'hex_color'` Hexadecimal color (like 101 or bada55, hash is optional)

### Predefined sanitation

There are a few predefined sanitation options:

* `'checkbox'` Always 1 or 0.
* `'int'` Integer, positive or negative.
* `'absint'` Non-negative integer through [absint](http://codex.wordpress.org/Function_Reference/absint).
* `'float'` Floating point number through [floatval](http://php.net/floatval).
* `'alphanumeric'` Letters, numbers, underscore and dash.
* `'url'` Escapes a URL with [esc_url_raw](http://codex.wordpress.org/Function_Reference/esc_url_raw).
* `'no_html'` Strips HTML with [strip_tags](http://php.net/strip_tags).
* `'shortcode'` Removes greater/less than and forces enclosing square brackets.
* `'empty'` No value. Useful for fields acting as 'tools' that shouldn't save anything.

### Custom output callback

For fine-grained control of the output, a custom callback can be used. The format is standard PHP, so a string for regular functions and the array notation for class methods.

	$example_settings->field(
		'my_field',
		__( 'My field', 'TEXTDOMAIN' ),
		array(
			'sanitize' => 'alphanumeric',
			'output_callback' => 'my_field_callback'
		)
	);

	/**
	 * Custom settings field callback.
	 *
	 * @param array $args Field options.
	 */
	function my_field_callback( $args ) { ?>

		<input type="text" id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" value="<?php echo esc_attr( $args['value'] ); ?>">

	<?php }

Arguments like `sanitize` that are not related to output work the same. The `$args` parameter contains all the `field` arguments, as well as some extras like `prefix`, `id` and `value`. Keeping the id, name and value attributes like the example is recommended, to ensure labels and saving works as intended.

## HTML

Arbitrary HTML can be added with the `html` method, which takes two arguments:

* A field ID, which the HTML will be inserted after.
* The string of HTML to be added.

An alternative is to use the `field` method with an empty label and a custom output callback. The benefit with that is that it's not dependant on another field.

## Complete examples

Since there are quite a few options, here are some examples to get the gist of it.

	// Setup
	$example_settings = new Lucid_Settings( 'example_settings', __( 'My example settings', 'TEXTDOMAIN' ) );

	$example_settings->submenu( 'Example settings', array(
		'tabs' => array(
			'my_general_settings' => _x( 'General', 'Settings tab', 'TEXTDOMAIN' ),
			'my_advanced_settings' => _x( 'Advanced', 'Settings tab', 'TEXTDOMAIN' )
		)
	) );

	// Sections
	$example_settings->section( 'first_section', array(
		'heading' => __( 'My first section', 'TEXTDOMAIN' ),
		'tab' => 'my_general_settings'
	) );

	$example_settings->section( 'second_section', array(
		'heading' => __( 'My second section', 'TEXTDOMAIN' ),
		'tab' => 'my_advanced_settings'
	) );

	if ( $example_settings->is_on_settings_page() ) :

		// Fields
		$example_settings->field(
			'my_text',
			__( 'Text field', 'TEXTDOMAIN' ),
			array(
				'section' => 'first_section',
				'description' => __( 'must_not_match says value cannot contain numbers 0-5.', 'TEXTDOMAIN' ),
				'must_not_match' => '/[0-5]/'
			)
		);

		$example_settings->field(
			'my_monospaced',
			__( 'Monospaced text field', 'TEXTDOMAIN' ),
			array(
				'type' => 'text_monospace',
				'section' => 'first_section',
				'description' => __( 'must_match says value must be 3 letters long.', 'TEXTDOMAIN' ),
				'must_match' => '/^[a-z]{3}$/'
			)
		);

		$example_settings->field(
			'my_checkbox',
			__( 'Checkbox', 'TEXTDOMAIN' ),
			array(
				'type' => 'checkbox',
				'section' => 'first_section',
				'inline_label' => __( 'Checkboxes need inline labels', 'TEXTDOMAIN' ),
				'default' => 1
			)
		);

		$example_settings->field(
			'my_select',
			__( 'Select list', 'TEXTDOMAIN' ),
			array(
				'type' => 'select',
				'section' => 'second_section',
				'options' => array(
					'red' => __( 'This is red', 'TEXTDOMAIN' ),
					'blue' => __( 'Blue is cool', 'TEXTDOMAIN' ),
					'green' => __( 'Green is... green', 'TEXTDOMAIN' )
				),
				'default' => 'blue'
			)
		);

		$example_settings->field(
			'my_radio_buttons',
			__( 'Radio buttons', 'TEXTDOMAIN' ),
			array(
				'type' => 'radios',
				'section' => 'second_section',
				'description' => __( 'Description goes below.', 'TEXTDOMAIN' ),
				'options' => array(
					'black' => __( 'Black as the night', 'TEXTDOMAIN' ),
					'white' => __( 'White as an angel', 'TEXTDOMAIN' )
				)
			)
		);

		$example_settings->field(
			'my_checklist',
			__( 'List of checkboxes', 'TEXTDOMAIN' ),
			array(
				'type' => 'checklist',
				'section' => 'second_section',
				'options' => array(
					'strawberries' => __( 'Strawberries', 'TEXTDOMAIN' ),
					'blueberries' => __( 'Blueberries', 'TEXTDOMAIN' )
				)
			)
		);

	endif;

	$example_settings->init();

## Changelog

### 1.7.0: Dec 09, 2013

* New: Add `is_on_settings_page` method, which returns true if the settings page is currently displayed. This can be wrapped in an if statement around the `field` calls to reduce unnecessary function calls.
* Tweak/fix: Fix some notices and encoding issues, and improve the error highlighting script.
* Fix: Restore missing inline label argument for `add_settings_field` callbacks.

### 1.6.0: Nov 03, 2013

* New: Add `'output_callback'` parameter to `field`, which allows custom callback methods for the field HTML.
* New/tweak: What should have been done right from the start: if a field type is not 'supported', just add it as input [type] instead of converting to text.
* Tweak: Add `novalidate` to the form, to disable inconsistent browser validation of some field types.
* Fix: The `html` method now works with checklists.

### 1.5.1: Oct 20, 2013

* Tweak: Don't call `settings_errors` on options pages, since they're apparently [called automatically there](http://wordpress.stackexchange.com/a/18637/33110). If I've finally understood this correctly, `pass_settings_errors_id` should no longer be needed.

### 1.5.0: Oct 06, 2013

* New: Add `color_picker` field type, to show a color picker (duh).
* Tweak: Default `pass_settings_errors_id` to false instead of true. I seem to always set this to false nowadays, so the double message behavior may have been a bug that has been fixed.

### 1.4.0: Aug 25, 2013

* New: Add post and page select list fields, named `post_select` and `page_select` respectively. The value saved from the field is the selected post's ID. The fields work pretty much the same way, only difference being that `page_select` displayes hierarchy. The post type used for a `page_select` field must be hierarchial. What post type(s) to use can be set with the `select_post_type` argument.
* Tweak: Change hex color validation to make the hash optional, instead of stripping it beforehand.

### 1.3.6: June 13, 2013

* Fix: Set required save capability with `option_page_capability_[id]` when using a custom one. The Settings API requires posting to `options.php`, which defaults to requiring the `manage_options` capability, regardless of what the option page with the form is set to require.

### 1.3.5: June 11, 2013

* New: Add `editor` field type, to show a visual editor/WYSIWYG area/TinyMCE box.

### 1.3.4: May 22, 2013

* Fix: Don't validate empty values, since that could prevent erasing them.

### 1.3.3: May 18, 2013

* Fix: Prevent notice with unsaved checklists.

### 1.3.2: Apr 14, 2013

* New: Add `pass_settings_errors_id` property to control `settings_errors`. Passing the ID seems to be needed sometimes to prevent double update messages. Other times, passing it prevents messages from showing up at all. I don't know the reason yet, so this is all trial and error.

### 1.3.1: Mar 27, 2013

* Initial public release.
* Fix: Prevent notices with default values when using checklists by not overwriting the ID variable.

### 1.3.0

* New: Add 'button field'.
* Tweak: A lot of internal restructuring.

### 1.2.0

* New: Add custom validation and sanitation.
* New: Highlight fields with errors.
* New: Add ability to include general HTML.

### 1.1.0

* New: Add checklists, a grouped list of checkboxes, as a field type.
* New: Add ability to set default values.

### 1.0.0

* Initial version.