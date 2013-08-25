# Lucid Toolbox

A set of classes used to speed up and automate common tasks.

* `Lucid_Post_Type` for creating custom post types.
* `Lucid_Taxonomy` for creating custom taxonomies.
* `Lucid_Settings` for creating settings pages using the Settings API.
* `Lucid_Contact` for creating contact forms.
* `WPAlchemy_Metabox` for creating metaboxes.

The plugin doesn't do anything by itself, apart from loading language files. It simply allows usage of the classes through something like:

	if ( defined( 'LUCID_TOOLBOX_CLASS' ) && ! class_exists( 'Lucid_Settings' ) )
		require LUCID_TOOLBOX_CLASS . 'lucid-settings.php';

Read more about each class in the included documentation, located in the 'doc' directory.

Lucid Toolbox is currently available in the following languages:

* English
* Swedish

## Changelog

### 1.1.5: Aug 25, 2013

#### Lucid\_Post\_Type 1.1.2

* Tweak: Check the post type name before registering and trigger errors if it's invalid.

#### Lucid\_Taxonomy 1.1.3

* Tweak: Check the taxonomy name before registering and trigger errors if it's invalid.

#### Lucid\_Settings 1.4.0

* New: Add post and page select list fields, named `post_select` and `page_select` respectively. The value saved from the field is the selected post's ID. The fields work pretty much the same, only difference being that `page_select` displayes hierarchy. The post type used for a `page_select` field must be hierarchial. What post type(s) to use can be set with the `select_post_type` argument.
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