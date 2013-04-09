<?php
/**
 * Contact class definition.
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Handles contact forms; building, validating and sending.
 *
 * Not particularly pretty or flexible, but it gets the job done.
 *
 * $to_address and $message_template or $message_format are required properties
 * and _send() will throw errors if they are empty.
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
 * @package Lucid
 * @subpackage Toolbox
 * @version 1.4.1
 */
class Lucid_Contact {

	/*
	|------------------------------------------------------------------------
	| Table of contents
	|------------------------------------------------------------------------
	|
	| Properties
	| ------------------------------
	| $from_name
	| $from_address
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
	| $_fields
	| $field_wrap
	| $_ignore_field_attrs
	| $_html_count
	| $form_action
	| $form_method
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
	| [=Validation, sending and form rendering]
	| _validate
	| _get_subject
	| _get_message_from_format
	| _get_message_conditionals
	| _get_message_tags
	| _filter_conditional_tag
	| _filter_tag
	| _get_message
	| _get_html_message
	| _get_headers
	| _get_attachments
	| _get_unique_file_path
	| _send
	| _has_required_send_data
	| _clear_send
	| assemble_form
	|
	| [=Misc. functions and utilities]
	| render_form
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
	| get_words_from_string
	| _array_insert
	| _preg_grep_keys
	| _debug_open
	| _debug_close
	*/

	/**
	 * Sender's name. Set to a field name like 'name' to use the data from that
	 * field.
	 *
	 * @var string
	 */
	public $from_name = '';

	/**
	 * Sender's email address. Set to a field name like 'email' to use the data
	 * from that field.
	 *
	 * @var string
	 */
	public $from_address = '';

	/**
	 * Recipient's email address.
	 *
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
	 * <code>
	 * $instance->message_format = array(
	 * 	'name',
	 * 	"\n ----- \n",
	 * 	'message'
	 * );
	 * </code>
	 *
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
	 * <code>
	 * $instance->message_template = '
	 * Message:
	 * Name: {{name}}
	 * {{#if}}Not displayed if phone is empty {{phone}}.{{/if}} But this is.
	 * Email: {{email}}
	 *
	 * {{#if_block}}
	 * This entire block only shows if address is not empty
	 * Address here: {{address}}
	 * Use if_block for whole and/or multiple lines, since an extra line break
	 * needs to be removed.
	 * {{/if_block}}
	 * ';
	 * </code>
	 *
	 * @var string
	 */
	public $message_template = '';

	/**
	 * Template tags contained in the message.
	 *
	 * @var array
	 */
	protected $_message_tags = array();

	/**
	 * Conditional tags contained in the message.
	 *
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
	 * <code>
	 * $instance->custom_template_tags = array(
	 *    'tag_name' => 'tag value',
	 *    'price_total' => 99 * (int) $_POST['number_products']
	 * );
	 * </code>
	 *
	 * These can then be used in the template like any other: {{price_total}}.
	 *
	 * @var array
	 */
	public $custom_template_tags = array();

	/**
	 * Path to HTML email template.
	 *
	 * Full include path to an HTML file to use as email template. The file can
	 * contain the same template tags as $message_template.
	 *
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
	 * @var string
	 */
	public $subject_text = '';

	/**
	 * The form fields.
	 *
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
	 * @var string
	 */
	public $field_wrap = 'div';

	/**
	 * Default ignored custom attributes for fields.
	 *
	 * @var array
	 * @see assemble_form()
	 */
	protected $_ignore_field_attrs = array( 'value', 'name', 'id', 'type', 'rows', 'cols' );

	/**
	 * Number of add_to_field_list() entries.
	 *
	 * To get a nicer key for miscellaneous HTML added via add_to_field_list()
	 * (html-# instead of a number), the key is kept unique with this counter.
	 *
	 * @var int
	 * @see add_to_field_list()
	 */
	protected $_html_count = 0;

	/**
	 * The form action. Default set in constructor.
	 *
	 * @var string
	 */
	public $form_action = '';

	/**
	 * The form method.
	 *
	 * @var string
	 */
	public $form_method = 'post';

	/**
	 * If email attachments should be handled. Is set if there is a file input
	 * added.
	 *
	 * @var bool
	 */
	public $handle_attachments = false;

	/**
	 * Remove uploaded attachment files from the server when they've been sent.
	 *
	 * @var bool
	 */
	public $delete_sent_files = true;

