<?php
/**
 * Contact class definition.
 *
 * @package Lucid\Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Handles contact forms; building, validating and sending.
 *
 * Not particularly pretty or flexible, but it gets the job done.
 *
 * $to_address and some template ($message_format, $message_template or
 * $html_template) are required properties and send() will throw errors if
 * they are empty.
 *
 * Usage:
 *
 * - Set properties to match your needs.
 * - Use add_field(), add_to_field_list() and add_submit() to build the form.
 * - Use render_form() to show the form. This automatically includes validation
 *   and sending with wp_mail().
 *
 * There is an unfortunate lack of separation between logic and view, something
 * that will hopefully be remedied someday in the future.
 *
 * @package Lucid\Toolbox
 * @version 1.9.0
 */
class Lucid_Contact {

	/*
	|------------------------------------------------------------------------
	| Table of contents
	|------------------------------------------------------------------------
	|
	| Properties
	| ------------------------------
	| $_form_count
	| $_form_id
	| $from_name
	| $from_address
	| $reply_to_name
	| $reply_to_address
	| $to_address
	| $message_format
	| $message_format_separator
	| $message_template
	| $_message_tags
	| $_message_conditionals
	| $custom_template_tags
	| $html_template
	| $subject_label
	| $subject_text
	| $extra_headers
	| $extra_recipients
	| $extras_from_name
	| $extras_from_address
	| $extras_reply_to_name
	| $extras_reply_to_address
	| $_fields
	| $field_wrap
	| $_ignore_field_attrs
	| $_html_count
	| $form_action
	| $form_method
	| $use_nonce
	| $handle_attachments
	| $delete_sent_files
	| $_allowed_extensions
	| $_allowed_mime_types
	| $_attachments
	| $max_file_size
	| $form_attributes
	| $form_location
	| $_form_messages
	| $_file_form_messages
	| $_form_status
	| $handle_post
	| $_form_sent
	| $do_email_dns_check
	| $debug_mode
	|
	| Methods, section separated
	| ------------------------------
	| __construct
	| set_form_messages
	| set_file_form_messages
	| set_allowed_files
	|
	| [=Adding fields]
	| add_field
	| _get_field_wrap_open
	| _get_field_wrap_close
	| _get_field_label
	| _get_field_description
	| _get_textarea
	| _get_select
	| _get_radio
	| _get_hidden
	| _get_input_field
	| add_to_field_list
	| add_submit
	|
	| [=Validation and sending]
	| is_valid_post
	| validate
	| get_subject
	| _get_message_from_format
	| _get_message_conditionals
	| _get_message_tags
	| _filter_conditional_tag
	| _filter_tag
	| _get_template_message
	| _get_html_message
	| get_message
	| _get_field_post
	| _get_headers
	| _get_attachments
	| _get_unique_file_path
	| send
	| has_required_send_data
	| _clear_send
	|
	| [=Form rendering]
	| _get_id_field
	| get_form_status
	| form_status
	| get_form_start
	| form_start
	| get_form_end
	| form_end
	| assemble_form
	| get_form
	| render_form
	| get_field
	| render_field
	|
	| [=Misc. functions and utilities]
	| is_form_sent
	| _debug_filter
	| _get_attributes_string
	| is_checkbox
	| is_valid_email
	| is_valid_tel
	| clean_html
	| filter_html
	| filter_name
	| filter_email
	| filter_other
	| normalize_line_break
	| get_words_from_string
	| _array_insert
	| _preg_grep_keys
	| _debug_open
	| _debug_close
	*/

	/**
	 * Forms counter. Increased every time a form is assembled.
	 *
	 * @since 1.5.2
	 * @var int
	 */
	protected static $_form_count = 0;

	/**
	 * ID for current form. Is used for the nonce field and uses $_form_count to
	 * be unique.
	 *
	 * @since 1.5.2
	 * @var string
	 */
	protected $_form_id = '';

	/**
	 * Sender's name. Set to a field name like 'name' to use the data from that
	 * field.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $from_name = '';

	/**
	 * Sender's email address. Set to a field name like 'email' to use the data
	 * from that field.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $from_address = '';

	/**
	 * 'Reply-To' name. Set to a field name like 'name' to use the data from that
	 * field.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $reply_to_name = '';

	/**
	 * 'Reply-To' email address. Set to a field name like 'email' to use the data
	 * from that field.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $reply_to_address = '';

	/**
	 * Recipient's email address.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $to_address = '';

	/**
	 * Email message fomat.
	 *
	 * Set the name of the form fields whose data should be included in the
	 * message. Data from the fields will print in the order they appear in the
	 * array, with the string set in $message_format_separator between each.
	 *
	 * If a value in this array doesn't exist as a form field, the value will
	 * appear as is, so separators and the like are possible.
	 *
	 * Example:
	 *
	 *     $instance->message_format = array(
	 *        'name',
	 *        "\n ----- \n",
	 *        'message'
	 *     );
	 *
	 * @since 1.0.0
	 * @var array
	 * @see $message_format_separator
	 */
	public $message_format = array();

	/**
	 * What to use as glue between message parts.
	 *
	 * The email message is assembled by taking the submitted data from every
	 * field and joining it with implode(). This string is used as glue.
	 *
	 * @since 1.0.0
	 * @var string
	 * @see $message_format
	 */
	public $message_format_separator = "\n";

	/**
	 * Template for the message to be sent, replacement for message_format.
	 *
	 * A string with arbitrary text, accepting mustache-style template tags for
	 * field data. {{field_name}} is replaced with the field's POST content.
	 *
	 * Also available are conditional tags wrapping field tags, whose entire
	 * content is only displayed if the field POST value is not empty. Tags are
	 * different for inline and blocks.
	 *
	 * Whitespace is trimmed from begining and end of message.
	 *
	 * Example:
	 *
	 *     $instance->message_template = '
	 *     Message:
	 *     Name: {{name}}
	 *     {{#if}}Not displayed if phone is empty {{phone}}.{{/if}} But this is.
	 *     Email: {{email}}
	 *
	 *     {{#if_block}}
	 *     This entire block only shows if address is not empty
	 *     Address here: {{address}}
	 *     Use if_block for whole and/or multiple lines, since an extra line break
	 *     needs to be removed.
	 *     In most cases pointless when using an HTML template.
	 *     {{/if_block}}
	 *     ';
	 *
	 * @since 1.4.0
	 * @var string
	 */
	public $message_template = '';

	/**
	 * Template tags contained in the message.
	 *
	 * @since 1.4.0
	 * @var array
	 */
	protected $_message_tags = array();

	/**
	 * Conditional tags contained in the message.
	 *
	 * @since 1.4.0
	 * @var array
	 */
	protected $_message_conditionals = array();

	/**
	 * Non-field template tags for the message.
	 *
	 * Since this class only handles find and replace for template tags, custom
	 * tags can be used when processing of the tag value is needed, like for a
	 * total price.
	 *
	 * Example:
	 *
	 *     $instance->custom_template_tags = array(
	 *        'tag_name' => 'tag value',
	 *        'price_total' => 99 * (int) $_POST['number_products']
	 *     );
	 *
	 * These can then be used in the template like any other: {{price_total}}.
	 *
	 * @since 1.4.0
	 * @var array
	 */
	public $custom_template_tags = array();

	/**
	 * Path to HTML email template.
	 *
	 * Full include path to an HTML file to use as email template. The file can
	 * contain the same template tags as $message_template.
	 *
	 * @since 1.4.0
	 * @see $message_template For how template tags work.
	 * @var string
	 */
	public $html_template = '';

	/**
	 * A label in square brackets to add in front of the message subject.
	 *
	 * If the string is a form field name, the value of that field will be used
	 * (with a three word limit). Otherwise, the string will be used as is.
	 *
	 * Takes the form of: [Label] Subject goes after.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public $subject_label = '';

	/**
	 * The subject text.
	 *
	 * If the string is a form field name, the value of that field will be used.
	 * Otherwise, the string will be used as is. The value of a field will be a
	 * maximum of six words long and if shortened will have '...' appended to it.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $subject_text = '';

	/**
	 * Extra headers to use, one full header per item.
	 *
	 * @since 1.5.0
	 * @var array
	 */
	public $extra_headers = array();

	/**
	 * Extra recipients to send to, in addition to the to_address.
	 *
	 * @since 1.5.0
	 * @var array
	 */
	public $extra_recipients = array();

	/**
	 * Sender's name for extra recipients. Set to a field name like 'name' to
	 * use the data from that field.
	 *
	 * @since 1.5.0
	 * @var string
	 */
	public $extras_from_name = '';

	/**
	 * Sender's address for extra recipients. Set to a field name like 'email'
	 * to use the data from that field.
	 *
	 * @since 1.5.0
	 * @var string
	 */
	public $extras_from_address = '';

	/**
	 * 'Reply-To' name for extra recipients. Set to a field name like 'name' to
	 * use the data from that field.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $extras_reply_to_name = '';

	/**
	 * 'Reply-To' address for extra recipients. Set to a field name like 'email'
	 * to use the data from that field.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $extras_reply_to_address = '';

	/**
	 * The form fields.
	 *
	 * @since 1.0.0
	 * @var array
	 * @see add_field()
	 */
	protected $_fields = array();

	/**
	 * HTML element to wrap around every form field. False to disable.
	 *
	 * The wrap includes the field's label, description, error etc. Defaults to
	 * div.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $field_wrap = 'div';

	/**
	 * Default ignored custom attributes for fields.
	 *
	 * @since 1.0.0
	 * @var array
	 * @see _get_attributes_string()
	 */
	protected $_ignore_field_attrs = array( 'value', 'name', 'id', 'type', 'rows', 'cols', 'aria-required', 'aria-invalid' );

	/**
	 * Number of add_to_field_list() entries.
	 *
	 * To get a nicer key for miscellaneous HTML added via add_to_field_list()
	 * (html-# instead of a number), the key is kept unique with this counter.
	 *
	 * @since 1.0.0
	 * @var int
	 * @see add_to_field_list()
	 */
	protected $_html_count = 0;

	/**
	 * The form action. Default set in constructor.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $form_action = '';

	/**
	 * The form method.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $form_method = 'post';

	/**
	 * Add a nonce field to the form.
	 *
	 * This can cause issues if caching is used, since the nonce string can be
	 * cached and thus invalid for a time until the cache is renewed.
	 *
	 * @since 1.6.0
	 * @var bool
	 */
	public $use_nonce = false;

	/**
	 * If email attachments should be handled. Is set if there is a file input
	 * added.
	 *
	 * @since 1.3.0
	 * @var bool
	 */
	public $handle_attachments = false;

	/**
	 * Remove uploaded attachment files from the server when they've been sent.
	 *
	 * @since 1.3.0
	 * @var bool
	 */
	public $delete_sent_files = true;

