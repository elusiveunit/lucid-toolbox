# Lucid Toolbox

[![devDependency Status](https://david-dm.org/elusiveunit/lucid-toolbox/dev-status.svg)](https://david-dm.org/elusiveunit/lucid-toolbox#info=devDependencies)

A set of classes used to speed up and automate common tasks.

* `Lucid_Admin_Column` for adding custom admin columns.
* `Lucid_Post_Type` for creating custom post types.
* `Lucid_Taxonomy` for creating custom taxonomies.
* `Lucid_Settings` for creating settings pages using the Settings API.
* `Lucid_Contact` for creating contact forms.
* `Lucid_WPAlchemy` (forked from `WPAlchemy_MetaBox`) for creating metaboxes.

The plugin doesn't do anything by itself, apart from loading language files. It simply allows usage of the classes through something like:

    if ( defined( 'LUCID_TOOLBOX_CLASS' ) && ! class_exists( 'Lucid_Settings' ) )
        require LUCID_TOOLBOX_CLASS . 'lucid-settings.php';

Read more about each class in the included documentation, located in the 'doc' directory.

Lucid Toolbox is currently available in the following languages:

* English
* Swedish

## Changelog

### 1.2.3: Feb 18, 2018

#### Lucid\_Settings 1.8.1

* Tweak: Allow numbers in editor IDs.
* Fix: Remove deprecated `screen_icon` calls.

#### Lucid\_WPAlchemy 1.6.1

* Fix: Don't count `null`, fixes PHP 7.2 warning.


### 1.2.2: Jan 08, 2017

#### Lucid\_Contact 1.9.0

* New: Public setters for form and field messages, `set_field_error` and `set_form_status` (with `set_form_error`, `set_form_warning` and `set_form_success` helpers for using the correct class names). Should probably add the same three 'levels' for field messages.
* New: Run the `lucid_contact_sent` action when a message is sent.
* Tweak: Improve file fields, primarily the error handling
    * Improve inline error messages
    * Remove successfully uploaded files when there are errors
    * Improve validation, including stricter checking of file size and MIME types
* Tweak: More default allowed extensions and MIME types should now cover most of the commonly used document and image formats. Allow merging with defaults when adding custom ones via new parameter, instead of just overwriting.
* Tweak: Disable the email DNS check by default.
* Fix: Ensure POST values belong to the current form, in case of multiple forms with the same field names on the same page.

#### Lucid\_WPAlchemy 1.6.0

* New: Handle cloning of multiple 'roots' for repeatable groups, instead of just the first one.
* New: Add a second parameter to `[get_]the_group_open` for adding extra classes to the group warpping element.

#### Lucid\_Admin\_Column 1.0.1

* Fix: Use `call_user_func` PHP 5.3 compatibility.


### 1.2.1: Sep 25, 2016

#### Lucid\_Contact 1.8.0

* New: Set separate Reply-To addresses with `$[extras_]reply_to_name` and `$[extras_]reply_to_address`. The extra recipient properties are also no longer required, falling back to the regular ones if not set.
* New: Setting the `value` on textual fields now pre-fills instead of ignoring.
* New: `get_subject` and `get_message` are now public, because anything else doesn't make any sense.


### 1.2.0: Mar 01, 2015

#### Lucid\_Admin\_Column 1.0.0

A new class for adding custom columns to admin post lists. Can be used both as a standalone class and as a new argument for `Lucid_Post_Type`. See included docs for usage.

#### Lucid\_Post\_Type 1.3.0

* New: Add `'columns'` argument for adding custom admin columns to the post type, using `Lucid_Admin_Column`.


### 1.1.12: Feb 23, 2015

#### Lucid\_Post\_Type 1.2.1

* Tweak: Deprecate the custom icon arguments, use the built-in `menu_icon` instead.

#### Lucid\_Settings 1.8.0

* New: Add `menu` method to create top level menu items.
* New: Add `'editor_settings'` parameter to `field` for custom arguments to wp_editor when adding editor fields.
* New: Add `load_callback` property that can be set to a callable, which is called on the load-(page) hook only when on the settings page.

#### WPAlchemy\_MetaBox 1.5.2.lucid-2, now Lucid\_WPAlchemy

With this version it's a complete fork and will not fully match the original class.

* New: Add defaults to checkbox, select and radio from upstream.
* Tweak: Change the class name to `Lucid_WPAlchemy`. If no `WPAlchemy_MetaBox` class exists, it will be defined and extend `Lucid_WPAlchemy` to keep backwards compatibility. This may be removed eventually.
* Tweak/fix: Improve the JavaScript for repeatable field's copy/delete and make it work when using the `area` argument.
* Tweak: Some general code style changes and cleanup.
* Remove: All display options have been removed: `hide_title`, `hide_editor`, `lock`, `view` and `hide_screen_option`. They were relying on JavaScript and CSS to accomplish their purpose and didn't feel future proof or maintainable. Removing the editor can be done on the post type itself and the `area` argument can handle different placements. The title should always be editable, even if it's not used publicly.


### 1.1.11: Apr 13, 2014

#### Lucid\_Contact 1.7.0

* New: Add `reverse_validation` argument to `add_field`, which reverses any custom regex validation result. True by default due to initially stupid thinking and thus backwards compatibility.
* New: Add the self-explanatory `get_field_data` method.
* New/tweak: Allow any values for checkboxes instead of defaulting to boolean. Derp.
* New/tweak: Validation methods are now public and the default validation can be disabled by setting the new `validate_send` property to false. Allows 'faking' a POST and using data from another source.
* Tweak: Don't count zeroes as empty values in validation.
* Tweak/fix: Don't add `aria-required` to checkboxes, since it's invalid HTML for some reason.
* Tweak/fix: Remove referer check, since it's not always set.
* Fix: Add UTF-8 modifier (`u`) to line break regex. Fixes some other characters getting garbled.


### 1.1.10: Dec 09, 2013

* Tweak/fix: Include [this](https://gist.github.com/aubreypwd/7828624) temporary workaround for the issue with `__FILE__` in symlinked plugins, see [trac ticket #16953](http://core.trac.wordpress.org/ticket/16953).

#### Lucid\_Post\_Type 1.2.0

* New: Add `icon` argument for the new Dashicons in WordPress 3.8.

#### Lucid\_Settings 1.7.0

* New: Add `is_on_settings_page` method, which returns true if the settings page is currently displayed. This can be wrapped in an if statement around the `field` calls to reduce unnecessary function calls.
* Tweak/fix: Fix some notices and encoding issues, and improve the error highlighting script.
* Fix: Restore missing inline label argument for `add_settings_field` callbacks.

#### WPAlchemy\_MetaBox 1.5.2.lucid-1

* New: Add `area` argument (string). Defaults to `metabox`, which doesn't do anything different. Can also be set to `after_title` or `after_editor`, which will add the metabox template content directly to the page in those places. Since it's not a meta 'box' in those cases, arguments like title, context etc. won't have any effect.
* Tweak: Move the htmlentities filtering from `get_the_value` to `the_value`, so that the pure data can be passed to a wp_editor instance.


### 1.1.9: Nov 03, 2013

#### Lucid\_Settings 1.6.0

* New: Add `'output_callback'` parameter to `field`, which allows custom callback methods for the field HTML.
* New/tweak: What should have been done right from the start: if a field type is not 'supported', just add it as input [type] instead of converting to text.
* Tweak: Add `novalidate` to the form, to disable inconsistent browser validation of some field types.
* Fix: The `html` method now works with checklists.

#### Lucid\_Contact 1.6.2

* New: Add the `is_form_sent` method for checking the success state.
* New: Add `aria-required` and `aria-invalid` attributes to fields when appropriate.


### 1.1.8: Oct 20, 2013

#### Lucid\_Settings 1.5.1

* Tweak: Don't call `settings_errors` on options pages, since they're apparently [called automatically there](http://wordpress.stackexchange.com/a/18637/33110). If I've finally understood this correctly, `pass_settings_errors_id` should no longer be needed.


### 1.1.7: Oct 06, 2013

#### Lucid\_Settings 1.5.0

* New: Add `color_picker` field type, to show a color picker (duh).
* Tweak: Default `pass_settings_errors_id` to false instead of true. I seem to always set this to false nowadays, so the double message behavior may have been a bug that has been fixed.

#### Lucid\_Contact 1.6.1

* Tweak: Ignore validation on optional fields if the value is empty. This allows validation on optional fields, while still allowing them to be optional. One example would be an optional email field; an empty value shouldn't cause an error, but a filled one should if the email is invalid.
* Tweak: Set `input-error` CSS class on input fields with errors.


### 1.1.6: Sep 16, 2013

#### Lucid\_Contact 1.6.0

* New: Finally add a stupidly obvious way of freely using HTML without `add_to_field_list`, by separating the form assembly and render methods. See new 'Separate field rendering' section in the documentation.
* Tweak: Disable the nonce field added in 1.5.2 by default. A nonce can cause issues if caching is used, since the nonce string can be cached and thus invalid for a time until the cache is renewed. A hidden field with the internal form ID is used instead, so there can still be multiple forms on the same page. The new `use_nonce` property (defaults to false) can be used to get the old behavior.


### 1.1.5: Aug 25, 2013

#### Lucid\_Post\_Type 1.1.2

* Tweak: Check the post type name before registering and trigger errors if it's invalid.

#### Lucid\_Taxonomy 1.1.3

* Tweak: Check the taxonomy name before registering and trigger errors if it's invalid.

#### Lucid\_Settings 1.4.0

* New: Add post and page select list fields, named `post_select` and `page_select` respectively. The value saved from the field is the selected post's ID. The fields work pretty much the same way, only difference being that `page_select` displayes hierarchy. The post type used for a `page_select` field must be hierarchial. What post type(s) to use can be set with the `select_post_type` argument.
* Tweak: Change hex color validation to make the hash optional, instead of stripping it beforehand.

#### WPAlchemy\_MetaBox

* Tweak: Update PHP 4-style declarations to PHP 5. This prevents Strict Standards notices in WordPress 3.6 on PHP 5.4.


### 1.1.4: June 30, 2013

#### Lucid\_Contact 1.5.2

* New: Add a nonce field for more secure forms and to differentiate between multiple forms on the same page.
* New: Add new error message (with key `invalid_post`) for when the nonce check fails.
* Fix: Display the correct error message (`not_sent` instead of `some_sent`) when not using extra recipients and the message couldn't be sent.
* Tweak: Change form errors from `p` tags to `div` tags. Add the better classes `form-error` and `field-error`, to the form error and field errors respectively.
* Tweak: Remove plain text word wrapping, since it's wrapping at weird places sometimes.


### 1.1.3: June 13, 2013

#### Lucid\_Settings 1.3.6

* Fix: Set required save capability with `option_page_capability_[id]` when using a custom one. The Settings API requires posting to `options.php`, which defaults to requiring the `manage_options` capability, regardless of what the option page with the form is set to require.


### 1.1.2: June 11, 2013

#### Lucid\_Settings 1.3.5

* New: Add `editor` field type, to show a visual editor/WYSIWYG area/TinyMCE box.


### 1.1.1: May 22, 2013

#### Lucid\_Post\_Type 1.1.1

* Tweak: Register post type in the constructor, allowing the user to choose the hook.

#### Lucid\_Taxonomy 1.1.2

* Tweak: Register taxonomy in the constructor, allowing the user to choose the hook.

#### Lucid\_Settings 1.3.4

* Fix: Don't validate empty values, since that could prevent erasing them.

#### Lucid\_Contact 1.5.1

* Fix: Rewrite overly complicated regex that could cause a stack overflow, and thus crash Apache or PHP. This was a very interesting bug that would show itself if the message string passed to the conditional tag regex was too large or complex (probably when using an HTML template). The crash is due to a PHP issue, described in detail in this [Stack Overflow (hey!) answer](http://stackoverflow.com/questions/7620910/regexp-in-preg-match-function-returning-browser-error).
* Tweak: Allow the number 0 in template tags.


### 1.1.0: May 18, 2013

#### General

* New: Add @since tags to docblocks. Since I didn't have this properly version controlled until the public release this March, they are mostly from memory and very possibly incorrect in a few places.
* New: Update changelogs to include versions before release, mostly for myself to keep track. Won't be dated or as detailed since they're from memory.
* New: Add documentation link to description on the plugins page.

#### Lucid\_Settings 1.3.3

* Fix: Prevent notice with unsaved checklists.

#### Lucid\_Contact 1.5.0

* New: It's now possible to add extra recipients and extra headers. See documentation section 'Multiple recipients and extra headers'.
* New: Add form message for `some_sent`. This is displayed when sending to multiple recipients and at least one message, but not all, fail to send.
* Tweak: Improve line break normalization.


### 1.0.2: Apr 14, 2013

#### Lucid\_Taxonomy 1.1.1

* Tweak: Change the select list width 'algorithm' and prevent double digit result.

#### Lucid\_Settings 1.3.2

* New: Add `pass_settings_errors_id` property to control `settings_errors`. Passing the ID seems to be needed sometimes to prevent double update messages. Other times, passing it prevents messages from showing up at all. I don't know the reason yet, so this is all trial and error.


### 1.0.1: Apr 14, 2013

#### WPAlchemy\_MetaBox

* Tweak: Minify the JS output slightly.


### 1.0.0: Mar 27, 2013

* Initial version.