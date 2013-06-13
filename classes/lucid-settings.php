<?php
/**
 * Settings class definition.
 *
 * @package Lucid
 * @subpackage Toolbox
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) ) die( 'Nope' );

/**
 * Simple settings page generation, using the Settings API.
 *
 * Create a new settings object with a unique ID and a heading:
 *
 * <code>
 * $example = new Lucid_Settings( 'example_settings_id' );
 * $example->page_heading = __( 'Settings Lucid', 'TEXTDOMAIN' );
 * </code>
 *
 * Then add each part with:
 *
 * - $example->submenu( [...] );
 * - $example->section( [...] );
 * - $example->field( [...] );
 *
 * Finally initialize the page with $example->init();
 *
 * To use a tabbed settings page, simply add a 'tabs' array to submenu(), see
 * function description.
 *
 * @package Lucid
 * @subpackage Toolbox
 * @version 1.3.5
 */
class Lucid_Settings {

	/*
	|------------------------------------------------------------------------
	| Table of contents
	|------------------------------------------------------------------------
	|
	| Properties
	| ------------------------------
	| $id
	| $page_heading
	| $_screen_id
	| $capability
	| $pass_settings_errors_id
	| $_submenu
	| $_tabs
	| $_tab_sections
	| $_sections
	| $_fields
	| $_html
	| $_checkboxes
	| $_checklists
	|
	| Methods, section separated
	| ------------------------------
	| __construct
	| submenu
	| section
	| field
	| html
	| init
	| get_screen_id
	|
	| [=Settings]
	| _load_settings
	| _add_sections
	| _add_fields
	| _display_page
	| _display_section
	| _settings_tabs
	| _register_setting
	| _add_defaults
	|
	| [=Fields]
	| _display_field
	| _add_text
	| _add_textarea
	| _add_checkbox
	| _add_checklist
	| _add_select
	| _add_radios
	| _add_button_field
	| _add_description
	| _add_html
	|
	| [=Sanitation and validation]
	| sanitize_options
	| _validate
	| _validate_custom
	| _sanitize
	| _sanitize_custom
	| _error_highlighting
	*/

	/**
	 * The unique settings ID.
	 *
	 * This is the key used for the settings in the database and what is used
	 * with get_option().
	 *
	 * NOTE: Only relevant when tabs are not used. In the case of tabs, every
	 * tab ID is a separate settings collection.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $id = '';

	/**
	 * The heading for the settings page. Doesn't show when using tabs.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $page_heading = '';

	/**
	 * The screen ID of the settings page.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $_screen_id = '';

	/**
	 * Capability required to edit the settings.
	 *
	 * @var string
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Roles_and_Capabilities
	 */
	public $capability = 'manage_options';

	/**
	 * Pass setting ID to settings_errors().
	 *
	 * If true, the current page ID will be passed to settings_errors. This is
	 * sometimes needed to avoid multiple update messages, other times it
	 * causes update messages to not be displayed at all. I have yet to find
	 * the reason for the issue.
	 *
	 * @var bool
	 * @since 1.3.2
	 * @see _display_page()
	 */
	public $pass_settings_errors_id = true;

	/**
	 * The submenu item for the settings page.
	 *
	 * @var array
	 * @since 1.0.0
	 * @see submenu()
	 */
	protected $_submenu = array();

	/**
	 * Settings page tabs. Stored as 'unique_id' => 'Tab label'.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_tabs = array();

	/**
	 * Which sections belong to which tabs.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_tab_sections = array();

	/**
	 * Settings sections.
	 *
	 * @var array
	 * @since 1.0.0
	 * @see section()
	 */
	protected $_sections = array();

	/**
	 * Settings fields.
	 *
	 * @var array
	 * @since 1.0.0
	 * @see field()
	 */
	protected $_fields = array();

	/**
	 * HTML blocks.
	 *
	 * @var array
	 * @since 1.2.0
	 * @see html()
	 */
	protected $_html = array();

	/**
	 * All checkboxes.
	 *
	 * Used to check if unchecking is required, since unchecked checkboxes don't
	 * get POSTed.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $_checkboxes = array();

	/**
	 * Added checklists.
	 *
	 * A checklist need every option as a separate entry in $fields, since they
	 * are separate options. This means looping $fields in _add_fields() will
	 * result in multiple stops on the same collection of checkboxes, which in
	 * turn will cause duplicate output. The 'main' ID of the checklist (first
	 * param to field()) is thus added here and chacked in in _add_fields().
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $_checklists = array();

	/**
	 * Constructor, set ID and heading.
	 *
	 * @since 1.0.0
	 * @param string $id Unique setting ID.
	 * @param string $page_heading Heading for the settings page.
	 */
	public function __construct( $id, $page_heading ) {
		$this->id = (string) $id;
		$this->page_heading = (string) $page_heading;
	}

