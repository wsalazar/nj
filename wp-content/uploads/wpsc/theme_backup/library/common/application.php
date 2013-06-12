<?php
/**
 * All of the global functions to be used everywhere in Headway.
 *
 * @package Headway
 * @author Clay Griffiths
 **/

class Headway {
	
	
	public static $loaded_classes = array();
	
	
	/**
	 * Let's get Headway on the road!  We'll define constants here, run the setup function and do a few other fun things.
	 * 
	 * @return void
	 **/
	public static function init() {
				
		//Define simple constants
		define('SITE', strtolower(str_replace(' ', '-', preg_replace("/[^A-Za-z0-9 ]/", '', get_bloginfo('name')))));
		define('THEME_FRAMEWORK', 'headway');
		
		define('HEADWAY_VERSION', '3.1.2');

		define('HEADWAY_TRACKER', false);
		define('HEADWAY_UPDATER', 'http://headwaythemes.com/updater/check/');

		//Define directories
		define('HEADWAY_ROOT', self::change_to_unix_path(TEMPLATEPATH));
		define('HEADWAY_LIBRARY', self::change_to_unix_path(HEADWAY_ROOT . '/library'));
				
		define('HEADWAY_URL', get_template_directory_uri());

		//Handle child themes
		if ( get_template_directory_uri() !== get_stylesheet_directory_uri() ) {
			
			define('HEADWAY_CHILD_THEME_ACTIVE', true);
			define('HEADWAY_CHILD_THEME_URL', get_stylesheet_directory_uri());
			
		} else {
			
			define('HEADWAY_CHILD_THEME_ACTIVE', false);
			define('HEADWAY_CHILD_THEME_URL', null);
			
		}
	
		//Handle uploads directory and cache
		$uploads = wp_upload_dir();
		
		define('HEADWAY_UPLOADS', self::change_to_unix_path($uploads['basedir'] . '/headway'));		
		define('HEADWAY_CACHE', self::change_to_unix_path(HEADWAY_UPLOADS . '/cache'));
		
		define('HEADWAY_UPLOADS_URL', self::format_url_ssl(self::change_to_unix_path($uploads['baseurl'] . '/headway')));		
		define('HEADWAY_CACHE_URL', self::change_to_unix_path(HEADWAY_UPLOADS_URL . '/cache'));

		//Load locale
		load_theme_textdomain('headway', self::change_to_unix_path(HEADWAY_LIBRARY . '/languages'));
		
		//Make directories if they don't exist
		if ( !is_dir(HEADWAY_UPLOADS) )
			wp_mkdir_p(HEADWAY_UPLOADS);
			
		if ( !is_dir(HEADWAY_CACHE) )
			wp_mkdir_p(HEADWAY_CACHE);
			
		//Add support for WordPress features
		add_action('after_setup_theme', array(__CLASS__, 'add_theme_support'), 1);
		add_action('after_setup_theme', array(__CLASS__, 'child_theme_setup'), 2);
				
		//Setup
		add_action('after_setup_theme', array(__CLASS__, 'load_dependencies'), 3);
		add_action('after_setup_theme', array(__CLASS__, 'do_update'), 4);
								
	}
	
	
	/**
	 * Loads all of the required core classes and initiates them.
	 * 
	 * Dependency array setup: class (string) => init (bool)
	 **/
	public static function load_dependencies() {
						
		//Load route right away so we can optimize dependency loading below
		Headway::load(array('common/route' => true));		
						
		//Core loading set
		$dependencies = array(
			'defaults/default-design-settings',
			
			'common/functions',
		  	'common/layout',
			'common/capabilities' => true,
			'common/grid' => true,
			'common/responsive-grid' => true,
			'common/seo' => true,
			'common/social-optimization' => true,
			'common/feed' => true,
			'common/fonts',
						
			'admin/admin-bar' => true,		
						
			'data/data-options',
			'data/data-layout-options',
			'data/data-blocks',
			
			'api/api-panel',
				
			'blocks' => true,			
			'elements' => true,
			
			'compiler',
			
			'display' => true,
			
			'widgets' => true
		);
		
		//Child theme API
		if ( HEADWAY_CHILD_THEME_ACTIVE === true )
			$dependencies['api/api-child-theme'] = 'ChildThemeAPI';
		
		//Visual editor classes
		if ( HeadwayRoute::is_visual_editor() || (defined('DOING_AJAX') && DOING_AJAX) )
			$dependencies['visual-editor'] = true;

		//Admin classes
		if ( is_admin() ) {
			$dependencies['admin'] = true;
			$dependencies['common/updater'] = true;
		}
			
		//Load stuff now
		Headway::load(apply_filters('headway_dependencies', $dependencies));
		
		do_action('headway_setup');

	}
	
	
	/**
	 * Tell WordPress that Headway supports its features.
	 **/
	public static function add_theme_support() {
				
		/* Headway Functionality */
		add_theme_support('headway-grid');
		add_theme_support('headway-responsive-grid');
		add_theme_support('headway-design-editor');
		
		/* Headway CSS */
		add_theme_support('headway-live-css');
		add_theme_support('headway-block-basics-css');
		add_theme_support('headway-dynamic-block-css');
		add_theme_support('headway-content-styling-css');
				
		/* WordPress Functionality */
		add_theme_support('post-thumbnails');		
		add_theme_support('menus');
		add_theme_support('widgets');
		add_theme_support('editor-style');
		
		/* Loop Standard by PluginBuddy */
		require_once HEADWAY_LIBRARY . '/resources/dynamic-loop.php';
		add_theme_support('loop-standard');
				
	}
	
	
	public static function child_theme_setup() {
		
		if ( !HEADWAY_CHILD_THEME_ACTIVE )
			return false;
			
		do_action('headway_setup_child_theme');
		
	}
	
	
	/**
	 * This will process upgrades from one version to another.
	 **/
	public static function do_update() {
		
		$headway_settings = get_option('headway', array('version' => 0));
		$db_version = $headway_settings['version'];
		
		/* If the version in the database is already up to date, then there are no upgrade functions to be ran. */
		if ( version_compare($db_version, HEADWAY_VERSION, '>=') )
			return false;
			
		Headway::load('common/maintenance');
		
		return HeadwayMaintenance::do_update($db_version);
		
	}
	
	
	/**
	 * Here's our function to load classes and files when needed from the library.
	 **/
	public static function load($classes, $init = false) {
		
		//Build in support to either use array or a string
		if ( !is_array($classes) ) {
			$load[$classes] = $init;
		} else {
			$load = $classes;
		}
		
		$classes_to_init = array();
		
		//Remove already loaded classes from the array
		foreach(Headway::$loaded_classes as $class) {
			unset($load[$class]);
		}
				
		foreach($load as $file => $init){
			
			//Check if only value is used instead of both key and value pair
			if ( is_numeric($file) ){
				$file = $init;
				$init = false;
			} 
						
			//Handle anything with .php or a full path
			if ( strpos($file, '.php') !== false ) 
				require_once HEADWAY_LIBRARY . '/' . $file;
				
			//Handle main-helpers such as admin, data, etc.
			elseif ( strpos($file, '/') === false )
				require_once HEADWAY_LIBRARY . '/' . $file . '/' . $file . '.php';
				
			//Handle anything and automatically insert .php if need be
			elseif ( strpos($file, '/') !== false )
				require_once HEADWAY_LIBRARY . '/' . $file . '.php';
				
			//Add the class to the main variable so we know that it has been loaded
			Headway::$loaded_classes[] = $file;
			
			//Set up init, if init is true, just figure out the class name from filename.  If argument is string, use that.
			if ( $init === true ) {
				
				$class = array_reverse(explode('/', str_replace('.php', '', $file)));
				
				//Check for hyphens/underscores and CamelCase it
				$class = str_replace(' ', '', ucwords(str_replace('-', ' ', str_replace('_', ' ', $class[0]))));
				
				$classes_to_init[] = $class;
				
			} else if ( is_string($init) ) {
				
				$classes_to_init[] = $init;
				
			}
			
		}	
		
		//Init everything after dependencies have been loaded
		foreach($classes_to_init as $class){
			
			if ( method_exists('Headway' . $class, 'init') ) {
				
				call_user_func(array('Headway' . $class, 'init'));
				
			} else {
				
				trigger_error('Headway' . $class . '::init is not a valid method', E_USER_WARNING);
				
			}
			
		}
		
	}

	
	/**
	 * Starts the GZIP output buffer.
	 *
	 * @return bool
	 **/
	public static function start_gzip() {
		
		//Allow gzip to be canceled by plugins
		$gzip = apply_filters('headway_gzip', HeadwayOption::get('gzip'));
		
		//If zlib is not loaded, we can't gzip.
		if ( !extension_loaded('zlib') )
			return false;

		//Allow headway_gzip filter to cancel gzip compression.
		if ( $gzip == false )
			return false;

		//If W3 Total Cache or WP Super Cache are running, do not gzip.
		if ( HeadwayCompiler::is_plugin_caching() )
			return false;

		//If AIOSEOP is running, do not gzip.
		if( class_exists('All_in_One_SEO_Pack') )
			return false;
		
		if ( $gzip === true ) {
			
			ob_start('ob_gzhandler');
			
			return true;
			
		} 
				
		return false;
		
	}
	
	
	/**
	 * A simple function to retrieve a key/value pair from the $_GET array or any other user-specified array.  This will automatically return false if the key is not set.
	 * 
	 * @param string Key to retrieve
	 * @param array Optional array to retrieve from.  Default is $_GET
	 * 
	 * @return mixed
	 **/
	public static function get($name, $array = false, $default = null) {
		
		if ( $array === false )
			$array = $_GET;
		
		if ( isset($array[$name]) )
			return $array[$name];
			
		return $default;	
			
	}
	
	
	/**
	 * Extension of Headway::get().  Use this to fetch a key/value pair from the $_POST array.
	 * 
	 * @uses Headway::get()
	 * 
	 * @param string Key to retrieve
	 * 
	 * @return mixed
	 **/
	public static function post($name, $default = null) {
		
		return self::get($name, $_POST, $default);
		
	}
	
	
	public static function format_url_ssl($url) {
		
		if ( !is_ssl() )
			return $url;
			
		return str_replace('http://', 'https://', $url);
		
	}
	
	
	/**
	 * Retrieves the current URL.
	 *
	 * @return string
	 **/
	public static function get_current_url() {

		$prefix = Headway::get('HTTPS', $_SERVER) != 'on' ? 'http://' : 'https://';
		$http_host = !isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_FORWARDED_HOST'];

		return $prefix . $http_host . $_SERVER['REQUEST_URI'];

	}
	
	
	public static function change_to_unix_path($path) {
		
		return str_replace('\\', '/', $path);
		
	}
	
	
	public static function fix_data_type($data) {
		
		if ( is_numeric($data) ) {
			
			return (int)$data;
			
		} elseif ( $data === 'true' ) {
			
		 	return true;
			
		} elseif ( $data === 'false' ) {
			
		 	return false;
			
		} elseif ( $data === '' ) {
			
			return null;
			
		} else {

			$data = maybe_unserialize($data);
			
			if ( !is_array($data) ) {
				return stripslashes($data);
				
			//If it's an array, run this function across all of the nodes in the array.
			} else {
				
				return array_map('maybe_unserialize', $data);
				
			}
			
		}
		
	}
	
	
}