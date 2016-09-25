# Lucid\_Contact

Handles contact forms; building, validating and sending. Not particularly pretty or flexible, but it gets the job done.

`to_address` and `message_template` or `message_format` are required properties and the sending method will throw errors if they are empty.

Usage is fairly straightforward, create a new form object and set desired properties to match your needs:

	$form = new Lucid_Contact();
	$form->from_name = 'from_name'; // Field name

Continue with building the actual form:

* Use `add_field()`, `add_to_field_list()` and `add_submit()` to build the form.
* Use `render_form()` to show the form. This automatically includes validation and sending with [wp_mail()](http://codex.wordpress.org/Function_Reference/wp_mail).

There is an unfortunate lack of separation between logic and view, something that will hopefully be remedied someday in the future.

## Properties

There are quite a few properties to set. The first three are not forced requirements, but highly recommended.

* `from_name` **(string)** Sender's name. Set to a field name like 'name' to use the data from that field.
* `from_address` **(string)** Sender's email address. Set to a field name like 'email' to use the data from that field.
* `reply_to_name` **(string)** 'Reply-To' name, if different from the `from_name`.
* `reply_to_address` **(string)** 'Reply-To' address, if different from the `from_address`.
* `to_address` **(string)** Recipient's email address.
* `subject_label` **(string)** A label in square brackets to add in front of the message subject. If the string is a form field name, the value of that field will be used (with a three word limit). Otherwise, the string will be used as is. Takes the form of: '[Label] Subject goes after'.
* `subject_text` **(string)** The subject text. If the string is a form field name, the value of that field will be used. Otherwise, the string will be used as is. The value of a field will be a maximum of six words long and if shortened will have '...' appended to it.
* `field_wrap` **(string)** HTML element to wrap around every form field (including its label, description, error). False to disable. Defaults to div.
* `form_action` **(string)** The form action. Defaults to the permalink of the form page.
* `form_method` **(string)** The form method, defaults to `'post'`.
* `form_attributes` **(array)** Additional form attributes, like class. Format: `attr => value`.
* `handle_post` **(bool)** Whether to handle the POST data and sending.
* `validate_send` **(bool)** Whether to validate the POST data before trying to send.
* `do_email_dns_check` **(bool)** If email validation should include DNS lookup.
* `use_nonce` **(bool)** Whether to add a nonce field to the form. This can cause issues if caching is used, since the nonce string can be cached and thus invalid for a time until the cache is renewed.

Some properties are covered in their own sections:

Concerning the message:

* `message_format`
* `message_format_separator`
* `message_template`
* `custom_template_tags`
* `html_template`

Concerning attachments:

* `handle_attachments`
* `delete_sent_files`
* `max_file_size`

Concerning multiple recipients:

* `extra_headers`
* `extra_recipients`
* `extras_from_name`
* `extras_from_address`
* `extras_reply_to_name`
* `extras_reply_to_address`

## Form messages

Messages appear above the form on submission, to inform the user of the current state. There are set with `set_form_messages()` and `set_file_form_messages` (for file uploads). The default regular messages are:

	$defaults = array(
		'success'      => __( 'Thank you for your message!', 'lucid-toolbox' ),
		'error'        => __( 'There seems to be a problem with your information.', 'lucid-toolbox' ),
		'honeypot'     => __( 'To send the message, the last field must be empty. Maybe it was filled by mistake, delete the text and try again.', 'lucid-toolbox' ),
		'not_sent'     => __( 'Due to a technical issue, the message could not be sent, we apologize.', 'lucid-toolbox' ),
		'some_sent'    => __( 'There was an isuue sending the message, some recipients may not receive it properly.', 'lucid-toolbox' ),
		'invalid_post' => __( 'The request could not be verified, please try again.', 'lucid-toolbox' )
	);

To clarify:

* `'success'` If the message was successfully sent.
* `'error'` Some problem with the information provided by the user, like missing fields and validation errors.
* `'honeypot'` If the only problem was a filled-in honeypot field.
* `'not_sent'` If there was a problem during the sending process. Not something the user can do anything about.
* `'some_sent'` If sending to multiple recipients and there was a problem with some, but not all, during the sending process. Not something the user can do anything about.
* `'invalid_post'` If the nonce verification failed. This could be due to an expired nonce because of a long peroid of inactivity, or a malicious attempt of something.

Information about the file upload errors can be found on the [PHP manual page](http://www.php.net/manual/en/features.file-upload.errors.php). The defaults:

	$defaults = array(

		// Probably unused
		UPLOAD_ERR_OK         => __( 'The file was uploaded successfully.', 'lucid-toolbox' ),

		// User errors
		UPLOAD_ERR_INI_SIZE   => __( 'The attached file is too large.', 'lucid-toolbox' ),
		UPLOAD_ERR_FORM_SIZE  => __( 'The attached file is too large.', 'lucid-toolbox' ),
		UPLOAD_ERR_NO_FILE    => __( 'No attachment found.', 'lucid-toolbox' ),

		// Users don't need exact technical information
		UPLOAD_ERR_PARTIAL    => __( 'There was a technical problem with the attached file, we apologize.', 'lucid-toolbox' ),
		UPLOAD_ERR_NO_TMP_DIR => __( 'There was a technical problem with the attached file, we apologize.', 'lucid-toolbox' ),
		UPLOAD_ERR_CANT_WRITE => __( 'There was a technical problem with the attached file, we apologize.', 'lucid-toolbox' ),
		UPLOAD_ERR_EXTENSION  => __( 'There was a technical problem with the attached file, we apologize.', 'lucid-toolbox' ),

		// Additional custom
		'invalid_file_type' => __( 'The file seems to have an invalid format.', 'lucid-toolbox' )
	);

More about files in its own section.

## Adding fields

Fields are added with the `field()` method, which requires a type and a name. A majority of the regular field types are handled and for unknown or future stuff, it falls back to an input with `type="<type>"`.

In addition to the type and name, an array of optional arguments can be passed:

* `'description'` **(string)** Field description, placed under the field.
* `'label'` **(string)** The field label. Will have a matching 'for' attribute.
* `'label_break'` **(bool)** Add a `<br>` tag after the label. Defaults to true.
* `'label_attributes'` **(array)** Additional HTML attributes for the label, format: `attr => value`. Ignores 'for' attribute.
* `'rows'` **(string)** Textarea rows attribute.
* `'cols'` **(string)** Textarea cols attribute.
* `'value'` **(string)** Value attribute, only used for radio buttons.
* `'field_attributes'` **(array)** Additional HTML attributes for the field, format: `attr => value`. Some attributes like type, value, name and id are ignored due to usage in the class.
* `'field_wrap'` **(string)** HTML element to wrap around field. Use `'default'` (which is the default) to wrap with value from the `field_wrap` property. Use an empty string to disable wrapping for that particular field.
* `'wrap_attributes'` **(array)** Additional HTML attributes for the element wrapping the field, format: `attr => value`.
* `'options'` **(array)** Options for select element, format: `value => text`.
* `'required'` **(bool)** If field is required. Defaults to true, except for hidden fields.
* `'message_prefix'` **(string)** String to add before the field data in the message, output as <message_prefix>: <submitted value>. Leave empty to disable, set to `'field'` to use the field name. Defaults to `'field'` for radio buttons and select lists.
* `'validation'` **(string)** What type of validation to use. Predefined validation exists for strings `'email'` and `'tel'`. Any other string is passed as a regex to [preg_match()](http://php.net/preg_match). If a regex is passed, it should **match invalid** characters, i.e. `'/[\d]/'` to NOT allow digits.
* `'error_empty'` **(string)** Error message for when a required field is empty on submission.
* `'error_invalid'` **(string)** Error message for when a field with validation doesn't pass it.

Some arguments, like `rows` and `options`, are obviously ignored unless the proper field type is used. Honeypot example:

	$form->add_field( 'email', 'email', array(
		'label'           => __( 'Leave empty if you are human:', 'TEXTDOMAIN' ),
		'error_invalid'   => __( 'The field must be empty', 'TEXTDOMAIN' ),
		'required'        => false,
		'validation'      => 'honeypot',
		'wrap_attributes' => array( 'id' => 'honeypot' )
	) );

Read more about honeypots under 'Finishing touches'.

### Miscellaneous HTML

Any additional HTML outside the fields can be added with the strangely named `add_to_field_list()` method. It adds a strings as-is, so any HTML is allowed. Since version 1.6.0, a better way could be used, see the section under 'Finishing touches'.

	$form->add_to_field_list( '<fieldset>' );

	[Add some fields]

	$form->add_to_field_list( '</fieldset>' );

### Submit button

The submit button has its own simple method:

	$form->add_submit( __( 'Send the message', 'TEXTDOMAIN' ) );

It also accepts the same `attr => value` format array as the field, if additional attributes are needed.

### Attachments

Getting attachments working only requires adding a file input with `add_field()`. There are of course some options available though, starting with properties:

* `handle_attachments` **(bool)** If email attachments should be handled. Defaults to false and is automatically set to true if there is a file input added.
* `delete_sent_files` **(bool)** Remove uploaded attachment files from the server when they've been sent. Defaults to true.
* `max_file_size` **(int)** Maximum file size for attachments, in bytes. Is available for potential convenience, rather than security.

Additionally, there is the `set_allowed_files()` method for settings allowed file extensions and MIME types. First argument is an array of extensions, second is MIME types.

The defaults extensions are jpg, jpeg, png, gif, pdf, doc and docx. The default MIME types match the extensions:

* image/jpeg = .jpg/.jpeg
* image/pjpeg = .jpg/.jpeg
* image/png = .png
* image/gif = .gif
* application/pdf = .pdf
* application/msword = .doc
* application/vnd.openxmlformats-officedocument.wordprocessingml.document = .docx

Defaults are only set if the `set_allowed_files()` parameters are empty (the default values), so adding .bmp with `set_allowed_files( array( '.bmp' ) )` would get rid of jpg and all the others, only allowing bmp. Same is true for MIME types, so be sure to set everything explicitly when going custom.

## The message

There are two ways to specify the format of the message, an old way and a new way.

### Message format

The old way is through the `message_format` property. It's an array of the form field names whose data should be included in the message. Data from the fields will print in the order they appear in the array, with the string set in `message_format_separator` between each.

If a value in this array doesn't exist as a form field, the value will appear as is, so separators and the like are possible.

	$form->message_format = array(
		'name',
		"\n ----- \n",
		'message'
	);

	$form->message_format_separator = "\n";

### Message template

The `message_template` property is a string with arbitrary text, accepting mustache-style template tags for field data. `{{field_name}}` is replaced with the field's POST content.

Also available are conditional tags wrapping field tags, whose entire content is only displayed if the field POST value is not empty. They start with a hash and end with a slash (groovy!), like `{{#if}}content{{/if}}`. Tags are different for inline (`{{#if}}`) and blocks (`{{#if_block}}`), since an extra line break needs to be removed for blocks (except in HTML where line breaks generally don't matter). Whitespace is trimmed from the begining and the end of the message.

Example:

	$form->message_template = '
	Message:
	Name: {{name}}
	{{#if}}Not displayed if phone is empty {{phone}}. {{/if}}But this is.
	Email: {{email}}

	{{#if_block}}
	This entire block only shows if address is not empty
	Address here: {{address}}
	Use if_block for whole and/or multiple lines.
	{{/if_block}}
	';

**Note:** The conditionals will count the POST value as empty, therefore not showing it, for any falsy values except the number 0.

Since this class only handles find and replace for template tags, custom tags can be used when processing of the tag value is needed, like for a total price. These are set with the `custom_template_tags` property.

	$form->custom_template_tags = array(
	   'tag_name' => 'tag value',
	   'total_price' => 99 * (int) $_POST['number_of_products']
	);

The custom tags can then be used in the template like any other: `{{total_price}}`.

### HTML email

HTML email can be sent by setting the `html_template` property, which should be a full path (for include, so not a URL) to an HTML file. The necessary headers are sent if a template is set.

The file content is processed like `message_template`, so the same template tag rules apply there. Field data is run through [nl2br](http://php.net/nl2br), so line breaks in textareas should display properly.

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<h1>Hello</h1>
		{{#if}}<p><b>From:</b> {{from_name}}</p>{{/if}}
	</body>
	</html>

HTML emails are completely different from regular web development, so be sure to read up on the proper way of building them (yay tables).

### Multiple recipients and extra headers

Multiple recipients can be set in two ways: through adding CC headers with the `extra_headers` property, or through the `extra_recipients` property. Using extra headers is just like sending a copy in a regular mail client:

	$form->extra_headers = array(
		'Cc: send_carbon_copy@example.com',
		'Bcc: send_blind_carbon_copy@example.com'
	);

Extra headers aren't limited to carbon copies of course, anything can be added.

Sometimes you don't want to send copies though, for example when the form is used for ordering and both you and the customer should get a copy of the order confirmation. It probably looks more professional for the client to receive a confirmation sent to him/her only. They should possibly also have different 'from' and/or 'reply-to' data. This is done with these properties:

* `extra_recipients` **(array)** One email address per array item. Each will get a separate mail sent with `wp_mail()`.
* `extras_from_name` **(string)** The 'from' name to use when sending to the extra recipients. Defaults to the regular `from_name`
* `extras_from_address` **(string)** The 'from' email address to use when sending to the extra recipients.
* `extras_reply_to_name` and `extras_reply_to_address` **(string)** 'Reply-To' for the extra recipients. Defaults to the regular `reply_to_name` and `reply_to_address`.

The only difference between the extra messages and the regular ones will be the 'from' headers (unless set to the same of course).

## Finishing touches

Complete the form by calling:

	$form->render_form();

Form messages will display above the form, wrapped in `<p class="error">` (or class 'success' when sent). Any individual form field errors will display below the field, wrapped in `<span class="error">`.

A special case is the honeypot error, where the form message will be wrapped in `<p class="error error-honeypot">`. If you are unfamiliar with *honeypots*, they are used to stop spam. The field must be empty to pass validation and spambots are presumed to automatically fill in every field, thus getting stuck. To help humans, a proper label should be included and the field should be hidden with CSS.

In the case where a human fills in the field, this special error class allows the honeypot field to be displayed with a general sibling combinator, like so: `.error-honeypot ~ form #honeypot` (or whatever ID/class you use for the field, IE7+). This may be needed if some sort of auto form filler is used by a human. The special error class is only added if the honeypot is the only invalid field.

### Separate field rendering

Since version 1.6.0, the assembly and render methods are separated to enable free use of HTML between fields. Let's start with the new methods:

* `form_status` (return with `get_form_status`) The form status message. This is included at the top of `form_start` by default; pass `false` to it and use this method to place the message somewhere else.
* `form_start` (return with `get_form_start`) The start of the form, includes some hidden fields and the appropriate attributes. Also triggers the POST check and sending (given `handle_post` is true).
* `form_end` (return with `get_form_end`) The end of the form.
* `render_field` (return with `get_field`) A field specified by ID.

An example:

	$form = new Lucid_Contact();
	[set properties as usual]

	$form->add_field( 'text', 'favorite_color', array(
		'label'       => __( 'Your favorite color:', 'TEXTDOMAIN' ),
		'error_empty' => __( 'Enter a color dude(tte)!', 'TEXTDOMAIN' )
	) );

	$form->add_submit( __( 'Build a rainbow', 'TEXTDOMAIN' ) );

	$form->form_start( false ); // Don't include form message

	<div>
		<h2>Help build a rainbow!</h2>

		<?php $form->render_field( 'favorite_color' ); ?>

		<p>Herp derp</p>

		<?php $form->render_field( 'submit' ); ?>
	</div>

	$form->form_status(); // Form message at the bottom

	$form->form_end();

As demonstrated, the difference between this and `render_form` is really only in how the rendering is handled.

### Check if something was sent

Sometimes a task outside the form depends on the state of it, like updating an option every time the form is successfully sent. This can be checked with the `is_form_sent` method, which returns true if everything, including validation and extra recipients, passed. Keep in mind that it will always be false if used before the `send` method has been called.

## Complete example

An example setup with name, email, honeypot and message.

	$form = new Lucid_Contact();
	$form->from_name = 'from_name';
	$form->from_address = 'contact';
	$form->to_address = me@example.com;
	$form->subject_text = 'message'; // First six words of the message as subject

	$form->message_template = '
	From: {{from_name}}
	Email: {{contact}}

	* * *

	{{message}}
	';

	$form->add_to_field_list( '<div class="text-fields">' );

	$form->add_field( 'text', 'from_name', array(
		'label'       => __( 'Name:', 'TEXTDOMAIN' ),
		'error_empty' => __( 'Please enter your name', 'TEXTDOMAIN' )
	) );

	$form->add_field( 'email', 'contact', array(
		'label'            => __( 'Email:', 'TEXTDOMAIN' ),
		'field_attributes' => array( 'placeholder' => __( 'i.e. joe@example.com', 'TEXTDOMAIN' ) ),
		'validation'       => 'email',
		'error_empty'      => __( 'Please enter your email address', 'TEXTDOMAIN' ),
		'error_invalid'    => __( 'The email address seems to be invalid', 'TEXTDOMAIN' )
	) );

	// Honeypot
	$form->add_field( 'email', 'email', array(
		'label'           => __( 'Leave empty if you are human:', 'TEXTDOMAIN' ),
		'error_invalid'   => __( 'The field must be empty', 'TEXTDOMAIN' ),
		'required'        => false,
		'validation'      => 'honeypot',
		'wrap_attributes' => array( 'id' => 'pot' )
	) );

	$form->add_to_field_list( '</div><div class="message-field">' );

	$form->add_field( 'textarea', 'message', array(
		'label'       => __( 'Message:', 'TEXTDOMAIN' ),
		'error_empty' => __( 'Please enter a message', 'TEXTDOMAIN' )
	) );

	$form->add_to_field_list( '</div>' );

	$form->add_submit( __( 'Send message', 'TEXTDOMAIN' ) );

	$form->render_form();

## Changelog

### 1.8.0: Sep 25, 2016

* New: Set separate Reply-To addresses with `$[extras_]reply_to_name` and `$[extras_]reply_to_address`. The extra recipient properties are also no longer required, falling back to the regular ones if not set.
* New: Setting the `value` on textual fields now pre-fills instead of ignoring.
* New: `get_subject` and `get_message` are now public, because anything else doesn't make any sense.

### 1.7.0: Apr 13, 2014

* New: Add `reverse_validation` argument to `add_field`, which reverses any custom regex validation result. True by default due to initially stupid thinking and thus backwards compatibility.
* New: Add the self-explanatory `get_field_data` method.
* New/tweak: Allow any values for checkboxes instead of defaulting to boolean. Derp.
* New/tweak: Validation methods are now public and the default validation can be disabled by setting the new `validate_send` property to false. Allows 'faking' a POST and using data from another source.
* Tweak: Don't count zeroes as empty values in validation.
* Tweak/fix: Don't add `aria-required` to checkboxes, since it's invalid HTML for some reason.
* Tweak/fix: Remove referer check, since it's not always set.
* Fix: Add UTF-8 modifier (`u`) to line break regex. Fixes some other characters getting garbled.

### 1.6.2: Nov 03, 2013

* New: Add the `is_form_sent` method for checking the success state.
* New: Add `aria-required` and `aria-invalid` attributes to fields when appropriate.

### 1.6.1: Oct 06, 2013

* Tweak: Ignore validation on optional fields if the value is empty. This allows validation on optional fields, while still allowing them to be optional. One example would be an optional email field; an empty value shouldn't cause an error, but a filled one should if the email is invalid.
* Tweak: Set `input-error` CSS class on input fields with errors.

### 1.6.0: Sep 16, 2013

* New: Finally add a stupidly obvious way of freely using HTML without `add_to_field_list`, by separating the form assembly and render methods. See new 'Separate field rendering' section in the documentation.
* Tweak: Disable the nonce field added in 1.5.2 by default. A nonce can cause issues if caching is used, since the nonce string can be cached and thus invalid for a time until the cache is renewed. A hidden field with the internal form ID is used instead, so there can still be multiple forms on the same page. The new `use_nonce` property (defaults to false) can be used to get the old behavior.

### 1.5.2: June 30, 2013

* New: Add a nonce field for more secure forms and to differentiate between multiple forms on the same page.
* New: Add new error message (with key `invalid_post`) for when the nonce check fails.
* Fix: Display the correct error message (`not_sent` instead of `some_sent`) when not using extra recipients and the message couldn't be sent.
* Tweak: Change form errors from `p` tags to `div` tags. Add the better classes `form-error` and `field-error`, to the form error and field errors respectively.
* Tweak: Remove plain text word wrapping, since it's wrapping at weird places sometimes.

### 1.5.1: May 22, 2013

* Fix: Rewrite overly complicated regex that could cause a stack overflow, and thus crash Apache or PHP. This was a very interesting bug that would show itself if the message string passed to the conditional tag regex was too large or complex (probably when using an HTML template). The crash is due to a PHP issue, described in detail in this [Stack Overflow (hey!) answer](http://stackoverflow.com/questions/7620910/regexp-in-preg-match-function-returning-browser-error).
* Tweak: Allow the number 0 in template tags.

### 1.5.0: May 18, 2013

* New: It's now possible to add extra recipients and extra headers. See documentation section 'Multiple recipients and extra headers'.
* New: Add form message for `some_sent`. This is displayed when sending to multiple recipients and at least one message, but not all, fail to send.
* Tweak: Improve line break normalization.

### 1.4.1: Mar 27, 2013

* Initial public release.
* Fix: Prevent some notices with the new template system.

### 1.4.0

* New: Add a template system for the message, with the ability to use mustache-style template tags as well as conditional blocks.
* New: Add ability to send HTML email. The template tag system is also available in HTML templates.

### 1.3.0

* New: Add attachment handling. To enable, the `handle_attachments` property must be set to `true` (which happens automatically if a file input is added with `add_field()`).

### 1.2.0

* New: Filter headers against email injection.
* Tweak: A lot of code cleanup and restructuring of the field methods.

### 1.1.0

* New: Add subject label property.
* Tweak: A lot of code cleanup and restructuring of the assembly and send process.

### 1.0.0

* Initial version.