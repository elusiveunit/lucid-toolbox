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

### 1.1.0: May 18, 2013

**General**

* New: Add @since tags to docblocks. Since I didn't have this properly version controlled until the public release this March, they are mostly from memory and very possibly incorrect in a few places.
* New: Update changelogs to include versions before release, mostly for myself to keep track. Won't be dated or as detailed since they're from memory.
* New: Add documentation link to description on the plugins page.

**Lucid_Contact 1.5.0**

* New: It's now possible to add extra recipients and extra headers. See documentation section 'Multiple recipients and extra headers'.
* New: Add form message for `some_sent`. This is displayed when sending to multiple recipients and at least one message, but not all, fail to send.
* Tweak: Improve line break normalization.
* Fix: Allow empty data for template tags, like the number 0.

**Lucid_Settings 1.3.3**

* Fix: Prevent notice with unsaved checklists.

### 1.0.2: Apr 14, 2013

**Lucid_Settings 1.3.2**

* New: Add `pass_settings_errors_id` property to control `settings_errors`. Passing the ID seems to be needed sometimes to prevent double update messages. Other times, passing it prevents messages from showing up at all. I don't know the reason yet, so this is all trial and error.

**Lucid_Taxonomy 1.1.1**

* Tweak: Change the select list width 'algorithm' and prevent double digit result.

### 1.0.1: Apr 14, 2013

**WPAlchemy_MetaBox**

* Tweak: Minify the JS output slightly.

### 1.0.0: Mar 27, 2013

* Initial version.