	/**
	 * Allowed file extensions for mail attachments.
	 *
	 * @since 1.3.0
	 * @var array
	 * @see _get_attachments()
	 * @see set_allowed_files()
	 * @link http://www.fileinfo.com/filetypes/common
	 */
	protected $_allowed_extensions = array();

	/**
	 * Allowed MIME types for mail attachments.
	 *
	 * @since 1.3.0
	 * @var array
	 * @see _get_attachments()
	 * @see set_allowed_files()
	 */
	protected $_allowed_mime_types = array();

	/**
	 * Paths to sent attachments.
	 *
	 * @since 1.3.0
	 * @var array
	 */
	protected $_attachments = array();

	/**
	 * Maximum file size for attachments, in bytes.
	 *
	 * - X * 1024 for kb to bytes.
	 * - X * 1024 * 1024 for MB to bytes.
	 *
	 * @since 1.3.0
	 * @var int
	 */
	public $max_file_size = 0;

	/**
	 * Additional form attributes. Format attr => value.
	 *
	 * @since 1.0.0
	 * @var array
	 * @see get_form_start()
	 */
	public $form_attributes = array();

	/**
	 * Referer to check when submitting, for a teeny-weeny bit of spoofable extra
	 * CSRF security... Pointless? Maybe. Default set in constructor.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $form_location = '';

	/**
	 * Stores user feedback messages.
	 *
	 * @since 1.0.0
	 * @var array
	 * @see set_form_messages()
	 */
	protected $_form_messages = array();

	/**
	 * Stores user feedback messages for file uploads.
	 *
	 * @since 1.3.0
	 * @var array
	 * @see set_file_form_messages()
	 */
	protected $_file_form_messages = array();

	/**
	 * Current form error or success message.
	 *
	 * @since 1.0.0
	 * @var string
	 * @see is_valid_data()
	 */
	protected $_form_status = '';

	/**
	 * Handle the POST data and sending.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $handle_post = true;

	/**
	 * Whether to validate the POST data before trying to send.
	 *
	 * @since 1.7.0
	 * @var bool
	 * @see is_valid_send()
	 */
	public $validate_send = true;

	/**
	 * Cached status from is_valid_send().
	 *
	 * @since 1.7.0
	 * @var bool
	 * @see is_valid_send()
	 */
	protected $_is_valid_send = null;

	/**
	 * If send() has run and was successful.
	 *
	 * @since 1.6.2
	 * @var bool
	 */
	protected $_form_sent = false;

	/**
	 * If email validation should include DNS lookup.
	 *
	 * @since 1.0.0
	 * @var bool
	 * @see is_valid_email()
	 */
	public $do_email_dns_check = false;

	/**
	 * Primitive debug mode.
	 *
	 * Prints the email content instead of sending it, together with $_POST and
	 * $_fields contents. Debug parts of the class is preceded by a --Debug--
	 * comment. Quicker toggle through the constructor.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $debug_mode = false;

	/**
	 * Constructor. Set some default properties, data and messages.
	 *
	 * @since 1.0.0
	 * @param bool $debug_mode Whether to display form and POST contents.
	 */
	public function __construct( $debug_mode = false ) {
		self::$_form_count++;

		$this->_form_id = ( $this->use_nonce ) ? 'lucid-form-' . self::$_form_count : self::$_form_count;
		$this->form_action = esc_attr( get_permalink( get_queried_object_id() ) );
		$this->form_location = get_permalink( get_queried_object_id() );
		$this->subject_text = sprintf( __( 'From form at %s', 'lucid-toolbox' ), get_bloginfo( 'name' ) );
		$this->debug_mode = (bool) $debug_mode;

		$this->set_form_messages();
		$this->set_file_form_messages();
		$this->set_allowed_files();

		// --Debug--
		if ( $this->debug_mode && ! empty( $_POST ) ) :
			$this->_debug_open( '$_POST' );
			print_r( $_POST );
			$this->_debug_close();
		endif;
	}

	/**
	 * Set form messages.
	 *
	 * These are the messages that appear above the form on submission:
	 *
	 * - 'success' If the message was successfully sent.
	 * - 'error' Some problem with the information provided by the user, like
	 *   missing fields and validation errors.
	 * - 'honeypot' If the only problem was a filled-in honeypot field.
	 * - 'not_sent' If there was a problem during the sending process. Not
	 *   something the user can do anything about.
	 * - 'some_sent' If sending to multiple recipients and there was a problem
	 *   with some, but not all, during the sending process. Not something the
	 *   user can do anything about.
	 * - 'invalid_post' If a nonce is used and its verification failed. This
	 *   could be due to an expired nonce because of a long peroid of inactivity,
	 *   or a malicious attempt of some sort.
	 *
	 * @since 1.0.0
	 * @param array $messages Associative array of messages.
	 */
	public function set_form_messages( array $messages = array() ) {
		$defaults = array(
			'success'      => __( 'Thank you for your message!', 'lucid-toolbox' ),
			'error'        => __( 'There seems to be a problem with your information.', 'lucid-toolbox' ),
			'honeypot'     => __( 'To send the message, the last field must be empty. Maybe it was filled by mistake, delete the text and try again.', 'lucid-toolbox' ),
			'not_sent'     => __( 'The message could not be sent due to a technical issue.', 'lucid-toolbox' ),
			'some_sent'    => __( 'There was an isuue sending the message, some recipients may not receive it properly.', 'lucid-toolbox' ),
			'invalid_post' => __( 'The request could not be verified, please try again.', 'lucid-toolbox' )
		);

		$this->_form_messages = array_merge( $defaults, $messages );
	}

	/**
	 * Set form messages for file uploads.
	 *
	 * @since 1.3.0
	 * @param array $messages Associative array of messages.
	 * @link http://www.php.net/manual/en/features.file-upload.errors.php
	 */
	public function set_file_form_messages( array $messages = array() ) {
		$defaults = array(

			// Probably unused
			UPLOAD_ERR_OK         => __( 'The file was uploaded successfully.', 'lucid-toolbox' ),

			// User errors
			UPLOAD_ERR_INI_SIZE   => __( 'The file is too large.', 'lucid-toolbox' ),
			UPLOAD_ERR_FORM_SIZE  => __( 'The file is too large.', 'lucid-toolbox' ),
			UPLOAD_ERR_NO_FILE    => __( 'No file found.', 'lucid-toolbox' ),

			// Keep the technical details vague
			UPLOAD_ERR_PARTIAL    => sprintf(
				__( 'There was a technical problem with the file (%s).', 'lucid-toolbox' ),
				__( 'partially uploaded', 'lucid-toolbox' )
			),
			UPLOAD_ERR_NO_TMP_DIR => sprintf(
				__( 'There was a technical problem with the file (%s).', 'lucid-toolbox' ),
				__( 'missing temporary folder', 'lucid-toolbox' )
			),
			UPLOAD_ERR_CANT_WRITE => sprintf(
				__( 'There was a technical problem with the file (%s).', 'lucid-toolbox' ),
				__( 'failed to write', 'lucid-toolbox' )
			),
			UPLOAD_ERR_EXTENSION  => sprintf(
				__( 'There was a technical problem with the file (%s).', 'lucid-toolbox' ),
				__( 'stopped by extension', 'lucid-toolbox' )
			),

			// Additional custom
			'invalid_file_type' => __( 'The file format is invalid.', 'lucid-toolbox' )
		);

		// The UPLOAD_ERR constants are numeric; using array_merge will re-index the
		// result and thus ruin it. Using union operator instead prevents that,
		// instead adding keys from the right operand to the left only if they're
		// missing.
		$this->_file_form_messages = $messages + $defaults;
	}

	/**
	 * Set allowed extensions and MIME types. Sets default if there are none.
	 *
	 * @since 1.3.0
	 * @param array $extensions Array of file extensions.
	 * @param array $mime_types Array of file MIME types.
	 * @param bool $merge Add to existing values instead of overwriting.
	 * @link http://www.webmaster-toolkit.com/mime-types.shtml MIME types list.
	 * @link http://filext.com/faq/office_mime_types.php Office MIME types list.
	 */
	public function set_allowed_files( array $extensions = array(), array $mime_types = array(), $merge = false ) {

		// Extensions
		$default_extensions = array(
			'csv',

			// Images
			'jpg',
			'jpeg',
			'png',
			'gif',
			'bmp',
			'tif',
			'tiff',

			// Documents
			'txt',
			'pdf',
			'rtf',

			// MS Office, limit to Word/Excel/PowerPoint, grabbed from wp_get_mime_types
			'doc',
			'pot',
			'pps',
			'ppt',
			'xla',
			'xls',
			'xlt',
			'xlw',
			'docx',
			'xlsx',
			'pptx',

			// OpenOffice, like above
			'odt',
			'odp',
			'ods',
		);

		if ( $merge )
			$this->_allowed_extensions = array_merge( $default_extensions, $extensions );
		else
			$this->_allowed_extensions = ( empty( $extensions ) ) ? $default_extensions : $extensions;

		// MIME types
		$default_mime_types = array(
			'text/csv',

			// Images
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/gif',
			'image/bmp',
			'image/tiff',

			// Documents
			'text/plain',
			'application/pdf',
			'application/rtf',

			// MS Office, limit to Word/Excel/PowerPoint, grabbed from wp_get_mime_types
			'application/msword',
			'application/vnd.ms-powerpoint',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',

			// OpenOffice, like above
			'application/vnd.oasis.opendocument.text',
			'application/vnd.oasis.opendocument.presentation',
			'application/vnd.oasis.opendocument.spreadsheet',
		);

		if ( $merge )
			$this->_allowed_mime_types = array_merge( $default_mime_types, $mime_types );
		else
			$this->_allowed_mime_types = ( empty( $mime_types ) ) ? $default_mime_types : $mime_types;
	}










	/*-------------------------------------------------------------------------*\
	      =Adding fields
	\*-------------------------------------------------------------------------*/


