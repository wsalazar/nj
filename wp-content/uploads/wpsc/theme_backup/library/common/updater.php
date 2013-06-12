<?php
class HeadwayUpdater {
	
	
	/**
	 * Used to store the update info so only one request to the Headway server is made.
	 **/
	public static $update_info = false;
	
	
	public static function init() {
		
		add_filter('http_request_args', array(__CLASS__, 'exclude_from_wp_extend_check'), 5, 2);
		
		add_action('admin_init', array(__CLASS__, 'check_for_update'), 12);
		add_action('admin_notices', array(__CLASS__, 'show_update_notice')); 
	
	}
	
	
	/**
	 * http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
	 **/
	public static function exclude_from_wp_extend_check($r, $url) {
		
		if ( strpos($url, 'http://api.wordpress.org/themes/update-check') !== 0 )
			return $r; // Not a theme update request. Bail immediately.
			
		$themes = unserialize($r['body']['themes']);
		
		unset($themes['headway']);

		$r['body']['themes'] = serialize($themes);
		
		return $r;
		
	}
	
	
	public static function get_update_info($force_request = false) {
		
		if ( self::$update_info !== false && !$force_request )
			return self::$update_info;
		
		$update_info_request = wp_remote_get(add_query_arg(array('beta' => 'true', 'version' => HEADWAY_VERSION), HEADWAY_UPDATER), array('timeout' => 2));
						
		if ( is_wp_error($update_info_request) ) 
			return false;
			
		$update_info = json_decode($update_info_request['body'], true);

		//Make sure that the response is a success
		if ( wp_remote_retrieve_response_code($update_info_request) !== 200 )
			return false;
			
		//Store the update info to the static property
		self::$update_info = $update_info;
		
		return self::$update_info;
		
	}
	
	
	public static function get_latest_version() {
		
		$check = self::get_update_info();
		
		//Make sure the JSON returned back is legitimate.
		if ( !is_array($check) || !isset($check['version']) )
			return false;
			
		return (string)$check['version'];
		
	}


	public static function is_update_available() {
		
		$latest_version = self::get_latest_version();
				
		if ( !is_string($latest_version) )
			return false;

		return version_compare($latest_version, HEADWAY_VERSION, '>');
	
	}
	
	
	public static function show_update_notice() {
				
		if ( !self::is_update_available() || HeadwayOption::get('disable-update-notices', false, false) )
			return;
			
		$update_info = self::get_update_info();
		$info_url = Headway::get('info-url', $update_info, false);
		$latest_version = $update_info['version'];
		
		$learn_more = $info_url ? ' or <a href="' . $info_url . '" target="_blank">learn more</a> about the update.' : null;
		
		echo '<div id="update-nag">Headway ' . $latest_version . ' is available, you\'re running ' . HEADWAY_VERSION . '! &nbsp;<a id="headway-update-notice-more-info" href="' . admin_url('update-core.php') . '">Click here to update</a>' . $learn_more . '</div>';
		
	}


	/**
	 * Do not confuse this with HeadwayUpdater::is_update_available()
	 * 
	 * This method is for updating the transient for the themes so the update will show up in WordPress' interface.
	 **/
	public static function check_for_update() {
		
		$update_themes_transient = get_site_transient('update_themes');
		
		//This has to be used in case the user has the headway folder not named 'headway' for some reason.
		$template_id = get_option('template');
				
		//If the update is available, set the latest version in the transient for Headway
		if ( self::is_update_available() ) {
			
			$update_info = self::get_update_info();
			
			$update_themes_transient->response[$template_id] = array(
				'new_version' => $update_info['version'],
				'url' => Headway::get('info-url', $update_info, 'http://headwaythemes.com'),
				'package' => $update_info['download-url']
			);
		
		//If there is no update available, but Headway is still in the transient, then remove it.	
		} elseif ( isset($update_themes_transient->response[$template_id]) ) {
						
			unset($update_themes_transient->response[$template_id]);
		
		//Nothing to do here.
		} else {
			
			return false;
			
		}
				
		return set_site_transient('update_themes', $update_themes_transient);
		
	}
	
	
}