	/**
	 * Add a submenu to an admin menu.
	 *
	 * NOTE: If tabs are used, each tab ID will be used as the key for the
	 * settings on that tab page, not the ID set with the constructor.
	 *
	 * Additional arguments through the $args array:
	 *
	 * - 'add_to' (string) Slug for the parent menu, or the file name of a
	 *   standard WordPress admin page (wp-admin/<file_name>). Includes .php
	 *   extension and defaults to 'options-general.php'.
	 * - 'title' (string) HTML <title> text.
	 * - 'tabs' (array) Tabs to add, format 'unique_id' => 'Tab label'.
	 * - 'capability' (string) Capability needed to edit the settings. If not
	 *   set, the $capability property is used, which defaults to manage_options.
	 *
	 * @since 1.0.0
	 * @param string $menu_label Text for the link in the menu.
	 * @param array $args Additional arguments.
	 * @link http://codex.wordpress.org/Function_Reference/add_submenu_page
	 * @link http://codex.wordpress.org/Roles_and_Capabilities
	 */
	public function submenu( $menu_label, array $args = array() ) {
		$defaults = array(
			'add_to' => 'options-general.php',
			'title' => $menu_label,
			'tabs' => array(),
			'capability' => $this->capability
		);
		$args = array_merge( $defaults, $args );

		$this->added_to = $args['add_to'];

		if ( $args['tabs'] )
			$this->_tabs = (array) $args['tabs'];

		$this->_submenu = array_merge( array(
			'menu_label' => $menu_label
		), $args );
	}

	/**
	 * Add a settings section.
	 *
	 * Additional arguments through the $args array:
	 *
	 * - 'heading' (string) Section heading.
	 * - 'tab' (string) Tab to add section to. Tabs are defined with submenu().
	 *   Defaults to first tab if there are any.
	 * - 'output' (string) HTML to display at the top of the section, below the
	 *   heading.
	 *
	 * @since 1.0.0
	 * @param string $id A unique section ID.
	 * @param array $args Additional arguments.
	 * @link http://codex.wordpress.org/Function_Reference/add_submenu_page
	 * @link http://codex.wordpress.org/Roles_and_Capabilities
	 */
	public function section( $id, array $args = array() ) {

		// Set the first tab as default if none is set.
		reset( $this->_tabs );
		$default_tab = ( $this->_tabs ) ? key( $this->_tabs ) : '';

		$defaults = array(
			'heading' => '',
			'tab' => $default_tab,
			'output' => ''
		);
		$args = array_merge( $defaults, $args );

		// Create an array of which sections belong to which tabs
		if ( array_key_exists( $args['tab'], $this->_tabs ) ) :
			$this->_tab_sections[$args['tab']][] = $id;
		endif;

		$this->_sections[$id] = $args;
	}

