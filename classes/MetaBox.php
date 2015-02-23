<?php
/**
 * A forked version of the original WPAlchemy_MetaBox.
 *
 * @author    Dimas Begunoff
 * @copyright Copyright (c) 2009, Dimas Begunoff, http://farinspace.com
 * @license   http://en.wikipedia.org/wiki/MIT_License The MIT License
 * @package   WPAlchemy
 * @version   1.5.2.lucid-2
 * @link      http://github.com/farinspace/wpalchemy
 * @link      http://farinspace.com
 */

// Compat for when the original class was used as is. May be removed in the
// future.
if (!class_exists('WPAlchemy_MetaBox')) {
	class WPAlchemy_MetaBox extends Lucid_WPAlchemy {}
}

if (!defined('WPALCHEMY_MODE_ARRAY'))
	define('WPALCHEMY_MODE_ARRAY', 'array');

if (!defined('WPALCHEMY_MODE_EXTRACT'))
	define('WPALCHEMY_MODE_EXTRACT', 'extract');

class Lucid_WPAlchemy {

	/**
	 * User defined identifier for the meta box, prefix with an underscore to
	 * prevent option(s) form showing up in the custom fields meta box, this
	 * option should be used when instantiating the class.
	 *
	 * @since	1.0
	 * @access	public
	 * @var		string required
	 */
	public $id;

	/**
	 * Used to set the title of the meta box, this option should be used when
	 * instantiating the class.
	 *
	 * @since	1.0
	 * @access	public
	 * @var		string required
	 * @see		$hide_title
	 */
	public $title = 'Custom Meta';

	/**
	 * Used to set the meta box content, the contents of your meta box should be
	 * defined within this file, this option should be used when instantiating
	 * the class.
	 *
	 * @since	1.0
	 * @access	public
	 * @var		string required
	 */
	public $template;

	/**
	 * Used to set the post types that the meta box can appear in, this option
	 * should be used when instantiating the class.
	 *
	 * @since	1.0
	 * @access	public
	 * @var		array
	 */
	public $types;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $context = 'normal';

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $priority = 'high';

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $autosave = TRUE;

	/**
	 * Used to set how the class does its data storage, data will be stored as
	 * an associative array in a single meta entry in the wp_postmeta table or
	 * data can be set and individual entries in the wp_postmeta table, the
	 * following constants should be used when setting this option,
	 * WPALCHEMY_MODE_ARRAY (default) and WPALCHEMY_MODE_EXTRACT, this option
	 * should be used when instantiating the class.
	 *
	 * @since	1.2
	 * @access	public
	 * @var		string
	 */
	public $mode = WPALCHEMY_MODE_ARRAY;

	/**
	 * What 'area' to use for the metabox.
	 *
	 * Other possible values are 'after_title' and 'after_editor', which won't
	 * actually create a metabox, but add the fields to the page in the
	 * appropriate places.
	 *
	 * @since	1.5.2.lucid-1
	 * @access	public
	 * @var		string
	 */
	public $area = 'metabox';

	/**
	 * When the mode option is set to WPALCHEMY_MODE_EXTRACT, you have to take
	 * care to avoid name collisions with other meta entries. Use this option to
	 * automatically add a prefix to your variables, this option should be used
	 * when instantiating the class.
	 *
	 * @since	1.2
	 * @access	public
	 * @var		array
	 */
	public $prefix;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_template;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_category_id;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_category;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_tag_id;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_tag;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $exclude_post_id;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_template;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_category_id;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_category;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_tag_id;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_tag;

	/**
	 * @since	1.0
	 * @access	public
	 * @var		bool
	 */
	public $include_post_id;

	/**
	 * Callback used on the WordPress "admin_init" action, the main benefit is
	 * that this callback is executed only when the meta box is present, this
	 * option should be used when instantiating the class.
	 *
	 * @since	1.3.4
	 * @access	public
	 * @var		string|array optional
	 */
	public $init_action;

	/**
	 * Callback used to override when the meta box gets displayed, must return
	 * true or false to determine if the meta box should or should not be
	 * displayed, this option should be used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @param	array $post_id first variable passed to the callback function
	 * @see		can_output()
	 */
	public $output_filter;

	/**
	 * Callback used to override or insert meta data before saving, you can halt
	 * saving by passing back FALSE (return FALSE), this option should be used
	 * when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @param	array $meta meta box data, first variable passed to the callback function
	 * @param	string $post_id second variable passed to the callback function
	 * @see		$save_action, add_filter()
	 */
	public $save_filter;

	/**
	 * Callback used to execute custom code after saving, this option should be
	 * used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @param	array $meta meta box data, first variable passed to the callback function
	 * @param	string $post_id second variable passed to the callback function
	 * @see		$save_filter, add_filter()
	 */
	public $save_action;

	/**
	 * Callback used to override or insert STYLE or SCRIPT tags into the head,
	 * this option should be used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @param	array $content current head content, first variable passed to the callback function
	 * @see		$head_action, add_filter()
	 */
	public $head_filter;

	/**
	 * Callback used to insert STYLE or SCRIPT tags into the head,
	 * this option should be used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @see		$head_filter, add_action()
	 */
	public $head_action;

	/**
	 * Callback used to override or insert SCRIPT tags into the footer, this
	 * option should be used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @param	array $content current foot content, first variable passed to the callback function
	 * @see		$foot_action, add_filter()
	 */
	public $foot_filter;

	/**
	 * Callback used to insert SCRIPT tags into the footer, this option should
	 * be used when instantiating the class.
	 *
	 * @since	1.3
	 * @access	public
	 * @var		string|array optional
	 * @see		$foot_filter, add_action()
	 */
	public $foot_action;