	/**
	 * Add a field to the $_fields array.
	 *
	 * This is the cornerstone of the class. The field is split up in parts and
	 * added to an associative array, which in turn is added to the $_fields
	 * property. A majority the regular field types are handled and for unknown
	 * or future stuff it falls back to an input with type="(type)".
	 *
	 * The field part constructing is split up into smaller functions that return
	 * associative arrays, which are then merged into a complete array to be set
	 * in $this->_fields.
	 *
	 * Additional arguments through the $args array:
	 *
	 * - 'description' (string) Field description, placed under the field.
	 * - 'label' (string) The field label. Will have a matching 'for' attribute.
	 * - 'label_break' (bool) Add a <br> tag after the label. Defaults to true.
	 * - 'label_attributes' (array) Additional HTML attributes for the label,
	 *   format attr => value. Ignores 'for' attribute.
	 * - 'rows' (string) Textarea rows attribute.
	 * - 'cols' (string) Textarea cols attribute.
	 * - 'value' (string) Value attribute, required for radio buttons.
	 * - 'field_attributes' (array) Additional HTML attributes for the field,
	 *   format attr => value. Some attributes like id and value are ignored
	 *   due to usage in the class. See $this->_ignore_field_attrs.
	 * - 'field_wrap' (string) HTML element to wrap around field. Use 'default'
	 *   to wrap with value from the $field_wrap property. Use an empty string
	 *   to disable wrapping for that particular field.
	 * - 'wrap_attributes' (array) Additional HTML attributes for the element
	 *   wrapping the field, format attr => value.
	 * - 'options' (array) Options for select element, format: value => text.
	 * - 'required' (bool) If field is required.
	 * - 'message_prefix' (string) String to add before the field data in the
	 *   message, output as <message_prefix>: <submitted value>. Leave empty
	 *   to disable, set to 'field' to use the field name. Defaults to 'field'
	 *   for radio buttons and select lists.
	 * - 'validation' (string) What type of validation to use. Predefined
	 *   validation exists for strings 'email' and 'tel'. Any other string is
	 *   passed as a regex to preg_match().
	 * - 'reverse_validation' (bool) Whether to reverse results of custom regex
	 *   in the 'validation' argument, i.e. '/[\d]/' to NOT allow digits.
	 * - 'error_empty' (string) Error message for when a required field is empty
	 *   on submission.
	 * - 'error_invalid' (string) Error message for when a field with validation
	 *   doesn't pass it.
	 *
	 * @since 1.0.0
	 * @param string $type The field type.
	 * @param string $name Name and, in most cases, ID attributes for the field.
	 * @param array $args Array of additional arguments.
	 */
	public function add_field( $type, $name, array $args = array() ) {
		$message_prefix = ( 'radio' == $type || 'select' == $type ) ? 'field' : '';

		$defaults = array(
			'description' => '',
			'label' => '',
			'label_break' => true,
			'label_attributes' => array(),
			'rows' => '7',
			'cols' => '40',
			'value' => '',
			'field_attributes' => array(),
			'field_wrap' => ( 'hidden' != $type ) ? 'default' : '', // No wrapping for hidden
			'wrap_attributes' => array(),
			'options' => array(),
			'required' => ( 'hidden' != $type ), // Hidden never required
			'message_prefix' => $message_prefix,
			'validation' => '',
			'reverse_validation' => true,
			'error_empty' => __( 'The field is required', 'lucid-toolbox' ),
			'error_invalid' => __( 'The provided information is invalid', 'lucid-toolbox' )
		);
		$args = array_merge( $defaults, $args );

		$field = array(); // The field data
		$type = $this->clean_html( $type );
		$name = esc_attr( $name );

		// Prepare for attachments if there is a file input
		if ( 'file' == $type ) $this->handle_attachments = true;

		// Validation properties
		$field['type'] = $type;
		$field['required'] = (bool) $args['required'];
		$field['message_prefix'] = (string) $args['message_prefix'];
		$field['value'] = (string) $args['value'];
		$field['reverse_validation'] = (bool) $args['reverse_validation'];
		if ( ! empty( $args['validation'] ) ) $field['validation'] = $args['validation'];
		if ( ! empty( $args['error_empty'] ) ) $field['error_empty'] = $args['error_empty'];
		if ( ! empty( $args['error_invalid'] ) ) $field['error_invalid'] = $args['error_invalid'];

		// Radio button ID clash handling: add -# to the ID.
		// Since related radio buttons must have the same 'name', they can't be
		// stored in the $_fields array by the key alone, due to all but the last
		// one getting overwritten. Thus, a string '-[digit]' is appended to radio
		// button IDs following the first one.
		// Since the first one keeps the original name, it can be used without
		// problems for the POST handling currently in place.
		$id = $name;
		if ( isset( $this->_fields[$id] ) ) :
			$same_ids = count( $this->_preg_grep_keys( "/$id/", $this->_fields ) );
			$id .= '-' . ( $same_ids + 1 );
		endif;

		// Field wrap opening tag
		if ( ! empty( $args['field_wrap'] ) )
			$field = array_merge( $field, $this->_get_field_wrap_open( $args ) );

		// Field label
		if ( $args['label'] )
			$field = array_merge( $field, $this->_get_field_label( $id, $args ) );

		// Different form fields requires different structures
		switch ( $type ) :

			case 'textarea' :
				$field = array_merge( $field, $this->_get_textarea( $name, $args ) );
				break;

			case 'select' :
				$field = array_merge( $field, $this->_get_select( $name, $args ) );
				break;

			case 'radio' :
				$field = array_merge( $field, $this->_get_radio( $name, $id, $args ) );
				break;

			case 'hidden' :
				$field = array_merge( $field, $this->_get_hidden( $name, $args ) );
				break;

			default :
				$field = array_merge( $field, $this->_get_input_field( $type, $name, $args ) );
				break;

		endswitch;

		// Description span
		if ( $args['description'] )
			$field = array_merge( $field, $this->_get_field_description( $args['description'] ) );

		// Field wrap closing tag
		if ( ! empty( $args['field_wrap'] ) )
			$field = array_merge( $field, $this->_get_field_wrap_close( $args['field_wrap'] ) );

		$this->_fields[$id] = $field;
	}

	/**
	 * Field wrap opening HTML tag.
	 *
	 * @since 1.2.0
	 * @param array $args Arguments passed to add_field().
	 * @return array Field part with the 'open' key.
	 */
	protected function _get_field_wrap_open( $args ) {
		$field = array();
		$field_part = '';

		// Add custom tag if defined
		if ( 'default' != $args['field_wrap'] ) :
			$field_part = '<' . $this->clean_html( $args['field_wrap'] );

		// Add default tag if it isn't empty
		elseif ( $this->field_wrap ) :
			$field_part = '<' . $this->clean_html( $this->field_wrap );
		endif;

		// Additional attributes, ignore none
		$field_part .= $this->_get_attributes_string( $args['wrap_attributes'], array() );

		$field_part .= '>';

		// Can't check for empty, since closing bracket gets added no matter what
		if ( false !== strpos( $field_part, '<' ) )
			$field['open'] = $field_part;

		return $field;
	}

	/**
	 * Field wrap closing HTML tag.
	 *
	 * @since 1.2.0
	 * @param string $wrap HTML tag to use as a wrapping element.
	 * @return array Field part with the 'close' key.
	 */
	protected function _get_field_wrap_close( $wrap ) {
		$field = array();
		$field_part = '';

		// Custom tag if defined
		if ( 'default' != $wrap ) :
			$field_part = '</' . $this->clean_html( $wrap ) . '>';

		// Default tag if it isn't empty
		elseif ( $this->field_wrap ) :
			$field_part = '</' . $this->clean_html( $this->field_wrap ) . '>';
		endif;

		if ( $field_part )
			$field['close'] = $field_part;

		return $field;
	}

	/**
	 * The field label.
	 *
	 * @since 1.2.0
	 * @param string $id Field ID to set in the 'for' attribute.
	 * @param array $args Arguments passed to add_field().
	 * @return array Field part with the 'label' key.
	 */
	protected function _get_field_label( $id, $args ) {
		$field_part = "<label for=\"{$id}\"";

		// Additional attributes, ignore 'for'.
		$field_part .= $this->_get_attributes_string( $args['label_attributes'], 'for' );

		$field_part .= ">{$args['label']}</label>";

		if ( $args['label_break'] )
			$field_part .= '<br>';

		return array( 'label' => $field_part );
	}

	/**
	 * Field description.
	 *
	 * @since 1.2.0
	 * @param string $description_text Text to display.
	 * @return array Field part with the 'description' key.
	 */
	protected function _get_field_description( $description_text ) {
		return array( 'description' => "<span class=\"description\">{$description_text}</span>" );
	}

	/**
	 * Assemble a textarea as an array of parts.
	 *
	 * @since 1.2.0
	 * @see add_field()
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_textarea( $name, $args ) {
		$aria_required = ( $args['required'] ) ? 'aria-required="true"' : 'aria-required="false"';

		// Start field tag
		$field_part = "<textarea name=\"{$name}\" id=\"{$name}\" rows=\"{$args['rows']}\" cols=\"{$args['cols']}\" {$aria_required}";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field_part .= ">";

		$field['tag_open'] = $field_part;

		// POST data as separate item, for easier cleaning.
		// Textareas keep the text between the tags.
		if ( $this->is_valid_field_post( $name ) )
			$field['post'] = esc_textarea( $_POST[$name] );

		// Close field tag
		$field_part = '</textarea>';

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Assemble a select box as an array of parts.
	 *
	 * @since 1.2.0
	 * @see add_field()
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_select( $name, $args ) {
		$aria_required = ( $args['required'] ) ? 'aria-required="true"' : 'aria-required="false"';

		// Start field tag
		$field_part = "<select name=\"{$name}\" id=\"{$name}\" {$aria_required}";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field_part .= ">";

		$field['tag_open'] = $field_part;

		// Instead of keeping the <option>s as nested, multi-part arrays, they are
		// stored as a string. This means the POST part can't just be emptied for
		// select boxes, but selected="selected" is easy enough to regex away and
		// this keeps the $_fields array structure much simpler.
		$field_part = '';
		foreach ( $args['options'] as $val => $text ) :
			$field_part .= "<option value=\"{$val}\"";

			// Check selected state
			if ( $this->is_valid_field_post( $name ) && $_POST[$name] == $val )
				$field_part .= ' selected="selected"';

			$field_part .= ">{$text}</option>";
		endforeach;

		$field['options'] = $field_part;

		// Close field tag
		$field_part = "</select>";

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Assemble a radio button input as an array of parts.
	 *
	 * @since 1.2.0
	 * @see add_field()
	 * @param string $name Name attribute.
	 * @param string $id Id attribute, different from name for radio buttons.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_radio( $name, $id, $args ) {

		// Start field tag
		$field_part = "<input type=\"radio\" name=\"{$name}\" id=\"{$id}\"";

		if ( $args['value'] )
			$field_part .= " value=\"{$args['value']}\"";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field['tag_open'] = $field_part;

		// POST data as separate item, for easier cleaning.
		// Radio buttons keep the checked state.
		if ( $this->is_valid_field_post( $name ) && $args['value'] )
			$field['post'] = ( $_POST[$name] == $args['value'] ) ? ' checked="checked"' : '';

		// Close field tag
		$field_part = ">";

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Assemble a hidden input as an array of parts.
	 *
	 * @since 1.2.0
	 * @see add_field()
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_hidden( $name, $args ) {

		// Start field tag
		$field_part = "<input type=\"hidden\" name=\"{$name}\" id=\"{$name}\"";

		if ( $args['value'] )
			$field_part .= " value=\"{$args['value']}\"";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field['tag_open'] = $field_part;

		// Close field tag
		$field['tag_close'] = '>';

		return $field;
	}

	/**
	 * Assemble a default input field as an array of parts.
	 *
	 * @since 1.2.0
	 * @see add_field()
	 * @param string $type The field type, i.e. text or email.
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_input_field( $type, $name, $args ) {
		$aria_required = ( $args['required'] ) ? 'aria-required="true"' : 'aria-required="false"';
		$checkbox_value = ( $args['value'] ) ? $args['value'] : '1';

		// "Attribute aria-required not allowed on element input at this point."
		if ( 'checkbox' == $type )
			$aria_required = '';

		// Start field tag
		$field_part = "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" {$aria_required}";

		if ( 'checkbox' == $type )
			$field_part .= " value=\"{$checkbox_value}\"";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field['tag_open'] = $field_part;

		// POST data as separate item, for easier cleaning.
		// Checkboxes keep checked state, other inputs have the value attribute.
		if ( $this->is_valid_field_post( $name ) ) :
			if ( 'checkbox' == $type ) :
				$field['post'] = ( (string) $checkbox_value === (string) $_POST[$name] ) ? ' checked="checked"' : '';
			else :
				$field['post'] = ' value="' . esc_attr( $_POST[$name] ) . '"';
			endif;
		endif;

		// Close field tag
		$field_part = ">";

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Add a string to the $_fields array.
	 *
	 * This could be used to add stuff like fieldsets, horizontal rules and
	 * other miscellaneous HTML. It's also useful for grouping multiple fields
	 * in one 'field wrap': set the field_wrap argument for add_field() to an
	 * empty string and use this to insert starting and closing tags.
	 *
	 * This is added as-is, so remember to sanitize if user data is involved.
	 *
	 * @since 1.0.0
	 * @param string $string String of HTML to add.
	 */
	public function add_to_field_list( $string ) {
		$this->_html_count++;

		$this->_fields['html-' . $this->_html_count] = array(
			'type' => 'html',
			'string' => $string
		);
	}