	/**
	 * Add a settings field.
	 *
	 * Additional arguments through the $args array:
	 *
	 * - 'type' (string) Type of field. Unsupported types will fall back to
	 *   'text', which is also the default. Supported types:
	 *   - 'text'
	 *   - 'text_monospace'
	 *   - 'textarea'
	 *   - 'textarea_large'
	 *   - 'textarea_monospace'
	 *   - 'textarea_large_monospace'
	 *   - 'editor'
	 *   - 'checkbox'
	 *   - 'checklist' (List of checkboxes)
	 *   - 'radios'
	 *   - 'select'
	 *   - 'button_field' (Text field with a button beside it)
	 *   - 'button_field_monospace'
	 * - 'section' (string) Section to add the field to, defined with section().
	 * - 'default' (mixed) Default field value. Is only set if options don't
	 *   exist, so will probably only run on theme/plugin activation.
	 * - 'description' (string) A help text to show under the field. Prints
	 *   unfiltered, so beware if user input is somehow involved.
	 * - 'inline_label' (string) Field label for checkbox and radio button.
	 * - 'options' (array) Options for types 'select', 'radios', and 'checklist',
	 *   format: value => text.
	 * - 'button_text' (string) Text for the button when using button_field.
	 * - 'validate' (string) Validate value against predefined functions, see
	 *   _validate().
	 * - 'must_match' (regex string) A regular expression that is matched
	 *   against the value, i.e. '/^\d{3}$/' to require exactly three digits.
	 * - 'must_not_match' (regex string) A regular expression that is matched
	 *   against the value, where the result is reversed. So something like
	 *   '/\d{3}/' would mean the value can not contain three digits in a row.
	 * - 'error_message' (string) Message for when validation fails.
	 * - 'sanitize' (string) Sanitize value against predefined functions, see
	 *   _sanitize(). Defaults to 'checkbox' for checkboxes.
	 * - 'sanitize_custom' (regex string) Sanitize value with a regular
	 *   expression. Value will go through preg_replace.
	 *
	 * @since 1.0.0
	 * @param string $id A unique ID for the field.
	 * @param string $label The field label.
	 * @param array $args Array of additional arguments.
	 */
	public function field( $id, $label, array $args = array() ) {
		$defaults = array(
			'type' => 'text',
			'section' => '',
			'default' => '',
			'description' => '',
			'inline_label' => '',
			'options' => array(),
			'button_text' => '',
			'validate' => '',
			'must_match' => '',
			'must_not_match' => '',
			'error_message' => '',
			'sanitize' => '',
			'sanitize_custom' => ''
		);

		// Probably no reason not to sanitize checkboxes as 0 or 1
		if ( 'checkbox' == $args['type'] )
			$defaults['sanitize'] = 'checkbox';

		$args = array_merge( $defaults, $args );

		// Since a function gets called based on the value of 'type', unsupported
		// types are converted to text.
		$supported_types = array(
			'text',
			'text_monospace',
			'textarea',
			'textarea_large',
			'textarea_monospace',
			'textarea_large_monospace',
			'editor',
			'checkbox',
			'checklist',
			'radios',
			'select',
			'button_field',
			'button_field_monospace'
		);
		if ( ! in_array( $args['type'], $supported_types ) )
			$args['type'] = 'text';

		// If a section is not defined, set it to the first one.
		if ( ! $args['section'] ) :
			reset( $this->_sections );
			$args['section'] = key( $this->_sections );
		endif;

		// Keep track of checkboxes grouped by section.
		if ( 'checkbox' == $args['type'] ) :
			$this->_checkboxes[$args['section']][] = $id;

		// Checklists need special handling.
		elseif ( 'checklist' == $args['type'] ) :
			foreach ( $args['options'] as $field_id => $field_label ) :

				// Same as above.
				$this->_checkboxes[$args['section']][] = $field_id;

				// Add each checkbox as a separate entry in $fields.
				$this->_fields[$field_id] = array_merge( array(
					'label' => $label,
					'checklist' => $id
				), $args );
			endforeach;

			// Drop out so they don't get overwritten.
			return;
		endif;

		$this->_fields[$id] = array_merge( array(
			'label' => $label
		), $args );
	}

	/**
	 * Add general HTML to the settings page.
	 *
	 * @since 1.2.0
	 * @param string $id Which field ID to insert after.
	 * @param string $html HTML content.
	 */
	public function html( $id, $html = '' ) {
		$this->_html[$id] = $html;
	}

	/**
	 * Run the settings registration on apropriate hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, '_load_settings' ) );
		add_action( 'admin_init', array( $this, '_register_setting' ) );
	}

	/**
	 * Get settings page ID.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_screen_id() {
		return $this->_screen_id;
	}





	/*========================================================================*\
	      =Settings
	\*========================================================================*/

	/**
	 * Load and register settings.
	 *
	 * Add a menu entry to the defined menu and set up loading of the content
	 * for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function _load_settings() {
		if ( ! $this->_submenu ) return;

		$this->_screen_id = add_submenu_page(
			$this->_submenu['add_to'],
			$this->_submenu['title'],
			$this->_submenu['menu_label'],
			$this->_submenu['capability'],
			$this->id,
			array( $this, '_display_page' )
		);

		// Only load the settings content when on the added submenu page
		if ( $this->_screen_id ) :
			add_action( 'load-' . $this->_screen_id, array( $this, '_add_sections' ) );
			add_action( 'load-' . $this->_screen_id, array( $this, '_add_fields' ) );
		endif;
	}

	/**
	 * Add all sections from $this->_sections.
	 *
	 * @since 1.0.0
	 */
	public function _add_sections() {
		foreach ( $this->_sections as $section => $args ) :

			// If using tabs, the page ID the section should be added to is the
			// tab it's set to show on. Otheriwse it's just the initially set
			// setting ID.
			$page = ( $this->_tabs && ! empty( $this->_sections[$section]['tab'] ) )
				? $this->_sections[$section]['tab']
				: $this->id;

			add_settings_section(
				$section,
				$args['heading'],
				array( $this, '_display_section' ),
				$page
			);
		endforeach;
	}