	// Internal
	protected $meta;
	protected $name;
	protected $subname;
	protected $length = 0;
	protected $current = -1;
	protected $in_loop = false;
	protected $in_template = false;
	protected $group_tag;
	protected $current_post_id;
	protected static $_is_head_foot_done = false;

	/**
	 * Used to store current loop details, cleared after loop ends
	 *
	 * @since	1.4
	 * @access	private
	 * @var		stdClass
	 * @see		have_fields_and_multi(), have_fields()
	 */
	public $_loop_data;

	/**
	 * Constructor.
	 *
	 * @param array $args Metabox options.
	 */
	public function __construct(array $args)
	{
		$this->_loop_data = new stdClass;

		$this->meta = array();

		$this->types = array('post', 'page');

		foreach ($args as $n => $v)
		{
			$this->$n = $v;
		}

		if (empty($this->id)) die('Meta box ID required');

		if (!is_string($this->id)) die('Meta box ID must be a string');

		if (empty($this->template)) die('Meta box template file required');

		// check for nonarray values

		$exc_inc = array(
			'exclude_template',
			'exclude_category_id',
			'exclude_category',
			'exclude_tag_id',
			'exclude_tag',
			'exclude_post_id',

			'include_template',
			'include_category_id',
			'include_category',
			'include_tag_id',
			'include_tag',
			'include_post_id'
		);

		foreach ($exc_inc as $v)
		{
			// ideally the exclude and include values should be in array form, convert to array otherwise
			if (!empty($this->$v) AND !is_array($this->$v))
			{
				$this->$v = array_map('trim',explode(',',$this->$v));
			}
		}

		if (!self::$_is_head_foot_done) {
			add_action('admin_head', array('WPAlchemy_MetaBox', '_global_head'));
			add_action('admin_footer', array('WPAlchemy_MetaBox', '_global_foot'));
			self::$_is_head_foot_done = true;
		}

		add_action('admin_init', array($this,'_init'));
	}

	/**
	 * Used to initialize the meta box, runs on WordPress admin_init action,
	 * properly calls internal WordPress methods
	 *
	 * @since	1.0
	 * @access	private
	 */
	public function _init()
	{
		// must be creating or editing a post or page
		if ( ! WPAlchemy_MetaBox::_is_post() AND ! WPAlchemy_MetaBox::_is_page()) return;

		if ( ! empty($this->output_filter))
		{
			$this->add_filter('output', $this->output_filter);
		}

		if ($this->can_output())
		{
			$post_type = WPAlchemy_MetaBox::_get_current_post_type();

			if (in_array($this->area, array('after_title', 'after_editor')) && in_array($post_type, $this->types))
			{
				$hook = ('after_title' == $this->area) ? 'edit_form_after_title' : 'edit_form_after_editor';
				add_action($hook, array($this, '_setup'));
			}
			else
			{
				foreach ($this->types as $type)
				{
					add_meta_box($this->id . '_metabox', $this->title, array($this, '_setup'), $type, $this->context, $this->priority);
				}
			}

			add_action('save_post', array($this,'_save'));

			$filters = array('save', 'head', 'foot');

			foreach ($filters as $filter)
			{
				$var = $filter . '_filter';

				if (!empty($this->$var))
				{
					if ('save' == $filter)
					{
						$this->add_filter($filter, $this->$var, 10, 2);
					}
					else
					{
						$this->add_filter($filter, $this->$var);
					}
				}
			}

			$actions = array('save', 'head', 'foot', 'init');

			foreach ($actions as $action)
			{
				$var = $action . '_action';

				if (!empty($this->$var))
				{
					if ('save' == $action)
					{
						$this->add_action($action, $this->$var, 10, 2);
					}
					else
					{
						$this->add_action($action, $this->$var);
					}
				}
			}

			add_action('admin_head', array($this,'_head'), 11);

			add_action('admin_footer', array($this,'_foot'), 11);

			// action: init
			if ($this->has_action('init'))
			{
				$this->do_action('init');
			}
		}
	}

	/**
	 * Used to insert STYLE or SCRIPT tags into the head, called on WordPress
	 * admin_head action.
	 *
	 * @since	1.3
	 * @access	private
	 * @see		_foot()
	 */
	public function _head()
	{

		// action: head
		if ($this->has_action('head'))
		{
			$this->do_action('head');
		}
	}

	/**
	 * Used to insert SCRIPT tags into the footer, called on WordPress
	 * admin_footer action.
	 *
	 * @since	1.3
	 * @access	private
	 * @see		_head()
	 */
	public function _foot()
	{

		// action: foot
		if ($this->has_action('foot'))
		{
			$this->do_action('foot');
		}
	}

	/**
	 * Used to setup the meta box content template
	 *
	 * @since	1.0
	 * @access	private
	 * @see		_init()
	 */
	public function _setup()
	{
		$this->in_template = TRUE;

		// also make current post data available
		global $post;

		// shortcuts
		$mb =& $this;
		$metabox =& $this;
		$id = $this->id;
		$meta = $this->_meta(NULL, TRUE);

		// Used in _global_head for copy and delete
		if ('metabox' != $this->area)
			echo '<div class="wpalchemy_metabox">';

		// use include because users may want to use one templete for multiple meta boxes
		include $this->template;

		if ('metabox' != $this->area)
			echo '</div>';

		// create a nonce for verification
		echo '<input type="hidden" name="'. $this->id .'_nonce" value="' . wp_create_nonce($this->id) . '" />';

		$this->in_template = FALSE;
	}