	/**
	 * Allowed file extensions for mail attachments.
	 *
	 * @var array
	 * @see _get_attachments()
	 * @see set_allowed_files()
	 * @link http://www.fileinfo.com/filetypes/common
	 */
	protected $_allowed_extensions = array();

	/**
	 * Allowed MIME types for mail attachments.
	 *
	 * @var array
	 * @see _get_attachments()
	 * @see set_allowed_files()
	 */
	protected $_allowed_mime_types = array();

	/**
	 * Paths to sent attachments.
	 *
	 * @var array
	 */
	protected $_attachments = array();

	/**
	 * Maximum file size for attachments, in bytes.
	 *
	 * - X * 1024 for kb to bytes.
	 * - X * 1024 * 1024 for MB to bytes.
	 *
	 * @var int
	 */
	public $max_file_size = 0;

	/**
	 * Additional form attributes. Format attr => value.
	 *
	 * @var array
	 * @see assemble_form()
	 */
	public $form_attributes = array();

	/**
	 * Referer to check when submitting, for a teeny-weeny bit of spoofable extra
	 * CSRF security... Pointless? Maybe. Default set in constructor.
	 *
	 * @var string
	 */
	public $form_location = '';

	/**
	 * Stores user feedback messages.
	 *
	 * @var array
	 * @see set_form_messages()
	 */
	protected $_form_messages = array();

	/**
	 * Stores user feedback messages for file uploads.
	 *
	 * @var array
	 * @see set_file_form_messages()
	 */
	protected $_file_form_messages = array();

	/**
	 * Current form error or success message.
	 *
	 * @var string
	 * @see _validate()
	 */
	protected $_form_status = '';

	/**
	 * Handle the $_POST data and sending.
	 *
	 * @var bool
	 */
	public $handle_post = true;

	/**
	 * If email validation should include DNS lookup.
	 *
	 * @var bool
	 * @see is_valid_email()
	 */
	public $do_email_dns_check = true;

	/**
	 * Primitive debug mode.
	 *
	 * Prints the email content instead of sending it, together with $_POST and
	 * $_fields contents. Debug parts of the class is preceded by a --Debug--
	 * comment. Quicker toggle through the constructor.
	 *
	 * @var bool
	 */
	public $debug_mode = false;