	/**
	 * Add all fields from $this->_fields.
	 *
	 * @since 1.0.0
	 */
	public function _add_fields() {
		foreach ( $this->_fields as $field_id => $args ) :

			// If using tabs, the page ID the field should be added to is the tab
			// of the section it belongs in. Otheriwse it's just the initially set
			// setting ID.
			$page = ( $this->_tabs && ! empty( $this->_sections[$args['section']]['tab'] ) )
				? $this->_sections[$args['section']]['tab']
				: $this->id;

			// Don't add 'for' to the left column label for checkboxes and radio
			// buttons, since they will have adjacent labels.
			$label_for = ( $args['type'] == 'checkbox'
				|| $args['type'] == 'checklist'
				|| $args['type'] == 'radios'
				|| $args['type'] == 'radio' )
				? ''
				: $field_id;

			// Get option value here instead of in every function. If using tabs,
			// every tab ID is used as a separate options entry.
			if ( $this->_tabs ) :
				// Get first tab if none is set
				reset( $this->_tabs );
				$settings_id = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $this->_tabs );
			else :
				$settings_id = $this->id;
			endif;

			$options = (array) get_option( $settings_id );

			// Value
			$value = '';
			if ( isset( $options[$field_id] ) )
				$value = trim( $options[$field_id] );

			// Method
			$method = '_add_' . $args['type'];

			// Checklist handling. Check for current page.
			if ( $page == $settings_id && 'checklist' == $args['type'] ) :

				// Checklist options are stored by each option value, since the
				// checkboxes are not related to each other other than visually.
				// Thus the key for $field_ids can not be used.
				$value = array();
				foreach ( $args['options'] as $id => $label ) :
					$value[$id] = ( ! empty( $options[$id] ) ) ? $options[$id] : 0;
				endforeach;

				// Only add the checklist fields once. See $this->_checklists.
				if ( in_array( $args['checklist'], $this->_checklists ) ) continue;
				$this->_checklists[] = $args['checklist'];
			endif;

			// Add the field
			add_settings_field(
				$field_id,
				$args['label'],
				//array( $this, $method ),
				array( $this, '_display_field' ),
				$page,
				$args['section'],

				// Pass arguments to the _add_<type> functions
				array(
					'type' => $args['type'],
					'label_for' => $label_for,
					'prefix' => $page,
					'id' => $field_id,
					'value' => $value,
					'label' => $args['inline_label'],
					'description' => $args['description'],
					'options' => $args['options'],
					'button_text' => $args['button_text']
				)
			);
		endforeach;

