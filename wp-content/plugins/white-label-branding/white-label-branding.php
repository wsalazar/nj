<?php

/**
Plugin Name: White Label Branding for WordPress
Plugin URI: http://plugins.righthere.com/white-label-branding/
Description: This plugin lets you take complete control over wp-admin. Add your own branding to WordPress. From customizing the login logo, footer logo in wp-admin to creating your own Login Templates and Color Schemes. Add your own Dashboard Panels viewable to all users, Editors or only Administrators. Remove the standard WordPress Dashboard Panels one-by-one and even custom Dashboard Panels added by installed plugins. Control the visibility of top level menu and sub-menus. Change the order of the top level menus. Hide update nag, Download link, Contextual Help, Screen Options, and hide the Administrator Role from the User List. Save your settings partially or complete, import and export settings. Enable advanced features like Dashboard Tool to add your own Dashboard Panels (visibility of these panels can be controlled with <a href="http://codecanyon.net/item/pages-by-user-role-for-wordpress/136020?ref=RightHere" target="_blank">Pages by User Role</a> plugin. Enable Role and Capability Manager lets you take complete control over user roles, create your own user roles and add custom capabilities. Enter the License Key (Item Purchase Code) and get access to Free Downloadable Content.
Version: 3.0.5 rev26174
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('WLB_VERSION','3.0.5');
define('WLB_PATH', plugin_dir_path(__FILE__) ); 
define("WLB_URL", plugin_dir_url(__FILE__) ); 
define("WLB_ADMIN_ROLE",'administrator');
define("WLB_ADMIN_CAP",'wlb_options');
define("WLB_PLUGIN_CODE",'WLB');
//---
define('WLB_SUBSITE_ADMINISTRATOR',WLB_ADMIN_ROLE); 

load_plugin_textdomain('wlb', null, dirname( plugin_basename( __FILE__ ) ).'/languages' );
if(is_admin()&&!defined('TDOM_POP')){define('TDOM_POP',true);load_plugin_textdomain('pop', null, dirname( plugin_basename( __FILE__ ) ).'/options-panel/languages' );}

require_once WLB_PATH.'includes/class.plugin_white_label_branding.php';

$settings = array(
	'options_capability' 	=> WLB_ADMIN_CAP,
	'options_panel_version'	=> '2.0.2'
);

global $wlb_plugin;
$wlb_plugin = new plugin_white_label_branding($settings);

//---register the starter bundle
//if(is_admin()){
//	add_filter( sprintf("%s_%s",$wlb_plugin->id.'-opt','bundles'), create_function('$t','$t[]=array("login_starter","'.__('Login starter','wlb').'","'.WLB_PATH.'bundles/login_starter.php'.'");return $t;'), 10, 1 );
//}

//-- Installation script:---------------------------------
function wlb_install(){
	$WP_Roles = new WP_Roles();	
	foreach(array(
		'wlb_branding',
		'wlb_navigation',
		'wlb_login',
		'wlb_color_scheme',
		'wlb_options',
		'wlb_role_manager',
		'wlb_license',
		'wlb_downloads',
		'wlb_dashboard_tool'
		) as $cap){
		$WP_Roles->add_cap( WLB_ADMIN_ROLE, $cap );
	}
	include WLB_PATH.'includes/install.php';
	if(function_exists('handle_wlb_install'))handle_wlb_install();	
}
register_activation_hook(__FILE__, 'wlb_install');
//-------------------------------------------------------- 
function wlb_uninstall(){
	$WP_Roles = new WP_Roles();
	foreach(array(
		'wlb_branding',
		'wlb_navigation',
		'wlb_login',
		'wlb_color_scheme',
		'wlb_options',
		'wlb_role_manager',
		'wlb_license',
		'wlb_downloads',
		'wlb_dashboard_tool'
		) as $cap){
		$WP_Roles->remove_cap( WLB_ADMIN_ROLE, $cap );
	}
	//-----
}
register_deactivation_hook( __FILE__, 'wlb_uninstall' );
//--------------------------------------------------------
?>