	/**
	 * Add a submit button to the $_fields array.
	 *
	 * This should most likely always run last, after every add_field() call.
	 *
	 * @since 1.0.0
	 * @param string $value The value attribute i.e. text for the button.
	 * @param array $attributes Additional HTML attributes, format	attr => value.
	 */
	public function add_submit( $value, array $attributes = array() ) {
		$field = "<input type=\"submit\" value=\"{$value}\"";

		// Additional attributes, ignore 'type' and 'value'
		$field .= $this->_get_attributes_string( $attributes, array( 'type', 'value' ) );

		$field .= '>';

		$this->_fields['submit'] = array( 'type' => 'submit', 'tag_open' => $field );
	}










	/*-------------------------------------------------------------------------*\
	      =Validation and sending
	\*-------------------------------------------------------------------------*/

	/**
	 * Verify that the form is posted correctly.
	 *
	 * Checks several things:
	 *
	 * - There is a POST request
	 * - The $_POST superglobal is not empty
	 * - The referer is 'correct' (easily spoofed, but no harm in checking)
	 * - The correct form ID is posted, or the nonce is correct
	 *
	 * @since 1.5.2
	 * @return bool True if everything passed, false otherwise.
	 */
	public function is_valid_post() {

		// Check POST request and correct referer
		$is_posted = ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST ) );

		// Check that the correct form is processed
		if ( $this->use_nonce )
			$is_verified = ( $is_posted && wp_verify_nonce( $_POST[$this->_form_id], $this->_form_id ) );
		else
			$is_verified = (
				$is_posted
				&& isset( $_POST['lucid-form-id'] )
				&& $_POST['lucid-form-id'] == $this->_form_id
			);

		// Posted but invalid nonce
		if ( $this->use_nonce && $is_posted && ! $is_verified )
			$this->set_form_error( $this->_form_messages['invalid_post'] );

		return ( $is_posted && $is_verified );
	}

	/**
	 * Check if a field has been posted.
	 *
	 * Verifies that the field has a posted value and that the value belongs to
	 * the current form.
	 *
	 * @since 1.9.0
	 * @param string $field_name The field name.
	 * @return bool
	 */
	public function is_valid_field_post( $field_name ) {
		return ( $this->is_valid_post() && isset( $_POST[$field_name] ) );
	}

	/**
	 * Set a field's error message.
	 *
	 * @since 1.9.0
	 * @param string $field_name The field name.
	 * @param string $error_msg Error message to display.
	 * @param string $field_class Optional. Input field class name.
	 * @param string $msg_class Optional. Error message class name.
	 */
	public function set_field_error( $field_name, $error_msg, $field_class = null, $msg_class = null ) {
		$data = $this->_fields[$field_name];

		if ( null === $field_class )
			$field_class = 'error input-error';
		if ( null === $msg_class )
			$msg_class = 'error field-error';

		$field_class_attr = ( $field_class ) ? " class=\"{$field_class}\"" : '';
		$msg_class_attr = ( $msg_class ) ? " class=\"{$msg_class}\"" : '';

		$error_msg_html = sprintf( '<span%s>%s</span>', $msg_class_attr, $error_msg );

		// Add error span before closing wrap tag if it exists in $fields
		if ( isset( $data['close'] ) ) :
			$this->_fields[$field_name] = $this->_array_insert(
				'after',
				'tag_close',
				$this->_fields[$field_name],
				'error',
				$error_msg_html
			);

		// Otherwise just add it last
		else :
			$this->_fields[$field_name]['error'] = $error_msg_html;
		endif;

		$tag = $data['tag_open'];

		// Update form tag with error classes and aria-invalid. If there is
		// a class attribute, add to it. Otherwise, add a class attribute
		// with the regex: |<tag|> => |<tag class="[...]"|>
		if ( strpos( $tag, 'class="' ) ) :
			$this->_fields[$field_name]['tag_open'] = str_replace( 'class="', sprintf( 'aria-invalid="true" class="%s ', $field_class ), $tag );
		else :
			$this->_fields[$field_name]['tag_open'] = preg_replace( '/<[\w\-]+(?=\s|>)/', sprintf( '$0%s aria-invalid="true"', $field_class_attr ), $tag );
		endif;
	}

	/**
	 * Check if a field has an error set.
	 *
	 * @since 1.9.0
	 * @param string $field_name The field name.
	 * @return bool
	 */
	public function field_has_error( $field_name ) {
		return ( ! empty( $this->_fields[$field_name]['error'] ) );
	}

	/**
	 * Validate POST data and set error messages.
	 *
	 * TODO: Break apart this monster of a method.
	 *
	 * @since 1.0.0
	 * @return bool True if POST data is valid, false if there were errors.
	 */
	public function is_valid_data() {
		$all_is_well = true;

		// Honeypot checks
		$honeypot_error = false;
		$error_count = 0;

		// Check every field
		foreach ( $this->_fields as $name => $data ) :

			// Skip submit fields
			if ( isset( $data['type'] ) && 'submit' == $data['type'] )
				continue;

			$error_msg = '';
			$validation = isset( $data['validation'] ) ? $data['validation'] : '';

			// Radio button ID clash handling, remove added -# string
			$id = $name;
			if ( 'radio' == $data['type'] )
				$id = preg_replace( '/-\d+$/', '', $name );

			$value = ( isset( $_POST[$id] ) ) ? $_POST[$id] : null;

			// If field is required and empty
			// Some ugly stuff here, but file and other inputs needs different
			// checks for empty, and all conditions must be checked at the same
			// level for elseif to kick in on non-empty fields.
			if ( (
				   'file' != $data['type']
				&& ! empty( $data['required'] )
				&& ( empty( $value ) && ! in_array( $value, array( 0, 0.0, '0' ), true ) ) // Allow zeroes
			) || (
				   'file' == $data['type']
				&& UPLOAD_ERR_NO_FILE == $_FILES[$name]['error']
				&& ! empty( $data['required'] )
			) ) :
				$error_count++;

				// Add error message for 'empty' if it exists
				if ( ! empty( $data['error_empty'] ) ) :
					$error_msg = $data['error_empty'];
				endif;

			// If field has some kind of validation defined and a message for
			// invalid data
			elseif ( ! empty( $validation ) ) :
				$is_valid = true;

				switch ( $validation ) :
					case 'email' :
						$is_valid = $this->is_valid_email( $value );
						break;

					case 'tel' :
						$is_valid = $this->is_valid_tel( $value );
						break;

					case 'honeypot' :
						$is_valid = ( '' == $value );
						break;

					default :
						// Non-reserved strings assumed to be regex.
						$is_valid = (bool) preg_match( $validation, $value );
						$is_valid = ( $data['reverse_validation'] ) ? ! $is_valid : $is_valid;
						break;
				endswitch;

				// If the field is optional and empty, ignore any validation done
				if ( empty( $data['required'] ) && empty( $value ) )
					$is_valid = true;

				// Invalid, add relevant data
				if ( ! $is_valid ) :
					$error_count++;

					// Add error message for 'invalid' if it exists
					if ( ! empty( $data['error_invalid'] ) )
						$error_msg = $data['error_invalid'];

					// See large comment about honeypot below
					if ( 'honeypot' == $validation )
						$honeypot_error = true;
				endif;

			// If a file input has an invalid file
			elseif ( 'file' == $data['type'] ) :
				$file_error = $this->get_file_error( $name );
				if ( $file_error ) :
					$error_msg = $file_error;
					$error_count++;
				endif;

			// If an error has been added externally
			elseif ( ! empty( $data['error'] ) ) :
				$all_is_well = false;
				$error_count++;
			endif;

			// Add error indications
			if ( $error_msg ) :
				$all_is_well = false;
				$this->set_field_error( $name, $error_msg );
			endif;
		endforeach; // Field loop

		// Set success/error message
		if ( $all_is_well ) :
			$this->set_form_success( $this->_form_messages['success'] );

		// If the honeypot is the only problem, an extra CSS class is available
		// for potentially showing the honeypot field if it's hidden (a general
		// sibling combinator can be used: [.error-honeypot ~ form <field>],
		// IE7+). This may be needed if some sort of auto form filler is used
		// by a human.
		elseif ( 1 === $error_count && $honeypot_error ) :
			$this->set_form_error( $this->_form_messages['honeypot'], 'error form-error error-honeypot' );

		else :
			$this->set_form_error( $this->_form_messages['error'] );
		endif;

		return $all_is_well;
	}

	/**
	 * Put together the subject line for the email.
	 *
	 * If the subject or the label is set to a field name, a snippet from the
	 * POST data of that field is used as the string. Otherwise the string is
	 * added as is.
	 *
	 * @since 1.1.0
	 * @return string The assembled subject line.
	 */
	public function get_subject() {
		$subject = '';

		// Label, within square brackets
		if ( $this->subject_label && ! empty( $_POST[$this->subject_label] ) ) :
			$temp = $this->get_words_from_string( $_POST[$this->subject_label], 3 );
			$temp = $this->filter_other( $temp );

			$subject = "[{$temp}] ";

		elseif ( $this->subject_label ) :
			$subject = "[{$this->subject_label}] ";
		endif;

		// Subject
		if ( ! empty( $_POST[$this->subject_text] ) ) :
			$temp = $this->get_words_from_string( $_POST[$this->subject_text] );
			$temp = $this->filter_other( $temp );

			$subject .= $temp;

		else :
			$subject .= $this->subject_text;
		endif;

		return $subject;
	}

	/**
	 * Put together the message for the email.
	 *
	 * _get_message and the usage of $message_template is a replacement for this
	 * clunky method.
	 *
	 * If the subject or the label is set to a field name, a snippet from the
	 * POST data of that field is used as the string. Otherwise the string is
	 * added as is.
	 *
	 * @since 1.4.0
	 * @see $message_format For how the message is assembled.
	 * @return array The assembled message.
	 */
	protected function _get_message_from_format() {
		$message = array();

		foreach ( $this->message_format as $part ) :

			// If string is a field ID
			if ( isset( $this->_fields[$part] ) && isset( $_POST[$part] ) ) :

				// Checkbox gets a <field name: yes> format
				if ( 'checkbox' == $this->_fields[$part]['type'] ) :
					$message[] = ucfirst( $part ) . ': ' . _x( 'yes', 'checkbox yes', 'lucid-toolbox' );

				// Prefix/label for the data if set. Use field name if set
				// to 'field'
				elseif ( $this->_fields[$part]['message_prefix'] ) :
					$prefix = ( 'field' == $this->_fields[$part]['message_prefix'] )
						? $part
						: $this->_fields[$part]['message_prefix'];

					$message[] = ucfirst( $prefix ) . ': ' . $_POST[$part];

				// Regular fields just have their data added
				else :
					$message[] = $_POST[$part];
				endif;

			// If string is a checkbox field ID that is not in POST, add it
			// with a <field name: no> format
			elseif ( isset( $this->_fields[$part] )
			      && ! isset( $_POST[$part] )
			      && 'checkbox' == $this->_fields[$part]['type'] ) :
				$message[] = ucfirst( $part ) . ': ' . _x( 'no', 'checkbox no', 'lucid-toolbox' );

			// Add non-field strings as is
			else :
				$message[] = $part;
			endif;

		endforeach;

		return $message;
	}

	/**
	 * Get conditionals in the email message.: {{if}}s and {{if_block}}s.
	 *
	 * @since 1.4.0
	 * @param string $message Text to search in, defaults to the message_template
	 *   property.
	 * @return array Found conditional blocks.
	 */
	protected function _get_message_conditionals( $message = '' ) {

		// Only process once
		if ( empty( $this->_message_conditionals ) ) :
			$message = ( ! empty( $message ) ) ? $message : $this->message_template;

			// Get conditionals: {{if}}s and {{if_block}}s
			// Compacted: '/\{\{\#(if|if_block)\}\}(?:.+?)\{\{\/(?:\1)\}\}/ms'
			preg_match_all( '/
				# Opening if or if_block between {{ }}
				\{\{
					\#(if|if_block)
				\}\}

				# Non-capturing lazy group, keep matching
				# anything until the next part
				(?:.+?)

				# Closing if or if_block between {{ }}.
				# Match whatever was found in the first group
				\{\{
					\/(?:\1)
				\}\}
			/msx', $message, $conditionals );

			$this->_message_conditionals = ( ! empty( $conditionals[0] ) ) ? $conditionals[0] : array();
		endif;

		return $this->_message_conditionals;
	}

	/**
	 * Get template tags in the email message.: anything between {{ and }},
	 * except strings containing #, / or }.
	 *
	 * @since 1.4.0
	 * @param string $message Text to search in, defaults to the message_template
	 *   property.
	 * @return array Found template tags.
	 */
	protected function _get_message_tags( $message = '' ) {

		// Only process once
		if ( empty( $this->_message_tags ) ) :
			$message = ( ! empty( $message ) ) ? $message : $this->message_template;

			// Match '{{', followed by anything but '}#/', and lastly '}}'
			preg_match_all( '/\{\{[^\}#\/]+\}\}/', $message, $tags );
			$this->_message_tags = array_unique( $tags[0] );
		endif;

		return $this->_message_tags;
	}

	/**
	 * Filter conditional template tags in message text.
	 *
	 * Finds {{if}} and {{if_block}} template tags, searches them for the passed
	 * tag and replaces it with relevant content. In the case of an empty value,
	 * the entire if/if_block is removed.
	 *
	 * @since 1.4.0
	 * @param string $tag Mustache style template tag, i.e. {{name}}
	 * @param string $text Text to search in.
	 * @return string Filtered text.
	 */
	protected function _filter_conditional_tag( $tag, $text ) {
		$field = trim( $tag, '{}' );

		// Tag must be a field ID or custom tag to be replaced
		if ( ! isset( $this->_fields[$field] )
		  && ! isset( $this->custom_template_tags[$field] ) )
			return $text;

		$conditionals = $this->_get_message_conditionals( $text );
		foreach ( $conditionals as $cond ) :

			// Skip if tag doesn't exists inside a conditional
			if ( false === strpos( $cond, $tag ) ) continue;

			// Remove the if tags to get what's inside them
			$cond_content = trim( str_replace( array( '{{#if}}', '{{/if}}', '{{#if_block}}', '{{/if_block}}' ), '', $cond ) );

			// Value POSTed and not empty: replace checkbox with 'yes',
			// others with POST value
			if ( ! empty( $_POST[$field] ) ) :
				$post_value = ( ! empty( $this->html_template ) )
					? nl2br( $this->filter_html( $_POST[$field] ) )
					: $_POST[$field];

				$replace = ( $this->is_checkbox( $field ) && ! $this->_fields[$field]['value'] )
					? str_replace( $tag, _x( 'yes', 'checkbox yes', 'lucid-toolbox' ), $cond_content )
					: str_replace( $tag, $post_value, $cond_content );

			// Field is a custom tag
			elseif ( isset( $this->custom_template_tags[$field] ) ) :
				$val = $this->custom_template_tags[$field];

				// Allow the number 0 as a value
				$replace = ( ! empty( $val ) || 0 === $val ) ? str_replace( $tag, $val, $cond_content ) : '';

			// Otherwise replace checkbox with 'no' and remove others
			else :
				$replace = ( $this->is_checkbox( $field ) && ! $this->_fields[$field]['value'] )
					? str_replace( $tag, _x( 'no', 'checkbox no', 'lucid-toolbox' ), $cond_content )
					: '';
			endif;

			// If removing a block, also remove following line break
			$search = ( '' == $replace && false !== strpos( $cond, 'if_block' ) )
				? $cond .= "\n"
				: $cond;

			$text = str_replace( $search, $replace, $text );
		endforeach;

		return $text;
	}

	/**
	 * Filter template tags in message text.
	 *
	 * Searches the text for the passed tag and replaces it with POST content.
	 *
	 * @since 1.4.0
	 * @param string $tag Mustache style template tag, i.e. {{name}}
	 * @param string $text Text to search in.
	 * @return string Filtered text.
	 */
	protected function _filter_tag( $tag, $text ) {
		$field = trim( $tag, '{}' );

		// Tag must be a field ID or custom tag to be replaced
		if ( ! isset( $this->_fields[$field] )
		  && ! isset( $this->custom_template_tags[$field] ) )
			return $text;

		// Field if POSTed
		if ( isset( $_POST[$field] ) ) :

			// Checkbox is set as yes or no
			if ( $this->is_checkbox( $field ) && ! $this->_fields[$field]['value'] ) :
				$text = str_replace( $tag, _x( 'yes', 'checkbox yes', 'lucid-toolbox' ), $text );

			// Regular fields just have their data added
			else :
				$post_value = ( ! empty( $this->html_template ) )
					? nl2br( $this->filter_html( $_POST[$field] ) )
					: $_POST[$field];

				$text = str_replace( $tag, $post_value, $text );
			endif;

		// Field is a custom tag
		elseif ( isset( $this->custom_template_tags[$field] ) ) :
			$val = $this->custom_template_tags[$field];

			// Allow the number 0 as a value
			$text = ( ! empty( $val ) || 0 === $val ) ? str_replace( $tag, $val, $text ) : $text;

		// Unchecked checkboxes don't get POSTed, so if string is a checkbox
		// field ID that is not in POST, add it with a 'no'
		elseif ( $this->is_checkbox( $field ) ) :
			$text = str_replace( $tag, _x( 'no', 'checkbox no', 'lucid-toolbox' ), $text );

		endif;

		return $text;
	}

	/**
	 * Put together the message for the email.
	 *
	 * The message_template property is used as a template and searched for
	 * mustache-style template tags, like {{tag}}. Found tags are checked
	 * against the $_fields array and matches replaced with POST values.
	 *
	 * @since 1.1.0
	 * @see $message_template For how templates work.
	 * @return string The message.
	 */
	protected function _get_template_message() {
		if ( empty( $this->message_template ) ) return '';

		$message = $this->normalize_line_break( $this->message_template );
		$tags = $this->_get_message_tags( $message );

		// Check every found template tag
		foreach ( $tags as $tag ) :
			$message = $this->_filter_conditional_tag( $tag, $message );
			$message = $this->_filter_tag( $tag, $message );
		endforeach;

		return trim( $message );
	}

	/**
	 * Put together the HTML message for the email.
	 *
	 * @since 1.4.0
	 * @return string The complete message.
	 */
	protected function _get_html_message() {

		if ( file_exists( $this->html_template ) ) :
			ob_start();
			include $this->html_template;
			$message = ob_get_clean();
		else :
			return '';
		endif;

		$message = $this->normalize_line_break( $message );
		$tags = $this->_get_message_tags( $message );

		// Check every found template tag
		foreach ( $tags as $tag ) :
			$message = $this->_filter_conditional_tag( $tag, $message );
			$message = $this->_filter_tag( $tag, $message );
		endforeach;

		// Remove HTML comments
		$message = preg_replace( '/\<\!--(?:(?!--\>).)+--\>/ms', '', $message );

		// Remove CSS comments
		$message = preg_replace( '/\/\*(?:(?!\*\/).)+\*\//ms', '', $message );

		// Remove tabs
		$message = str_replace( "\t", '', $message );

		// Collapse multiple line breaks
		$message = preg_replace( '/\R+/u', "\n", $message );

		return trim( $message );
	}

	/**
	 * Get the email message.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_message() {
		if ( ! empty( $this->html_template ) )
			$message = $this->_get_html_message();
		elseif ( ! empty( $this->message_template ) )
			$message = $this->_get_template_message();
		else
			$message = implode( $this->message_format_separator, $this->_get_message_from_format() );

		return $message;
	}

	/**
	 * Get and filter a field's POST data.
	 *
	 * If there is no POST data, the field key is returned as is. Specifically
	 * made for properties that can be either a field's data or a harcoded
	 * option.
	 *
	 * @since 1.5.0
	 * @param string $field Field ID to check.
	 * @param string $filter Filter to run on the data. Defaults to 'other',
	 *   with additional options being 'name', 'email' and 'none'. See
	 *   corresponding filter_x methods.
	 * @return string
	 */
	protected function _get_field_post( $field, $filter = 'other' ) {
		$data = ( isset( $this->_fields[$field] ) && isset( $_POST[$field] ) )
			? $_POST[$field]
			: $field;

		switch ( $filter ) :
			case 'name' :
				$data = $this->filter_name( $data );
				break;

			case 'email' :
				$data = $this->filter_email( $data );
				break;

			case 'other' :
				$data = $this->filter_other( $data );
				break;
		endswitch;

		return $data;
	}

	/**
	 * Put together email headers.
	 *
	 * What headers to include can be set with the $include array parameter.
	 *
	 * - 'regular' From and, depending on 'reply_to', Reply-To headers for the
	 *   main recipient.
	 * - 'extra' From and, depending on 'reply_to', Reply-To headers for the
	 *   extra recipients.
	 * - 'custom' Any custom headers set to the extra_headers property.
	 * - 'reply_to' Whether to include Reply-To with the 'regular' and 'extra'
	 *   headers.
	 *
	 * @since 1.1.0
	 * @param array $include Headers to include.
	 * @return array wp_mail accepts headers as an array.
	 */
	protected function _get_headers( array $include = array() ) {
		$defaults = array(
			'regular'  => true,
			'extra'    => false,
			'custom'   => true,
			'reply_to' => true
		);
		$include = array_merge( $defaults, $include );
		$headers = array();

		// From and Reply-To
		if ( $include['regular'] && $this->from_name && $this->from_address ) :
			$name = $this->_get_field_post( $this->from_name, 'name' );
			$address = $this->_get_field_post( $this->from_address, 'email' );

			$headers[] = "From: {$name} <{$address}>";

			if ( $include['reply_to'] ) :
				if ( $this->reply_to_name && $this->reply_to_address ) :
					$reply_name = $this->_get_field_post( $this->reply_to_name, 'name' );
					$reply_address = $this->_get_field_post( $this->reply_to_address, 'email' );
				else :
					$reply_name = $name;
					$reply_address = $address;
				endif;

				$headers[] = "Reply-To: {$reply_name} <{$reply_address}>";
			endif;
		endif;

		// From and Reply-To for extra recipients
		if ( $include['extra'] ) :
			if ( $this->extras_from_name && $this->extras_from_address ) :
				$extra_name = $this->extras_from_name;
				$extra_address = $this->extras_from_address;
			else :
				$extra_name = $this->from_name;
				$extra_address = $this->from_address;
			endif;

			$extra_name = $this->_get_field_post( $extra_name, 'name' );
			$extra_address = $this->_get_field_post( $extra_address, 'email' );

			$headers[] = "From: {$extra_name} <{$extra_address}>";

			if ( $include['reply_to'] ) :
				if ( $this->extras_reply_to_name && $this->extras_reply_to_address ) :
					$extra_reply_name = $this->_get_field_post( $this->extras_reply_to_name, 'name' );
					$extra_reply_address = $this->_get_field_post( $this->extras_reply_to_address, 'email' );
				elseif ( $this->reply_to_name && $this->reply_to_address ) :
					$extra_reply_name = $this->_get_field_post( $this->reply_to_name, 'name' );
					$extra_reply_address = $this->_get_field_post( $this->reply_to_address, 'email' );
				else :
					$extra_reply_name = $extra_name;
					$extra_reply_address = $extra_address;
				endif;

				$headers[] = "Reply-To: {$extra_reply_name} <{$extra_reply_address}>";
			endif;
		endif;

		// Custom headers
		if ( $include['custom'] && ! empty( $this->extra_headers ) ) :
			$this->extra_headers = array_map( array( $this, 'filter_other' ), (array) $this->extra_headers );
			$header = array_merge( $headers, $extra_headers );
		endif;

		// HTML email headers
		if ( ! empty( $this->html_template ) ) :
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-Type: text/html; charset=utf-8';
		endif;

		return $headers;
	}

	/**
	 * Get an array of attachments.
	 *
	 * @since 1.3.0
	 * @return array|bool wp_mail checks for empty array, so return that if
	 *   there are no attachments. False if there is a problem with a file.
	 */
	protected function _get_attachments() {
		$attachments = array();

		// Bail early if applicable
		if ( ! $this->handle_attachments )
			return $attachments;

		$has_errors = false;

		// Check every file field and add file paths to the $_attachments array.
		foreach ( $this->_fields as $field => $data ) :
			if ( 'file' != $data['type'] )
				continue;

			$file_info = pathinfo( $_FILES[$field]['name'] );
			$extension = strtolower( $file_info['extension'] );
			$tmp_file = $_FILES[$field]['tmp_name']; // Temp uploaded file
			$error_code = (int) $_FILES[$field]['error']; // Upload error code

			// Where to move the uploaded file, without trailing slash.
			$uploads = wp_upload_dir();
			$target_dir = rtrim( $uploads['basedir'], '/' );

			// Clean file name
			$target_name = str_replace( '.', '-', remove_accents( basename( $file_info['filename'] ) ) );

			// Complete target path, with a unique file name
			$target_path = $this->_get_unique_file_path( $target_dir, $target_name, $extension );

			// move_uploaded_file also checks if file was uploaded via HTTP POST
			if ( move_uploaded_file( $tmp_file, $target_path ) ) :
				$attachments[] = $target_path;

			// Upload error message
			else :
				$this->set_field_error( $data['name'], $this->_file_form_messages[$error_code] );
				$has_errors = true;
			endif;
		endforeach;

		// Remove any successfully uploaded files
		if ( $has_errors ) :
			$this->_remove_files( $attachments );
			$this->set_form_error( $this->_form_messages['error'] );
			return false;
		endif;

		// Keep the paths for possible file removal.
		$this->_attachments = $attachments;

		return $attachments;
	}

	/**
	 * Get a unique file path, to prevent collisions.
	 *
	 * Check if a file with the given name and path exists. If so, add a number
	 * to the name (file.ext => file-#.ext). Recursively call self with a higher
	 * number until a unique file name is found.
	 *
	 * @since 1.3.0
	 * @param string $dir Directory of the file.
	 * @param string $file The file name.
	 * @param string $extension The file extension.
	 * @param int $number Number to add to file name if it already exists.
	 * @return string Full unique path to the file.
	 */
	protected function _get_unique_file_path( $dir, $file, $extension, $number = 1 ) {
		$original_path = "{$dir}/{$file}.{$extension}";
		$new_path = "{$dir}/{$file}-{$number}.{$extension}";

		// Return original file path if possible
		if ( ! file_exists( $original_path ) ) :
			return $original_path;

		// Return new file path with a number added
		elseif ( ! file_exists( $new_path ) ) :
			return $new_path;

		// Call self with higher number
		else :
			return $this->_get_unique_file_path( $dir, $file, $extension, $number + 1 );
		endif;
	}

	/**
	 * Data validation before constructing a message.
	 *
	 * @since 1.7.0
	 * @return bool
	 */
	public function is_valid_send() {
		if ( ! is_null( $this->_is_valid_send ) )
			return $this->_is_valid_send;

		$this->_is_valid_send = (bool) (
			   $this->has_required_send_data()
			&& $this->is_valid_post()
			&& $this->is_valid_data()
		);

		return $this->_is_valid_send;
	}

	/**
	 * Send with wp_mail().
	 *
	 * TODO: Separate this a bit.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/wp_mail
	 * @return bool True if wp_mail was successful, false otherwise.
	 */
	public function send() {

		// Check posting and data validation
		if ( $this->validate_send && ! $this->is_valid_send() )
			return false;

		// Get form data
		$to = $this->to_address;
		$subject = $this->get_subject();
		$headers = $this->_get_headers();
		$attachments = $this->_get_attachments();

		// _get_attachments() sets errors and returns false if there is a
		// problem, so skip further processing if that's the case.
		if ( ! is_array( $attachments ) )
			return false;

		$message = $this->get_message();

		// RFC 5322 states lines should be delimited by a carriage return
		// character, followed immediately by a line feed character. [Page 5]
		if ( empty( $this->html_template ) )
			$message = $this->normalize_line_break( $message, "\r\n" );

		// --Debug--
		// Print headers and message
		if ( $this->debug_mode ) :
			$this->_debug_open( 'Message' );
			echo '<i>To</i>: ' . $to . '<br><br>';
			echo '<i>Subject</i>: ' . $subject . '<br><br>';
			echo '<i>Headers</i>:<br>' . htmlspecialchars( implode( "\n", $headers ) ) . '<br><br>';
			echo '<i>Message</i>:<br>' . htmlspecialchars( $message, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE ) . '<br><br>';
			$this->_debug_close();
		endif;

		// Send with wp_mail
		// _get_attachments() returns false if there is a problem, so check
		// if $attachments is an array.
		if ( ! empty( $message ) && ! $this->debug_mode ) :
			$sent = wp_mail( $to, $subject, $message, $headers, $attachments );

			if ( $sent )
				do_action( 'lucid_contact_sent', $to, $subject, $message );

			$send_extra = ( ! empty( $this->extra_recipients ) );

			// If sending extra, default to true and overwrite in loop below.
			// Otherwise match regular sent status.
			$extra_sent = ( $send_extra ) ? true : $sent;

			// Send to additional recipients
			if ( $send_extra ) :
				$extra_headers = $this->_get_headers( array( 'regular' => false, 'extra' => true, 'custom' => false ) );
				$this->extra_recipients = array_map( array( $this, 'filter_other' ), (array) $this->extra_recipients );

				foreach ( $this->extra_recipients as $to ) :
					$extra_mail = wp_mail( $to, $subject, $message, $extra_headers, $attachments );

					// Can't just set $extra_sent = wp_mail(), in case there is a
					// successful send after a failed one.
					if ( ! $extra_mail ) :
						$extra_sent = false;
					else :
						do_action( 'lucid_contact_sent', $to, $subject, $message );
					endif;
				endforeach;
			endif;

			// All sent, clear form
			if ( $sent && $extra_sent ) :
				$this->_clear_send();
				return true;

			// None sent
			elseif ( ! $sent && ! $extra_sent ) :
				$this->set_form_error( $this->_form_messages['not_sent'] );
				return false;

			// Some sent
			else :
				$this->set_form_error( $this->_form_messages['some_sent'] );
				return false;
			endif;

		// No message or debug mode on
		else :
			$this->set_form_error( $this->_form_messages['not_sent'] );
			return false;
		endif;
	}

	/**
	 * Check if there is a recipient address and a message format.
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public function has_required_send_data() {
		$has_data = true;

		// Show some errors instead of failing silently
		if ( empty( $this->to_address ) ) :
			trigger_error( 'No to_address specified', E_USER_WARNING );
			$has_data = false;
		endif;

		if ( empty( $this->message_template )
		  && empty( $this->message_format )
		  && empty( $this->html_template ) ) :
			trigger_error( 'No template for the message specified, see message_template or message_format', E_USER_WARNING );
			$has_data = false;
		endif;

		return $has_data;
	}

	/**
	 * Remove files from the server.
	 *
	 * @since 1.9.0
	 * @param array $paths Absolute file paths to delete.
	 */
	protected function _remove_files( array $paths ) {
		foreach ( $paths as $path ) :
			if ( is_file( $path ) ) :
				// Prevent error if file isn't removed successfully
				@unlink( $path );
			endif;
		endforeach;
	}

	/**
	 * Clear the form after message is sent.
	 *
	 * @since 1.0.0
	 */
	protected function _clear_send() {

		// Clear POST data to empty the form
		foreach ( $this->_fields as $field => $data ) :

			// Select fields store all the <options> as a single string
			// instead of separate POST values, so it can't be set to
			// an empty string.
			// Regex out | selected="<anything but quotes>"|.
			if ( 'select' == $data['type'] ) :
				$this->_fields[$field]['options'] = preg_replace( '/ selected=[\"\']([^"\']+)[\"\']/', '', $data['options'] );

			// All other fields keep it simple
			else :
				$this->_fields[$field]['post'] = '';
			endif;

		endforeach;

		// Delete attachment files from the server
		if ( $this->delete_sent_files )
			$this->_remove_files( (array) $this->_attachments );
	}










	/*-------------------------------------------------------------------------*\
	      =Form rendering
	\*-------------------------------------------------------------------------*/

	/**
	 * Get a hidden input with the form ID.
	 *
	 * Used to verify that the correct form POST is processed, since there can
	 * be multiple forms on a page.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	protected function _get_id_field() {
		if ( $this->use_nonce )
			$field = '<input type="hidden" name="' . $this->_form_id . '" value="' . wp_create_nonce( $this->_form_id ) . '">';
		else
			$field = '<input type="hidden" name="lucid-form-id" value="' . $this->_form_id . '">';

		return $field;
	}

	/**
	 * Set the form's status message.
	 *
	 * @since 1.9.0
	 */
	public function set_form_status( $msg, $class_name ) {
		$class_attr = ( $class_name ) ? sprintf( ' class="%s"', $class_name ) : '';
		$this->_form_status = sprintf( '<div%s>%s</div>', $class_attr, $msg );
	}

	/**
	 * Set form error message.
	 *
	 * @since 1.9.0
	 */
	public function set_form_error( $msg, $class_name = 'error form-error' ) {
		$this->set_form_status( $msg, $class_name );
	}

	/**
	 * Set form warning message.
	 *
	 * @since 1.9.0
	 */
	public function set_form_warning( $msg, $class_name = 'warning form-warning' ) {
		$this->set_form_status( $msg, $class_name );
	}

	/**
	 * Set form success message.
	 *
	 * @since 1.9.0
	 */
	public function set_form_success( $msg, $class_name = 'success form-success' ) {
		$this->set_form_status( $msg, $class_name );
	}

	/**
	 * Get the current form status message.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	public function get_form_status() {
		return $this->_form_status;
	}

	/**
	 * Output the current form status message.
	 *
	 * @since 1.6.0
	 * @see get_form_status()
	 */
	public function form_status() {
		echo $this->get_form_status();
	}

	/**
	 * Get the start of the form.
	 *
	 * Triggers the POST check and sending and returns the first part of the
	 * form. This includes some hidden fields and the appropriate attributes.
	 *
	 * @since 1.6.0
	 * @param bool $include_status Include form status message in output.
	 *   Defaults to true.
	 * @return string
	 */
	public function get_form_start( $include_status = true ) {

		// --Debug--
		// Print the $_fields array
		if ( $this->debug_mode ) :
			$this->_debug_open( '$this->_fields' );
			$debug_fields =  $this->_fields;
			array_walk_recursive( $debug_fields, array( 'self', '_debug_filter' ) );
			print_r( $debug_fields );
			$this->_debug_close();
		endif;

		if ( $this->handle_post )
			$this->_form_sent = $this->send();

		$form_start = '';

		// Status message
		if ( $include_status )
			$form_start .= $this->get_form_status();

		$form_start .= "<form action=\"{$this->form_action}\" method=\"{$this->form_method}\"";

		// Add required enctype if doing attachments
		if ( $this->handle_attachments )
			$form_start .= ' enctype="multipart/form-data"';

		// Additional attributes, ignore 'action', 'method' and 'novalidate'
		$form_start .= $this->_get_attributes_string(
			$this->form_attributes,
			array( 'action', 'method', 'novalidate' )
		);

		// Disable built-in browser validation. It's inconsistent both in
		// function and appearance
		$form_start .= " novalidate>\n";

		// Included just to possibly save processing if the file is too big, it's
		// not secure or reliable by any means.
		if ( $this->max_file_size )
			$form_start .= "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$this->max_file_size}\">\n";

		$form_start .= $this->_get_id_field();

		return $form_start;
	}

	/**
	 * Output the start of the form.
	 *
	 * @since 1.6.0
	 * @see get_form_start()
	 * @param bool $include_status Include form status message in output.
	 *   Defaults to true.
	 */
	public function form_start( $include_status = true ) {
		echo $this->get_form_start( $include_status );
	}

	/**
	 * Get the end of the form.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	public function get_form_end() {
		return '</form>';
	}

	/**
	 * Output the end of the form.
	 *
	 * @since 1.6.0
	 * @see get_form_end()
	 */
	public function form_end() {
		echo $this->get_form_end();
	}

	/**
	 * Assemble the $_fields array to a complete form.
	 *
	 * @since 1.0.0
	 * @return string The finished form.
	 */
	public function assemble_form() {
		$form = $this->get_form_start( true );

		foreach ( $this->_fields as $id => $field_data )
			$form .= $this->get_field( $id );

		$form .= $this->get_form_end();

		return $form;
	}

	/**
	 * Alias to assemble_form.
	 *
	 * @since 1.6.0
	 * @see assemble_form()
	 * @return string The finished form.
	 */
	public function get_form() {
		return $this->assemble_form();
	}

	/**
	 * Render the assembled form.
	 *
	 * This is the function to use after adding all the fields. In addition to
	 * rendering, sending and validation is part of the process.
	 *
	 * @since 1.0.0
	 * @see assemble_form()
	 */
	public function render_form() {
		echo $this->get_form();
	}

	/**
	 * Get a form field.
	 *
	 * Puts together form field HTML from the $_fields array data for the
	 * specified field.
	 *
	 * @since 1.6.0
	 * @param string $id Form field ID.
	 * @return string
	 */
	public function get_field( $id ) {
		if ( empty( $this->_fields[$id] ) )
			return '';

		$field = '';
		$include_parts = false;
		$type = $this->_fields[$id]['type'];
		$value = ( ! empty( $this->_fields[$id]['value'] ) ) ? $this->_fields[$id]['value'] : '';

		// Only add stuff between opening and closing tags, so stuff like
		// validation data is skipped.
		foreach ( $this->_fields[$id] as $part => $val ) :

			// Other keys apart from 'open' counts as start due to optional field
			// wraps, custom html keys etc.
			if ( in_array( $part, array( 'open', 'label', 'tag_open', 'string' ) ) )
				$include_parts = true;

			if ( $include_parts ) :
				$field .= $val;
				$field .= ( ! in_array( $part, array( 'post', 'tag_open' ) ) ) ? "\n" : '';
			endif;

			// Initially unsupported and therefore even more hacky than the rest of
			// this class: default value.
			// If there is a value set, the field has a 'value supported' type, we
			// just opened the tag and there is no posted value, add the set value
			// to the field output.
			if (
				$value &&
				! in_array( $type, array( 'select', 'radio', 'checkbox' ) ) &&
				$part == 'tag_open' &&
				! isset( $this->_fields[$id]['post'] )
			) :
				$field .= ( $type == 'textarea' ) ? $value : sprintf( ' value="%s"', $value );
			endif;

			if ( $part == 'close' )
				$include_parts = false;
		endforeach;

		return $field;
	}

	/**
	 * Output a form field.
	 *
	 * @since 1.6.0
	 * @param string $id Form field ID.
	 * @see get_field()
	 */
	public function render_field( $id ) {
		echo $this->get_field( $id );
	}










	/*-------------------------------------------------------------------------*\
	      =Misc. functions and utilities
	\*-------------------------------------------------------------------------*/


	/**
	 * Check if the form has sent successfully.
	 *
	 * @since 1.6.2
	 * @return bool
	 */
	public function is_form_sent() {
		return $this->_form_sent;
	}

	/**
	 * Get the _fields array.
	 *
	 * @since 1.7.0
	 * @return array
	 */
	public function get_field_data() {
		return $this->_fields;
	}

	/**
	 * For debug purposes, encodes all fields through array_walk_recursive() so
	 * the markup is displayed.
	 *
	 * @since 1.0.0
	 * @param string $value Array item value.
	 */
	protected function _debug_filter( &$value ) {
		$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Additional HTML attributes for an element.
	 *
	 * Loops through an array of HTML attributes in in 'attr' => 'val' format
	 * and merges them into a string.
	 *
	 * @since 1.2.0
	 * @param array $attributes HTML attributes.
	 * @param $ignored array|string Attributes to ignore. A string can be passed
	 *   in case of a single attribute. Defaults to $this->_ignore_field_attrs if
	 *   set to string 'default'. Pass empty to ignore none.
	 * @return string
	 */
	protected function _get_attributes_string( array $attributes, $ignored = 'default' ) {
		$ignored = ( 'default' == $ignored )
			? $this->_ignore_field_attrs
			: (array) $ignored;

		$attributes_string = '';

		foreach ( $attributes as $attr => $val ) :
			if ( in_array( $attr, $ignored ) ) continue;

			$attr = sanitize_html_class( $attr );
			$val = esc_attr( $val );

			$attributes_string .= " {$attr}=\"{$val}\"";
		endforeach;

		return $attributes_string;
	}

	/**
	 * Check if a field is a checkbox.
	 *
	 * @since 1.4.0
	 * @param string $field Field ID.
	 * @return boolean
	 */
	public function is_checkbox( $field ) {
		return isset( $this->_fields[$field]['type'] ) && 'checkbox' == $this->_fields[$field]['type'];
	}

	/**
	 * Validate an email address.
	 *
	 * Original code by Douglas Lovell.
	 *
	 * @since 1.0.0
	 * @param string $email Email address to validate.
	 * @param bool $check_dns Whether to do a DNS check of the email address
	 *   domain. If empty, checks do_email_dns_check property.
	 * @return bool True if email is valid, false otherwise.
	 */
	public function is_valid_email( $email, $check_dns = '' ) {
		if ( ! $check_dns ) $check_dns = (bool) $this->do_email_dns_check;

		$is_valid = true;
		$at_index = strrpos( $email, '@' );

		if ( false === $at_index ) :
			$is_valid = false; // No '@'

		else :
			$domain = substr( $email, $at_index + 1 );
			$local = substr( $email, 0, $at_index );
			$local_len = strlen( $local );
			$domain_len = strlen( $domain );

			if ( $local_len < 1 || $local_len > 64 ) :
				$is_valid = false; // Local part length exceeded

			elseif ( $domain_len < 1 || $domain_len > 255 ) :
				$is_valid = false; // Domain part length exceeded

			elseif ( '.' == $local[0] || '.' == $local[$local_len - 1] ) :
				$is_valid = false; // Local part starts or ends with '.'

			elseif ( preg_match( '/\\.\\./', $local ) ) :
				$is_valid = false; // Local part has two consecutive dots

			elseif ( ! preg_match( '/^[A-Za-z0-9\\-\\.]+$/', $domain ) ) :
				$is_valid = false; // Character not valid in domain part

			elseif ( '.' == $domain[0] || '.' == $domain[$domain_len - 1] ) :
				$is_valid = false; // Domain part starts or ends with '.'

			elseif ( preg_match( '/\\.\\./', $domain ) ) :
				$is_valid = false; // Domain part has two consecutive dots

			elseif ( ! preg_match( '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace( "\\\\", '', $local ) ) ) :

				// Character not valid in local part unless local part is quoted
				if ( ! preg_match( '/^"(\\\\"|[^"])+"$/', str_replace( "\\\\", '', $local ) ) ) :
					$is_valid = false;
				endif;

			endif;

			// DNS check
			if ( function_exists( 'checkdnsrr' ) && $is_valid && $check_dns ) :

				if ( ! checkdnsrr( $domain, 'MX' ) && ! checkdnsrr( $domain, 'A' ) ) :
					$is_valid = false; // Domain not found in DNS
				endif;

			endif;

		endif;

		return $is_valid;
	}

	/**
	 * Loosly validate a phone number.
	 *
	 * Since phone numbers can have vastly different formats, as well as be
	 * written in many different ways, this validation is very generous.
	 * Allowed are digits, dots, hyphens, plusses, round brackets and spaces
	 * in any format, as long as the number is at least eight characters long.
	 *
	 * @since 1.0.0
	 * @param string $tel Phone number to validate
	 * @return bool True if phone number is valid, false otherwise
	 */
	public function is_valid_tel( $tel ) {
		$is_valid = true;

		if ( strlen( $tel ) < 8 )
			$is_valid = false;
		elseif ( preg_match( '/[^\d\.\-\+\(\) ]/', $tel ) )
			$is_valid = false;

		return $is_valid;
	}

	/**
	 * Get a file's MIME type
	 *
	 * @since 1.9.0
	 * @param string $file_path Full file path.
	 * @param string $fallback MIME type to use if the file can't be checked with
	 *   finfo, probably the file's 'type' field from the $_FILES array.
	 * @return string
	 */
	protected function _get_file_mime_type( $file_path, $fallback = false ) {
		if ( ! function_exists( 'finfo_open' ) || ! defined( 'FILEINFO_MIME_TYPE' ) )
			return $fallback;

		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $file_path );

		if ( ! $mime )
			$mime = $fallback;

		finfo_close( $finfo );

		return $mime;
	}

	/**
	 * Get an error message for a file field, if there is one.
	 *
	 * @since 1.9.0
	 * @param string $field_name File field name to check.
	 * @return string|bool Error message or false.
	 */
	public function get_file_error( $field_name ) {
		if ( empty( $_FILES[$field_name] ) ) :
			trigger_error( 'There is no file for field ' . $field_name, E_USER_WARNING );
			return false;
		endif;

		$file = $_FILES[$field_name];

		// Upload error code
		$error_code = $file['error'];

		// Optional, empty field
		if ( ! $this->_fields[$field_name]['required'] && ! $file['size'] && $error_code == UPLOAD_ERR_NO_FILE )
			return false;

		// Temp uploaded file
		$tmp_file = $file['tmp_name'];

		if ( $error_code && ! empty( $this->_file_form_messages[$error_code] ) )
			return $this->_file_form_messages[$error_code];
		elseif ( $this->max_file_size && $file['size'] && $file['size'] > $this->max_file_size )
			return $this->_file_form_messages[UPLOAD_ERR_FORM_SIZE];
		elseif ( ! $tmp_file && ! empty( $this->_fields[$field_name]['error_empty'] ) )
			return $this->_fields[$field_name]['error_empty'];

		$file_info = pathinfo( $file['name'] );
		$extension = strtolower( $file_info['extension'] );

		// Using $file['type'] as a fallback; better to validate
		// something than nothing, even if it's sent from the browser and
		// therefore unreliable.
		$mime_type = $this->_get_file_mime_type( $tmp_file, $file['type'] );

		// Check file extension and MIME type against the whitelist.
		if (
			! in_array( $extension, $this->_allowed_extensions ) ||
			! in_array( $mime_type, $this->_allowed_mime_types )
		)
			return $this->_file_form_messages['invalid_file_type'];
		elseif ( $error_code && ! empty( $this->_file_form_messages[$error_code] ) )
			return $this->_file_form_messages[$error_code];

		return false;
	}

	/**
	 * Clean a string to be used as HTML.
	 *
	 * @since 1.0.0
	 * @param string $tag
	 * @return string
	 */
	public function clean_html( $tag ) {
		return preg_replace( '/[^a-z0-9_:]/', '', strtolower( $tag ) );
	}

	/**
	 * Filter text to be used in HTML by converting unsafe characters.
	 *
	 * OWASP recommends encoding slashes, something htmlspecialchars doesn't do.
	 * Also, htmlspecialchars may, according to the manual, encode single quotes
	 * to &apos;, which isn't standard, so quote replacement is done through
	 * str_replace.
	 *
	 * https://www.owasp.org/index.php/XSS_%28Cross_Site_Scripting%29_Prevention_Cheat_Sheet
	 *
	 * @since 1.4.0
	 * @param string $input Text to filter.
	 * @return string
	 */
	public function filter_html( $input ) {
		$input = htmlspecialchars( $input, ENT_NOQUOTES );
		$input = str_replace( array( '/', "'", '"' ), array( '&#x2F;', '&#x27;', '&quot;' ), $input );

		// Testing gives slashed quotes, even though magic quotes should be
		// disabled...
		return stripslashes( $input );
	}

	/**
	 * Filter a name to be used in email headers, to prevent email injection.
	 * Code from Zend_Mail.
	 *
	 * @since 1.2.1
	 * @param string $input The name to filter.
	 * @return string
	 */
	public function filter_name( $input ) {
		$forbidden = array( "\r" => '', "\n" => '', "\t" => '', '"' => "'", '<' => '[', '>' => ']' );

		return trim( strtr( $input, $forbidden ) );
	}

	/**
	 * Filter an email address to be used in email headers, to prevent email
	 * injection. Code from Zend_Mail.
	 *
	 * @since 1.2.1
	 * @param string $input The email address to filter.
	 * @return string
	 */
	public function filter_email( $input ) {
		$forbidden = array( "\r" => '', "\n" => '', "\t" => '', '"' => '', ',' => '', '<' => '', '>' => '' );

		return trim( strtr( $input, $forbidden ) );
	}

	/**
	 * Filter misc. fields to be used in email headers, to prevent email
	 * injection. Code from Zend_Mail.
	 *
	 * @since 1.2.1
	 * @param string $data The name to filter.
	 * @return string
	 */
	public function filter_other( $data ) {
		$forbidden = array( "\r" => '', "\n" => '', "\t" => '' );

		return trim( strtr( $data, $forbidden ) );
	}

	/**
	 * Normalize line break characters used in a string.
	 *
	 * @since 1.5.0
	 * @param string $input Data to filter.
	 * @param string $to_character Replacement to insert. Defaults to line feed.
	 * @return string
	 */
	public function normalize_line_break( $input, $to_character = "\n" ) {
		return preg_replace( '/\R/u', $to_character, $input );
	}

	/**
	 * Get a specified number of words from the beginning of a string.
	 *
	 * Converts all whitespace from the string to a single space, limits the
	 * string to a set number of words and adds dots to signify that the string
	 * is cut off, if specified.
	 *
	 * @since 1.1.0
	 * @param string $string String to get words from.
	 * @param int $words Number of words to get from the string.
	 * @param bool $add_dots Add three dots to the end of the string if the word
	 *   count exceeds the set limit.
	 * @return string
	 */
	public function get_words_from_string( $string, $words = 6, $add_dots = true ) {
		$string = (string) $string;
		$string = preg_replace( '/[\s]+/', ' ', $string );
		$string = explode( ' ', $string );
		$word_count = count( $string );
		$string = implode( ' ', array_splice( $string, 0, $words ) );
		if ( $word_count > $words && $add_dots ) $string .= '...';

		return $string;
	}

	/**
	 * Inserts a new key/value before or after the given key in the array.
	 *
	 * http://eosrei.net/articles/2011/11/php-arrayinsertafter-arrayinsertbefore
	 *
	 * @since 1.0.0
	 * @param string $where Where to insert, 'before' or 'after'.
	 * @param string $key The key to insert before of after.
	 * @param array $array An array to insert into.
	 * @param string $new_key The key to insert.
	 * @param string $new_value The value to insert.
	 * @return array|bool The new array if the key exists, false otherwise.
	 */
	protected function _array_insert( $where, $key, array &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) :
			$new = array();

			if ( 'before' == $where ) :
				foreach ( $array as $k => $value ) :
					if ( $k === $key ) $new[$new_key] = $new_value;
					$new[$k] = $value;
				endforeach;

			elseif ( 'after' == $where ) :
				foreach ( $array as $k => $value ) :
					$new[$k] = $value;
					if ( $k === $key ) $new[$new_key] = $new_value;
				endforeach;

			endif;

			return $new;
		endif;

		return false;
	}

	/**
	 * Return array keys that match the pattern.
	 *
	 * @since 1.0.0
	 * @param string $pattern Regex pattern to search for.
	 * @param array $input The array to search in.
	 * @param int $flags If set to PREG_GREP_INVERT, this function returns the
	 *   elements of the input array that do NOT match the given pattern.
	 * @link http://php.net/manual/en/function.preg-grep.php
	 */
	protected function _preg_grep_keys( $pattern, $input, $flags = 0 ) {
		$keys = preg_grep( $pattern, array_keys( $input ), $flags );
		$vals = array();

		foreach ( $keys as $key )
			$vals[$key] = $input[$key];

		return $vals;
	}

	/**
	 * Opening pre tag and heading for primitive debug mode.
	 *
	 * @since 1.1.0
	 * @param string $heading Text to display above the printed data.
	 */
	protected function _debug_open( $heading = '' ) {
		echo '<pre style="background: #fdfdfd; color: #333; font-size: 12px; width: 700px; padding: 10px; border: 1px solid #999;">';
		echo '<span style="display: block; width: 100%; padding: 10px; margin: -10px 0 5px -10px; border-bottom: 1px solid #999; background: #eaeff2;">' . $heading . '</span>';
	}

	/**
	 * Closing pre tag for primitive debug mode.
	 *
	 * @since 1.1.0
	 */
	protected function _debug_close() {
		echo '</pre>';
	}
}