		// Add highlighting for fields with errors.
		add_action( 'admin_footer', array( $this, '_error_highlighting' ) );
	}

	/**
	 * Display the settings page.
	 *
	 * @since 1.0.0
	 */
	public function _display_page() {
		if ( ! current_user_can( $this->capability ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'lucid-toolbox' ) );

		ob_start(); ?>

		<div class="wrap">

			<?php // If using tabs, the ID of the current settings section is the
			// same as the current tab. Otheriwse it's just the initially set ID.
			if ( $this->_tabs ) :
				// Get first tab if none is set
				reset( $this->_tabs );
				$settings = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $this->_tabs );
				$this->_settings_tabs();
			else :
				$settings = $this->id;
				screen_icon();
				echo "<h2>{$this->page_heading}</h2>";
			endif;

			// Different cases for different pages
			if ( $this->pass_settings_errors_id )
				settings_errors( $settings );
			else
				settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				// Renders settings fields lined up in tables and also
				// handles security with referer and nonce checks.
				settings_fields( $settings );
				do_settings_sections( $settings );
				submit_button();
				?>
			</form>

		</div>
	<?php echo ob_get_clean();
	}

	/**
	 * Display section output if set.
	 *
	 * @since 1.0.0
	 * @param array $section Data for the section being processed.
	 */
	public function _display_section( $section ) {
		if ( ! empty( $this->_sections[$section['id']]['output'] ) ) :
			echo wp_kses_post( $this->_sections[$section['id']]['output'] );
		endif;
	}

	/**
	 * Render settings tabs.
	 *
	 * @since 1.0.0
	 */
	protected function _settings_tabs() {
		if ( ! $this->_tabs ) return;

		// Get first tab if none is set
		reset( $this->_tabs );
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $this->_tabs );

		screen_icon(); ?>

		<h2 class="nav-tab-wrapper">
		<?php // echo $this->page_heading;
		foreach ( $this->_tabs as $tab => $label ) :
			$active = ( $current_tab == $tab ) ? ' nav-tab-active' : ''; ?>
			<a class="nav-tab<?php echo $active; ?>" href="<?php echo "?page={$this->id}&tab={$tab}"; ?>"><?php echo $label; ?></a>
		<?php endforeach; ?>
		</h2>
	<?php }

	/**
	 * Register settings page and fields.
	 *
	 * @since 1.0.0
	 */
	public function _register_setting() {

		// With tabs, every tab is a separate settings collection
		if ( $this->_tabs ) :
			foreach ( $this->_tabs as $tab => $label ) :
				$this->_add_defaults( $tab );

				register_setting(
					$tab,
					$tab,
					array( $this, 'sanitize_options' )
				);
			endforeach;

		// Without tabs the setting page ID is used
		else :
			$this->_add_defaults( $this->id );

			register_setting(
				$this->id,
				$this->id,
				array( $this, 'sanitize_options' )
			);
		endif;
	}

	/**
	 * Add default options.
	 *
	 * Should only run if there are no options set, which should only happen if
	 * the settings have never been saved.
	 *
	 * @since 1.1.0
	 * @param string $id Setting ID, tab or global.
	 */
	protected function _add_defaults( $id ) {

		// Don't to anything if the options exist.
		if ( get_option( $id ) ) return;

		add_option( $id );
		$added_checklists = array();
		$defaults = array();

		foreach ( $this->_fields as $field_id => $args ) :

			// Get out if the field is not on the tab being processed.
			if ( $this->_tabs && $this->_sections[$args['section']]['tab'] != $id ) continue;

			// Checkboxes use a zero for empty defaults
			$default = ( 'checkbox' == $args['type'] || 'checklist' == $args['type'] ) ? 0 : '';

			if ( ! empty( $args['default'] ) )
				$default = $args['default'];

			// Checklists have every option as a separate setting.
			if ( 'checklist' == $args['type'] ) :

				// Skip multiple iterations for the same checklist.
				if ( in_array( $field_id, $added_checklists ) ) continue;

				foreach ( $args['options'] as $option => $label ) :
					$defaults[$option] = $default;
				endforeach;

				$added_checklists[] = $field_id;

			// Regular fields have their ID.
			else :
				$defaults[$field_id] = $default;
			endif;
		endforeach;

		update_option( $id, $defaults );
	}





	/*========================================================================*\
	      =Fields
	\*========================================================================*/

	/**
	 * Display settings field.
	 *
	 * @since 1.3.0
	 * @param array $args Field options.
	 */
	public function _display_field( $args ) {

		// The field itself
		switch ( $args['type'] ) :

			case 'text' :
				$this->_add_text( $args );
				break;

			case 'text_monospace' :
				$this->_add_text( $args, 'code' );
				break;

			case 'textarea' :
				$this->_add_textarea( $args );
				break;

			case 'textarea_large' :
				$this->_add_textarea( $args, 'large-text' );
				break;

			case 'textarea_monospace' :
				$this->_add_textarea( $args, 'code' );
				break;

			case 'textarea_large_monospace' :
				$this->_add_textarea( $args, 'large-text code' );
				break;

			case 'editor' :
				$this->_add_editor( $args );
				break;

			case 'checkbox' :
				$this->_add_checkbox( $args );
				break;

			case 'checklist' :
				$this->_add_checklist( $args );
				break;

			case 'select' :
				$this->_add_select( $args );
				break;

			case 'radios' :
				$this->_add_radios( $args );
				break;

			case 'button_field' :
				$this->_add_button_field( $args );
				break;

			case 'button_field_monospace' :
				$this->_add_button_field( $args, 'code' );
				break;

		endswitch;

		// Field description
		$this->_add_description( $args['description'] );

		// Custom HTML
		$this->_add_html( $args['id'] );
	}

	/**
	 * Display a text field.
	 *
	 * @since 1.0.0
	 * @param array $args Field options.
	 * @param string $class CSS class for the field.
	 */
	protected function _add_text( $args, $class = '' ) {

		$class = ( $class ) ? 'regular-text ' . $class : 'regular-text'; ?>

		<input type="text" class="<?php echo $class; ?>" id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" value="<?php echo esc_attr( $args['value'] ); ?>">

		<?php
	}

	/**
	 * Display a textarea.
	 *
	 * @since 1.0.0
	 * @param array $args Field options.
	 * @param string $class CSS class for the field.
	 */
	protected function _add_textarea( $args, $class = '' ) { ?>
		<textarea id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" rows="8" cols="80"<?php if ( $class ) echo " class=\"{$class}\""; ?>><?php echo esc_textarea( $args['value'] ); ?></textarea>

		<?php
	}

	/**
	 * Display a visual editor.
	 *
	 * @since 1.3.5
	 * @param array $args Field options.
	 */
	protected function _add_editor( $args ) {
		wp_editor( $args['value'], "{$args['prefix']}[{$args['id']}]", array(
			'textarea_rows' => 12
		) );
	}

	/**
	 * Display a checkbox.
	 *
	 * @since 1.0.0
	 * @param array $args Field options.
	 */
	protected function _add_checkbox( $args ) { ?>
		<input type="checkbox" id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" value="1" <?php checked( $args['value'], 1 ); ?>>

		<?php if ( $args['label'] ) : ?>
			<label for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?></label>
		<?php endif;
	}

	/**
	 * Display a list of checkboxes.
	 *
	 * @since 1.1.0
	 * @param array $args Field options.
	 */
	protected function _add_checklist( $args ) {
		$count = 0;

		foreach ( $args['options'] as $id => $label ) :
			$value = get_option( $id );

			if ( $count > 0 ) echo '<br>';
			$count++; ?>
			<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo "{$args['prefix']}[{$id}]"; ?>" value="1" <?php checked( $args['value'][$id], 1 ); ?>>

			<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
		<?php endforeach;
	}

	/**
	 * Display a select list.
	 *
	 * @since 1.0.0
	 * @param array $args Field options.
	 */
	protected function _add_select( $args ) { ?>
		<select id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>">
			<?php foreach ( $args['options'] as $val => $text ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $args['value'], $val ); ?>><?php echo $text; ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Display radio buttons.
	 *
	 * @since 1.0.0
	 * @param array $args Field options.
	 */
	protected function _add_radios( $args ) {
		$count = 0;

		foreach ( $args['options'] as $val => $label ) :
			if ( $count > 0 ) echo '<br>';
			$count++; ?>
			<input type="radio" id="<?php echo $args['id'] . '_' . $count; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" value="<?php echo esc_attr( $val ); ?>" <?php checked( $args['value'], $val ); ?>>

			<label for="<?php echo $args['id'] . '_' . $count; ?>"><?php echo $label; ?></label>
		<?php endforeach;
	}

	/**
	 * Display a field with a button after.
	 *
	 * Useful when using JavaScript, for example an image upload field.
	 *
	 * @since 1.3.0
	 * @param array $args Field options.
	 * @param string $class CSS class for the field.
	 */
	protected function _add_button_field( $args, $class = '' ) {

		$class = ( $class ) ? 'regular-text ' . $class : 'regular-text'; ?>

		<input type="text" class="<?php echo $class; ?>" id="<?php echo $args['id']; ?>" name="<?php echo "{$args['prefix']}[{$args['id']}]"; ?>" value="<?php echo esc_attr( $args['value'] ); ?>">
		<a href="#" id="<?php echo $args['id']; ?>-button" class="button"><?php echo $args['button_text']; ?></a>

		<?php
	}

	/**
	 * Display a field description.
	 *
	 * @since 1.0.0
	 * @param string $text Text to display.
	 */
	protected function _add_description( $text ) {
		if ( $text ) echo '<br><span class="description">' . $text . '</span>';
	}

	/**
	 * Display custom HTML.
	 *
	 * @since 1.0.0
	 * @param string $text Text to display.
	 */
	protected function _add_html( $text ) {
		if ( ! empty( $this->_html[$text] ) )
			echo $this->_html[$text];
	}





	/*========================================================================*\
	      =Sanitation and validation
	\*========================================================================*/

	/**
	 * Sanitize the input from the settings.
	 *
	 * Checks for an explicitly defined sanitation 'none' to save unfiltered
	 * values. Runs through _sanitize() if none is set, where it defaults to
	 * stripping illegal tags with wp_kses_post().
	 *
	 * @since 1.0.0
	 * @see _validate()
	 * @see _validate_custom()
	 * @see _sanitize()
	 * @param array $input Each field's data to be sanitized.
	 */
	public function sanitize_options( $input ) {

		// Since the tabs are registered as individual settings, they are
		// considered separate pages and are available in $_POST
		if ( $this->_tabs ) :
			$settings_id = preg_replace( '/[^A-Za-z0-9\-_]/', '', $_POST['option_page'] );
		else :
			$settings_id = $this->id;
		endif;

		$output = (array) get_option( $settings_id );

		foreach ( $input as $name => $val ) :
			if ( isset( $input[$name] ) ) :

				// Anti-notice annoyances
				$f = $this->_fields[$name];
				$validate = ( ! empty( $f['validate'] ) ) ? $f['validate'] : false;
				$sanitize = ( ! empty( $f['sanitize'] ) ) ? $f['sanitize'] : '';
				$sanitize_custom = ( ! empty( $f['sanitize_custom'] ) ) ? $f['sanitize_custom'] : false;
				$must_match = ( ! empty( $f['must_match'] ) ) ? $f['must_match'] : false;
				$must_not_match = ( ! empty( $f['must_not_match'] ) ) ? $f['must_not_match'] : false;
				$error = ( ! empty( $f['error_message'] ) ) ? $f['error_message'] : '';

				// Empty or no checking, value saved as is
				if ( empty( $input[$name] ) || 'none' == $sanitize ) :
					$output[$name] = $input[$name];

				// Validation, sets error if there is a problem
				elseif ( $validate || $must_match || $must_not_match ) :
					$result = trim( $input[$name] );

					// Do appropriate validation depending on type
					if ( $validate ) :
						$result = $this->_validate( $validate, $input[$name], $error );
					elseif ( $must_match ) :
						$result = $this->_validate_custom( $must_match, $input[$name], $error );
					else :
						$result = $this->_validate_custom( $must_not_match, $input[$name], $error, true );
					endif;

					// Validation functions return an array in case of error. Kind
					// of ugly, but works...
					if ( is_array( $result ) && ! empty( $result['error'] ) ) :
						add_settings_error( $name, $name, $result['error'] );
					else :
						$output[$name] = $result;
					endif;

				// Custom sanitation, runs the value through preg_replace
				elseif ( $sanitize_custom ) :
					$output[$name] = $this->_sanitize_custom( $sanitize_custom, trim( $input[$name] ) );

				// Sanitation, converts the input to a fitting format
				else :
					$output[$name] = $this->_sanitize( $sanitize, trim( $input[$name] ) );
				endif;

			endif;
		endforeach;

		// Special checkbox handling: unchecked boxes don't get POSTed, so isset.
		foreach ( $this->_checkboxes as $section => $boxes ) :
			foreach ( $boxes as $key => $checkbox_id ) :

				// If using tabs, only look for checkboxes on the tab being
				// processed.
				if ( $this->_tabs && $this->_sections[$section]['tab'] != $settings_id ) continue;

				if ( ! isset( $input[$checkbox_id] ) )
					$output[$checkbox_id] = 0;

			endforeach;
		endforeach;

		return $output;
	}

	/**
	 * Run predefined validation on a value.
	 *
	 * Contains predefined validation checks for 'email', 'url' and 'hex_color'.
	 *
	 * The URL check contains a simple second part in addition to the complex
	 * regex, where if set matches (2 characters).(2-15 characters) (with an
	 * optional slash). This is so a URL like 'google.com' counts as valid. Can
	 * be filtered with 'lucid_validate_simple_url', simply return false.
	 *
	 * FILTER_VALIDATE_URL is not used due to a bug in some versions of PHP
	 * where dashes are invalid. It also doesn't handle non-ASCII characters.
	 *
	 * @since 1.0.0
	 * @param string $type Type of validation, predfined or custom.
	 * @param string $value Value to validate.
	 * @param string $error Error message to display in case of invalid value.
	 * @link http://daringfireball.net/2010/07/improved_regex_for_matching_urls
	 * @return string|array The value if it's valid, array in case of error.
	 */
	protected function _validate( $type, $value, $error = '' ) {
		$valid = true;

		// Email
		if ( 'email' == $type ) :
			if ( ! $error ) $error = __( 'The email address seems to be invalid.', 'lucid-toolbox' );

			$valid = (bool) filter_var( $value, FILTER_VALIDATE_EMAIL );

		// URL
		elseif ( 'url' == $type ) :
			if ( ! $error ) $error = __( 'The URL seems to be invalid.', 'lucid-toolbox' );

			// Is simple is tested, a URL like 'google.com' is valid
			$include_simple = true;
			$include_simple = apply_filters( 'lucid_validate_simple_url', $include_simple );
			$simple_url = ( $include_simple )
				? ( preg_match( '/^\w{2,}\.\w{2,15}\/?$/', $value ) )
				: false;

			// http://daringfireball.net/2010/07/improved_regex_for_matching_urls
			//
			// Modifications: Allow up to a 15 character TLD (since new ones can
			// be bought). Make second capture group optional, so only a single
			// character after a slash is needed. Match beginning and end of
			// string.
			$valid = (bool) (
				( preg_match( '/^((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,15}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))*(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'\".,<>?«»“”‘’]))$/', $value ) )
				|| $simple_url
			);

		// Hexadecimal color (like 101 or bada55, hash is stripped before checking)
		elseif ( 'hex_color' == $type ) :
			if ( ! $error ) $error = __( 'The hex color seems to have an invalid format.', 'lucid-toolbox' );
			$value = str_replace( '#', '', $value );

			$valid = (bool) preg_match( '/^([a-f0-9]{6}|[a-f0-9]{3})$/', strtolower( $value ) );

		endif;

		// If there is an error, send it back as an array. Kind of ugly way to
		// determine if it's an error or not (is_array).
		if ( ! $valid )
			$value = array( 'error' => $error );

		return $value;
	}

	/**
	 * Run custom validation on a value.
	 *
	 * Runs a custom regex validation. If reverse is set, the preg_match result
	 * is reversed.
	 *
	 * @since 1.2.0
	 * @param string $regex The regex to run in preg_match.
	 * @param string $value Value to validate.
	 * @param string $error Error message to display in case of invalid value.
	 * @param bool $reverse Reverse preg_match result?
	 * @return string|array The value if it's valid, array in case of error.
	 */
	protected function _validate_custom( $regex, $value, $error = '', $reverse = false ) {
		$valid = true;

		if ( ! $error )
			$error = __( 'There were settings with invalid values.', 'lucid-toolbox' );

		if ( $reverse ) :
			// must_not_match
			$valid = (bool) ! preg_match( $regex, $value );
		else :
			// must_match
			$valid = (bool) preg_match( $regex, $value );
		endif;

		// If there is an error, send it back as an array. Kind of ugly way to
		// determine if it's an error or not (is_array).
		if ( ! $valid )
			$value = array( 'error' => $error );

		return $value;
	}

	/**
	 * Sanitize a value.
	 *
	 * Options:
	 *
	 * - 'checkbox' Always 1 or 0.
	 * - 'int' Integer, positive or negative.
	 * - 'absint' Integer, non-negative.
	 * - 'float' Floating point number with floatval.
	 * - 'alphanumeric' Letters, numbers, underscore and dash.
	 * - 'url' Escapes a URL with esc_url_raw.
	 * - 'no_html' Strips HTML with strip_tags.
	 * - 'shortcode' Removes greater/less than and forces enclosing square
	 *   brackets.
	 * - 'empty' No value. Useful for fields acting as 'tools' that shouldn't
	 *   save anything.
	 *
	 * Falls back to stripping illegal HTML tags with wp_kses_post.
	 *
	 * @since 1.0.0
	 * @param string $type What kind of sanitation to run.
	 * @param string $value Value to sanitize
	 * @return mixed The sanitized value.
	 */
	protected function _sanitize( $type, $value ) {
		switch ( $type ) :

			case 'checkbox' :
				$value = ( ! empty( $value ) ) ? 1 : 0;
				break;

			case 'int' :
				// Hyphens inside the string are stripped, so bring it back
				// afterwards if it's in the first position.
				$is_negative = ( '-' == $value[0] ) ? true : false;
				$value = preg_replace( '/[^0-9]/', '', $value );
				if ( $is_negative ) $value = '-' . $value;
				$value = intval( $value );
				break;

			case 'absint' :
				$value = absint( $value );
				break;

			case 'float' :
				$value = floatval( $value );
				break;

			case 'alphanumeric' :
				$value = preg_replace( '/[^a-zA-Z0-9_-]/', '', $value );
				break;

			case 'no_html' :
				$value = strip_tags( $value );
				break;

			case 'url' :
				$value = esc_url_raw( $value );

			case 'shortcode' :
				$tmp = str_replace( array( '[', ']', '<', '>' ), '', $value );
				$value = '[' . trim( $tmp ) . ']';
				break;

			case 'empty' :
				$value = '';
				break;

			default :
				$value = wp_kses_post( $value );
				break;

		endswitch;

		return $value;
	}

	/**
	 * Sanitize a value with a custom regex.
	 *
	 * @since 1.2.0
	 * @param string $regex Regular expression to run in value.
	 * @param string $value Value to sanitize.
	 * @return mixed The sanitized value.
	 */
	protected function _sanitize_custom( $regex, $value ) {
		return preg_replace( $regex, '', $value );
	}

	/**
	 * Hightlight fields with errors.
	 *
	 * @since 1.2.0
	 */
	public function _error_highlighting() { ?>
		<script>
		;(function( $ ) {
			var $errors = $( '.settings-error' );

			if ( 0 !== $errors.length ) {
				$errors.each( function() {
					var id = $(this).attr('id').replace( /setting-error-/, '' );
					$( '#' + id ).css({ 'border-color': '#cc0000' });
				});
			}
		})( jQuery );
		</script>
	<?php }
}