	/**
	 * Constructor. Set some default properties, data and messages.
	 *
	 * @param bool $debug_mode Whether to display form and POST contents.
	 */
	public function __construct( $debug_mode = false ) {
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
	 * These are the messages that appear above the form on submission.
	 *
	 * - 'success' If the message was successfully sent.
	 * - 'error' Some problem with the information provided by the user, like
	 *   missing fields and validation errors.
	 * - 'honeypot' If the only problem was a filled-in honeypot field.
	 * - 'not_sent' If there was a problem during the sending process. Not
	 *   something the user can do anything about.
	 *
	 * @param array $messages Associative array of messages.
	 */
	public function set_form_messages( array $messages = array() ) {
		$defaults = array(
			'success'  => __( 'Thank you for your message!', 'lucid-toolbox' ),
			'error'    => __( 'There seems to be a problem with your information.', 'lucid-toolbox' ),
			'honeypot' => __( 'To send the message, the last field must be empty. Maybe it was filled by mistake, delete the text and try again.', 'lucid-toolbox' ),
			'not_sent' => __( 'Due to a technical issue, the message could not be sent, we apologize.', 'lucid-toolbox' )
		);

		$this->_form_messages = array_merge( $defaults, $messages );
	}

	/**
	 * Set form messages for file uploads.
	 *
	 * @param array $messages Associative array of messages.
	 * @link http://www.php.net/manual/en/features.file-upload.errors.php
	 */
	public function set_file_form_messages( array $messages = array() ) {
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

		$this->_file_form_messages = array_merge( $defaults, $messages );
	}

	/**
	 * Set allowed extensions and MIME types. Sets default if there are none.
	 *
	 * @param array $extensions Array of file extensions.
	 * @param array $mime_types Array of file MIME types.
	 * @link http://www.webmaster-toolkit.com/mime-types.shtml MIME types list.
	 * @link http://filext.com/faq/office_mime_types.php Office MIME types list.
	 */
	public function set_allowed_files( array $extensions = array(), array $mime_types = array() ) {

		// Extensions
		$default_extensions = array(
			'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'
		);

		if ( empty( $extensions ) )
			$extensions = $default_extensions;

		$this->_allowed_extensions = $extensions;

		// MIME types
		$default_mime_types = array(
			'image/jpeg', // .jpg/.jpeg
			'image/pjpeg', // .jpg/.jpeg
			'image/png', // .png
			'image/gif', // .gif
			'application/pdf', // .pdf
			'application/msword', // .doc
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // .docx
		);

		if ( empty( $mime_types ) )
			$mime_types = $default_mime_types;

		$this->_allowed_mime_types = $mime_types;
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
	 * - 'value' (string) Value attribute, only used for radio buttons.
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
	 *   passed as a regex to preg_match(). If a regex is passed, it should
	 *   MATCH INVALID characters, i.e. '/[\d]/' to NOT allow digits.
	 * - 'error_empty' (string) Error message for when a required field is empty
	 *   on submission.
	 * - 'error_invalid' (string) Error message for when a field with validation
	 *   doesn't pass it.
	 *
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
	 * Field label.
	 *
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
	 * @param string $description_text Text to display.
	 * @return array Field part with the 'description' key.
	 */
	protected function _get_field_description( $description_text ) {
		return array( 'description' => "<span class=\"description\">{$description_text}</span>" );
	}

	/**
	 * Assemble a textarea as an array of parts.
	 *
	 * @see add_field()
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_textarea( $name, $args ) {

		// Start field tag
		$field_part = "<textarea name=\"{$name}\" id=\"{$name}\" rows=\"{$args['rows']}\" cols=\"{$args['cols']}\"";

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field_part .= ">";

		$field['tag_open'] = $field_part;

		// POST data as separate item, for easier cleaning.
		// Textareas keep the text between the tags.
		if ( isset( $_POST[$name] ) ) $field['post'] = esc_textarea( $_POST[$name] );

		// Close field tag
		$field_part = '</textarea>';

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Assemble a select box as an array of parts.
	 *
	 * @see add_field()
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_select( $name, $args ) {

		// Start field tag
		$field_part = "<select name=\"{$name}\" id=\"{$name}\"";

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
			if ( isset( $_POST[$name] ) && $_POST[$name] == $val )
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
		if ( isset( $_POST[$name] ) && $args['value'] )
			$field['post'] = ( $_POST[$name] == $args['value'] ) ? ' checked="checked"' : '';

		// Close field tag
		$field_part = ">";

		$field['tag_close'] = $field_part;

		return $field;
	}

	/**
	 * Assemble a hidden input as an array of parts.
	 *
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
	 * @see add_field()
	 * @param string $type The field type, i.e. text or email.
	 * @param string $name Name and id attributes.
	 * @param array $args Arguments passed to add_field().
	 * @return array The input part of the field block.
	 */
	protected function _get_input_field( $type, $name, $args ) {

		// Start field tag
		$field_part = "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\"";

		if ( 'checkbox' == $type )
			$field_part .= ' value="1"';

		// Additional attributes
		$field_part .= $this->_get_attributes_string( $args['field_attributes'] );

		$field['tag_open'] = $field_part;

		// POST data as separate item, for easier cleaning.
		// Checkboxes keep checked state, other inputs have the value attribute.
		if ( isset( $_POST[$name] ) ) :
			if ( 'checkbox' == $type ) :
				$field['post'] = ( 1 === (int) $_POST[$name] ) ? ' checked="checked"' : '';
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
	      =Validation, sending and form rendering
	\*-------------------------------------------------------------------------*/


	/**
	 * Validate POST data and set error messages.
	 *
	 * TODO: Break apart this monster of a method.
	 *
	 * @return bool True if POST data is valid, false if there were errors.
	 */
	protected function _validate() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD']
		  && $this->form_location == $_SERVER['HTTP_REFERER']
		  && ! empty( $_POST ) ) :

			$all_is_well = true;

			// Honeypot checks
			$honeypot_error = false;
			$error_count = 0;

			// Check every field
			foreach ( $this->_fields as $name => $data ) :
				if ( isset( $this->_fields[$name]['type'] )
				  && 'submit' == $this->_fields[$name]['type'] ) continue;

				$error_msg = '';
				$validation = isset( $this->_fields[$name]['validation'] )
					? $this->_fields[$name]['validation']
					: '';

				// Radio button ID clash handling, remove added -# string
				$id = $name;
				if ( 'radio' == $data['type'] )
					$id = preg_replace( '/-\d+$/', '', $name );

				// If field is required and empty
				// Some ugly stuff here, but file and other inputs needs different
				// checks for empty, and all conditions must be checked at the same
				// level for elseif to kick in on non-empty fields.
				if ( (
					   'file' != $data['type']
					&& empty( $_POST[$id] )
					&& ! empty( $this->_fields[$name]['required'] )
				) || (
					   'file' == $data['type']
					&& 4 == $_FILES[$name]['error']
					&& ! empty( $this->_fields[$name]['required'] )
				) ) :
					$error_count++;

					// Add error message for 'empty' if it exists
					if ( ! empty( $this->_fields[$name]['error_empty'] ) ) :
						$error_msg = $this->_fields[$name]['error_empty'];
					endif;

				// If field has some kind of validation defined and a message for
				// invalid data
				elseif ( ! empty( $validation ) ) :
					$is_valid = true;

					switch ( $validation ) :
						case 'email' :
							$is_valid = $this->is_valid_email( $_POST[$id] );
							break;

						case 'tel' :
							$is_valid = $this->is_valid_tel( $_POST[$id] );
							break;

						case 'honeypot' :
							$is_valid = ( '' == $_POST[$id] );
							break;

						default :
							// Non-reserved strings assumed to be regex. If something
							// matches, is_valid should be false, so preg_match is
							// reversed
							$is_valid = ! preg_match( $validation, $_POST[$id] );
							break;
					endswitch;

					// Invalid, add relevant data
					if ( ! $is_valid ) :
						$error_count++;

						// Add error message for 'invalid' if it exists
						if ( ! empty( $this->_fields[$name]['error_invalid'] ) )
							$error_msg = $this->_fields[$name]['error_invalid'];

						// See large comment about honeypot below
						if ( 'honeypot' == $validation )
							$honeypot_error = true;
					endif;
				endif;

				// Add error indications
				if ( $error_msg ) :
					$all_is_well = false;

					// Add error span before closing wrap tag if it exists in $fields
					if ( isset( $this->_fields[$name]['close'] ) ) :
						$this->_fields[$name] = $this->_array_insert(
							'after',
							'tag_close',
							$this->_fields[$name],
							'error',
							'<span class="error">' . $error_msg . '</span>'
						);
					// Otherwise just add it last
					else :
						$this->_fields[$name]['error'] = '<span class="error">' . $error_msg . '</span>';
					endif;

					// Update form tag class with 'error'
					$tag = $this->_fields[$name]['tag_open'];

					// If there is a class attribute, add to it
					if ( strpos( $tag, 'class="' ) ) :
						$this->_fields[$name]['tag_open'] = str_replace( 'class="', 'class="error ', $tag );
					// Otherwise, add a class attribute
					// Regex: |<tag|> => |<tag class="error"|>
					else :
						$this->_fields[$name]['tag_open'] = preg_replace( '/<[\w\-]+(?=\s|>)/', '$0 class="error"', $tag );
					endif;
				endif;
			endforeach; // Field loop

			// Set success/error message
			if ( $all_is_well ) :
				$this->_form_status = '<p class="success">' . $this->_form_messages['success'] . '</p>';

			// If the honeypot is the only problem, an extra CSS class is available
			// for potentially showing the honeypot field if it's hidden (a general
			// sibling combinator can be used: [.error-honeypot ~ form <field>],
			// IE7+). This may be needed if some sort of auto form filler is used
			// by a human.
			elseif ( 1 === $error_count && $honeypot_error ) :
				$this->_form_status = '<p class="error error-honeypot">' . $this->_form_messages['honeypot'] . '</p>';

			else :
				$this->_form_status = '<p class="error">' . $this->_form_messages['error'] . '</p>';
			endif;

			return $all_is_well;

		// Not POST or bad referer
		else :
			return false;
		endif;
	}

	/**
	 * Put together the subject line for the email.
	 *
	 * If the subject or the label is set to a field name, a snippet from the
	 * POST data of that field is used as the string. Otherwise the string is
	 * added as is.
	 *
	 * @return string The assembled subject line.
	 */
	protected function _get_subject() {
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
	 * @see $message_format For how the message is assembled.
	 * @return array The assembled message.
	 */
	protected function _get_message_from_format() {
		$message = array();

		foreach ( $this->message_format as $part ) :

			// If string is a field ID
			if ( isset( $_POST[$part] ) ) :

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
	 * @param string $message Text to search in, defaults to the message_template
	 *   property.
	 * @return array Found conditional blocks.
	 */
	protected function _get_message_conditionals( $message = '' ) {

		// Only process once
		if ( ! is_array( $this->_message_conditionals ) ) :
			$message = ( ! empty( $message ) ) ? $message : $this->message_template;

			// Get conditionals: {{if}}s and {{if_block}}s
			// Compact version:
			// '/\{\{#(if|if_block)\}\}(?:(?!\{\{(\/|#)(if|if_block)\}\}).)+\{\{\/(if|if_block)\}\}/ms'
			preg_match_all( '/
				# Opening if or if_block between {{ }}
				\{\{
					\#(if|if_block)
				\}\}

				# Non-capturing greedy group
				(?:
					# Negative lookahead: match anything but
					# opening or closing if and if_block
					(?!
						\{\{
							(\/|\#)(if|if_block)
						\}\}
					)
					. # Dot here
				)+

				# Closing if or if_block between {{ }}
				\{\{
					\/(if|if_block)
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
	 * @param string $message Text to search in, defaults to the message_template
	 *   property.
	 * @return array Found template tags.
	 */
	protected function _get_message_tags( $message = '' ) {

		// Only process once
		if ( ! is_array( $this->_message_tags ) ) :
			$message = ( ! empty( $message ) ) ? $message : $this->message_template;

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
	 * @param string $tag Mustache style template tag, i.e. {{name}}
	 * @param string $text Text to search in.
	 * @return string Filtered text.
	 */
	protected function _filter_conditional_tag( $tag, $text ) {
		$field = trim( $tag, '{}' );

		// Tag must be a field ID or custom tag to be replaced
		if ( ! array_key_exists( $field, $this->_fields )
		  && ! array_key_exists( $field, $this->custom_template_tags ) )
			return $text;

		$conditionals = $this->_get_message_conditionals( $text );
		foreach ( $conditionals as $cond ) :

			// If tag exists inside a conditional
			if ( false !== strpos( $cond, $tag ) ) :
				$cond_content = str_replace( array( '{{#if}}', '{{/if}}', '{{#if_block}}', '{{/if_block}}' ), '', $cond );

				// Value POSTed and not empty: replace checkbox with 'yes',
				// others with POST value
				if ( array_key_exists( $field, $_POST ) && '' != $_POST[$field] ) :
					$post_value = ( ! empty( $this->html_template ) )
						? nl2br( $this->filter_html( $_POST[$field] ) )
						: $_POST[$field];

					$replace = ( $this->is_checkbox( $field ) )
						? str_replace( $tag, _x( 'yes', 'checkbox yes', 'lucid-toolbox' ), $cond_content )
						: str_replace( $tag, $post_value, $cond_content );

				elseif ( array_key_exists( $field, $this->custom_template_tags ) && ! empty( $this->custom_template_tags[$field] ) ) :
					$replace = str_replace( $tag, $this->custom_template_tags[$field], $cond_content );

				// Otherwise replace checkbox with 'no' and remove others
				else :
					$replace = ( $this->is_checkbox( $field ) )
						? str_replace( $tag, _x( 'no', 'checkbox no', 'lucid-toolbox' ), $cond_content )
						: '';
				endif;

				// If removing a block, also remove following line break
				$search = ( '' == $replace && false !== strpos( $cond, 'if_block' ) )
					? $cond .= "\n"
					: $cond;

				$text = str_replace( $search, $replace, $text );
			endif;
		endforeach;

		return $text;
	}

	/**
	 * Filter template tags in message text.
	 *
	 * Searches the text for the passed tag and replaces it with POST content.
	 *
	 * @param string $tag Mustache style template tag, i.e. {{name}}
	 * @param string $text Text to search in.
	 * @return string Filtered text.
	 */
	protected function _filter_tag( $tag, $text ) {
		$field = trim( $tag, '{}' );

		// Tag must be a field ID or custom tag to be replaced
		if ( ! array_key_exists( $field, $this->_fields )
		  && ! array_key_exists( $field, $this->custom_template_tags ) )
			return $text;

		// Field if POSTed
		if ( array_key_exists( $field, $_POST ) ) :

			// Checkbox is set as yes or no
			if ( $this->is_checkbox( $field ) ) :
				$text = str_replace( $tag, _x( 'yes', 'checkbox yes', 'lucid-toolbox' ), $text );

			// Regular fields just have their data added
			else :
				$post_value = ( ! empty( $this->html_template ) )
					? nl2br( $this->filter_html( $_POST[$field] ) )
					: $_POST[$field];

				$text = str_replace( $tag, $post_value, $text );
			endif;

		elseif ( array_key_exists( $field, $this->custom_template_tags ) && ! empty( $this->custom_template_tags[$field] ) ) :
			$text = str_replace( $tag, $this->custom_template_tags[$field], $text );

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
	 * @see $message_template For how templates work.
	 * @return string The message.
	 */
	protected function _get_message() {
		if ( empty( $this->message_template ) ) return '';

		// Hopefully normalize line breaks
		$message = str_replace( array( "\r\n", "\r" ), "\n", $this->message_template );
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
	 * @return string The complete message.
	 */
	protected function _get_html_message() {

		if ( file_exists( $this->html_template ) ) :
			ob_start();
			include $this->html_template;
		else :
			return '';
		endif;

		// Hopefully normalize line breaks
		$message = str_replace( array( "\r\n", "\r" ), "\n", ob_get_clean() );
		$tags = $this->_get_message_tags( $message );

		// Check every found template tag
		foreach ( $tags as $tag ) :
			$message = $this->_filter_conditional_tag( $tag, $message );
			$message = $this->_filter_tag( $tag, $message );
		endforeach;

		//echo '<pre>' . htmlspecialchars( str_replace( "\t", '  ', $message ) ) . '</pre>';

		// Remove HTML comments
		$message = preg_replace( '/\<\!--(?:(?!--\>).)+--\>/ms', '', $message );

		// Remove CSS comments
		$message = preg_replace( '/\/\*(?:(?!\*\/).)+\*\//ms', '', $message );

		// Collapse multiple line breaks
		$message = preg_replace( '/\n+/', "\n", $message );

		// Remove tabs
		$message = str_replace( "\t", '', $message );

		return trim( $message );
	}

	/**
	 * Put together from and reply-to headers, if data is available.
	 *
	 * @return array wp_mail accepts headers as an array.
	 */
	protected function _get_headers() {
		$headers = array();

		if ( $this->from_name && $this->from_address ) :
			$name = $this->filter_name( $_POST[$this->from_name] );
			$address = $this->filter_email( $_POST[$this->from_address] );

			$headers[] = "From: {$name} <{$address}>";
			$headers[] = "Reply-To: {$name} <{$address}>";
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
	 * TODO: Change $_FILES[$field]['type'] to something more secure, like finfo.
	 *
	 * @return array|bool wp_mail checks for empty array, so return that if
	 *   there are no attachments. False if there is a problem with a file.
	 */
	protected function _get_attachments() {
		$attachments = array();

		// Bail early if applicable
		if ( ! $this->handle_attachments ) return $attachments;

		// Check every file field and add file paths to the $_attachments array.
		foreach ( $this->_fields as $field => $data ) :
			if ( 'file' != $data['type'] ) continue;

			$tmp_file = $_FILES[$field]['tmp_name']; // Temp uploaded file
			$file = pathinfo( $_FILES[$field]['name'] ); // File info
			$extension = $file['extension']; // File extension
			$error_code = $_FILES[$field]['error']; // Upload error code
			$uploads = wp_upload_dir(); // WordPress uploads directory

			// Get MIME type. The type in $_FILES[$field]['type'] is sent
			// from the browser, which makes it kind of unreliable...
			$mime_type = $_FILES[$field]['type'];

			// The file name. Keep extra dots away, so image.php.jpg =>
			// image-php.jpg.
			$target_name = str_replace( '.', '-', $file['filename'] );
			$target_file = $target_name . '.' . $extension;

			// Where to move the uploaded file. Remove trailing slash.
			$target_dir = rtrim( $uploads['basedir'], '/' );

			// Check file extension and MIME type, add error message if
			// either of them is not on the whitelist.
			if ( ! in_array( $extension, $this->_allowed_extensions )
			  || ! in_array( $mime_type, $this->_allowed_mime_types ) ) :
				$this->_form_status = '<p class="error">' . $this->_file_form_messages['invalid_file_type'] . '</p>';
				$attachments = false;
				break;

			// Files okay, proceed with moving them
			else :

				// Complete target path, with a unique file name
				$target_path = $this->_get_unique_file_path( $target_dir, $target_name, $extension );

				// move_uploaded_file checks to ensure that the file was
				// uploaded via HTTP POST.
				if ( move_uploaded_file( $tmp_file, $target_path ) ) :

					$attachments[] = $target_path;

				// Upload error message
				else :
					$this->_form_status = '<p class="error">' . $this->_file_form_messages[$error_code] . '</p>';
					$attachments = false;
					break;
				endif;

			endif;

		endforeach;

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
	 * Send with wp_mail().
	 *
	 * @link http://codex.wordpress.org/Function_Reference/wp_mail
	 * @return bool True if wp_mail was successful, false otherwise.
	 */
	protected function _send() {
		$data_valid = $this->_validate();

		// Check posting and data validation
		if ( 'POST' == $_SERVER['REQUEST_METHOD']
		  && $this->form_location == $_SERVER['HTTP_REFERER']
		  && ! empty( $_POST )
		  && $data_valid
		  && $this->_has_required_send_data() ) :

			// Get form data
			$to = $this->to_address;
			$subject = $this->_get_subject();
			$headers = $this->_get_headers();
			$attachments = $this->_get_attachments();

			// Keep old message_format for compatibility
			if ( ! empty( $this->html_template ) )
				$message = $this->_get_html_message();
			elseif ( ! empty( $this->message_template ) )
				$message = $this->_get_message();
			else
				$message = implode( $this->message_format_separator, $this->_get_message_from_format() );

			// Wrap lines for plain text messages.
			// Internet Message Format RFC says max line length of 78
			// (http://www.rfc-editor.org/rfc/rfc5322.txt, [Page 6])
			// Format of Internet Message Bodies RFC says 76, so let's go with 75.
			// (http://www.rfc-editor.org/rfc/rfc2045.txt, [Page 19])
			// RFC 5322 also states lines should be delimited by a carriage return
			// character, followed immediately by a line feed character. [Page 5]
			if ( empty( $this->html_template ) )
				$message = wordwrap( str_replace( "\n", "\r\n", $message ), 75, "\r\n" );

			// --Debug--
			// Print headers and message
			if ( $this->debug_mode ) :
				$this->_debug_open( 'Message' );
				echo '<i>To</i>: ' . $to . '<br><br>';
				echo '<i>Subject</i>: ' . $subject . '<br><br>';
				echo '<i>Headers</i>:<br>' . htmlspecialchars( implode( "\n", $headers ) ) . '<br><br>';
				echo '<i>Message</i>:<br>' . htmlspecialchars( $message ) . '<br><br>';
				$this->_debug_close();
			endif;

			// Send with wp_mail
			// _get_attachments() returns false if there is a problem, so check
			// if $attachments is an array.
			if ( ! empty( $message ) && ! $this->debug_mode && is_array( $attachments ) ) :
				$sent = wp_mail( $to, $subject, $message, $headers, $attachments );

				// Clear form if sent
				if ( $sent ) :
					$this->_clear_send();
					return true;

				// Not sent
				else :
					$this->_form_status = '<p class="error">' . $this->_form_messages['not_sent'] . '</p>';
					return false;
				endif;

			else :
				$this->_form_status = '<p class="error">' . $this->_form_messages['not_sent'] . '</p>';
				return false;
			endif; // ! debug_mode

		endif; // POST and data valid
	}

	/**
	 * Check if there is a recipient address and a message format.
	 *
	 * @return boolean
	 */
	protected function _has_required_send_data() {
		$has_data = true;

		// Show some errors instead of failing silently
		if ( empty( $this->to_address ) ) :
			trigger_error( 'No to_address specified', E_USER_WARNING );
			$has_data = false;
		endif;

		if ( empty( $this->message_template ) && empty( $this->message_format ) ) :
			trigger_error( 'No template for the message specified, see message_template or message_format', E_USER_WARNING );
			$has_data = false;
		endif;

		return $has_data;
	}

	/**
	 * Clear the form after message is sent.
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
		if ( $this->delete_sent_files ) :
			foreach ( (array) $this->_attachments as $path ) :
				if ( is_file( $path ) ) :
					// Prevent error if file isn't removed successfully
					@unlink( $path );
				endif;
			endforeach;
		endif;
	}

	/**
	 * Assemble the $_fields array to a complete form and save it to the 'form'
	 * property.
	 *
	 * @return string $form The finished form.
	 */
	public function assemble_form() {
		$form = '';

		// Status message <p>
		if ( $this->_form_status )
			$form .= $this->_form_status;

		$form .= "<form action=\"{$this->form_action}\" method=\"{$this->form_method}\"";

		// Add required enctype if doing attachments
		if ( $this->handle_attachments )
			$form .= ' enctype="multipart/form-data"';

		// Additional attributes, ignore 'action', 'method' and 'novalidate'
		$form .= $this->_get_attributes_string(
			$this->form_attributes,
			array( 'action', 'method', 'novalidate' )
		);

		// Disable built-in browser validation. It's inconsistent both in
		// function and appearance
		$form .= " novalidate>\n";

		// Included just to possibly save processing if the file is too big, it's
		// not secure or reliable by any means.
		if ( $this->max_file_size )
			$form .= "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$this->max_file_size}\">\n";

		// Loop through the $_fields array and only add stuff between opening and
		// closing tags, so stuff like validation data is skipped. Others apart
		// from 'open' counts as start due to optional field wraps, custom html
		// keys etc.
		foreach ( $this->_fields as $field ) :
			$include_parts = false;

			foreach ( $field as $part => $val ) :
				if ( $part == 'open'
				  || $part == 'label'
				  || $part == 'tag_open'
				  || $part == 'string' ) $include_parts = true;

				if ( $include_parts ) :
					$form .= $val;
					$form .= ( $part != 'post' ) ? "\n" : '';
				endif;

				if ( $part == 'close' ) $include_parts = false;
			endforeach;
		endforeach;

		$form .= "</form>";

		return $form;
	}

	/**
	 * Render the assembled form.
	 *
	 * This is the function to use after adding all the fields. In addition to
	 * rendering, it calls _send(), which in turn calls _validate().
	 */
	public function render_form() {
		if ( $this->handle_post )
			$this->_send();

		$form = $this->assemble_form();

		// --Debug--
		// Print the $_fields array
		if ( $this->debug_mode ) :
			$this->_debug_open( '$this->_fields' );
			array_walk_recursive( $this->_fields, array( 'self', '_debug_filter' ) );
			print_r( $this->_fields );
			$this->_debug_close();
		endif;

		echo $form;
	}










	/*-------------------------------------------------------------------------*\
	      =Misc. functions and utilities
	\*-------------------------------------------------------------------------*/


	/**
	 * For debug purposes, encodes all fields through array_walk_recursive() so
	 * the markup is displayed.
	 *
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

			$attr = $this->clean_html( $attr );
			$val = esc_attr( $val );

			$attributes_string .= " {$attr}=\"{$val}\"";
		endforeach;

		return $attributes_string;
	}

	/**
	 * Check if a field is a checkbox.
	 *
	 * @param string $field Field ID.
	 * @return boolean
	 */
	public function is_checkbox( $field ) {
		return array_key_exists( $field, $this->_fields ) && 'checkbox' == $this->_fields[$field]['type'];
	}

	/**
	 * Validate an email address.
	 *
	 * Original code by Douglas Lovell.
	 *
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
	 * Clean a string to be used as HTML.
	 *
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
	 * @param string $data The name to filter.
	 * @return string
	 */
	public function filter_other( $data ) {
		$forbidden = array( "\r" => '', "\n" => '', "\t" => '' );

		return trim( strtr( $data, $forbidden ) );
	}

	/**
	 * Get a specified number of words from the beginning of a string.
	 *
	 * Converts all whitespace from the string to a single space, limits the
	 * string to a set number of words and adds dots to signify that the string
	 * is cut off, if specified.
	 *
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
	 * @param string $heading Text to display above the printed data.
	 */
	protected function _debug_open( $heading = '' ) {
		echo '<pre style="background: #fdfdfd; color: #333; font-size: 12px; width: 700px; padding: 10px; border: 1px solid #999;">';
		echo '<span style="display: block; width: 100%; padding: 10px; margin: -10px 0 5px -10px; border-bottom: 1px solid #999; background: #eaeff2;">' . $heading . '</span>';
	}

	/**
	 * Closing pre tag for primitive debug mode.
	 */
	protected function _debug_close() {
		echo '</pre>';
	}
}