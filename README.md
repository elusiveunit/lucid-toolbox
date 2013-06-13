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