	/**
	 * Used to properly prefix the filter tag, the tag is unique to the meta
	 * box instance
	 *
	 * @since	1.3
	 * @access	private
	 * @param	string $tag name of the filter
	 * @return	string uniquely prefixed tag name
	 */
	public function _get_filter_tag($tag)
	{
		$prefix = 'wpalchemy_filter_' . $this->id . '_';
		$prefix = preg_replace('/_+/', '_', $prefix);

		$tag = preg_replace('/^'. $prefix .'/i', '', $tag);
		return $prefix . $tag;
	}

	/**
	 * Uses WordPress add_filter() function, see WordPress add_filter()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L65
	 */
	public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		$tag = $this->_get_filter_tag($tag);;
		add_filter($tag, $function_to_add, $priority, $accepted_args);
	}

	/**
	 * Uses WordPress has_filter() function, see WordPress has_filter()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L86
	 */
	public function has_filter($tag, $function_to_check = FALSE)
	{
		$tag = $this->_get_filter_tag($tag);
		return has_filter($tag, $function_to_check);
	}

	/**
	 * Uses WordPress apply_filters() function, see WordPress apply_filters()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L134
	 */
	public function apply_filters($tag, $value)
	{
		$args = func_get_args();
		$args[0] = $this->_get_filter_tag($tag);
		return call_user_func_array('apply_filters', $args);
	}

	/**
	 * Uses WordPress remove_filter() function, see WordPress remove_filter()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L250
	 */
	public function remove_filter($tag, $function_to_remove, $priority = 10, $accepted_args = 1)
	{
		$tag = $this->_get_filter_tag($tag);
		return remove_filter($tag, $function_to_remove, $priority, $accepted_args);
	}

	/**
	 * Used to properly prefix the action tag, the tag is unique to the meta
	 * box instance
	 *
	 * @since	1.3
	 * @access	private
	 * @param	string $tag name of the action
	 * @return	string uniquely prefixed tag name
	 */
	public function _get_action_tag($tag)
	{
		$prefix = 'wpalchemy_action_' . $this->id . '_';
		$prefix = preg_replace('/_+/', '_', $prefix);

		$tag = preg_replace('/^'. $prefix .'/i', '', $tag);
		return $prefix . $tag;
	}

	/**
	 * Uses WordPress add_action() function, see WordPress add_action()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L324
	 */
	public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		$tag = $this->_get_action_tag($tag);
		add_action($tag, $function_to_add, $priority, $accepted_args);
	}

	/**
	 * Uses WordPress has_action() function, see WordPress has_action()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L492
	 */
	public function has_action($tag, $function_to_check = FALSE)
	{
		$tag = $this->_get_action_tag($tag);
		return has_action($tag, $function_to_check);
	}

	/**
	 * Uses WordPress remove_action() function, see WordPress remove_action()
	 *
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L513
	 */
	public function remove_action($tag, $function_to_remove, $priority = 10, $accepted_args = 1)
	{
		$tag = $this->_get_action_tag($tag);
		return remove_action($tag, $function_to_remove, $priority, $accepted_args);
	}

	/**
	 * Uses WordPress do_action() function, see WordPress do_action()
	 * @since	1.3
	 * @access	public
	 * @link	http://core.trac.wordpress.org/browser/trunk/wp-includes/plugin.php#L352
	 */
	public function do_action($tag, $arg = '')
	{
		$args = func_get_args();
		$args[0] = $this->_get_action_tag($tag);
		return call_user_func_array('do_action', $args);
	}

	/**
	 * Used to check if creating a new post or editing one
	 *
	 * @static
	 * @since	1.3.7
	 * @access	private
	 * @return	bool
	 * @see		_is_page()
	 */
	public static function _is_post()
	{
		if ('post' == WPAlchemy_MetaBox::_is_post_or_page())
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Used to check if creating a new page or editing one
	 *
	 * @static
	 * @since	1.3.7
	 * @access	private
	 * @return	bool
	 * @see		_is_post()
	 */
	public static function _is_page()
	{
		if ('page' == WPAlchemy_MetaBox::_is_post_or_page())
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Used to check if creating or editing a post or page
	 *
	 * @static
	 * @since	1.3.8
	 * @access	private
	 * @return	string "post" or "page"
	 * @see		_is_post(), _is_page()
	 */
	public static function _is_post_or_page()
	{
		$post_type = WPAlchemy_MetaBox::_get_current_post_type();

		if (isset($post_type))
		{
			if ('page' == $post_type)
			{
				return 'page';
			}
			else
			{
				return 'post';
			}
		}

		return NULL;
	}

	/**
	 * Used to check for the current post type, works when creating or editing a
	 * new post, page or custom post type.
	 *
	 * @static
	 * @since	1.4.6
	 * @return	string [custom_post_type], page or post
	 */
	public static function _get_current_post_type()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

		if ( isset( $uri ) )
		{
			$uri_parts = parse_url($uri);

			$file = basename($uri_parts['path']);

			if ($uri AND in_array($file, array('post.php', 'post-new.php')))
			{
				$post_id = WPAlchemy_MetaBox::_get_post_id();

				$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : NULL ;

				$post_type = $post_id ? get_post_type($post_id) : $post_type ;

				if (isset($post_type))
				{
					return $post_type;
				}
				else
				{
					// because of the 'post.php' and 'post-new.php' checks above, we can default to 'post'
					return 'post';
				}
			}
		}

		return NULL;
	}

	/**
	 * Used to get the current post id.
	 *
	 * @static
	 * @since	1.4.8
	 * @return	int post ID
	 */
	public static function _get_post_id()
	{
		global $post;

		$p_post_id = isset($_POST['post_ID']) ? $_POST['post_ID'] : null ;

		$g_post_id = isset($_GET['post']) ? $_GET['post'] : null ;

		$post_id = $g_post_id ? $g_post_id : $p_post_id ;

		$post_id = isset($post->ID) ? $post->ID : $post_id ;

		if (isset($post_id))
		{
			return (integer) $post_id;
		}

		return null;
	}

	/**
	 * @since	1.0
	 */
	public function can_output()
	{
		$post_id = WPAlchemy_MetaBox::_get_post_id();

		if (!empty($this->exclude_template) OR !empty($this->include_template))
		{
			$template_file = get_post_meta($post_id,'_wp_page_template',TRUE);
		}

		if
		(
			!empty($this->exclude_category) OR
			!empty($this->exclude_category_id) OR
			!empty($this->include_category) OR
			!empty($this->include_category_id)
		)
		{
			$categories = wp_get_post_categories($post_id,'fields=all');
		}

		if
		(
			!empty($this->exclude_tag) OR
			!empty($this->exclude_tag_id) OR
			!empty($this->include_tag) OR
			!empty($this->include_tag_id)
		)
		{
			$tags = wp_get_post_tags($post_id);
		}

		// processing order: "exclude" then "include"
		// processing order: "template" then "category" then "post"

		$can_output = TRUE; // include all

		if
		(
			!empty($this->exclude_template) OR
			!empty($this->exclude_category_id) OR
			!empty($this->exclude_category) OR
			!empty($this->exclude_tag_id) OR
			!empty($this->exclude_tag) OR
			!empty($this->exclude_post_id) OR
			!empty($this->include_template) OR
			!empty($this->include_category_id) OR
			!empty($this->include_category) OR
			!empty($this->include_tag_id) OR
			!empty($this->include_tag) OR
			!empty($this->include_post_id)
		)
		{
			if (!empty($this->exclude_template))
			{
				if (in_array($template_file,$this->exclude_template))
				{
					$can_output = FALSE;
				}
			}

			if (!empty($this->exclude_category_id))
			{
				foreach ($categories as $cat)
				{
					if (in_array($cat->term_id,$this->exclude_category_id))
					{
						$can_output = FALSE;
						break;
					}
				}
			}

			if (!empty($this->exclude_category))
			{
				foreach ($categories as $cat)
				{
					if
					(
						in_array($cat->slug,$this->exclude_category) OR
						in_array($cat->name,$this->exclude_category)
					)
					{
						$can_output = FALSE;
						break;
					}
				}
			}

			if (!empty($this->exclude_tag_id))
			{
				foreach ($tags as $tag)
				{
					if (in_array($tag->term_id,$this->exclude_tag_id))
					{
						$can_output = FALSE;
						break;
					}
				}
			}

			if (!empty($this->exclude_tag))
			{
				foreach ($tags as $tag)
				{
					if
					(
						in_array($tag->slug,$this->exclude_tag) OR
						in_array($tag->name,$this->exclude_tag)
					)
					{
						$can_output = FALSE;
						break;
					}
				}
			}

			if (!empty($this->exclude_post_id))
			{
				if (in_array($post_id,$this->exclude_post_id))
				{
					$can_output = FALSE;
				}
			}

			// excludes are not set use "include only" mode

			if
			(
				empty($this->exclude_template) AND
				empty($this->exclude_category_id) AND
				empty($this->exclude_category) AND
				empty($this->exclude_tag_id) AND
				empty($this->exclude_tag) AND
				empty($this->exclude_post_id)
			)
			{
				$can_output = FALSE;
			}

			if (!empty($this->include_template))
			{
				if (in_array($template_file,$this->include_template))
				{
					$can_output = TRUE;
				}
			}

			if (!empty($this->include_category_id))
			{
				foreach ($categories as $cat)
				{
					if (in_array($cat->term_id,$this->include_category_id))
					{
						$can_output = TRUE;
						break;
					}
				}
			}

			if (!empty($this->include_category))
			{
				foreach ($categories as $cat)
				{
					if
					(
						in_array($cat->slug,$this->include_category) OR
						in_array($cat->name,$this->include_category)
					)
					{
						$can_output = TRUE;
						break;
					}
				}
			}

			if (!empty($this->include_tag_id))
			{
				foreach ($tags as $tag)
				{
					if (in_array($tag->term_id,$this->include_tag_id))
					{
						$can_output = TRUE;
						break;
					}
				}
			}

			if (!empty($this->include_tag))
			{
				foreach ($tags as $tag)
				{
					if
					(
						in_array($tag->slug,$this->include_tag) OR
						in_array($tag->name,$this->include_tag)
					)
					{
						$can_output = TRUE;
						break;
					}
				}
			}

			if (!empty($this->include_post_id))
			{
				if (in_array($post_id,$this->include_post_id))
				{
					$can_output = TRUE;
				}
			}
		}

		$post_type = WPAlchemy_MetaBox::_get_current_post_type();

		if (isset($post_type) AND ! in_array($post_type, $this->types))
		{
			$can_output = FALSE;
		}

		// filter: output (can_output)
		if ($this->has_filter('output'))
		{
			$can_output = $this->apply_filters('output', $post_id);
		}

		return $can_output;
	}

	/**
	 * Used to insert global STYLE or SCRIPT tags into the head, called on
	 * WordPress admin_footer action.
	 *
	 * @static
	 * @since	1.3
	 * @access	private
	 * @see		_global_foot()
	 */
	public static function _global_head()
	{
		// must be creating or editing a post or page
		if ( ! WPAlchemy_MetaBox::_is_post() AND ! WPAlchemy_MetaBox::_is_page()) return;

		// LSJL CUSTOM: Start output buffering for minification.
		// Remove CDATA below. Change single line comment to multiline in JS.
		ob_start();

		// include javascript for special functionality
		?><style>.wpa_group.tocopy{display:none!important;}</style>
		<script>
		jQuery(function($) {
			var $copy_buttons = $('[class*=docopy-]');

			/* Do an initial limit check, show or hide buttons */
			$copy_buttons.each(function() {
				var $self = $(this),
				    name = $self.attr('class').match(/docopy-([a-zA-Z0-9_-]*)/i)[1];

				/* Wrap is only added if not in a metabox, see _setup */
				$self.parents('.postbox').addClass('wpalchemy_metabox');

				checkLoopLimit(name);
			});

			/* Delete buttons */
			$('#post').on('click', function(e) {
				var $elem = $(e.target),
				    $wrap, name, $group;

				if ($elem.attr('class') AND $elem.filter('[class*=dodelete]').length) {
					e.preventDefault();

					$wrap = $elem.parents('.wpalchemy_metabox');
					name = $elem.attr('class').match(/dodelete-([a-zA-Z0-9_-]*)/i);
					name = (name AND name[1]) ? name[1] : null ;

					if (confirm("<?php _e( 'This action can not be undone, are you sure?', 'lucid-toolbox' ); ?>")) {
						if (name) {
							$wrap.find('.wpa_group-'+ name).not('.tocopy').remove();
						} else {
							$elem.parents('.wpa_group').remove();
						}

						$group = $elem.parents('.wpa_group');

						if ($group AND $group.attr('class')) {
							name = $group.attr('class').match(/wpa_group-([a-zA-Z0-9_-]*)/i);
							name = (name AND name[1]) ? name[1] : null ;

							checkLoopLimit(name);
						}

						$.wpalchemy.trigger('wpa_delete');
					}
				}
			});

			$copy_buttons.on('click', function(e) {
				e.preventDefault();

				var $self = $(this),
				    $wrap = $self.parents('.wpalchemy_metabox'),
				    name = $self.attr('class').match(/docopy-([a-zA-Z0-9_-]*)/i)[1],
				    $group = $wrap.find('.wpa_group-'+ name +'.tocopy').first(),
				    $clone = $group.clone().removeClass('tocopy last'),
				    props = ['name', 'id', 'for', 'class'];

				$group.find('*').each(function(i, elem) {
					var j = 0,
					    len = props.length,
					    $elem = $(elem),
					    prop, match;

					for (j; j < len; j++) {
						prop = $elem.attr(props[j]);

						if (prop) {
							match = prop.match(/\[(\d+)\]/i);

							if (match) {
								prop = prop.replace(match[0],'['+ (+match[1] + 1) +']');

								$elem.attr(props[j], prop);
							}

							match = null;

							/* todo: this may prove to be too broad of a search */
							match = prop.match(/n(\d+)/i);

							if (match) {
								prop = prop.replace(match[0], 'n' + (+match[1] + 1));

								$elem.attr(props[j], prop);
							}
						}
					}
				});

				if ($self.hasClass('ontop')) {
					$wrap.find('.wpa_group-'+ name).first().before($clone);
				} else {
					$group.before($clone);
				}

				checkLoopLimit(name);

				$.wpalchemy.trigger('wpa_copy', [$clone]);
			});

			function checkLoopLimit(name) {
				var class_name = $('.wpa_loop-' + name).attr('class'),
				    match, limit, $elem;

				if (class_name) {
					match = class_name.match(/wpa_loop_limit-([0-9]*)/i);

					if (match) {
						limit = match[1];
						$elem = $('.docopy-' + name);

						if ($('.wpa_group-' + name).not('.wpa_group.tocopy').length >= limit) {
							$elem.hide();
						} else {
							$elem.show();
						}
					}
				}
			}
		});
		</script>
		<?php

		// LSJL CUSTOM: Removed CDATA above, slightly minify output
		$content = ob_get_clean();
		$content = str_replace( array( "\n", "\t" ), '', $content );
		echo preg_replace( '/[\s]+/', ' ', $content );
	}

	/**
	 * Used to insert global SCRIPT tags into the footer, called on WordPress
	 * admin_footer action.
	 *
	 * @static
	 * @since	1.3
	 * @access	private
	 * @see		_global_head()
	 */
	public static function _global_foot()
	{
		// must be creating or editing a post or page
		if ( ! WPAlchemy_MetaBox::_is_post() AND ! WPAlchemy_MetaBox::_is_page()) return;

		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		(function($){ /* not using jQuery ondomready, code runs right away in footer */

			/* use a global dom element to attach events to */
			$.wpalchemy = $('<div></div>').attr('id','wpalchemy').appendTo('body');

		})(jQuery);
		/* ]]> */
		</script>
		<?php
	}

	/**
	 * Gets the meta data for a meta box
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int $post_id optional post ID for which to retrieve the meta data
	 * @return	array
	 * @see		_meta
	 */
	public function the_meta($post_id = NULL)
	{
		return $this->_meta($post_id);
	}

	/**
	 * Gets the meta data for a meta box
	 *
	 * Internal method calls will typically bypass the data retrieval and will
	 * immediately return the current meta data
	 *
	 * @since	1.3
	 * @access	private
	 * @param	int $post_id optional post ID for which to retrieve the meta data
	 * @param	bool $internal optional boolean if internally calling
	 * @return	array
	 * @see		the_meta()
	 */
	public function _meta($post_id = NULL, $internal = FALSE)
	{
		if ( ! is_numeric($post_id))
		{
			if ($internal AND $this->current_post_id)
			{
				$post_id = $this->current_post_id;
			}
			else
			{
				global $post;

				$post_id = $post->ID;
			}
		}

		// this allows multiple internal calls to _meta() without having to fetch data everytime
		if ($internal AND !empty($this->meta) AND $this->current_post_id == $post_id) return $this->meta;

		$this->current_post_id = $post_id;

		// WPALCHEMY_MODE_ARRAY

		$meta = get_post_meta($post_id, $this->id, TRUE);

		// WPALCHEMY_MODE_EXTRACT

		$fields = get_post_meta($post_id, $this->id . '_fields', TRUE);

		if ( ! empty($fields) AND is_array($fields))
		{
			$meta = array();

			foreach ($fields as $field)
			{
				$field_noprefix = preg_replace('/^' . $this->prefix . '/i', '', $field);
				$meta[$field_noprefix] = get_post_meta($post_id, $field, TRUE);
			}
		}

		$this->meta = $meta;

		return $this->meta;
	}

	// user can also use the_ID(), php functions are case-insensitive
	/**
	 * @since	1.0
	 * @access	public
	 */
	public function the_id()
	{
		echo $this->get_the_id();
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function get_the_id()
	{
		return $this->id;
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function the_field($n, $hint = NULL)
	{
		if ($this->in_loop) $this->subname = $n;
		else $this->name = $n;

		$this->hint = $hint;
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function have_value($n = NULL)
	{
		if ($this->get_the_value($n)) return TRUE;

		return FALSE;
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function the_value($n = NULL)
	{
		$value = $this->get_the_value($n);

		if ($value) echo htmlentities($value, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function get_the_value($n = NULL, $collection = FALSE)
	{
		$this->_meta(NULL, TRUE);

		$value = null;

		if ($this->in_loop)
		{
			if(isset($this->meta[$this->name]))
			{
				$n = is_null($n) ? $this->subname : $n ;

				if(!is_null($n))
				{
					if ($collection)
					{
						if(isset($this->meta[$this->name][$this->current]))
						{
							$value = $this->meta[$this->name][$this->current];
						}
					}
					else
					{
						if(isset($this->meta[$this->name][$this->current][$n]))
						{
							$value = $this->meta[$this->name][$this->current][$n];
						}
					}
				}
				else
				{
					if ($collection)
					{
						if(isset($this->meta[$this->name]))
						{
							$value = $this->meta[$this->name];
						}
					}
					else
					{
						if(isset($this->meta[$this->name][$this->current]))
						{
							$value = $this->meta[$this->name][$this->current];
						}
					}
				}
			}
		}
		else
		{
			$n = is_null($n) ? $this->name : $n ;

			if(isset($this->meta[$n]))
			{
				$value = $this->meta[$n];
			}
		}

		if (is_string($value) || is_numeric($value))
		{
			if ($this->in_template)
			{
				return $value;
			}
			else
			{
				// http://wordpress.org/support/topic/call-function-called-by-embed-shortcode-direct
				// http://phpdoc.wordpress.org/trunk/WordPress/Embed/WP_Embed.html#run_shortcode

				global $wp_embed;

				return do_shortcode($wp_embed->run_shortcode($value));
			}
		}
		else
		{
			// value can sometimes be an array
			return $value;
		}
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function the_name($n = NULL)
	{
		echo $this->get_the_name($n);
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function get_the_name($n = NULL)
	{
		if (!$this->in_template AND $this->mode == WPALCHEMY_MODE_EXTRACT)
		{
			return $this->prefix . str_replace($this->prefix, '', is_null($n) ? $this->name : $n);
		}

		if ($this->in_loop)
		{
			$n = is_null($n) ? $this->subname : $n ;

			if (!is_null($n)) $the_field = $this->id . '[' . $this->name . '][' . $this->current . '][' . $n . ']' ;

			else $the_field = $this->id . '[' . $this->name . '][' . $this->current . ']' ;
		}
		else
		{
			$n = is_null($n) ? $this->name : $n ;

			$the_field = $this->id . '[' . $n . ']';
		}

		$hints = array
		(
			WPALCHEMY_FIELD_HINT_CHECKBOX_MULTI,
			WPALCHEMY_FIELD_HINT_SELECT_MULTI,
			WPALCHEMY_FIELD_HINT_SELECT_MULTIPLE,
		);

		if (in_array($this->hint, $hints))
		{
			$the_field .= '[]';
		}

		return $the_field;
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function the_index()
	{
		echo $this->get_the_index();
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function get_the_index()
	{
		return $this->in_loop ? $this->current : 0 ;
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function is_first()
	{
		if ($this->in_loop AND $this->current == 0) return TRUE;

		return FALSE;
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function is_last()
	{
		if ($this->in_loop AND ($this->current+1) == $this->length) return TRUE;

		return FALSE;
	}

	/**
	 * Used to check if a value is a match
	 *
	 * @since	1.1
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @return	bool
	 * @see		is_value()
	 */
	public function is_value($n, $v = NULL)
	{
		if (is_null($v))
		{
			$the_value = $this->get_the_value();

			$v = $n;
		}
		else
		{
			$the_value = $this->get_the_value($n);
		}

		if($v == $the_value) return TRUE;

		return FALSE;
	}

	/**
	 * Used to check if a value is selected, useful when working with checkbox,
	 * radio and select values.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @return	bool
	 * @see		is_value()
	 */
	public function is_selected($n, $v = NULL, $is_default = FALSE)
	{
		if (is_null($v))
		{
			$the_value = $this->get_the_value(NULL);

			$v = $n;
		}
		else
		{
			$the_value = $this->get_the_value($n);
		}

		if (is_array($the_value))
		{
			if (in_array($v, $the_value)) return TRUE;
		}
		elseif($v == $the_value)
		{
			return TRUE;
		}

			if(empty($the_value) && $is_default)
			{
				return TRUE;
			}

		return FALSE;
	}

	/**
	 * Prints the current state of a checkbox field and should be used inline
	 * within the INPUT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @see		get_the_checkbox_state()
	 */
	public function the_checkbox_state($n, $v = NULL, $is_default = FALSE)
	{
		echo $this->get_the_checkbox_state($n, $v, $is_default);
	}

	/**
	 * Returns the current state of a checkbox field, the returned string is
	 * suitable to be used inline within the INPUT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @return	string suitable to be used inline within the INPUT tag
	 * @see		the_checkbox_state()
	 */
	public function get_the_checkbox_state($n, $v = NULL, $is_default = FALSE)
	{
		if ($this->is_selected($n, $v, $is_default)) return ' checked="checked"';
	}

	/**
	 * Prints the current state of a radio field and should be used inline
	 * within the INPUT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @see		get_the_radio_state()
	 */
	public function the_radio_state($n, $v = NULL, $is_default = FALSE)
	{
		echo $this->get_the_checkbox_state($n, $v, $is_default);
	}

	/**
	 * Returns the current state of a radio field, the returned string is
	 * suitable to be used inline within the INPUT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @return	string suitable to be used inline within the INPUT tag
	 * @see		the_radio_state()
	 */
	public function get_the_radio_state($n, $v = NULL, $is_default = FALSE)
	{
		return $this->get_the_checkbox_state($n, $v, $is_default);
	}

	/**
	 * Prints the current state of a select field and should be used inline
	 * within the SELECT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @see		get_the_select_state()
	 */
	public function the_select_state($n, $v = NULL, $is_default = FALSE)
	{
		echo $this->get_the_select_state($n, $v, $is_default);
	}

	/**
	 * Returns the current state of a select field, the returned string is
	 * suitable to be used inline within the SELECT tag.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string $n the field name to check or the value to check for (if the_field() is used prior)
	 * @param	string $v optional the value to check for
	 * @return	string suitable to be used inline within the SELECT tag
	 * @see		the_select_state()
	 */
	public function get_the_select_state($n, $v = NULL, $is_default = FALSE)
	{
		if ($this->is_selected($n, $v, $is_default)) return ' selected="selected"';
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function the_group_open($t = 'div')
	{
		echo $this->get_the_group_open($t);
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function get_the_group_open($t = 'div')
	{
		$this->group_tag = $t;

		$loop_open = NULL;

		$loop_open_classes = array('wpa_loop', 'wpa_loop-' . $this->name);

		$css_class = array('wpa_group', 'wpa_group-'. $this->name);

		if ($this->is_first())
		{
			array_push($css_class, 'first');

			$loop_open = '<div class="wpa_loop">';

			if (isset($this->_loop_data->limit))
			{
				array_push($loop_open_classes, 'wpa_loop_limit-' . $this->_loop_data->limit);
			}

			$loop_open = '<div id="wpa_loop-'. $this->name .'" class="' . implode(' ', $loop_open_classes) . '">';
		}

		if ($this->is_last())
		{
			array_push($css_class, 'last');

			if ($this->in_loop == 'multi')
			{
				array_push($css_class, 'tocopy');
			}
		}

		return $loop_open . '<' . $t . ' class="'. implode(' ', $css_class) . '">';
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function the_group_close()
	{
		echo $this->get_the_group_close();
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function get_the_group_close()
	{
		$loop_close = NULL;

		if ($this->is_last())
		{
			$loop_close = '</div>';
		}

		return '</' . $this->group_tag . '>' . $loop_close;
	}

	/**
	 * @since	1.1
	 * @access	public
	 */
	public function have_fields_and_multi($n, $options = NULL)
	{
		if (is_array($options))
		{
			// use as stdClass object
			$options = (object)$options;

			$length = @$options->length;

			$this->_loop_data->limit = @$options->limit;
		}
		else
		{
			// backward compatibility (bc)
			$length = $options;
		}

		$this->_meta(NULL, TRUE);

		$this->in_loop = 'multi';

		return $this->_loop($n, $length, 2);
	}

	/**
	 * @deprecated
	 * @since	1.0
	 * @access	public
	 */
	public function have_fields_and_one($n)
	{
		$this->_meta(NULL, TRUE);
		$this->in_loop = 'single';
		return $this->_loop($n,NULL,1);
	}

	/**
	 * @since	1.0
	 * @access	public
	 */
	public function have_fields($n,$length=NULL)
	{
		$this->_meta(NULL, TRUE);
		$this->in_loop = 'normal';
		return $this->_loop($n,$length);
	}

	/**
	 * @since	1.0
	 * @access	private
	 */
	public function _loop($n,$length=NULL,$and_one=0)
	{
		if (!$this->in_loop)
		{
			$this->in_loop = TRUE;
		}

		$this->name = $n;

		$cnt = count(!empty($this->meta[$n])?$this->meta[$n]:NULL);

		$length = is_null($length) ? $cnt : $length ;

		if ($this->in_loop == 'multi' AND $cnt > $length) $length = $cnt;

		$this->length = $length;

		if ($this->in_template AND $and_one)
		{
			if ($length == 0)
			{
				$this->length = $and_one;
			}
			else
			{
				$this->length = $length+1;
			}
		}

		$this->current++;

		if ($this->current < $this->length)
		{
			$this->subname = NULL;

			$this->fieldtype = NULL;

			return TRUE;
		}
		else if ($this->current == $this->length)
		{
			$this->name = NULL;

			$this->current = -1;
		}

		$this->in_loop = FALSE;

		$this->_loop_data = new stdClass;

		return FALSE;
	}

	/**
	 * @since	1.0
	 * @access	private
	 */
	public function _save($post_id)
	{
		/**
		 * note: the "save_post" action fires for saving revisions and post/pages,
		 * when saving a post this function fires twice, once for a revision save,
		 * and again for the post/page save ... the $post_id is different for the
		 * revision save, this means that "get_post_meta()" will not work if trying
		 * to get values for a revision (as it has no post meta data)
		 * see http://alexking.org/blog/2008/09/06/wordpress-26x-duplicate-custom-field-issue
		 *
		 * why let the code run twice? wordpress does not currently save post meta
		 * data per revisions (I think it should, so users can do a complete revert),
		 * so in the case that this functionality changes, let it run twice
		 */

		$real_post_id = isset($_POST['post_ID']) ? $_POST['post_ID'] : NULL ;

		// check autosave
		if (defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE AND !$this->autosave) return $post_id;

		// make sure data came from our meta box, verify nonce
		$nonce = isset($_POST[$this->id.'_nonce']) ? $_POST[$this->id.'_nonce'] : NULL ;
		if (!wp_verify_nonce($nonce, $this->id)) return $post_id;

		// check user permissions
		if ($_POST['post_type'] == 'page')
		{
			if (!current_user_can('edit_page', $post_id)) return $post_id;
		}
		else
		{
			if (!current_user_can('edit_post', $post_id)) return $post_id;
		}

		// authentication passed, save data

		$new_data = isset( $_POST[$this->id] ) ? $_POST[$this->id] : NULL ;

		WPAlchemy_MetaBox::clean($new_data);

		if (empty($new_data))
		{
			$new_data = NULL;
		}

		// filter: save
		if ($this->has_filter('save'))
		{
			$new_data = $this->apply_filters('save', $new_data, $real_post_id);

			/**
			 * halt saving
			 * @since 1.3.4
			 */
			if (FALSE === $new_data) return $post_id;

			WPAlchemy_MetaBox::clean($new_data);
		}

		// get current fields, use $real_post_id (checked for in both modes)
		$current_fields = get_post_meta($real_post_id, $this->id . '_fields', TRUE);

		if ($this->mode == WPALCHEMY_MODE_EXTRACT)
		{
			$new_fields = array();

			if (is_array($new_data))
			{
				foreach ($new_data as $k => $v)
				{
					$field = $this->prefix . $k;

					array_push($new_fields,$field);

					$new_value = $new_data[$k];

					if (is_null($new_value))
					{
						delete_post_meta($post_id, $field);
					}
					else
					{
						update_post_meta($post_id, $field, $new_value);
					}
				}
			}

			$diff_fields = array_diff((array)$current_fields,$new_fields);

			if (is_array($diff_fields))
			{
				foreach ($diff_fields as $field)
				{
					delete_post_meta($post_id,$field);
				}
			}

			delete_post_meta($post_id, $this->id . '_fields');

			if ( ! empty($new_fields))
			{
				add_post_meta($post_id,$this->id . '_fields', $new_fields, TRUE);
			}

			// keep data tidy, delete values if previously using WPALCHEMY_MODE_ARRAY
			delete_post_meta($post_id, $this->id);
		}
		else
		{
			if (is_null($new_data))
			{
				delete_post_meta($post_id, $this->id);
			}
			else
			{
				update_post_meta($post_id, $this->id, $new_data);
			}

			// keep data tidy, delete values if previously using WPALCHEMY_MODE_EXTRACT
			if (is_array($current_fields))
			{
				foreach ($current_fields as $field)
				{
					delete_post_meta($post_id, $field);
				}

				delete_post_meta($post_id, $this->id . '_fields');
			}
		}

		// action: save
		if ($this->has_action('save'))
		{
			$this->do_action('save', $new_data, $real_post_id);
		}

		return $post_id;
	}

	/**
	 * Cleans an array, removing blank ('') values
	 *
	 * @static
	 * @since	1.0
	 * @access	public
	 * @param	array the array to clean (passed by reference)
	 */
	public function clean(&$arr)
	{
		if (is_array($arr))
		{
			foreach ($arr as $i => $v)
			{
				if (is_array($arr[$i]))
				{
					WPAlchemy_MetaBox::clean($arr[$i]);

					if (!count($arr[$i]))
					{
						unset($arr[$i]);
					}
				}
				else
				{
					if ('' == trim($arr[$i]) OR is_null($arr[$i]))
					{
						unset($arr[$i]);
					}
				}
			}

			if (!count($arr))
			{
				$arr = array();
			}
			else
			{
				$keys = array_keys($arr);

				$is_numeric = TRUE;

				foreach ($keys as $key)
				{
					if (!is_numeric($key))
					{
						$is_numeric = FALSE;
						break;
					}
				}

				if ($is_numeric)
				{
					$arr = array_values($arr);
				}
			}
		}
	}
}

/* eof */