<?php

if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;
 
class plugin_white_label_branding {
	var $id;
	var $plugin_page;
	var $menu;
	var $submenu;
	var $options=array();
	var $options_parameters=array();
	var $site_options=array();
	var $default_site_options=array();
	var $pop_menu_done =false;
	var $main_cap = false;
	function plugin_white_label_branding($args=array()){
		//------------
		$defaults = array(
			'id'					=> 'white-label-branding',
			'resources_path'		=> 'white-label-branding',
			'options_capability'	=> 'manage_options',
			'options_varname'		=> 'MWLB',
			'site_options_varname' 	=> 'MWLB_SETTINGS',
			'admin_menu'			=> true,
			'options_panel_version'	=> '2.0.3',
			'multisite'				=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//-----------		
		$this->default_site_options = array('blog_branding_type'=>'0','allow_blog_branding'=>'1');
		//-----------
		if($this->admin_menu)add_action('admin_menu',array(&$this,'admin_menu'));		
		add_action('plugins_loaded',array(&$this,'plugins_loaded'));
		add_action('init',array(&$this,'init'));
		//-----------
		$this->load_options();
		
		if(is_admin()){
			require_once WLB_PATH.'options-panel/load.pop.php';
			rh_register_php('options-panel',WLB_PATH.'options-panel/class.PluginOptionsPanelModule.php', $this->options_panel_version);
		}
	}
	
	function is_wlb_administrator(){		
		if( is_multisite() && !$this->is_wlb_network_admin() && '1'!=$this->get_site_option('allow_blog_branding') )return false;//on ms setups, this test controls if certain branding options apply to the subsite administrator.
		return WLB_ADMIN_ROLE==$this->get_user_role();
	}
	
	function load_options(){
		$this->options = get_option($this->options_varname);
		$this->options = is_array($this->options)?$this->options:array();
		//----
		if(function_exists('get_site_option')){
			$this->site_options = get_site_option( $this->site_options_varname, false );
			$this->site_options = is_array($this->site_options)?$this->site_options:$this->default_site_options;
		}
		do_action('wlb_options_loaded');
	}
	
	function get_option($name,$default=''){
		return isset($this->options[$name])?$this->options[$name]:$default;
	}
	
	function get_site_option($name,$default=''){
		return isset($this->site_options[$name])?$this->site_options[$name]:$default;
	}
	
	function get_user_role() {
		global $userdata;
		global $current_user;

		$user_roles = $current_user->roles;
		if(is_array($user_roles)&&count($user_roles)>0)
			$user_role = array_shift($user_roles);
		return @$user_role;
	}
	
	function init(){
		if(is_admin()):		
			wp_register_style( 'extracss-'.$this->id, WLB_URL.'css/wlb-pop.css', array(),'1.0.0');
		endif;
	}
		
	function plugins_loaded(){
		global $wp_version;
		if($wp_version<3.3){
			require_once WLB_PATH.'includes/class.prewp33_wlb_branding.php';
		}else{
			require_once WLB_PATH.'includes/class.wlb_branding.php';
		}	
		new wlb_branding();
		
		require_once WLB_PATH.'includes/class.wlb_login.php';
		new wlb_login();
		
		require_once WLB_PATH.'includes/class.wlb_color_scheme.php';
		new wlb_color_scheme();

		if(is_admin()):
			if($wp_version<3.3){
				require_once WLB_PATH.'includes/class.prewp33_wlb_dashboard.php';
			}else{
				require_once WLB_PATH.'includes/class.wlb_dashboard.php';
			}
			new wlb_dashboard(array(
				'show_ui'		=> ((1==$this->get_option('enable_wlb_dashboard'))?true:false),
				'show_in_menu'	=> $this->id,
				'menu_name'		=> __('Dashboard Tool','wlb')
			));	
		endif;
		
		require_once WLB_PATH.'includes/class.wlb_menu.php';
		new wlb_menu();
		
		require_once WLB_PATH.'includes/class.admin_menu_sort.php';
		new admin_menu_sort();
		
		require_once WLB_PATH.'includes/class.wlb_settings.php';
		new wlb_settings();
		
		if($wp_version<3.3){
			require_once WLB_PATH.'includes/class.wlb_admin_bar.prewp33.php';
		}else{
			require_once WLB_PATH.'includes/class.wlb_admin_bar.php';
		}
		new wlb_admin_bar();
		
		if(is_admin()):
			if(1==$this->get_option('enable_role_manager')){
				require_once WLB_PATH.'includes/class.wlb_capabilities.php';
				new wlb_capabilities();		
			}	
		endif;
			
		require_once WLB_PATH.'includes/class.wlb_screen_options.php';
		new wlb_screen_options();	
		
		if(is_admin()):
			$dc_options = array(
				'id'			=> $this->id.'-dc',
				'plugin_id'		=> $this->id,
				'capability'	=> 'wlb_downloads',
				'resources_path'=> $this->resources_path,
				'parent_id'		=> $this->id,
				'menu_text'		=> __('Downloads','wlb'),
				'page_title'	=> __('Downloadable content - White Label Branding for WordPress','wlb'),
				'license_keys'	=> $this->get_option('license_keys',array()),
				//'api_url'		=> 'http://dev.lawley.com/',
				'product_name'	=> __('White Label Branding','wlb'),
				'options_varname' => $this->options_varname,
				'tdom'			=> 'wlb'
			);
			
			$settings = array(				
				'id'					=> $this->id,
				'plugin_id'				=> $this->id,
				'capability'			=> $this->options_capability,
				'options_varname'		=> $this->options_varname,
				'menu_id'				=> $this->id,
				'page_title'			=> __('White Label Branding Options','wlb'),
				'menu_text'				=> __('White Label Branding','wlb'),
				'option_menu_parent'	=> $this->id,
				'notification'			=> (object)array(
					'plugin_version'=> WLB_VERSION,
					'plugin_code' 	=> WLB_PLUGIN_CODE,
					'message'		=> __('White Label Branding update %s is available!','wlb').' <a href="%s">'.__('Please update now','wlb').'</a>'
				),
				'theme'					=> false,
				'extracss'				=> 'extracss-'.$this->id,
				'rangeinput'			=> true,
				'fileuploader'			=> true,
				'dc_options'			=> $dc_options,
				'pluginurl'				=> WLB_URL,
				'tdom'					=> 'wlb'
			);	
			//require_once WLB_PATH.'options-panel/class.PluginOptionsPanelModule.php';	
			do_action('rh-php-commons');	
			//---------------
			$settings['id'] 		= $this->id.'-bra';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-bra','wlb_branding');//$this->id.'-bra';
			$settings['menu_text'] 	= __('Branding','wlb');
			$settings['import_export'] = true;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_branding';
			new PluginOptionsPanelModule($settings);
			
			$settings['id'] 		= $this->id.'-nav';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-nav','wlb_navigation');//$this->id.'-nav';
			$settings['menu_text'] 	= __('Navigation','wlb');
			$settings['import_export'] = false;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_navigation';
			new PluginOptionsPanelModule($settings);
			
			$settings['id'] 		= $this->id.'-log';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-log','wlb_login');//$this->id.'-log';
			$settings['menu_text'] 	= __('Login','wlb');
			$settings['import_export'] = true;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_login';
			new PluginOptionsPanelModule($settings);
			
			$settings['id'] 		= $this->id.'-css';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-css','wlb_color_scheme');//$this->id.'-css';
			$settings['menu_text'] 	= __('Color Scheme','wlb');
			$settings['import_export'] = true;
			$settings['import_export_options'] =false;
			$settings['capability'] = 'wlb_color_scheme';
			new PluginOptionsPanelModule($settings);
			
			$settings['id'] 		= $this->id.'-opt';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-opt','wlb_options');//$this->id.'-opt';
			$settings['menu_text'] 	= __('Options','wlb');
			$settings['import_export'] = true;
			$settings['import_export_options'] = true;
			$settings['capability'] = 'wlb_options';
			//$settings['bundles'] = true; Not really needed. TODO for next release.
			new PluginOptionsPanelModule($settings);
			//$settings['bundles'] = false;
			
			if(1==$this->get_option('enable_role_manager')){
				$settings['id'] 		= $this->id.'-cap';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-cap','wlb_role_manager');//$this->id.'-cap';
				$settings['menu_text'] 	= __('Role Manager','wlb');
				$settings['import_export'] = false;
				$settings['registration'] = false;
				$settings['import_export_options'] = false;
				$settings['capability'] = 'wlb_role_manager';
				new PluginOptionsPanelModule($settings);
			}			
			
			$settings['id'] 		= $this->id.'-reg';
			$settings['menu_id'] 	= $this->get_pop_menu_id('-reg','wlb_license');//$this->id.'-reg';
			$settings['menu_text'] 	= __('License','wlb');
			$settings['import_export'] = false;
			$settings['registration'] = true;
			$settings['downloadables'] = true;
			$settings['capability'] = 'wlb_license';
			new PluginOptionsPanelModule($settings);
					
		endif;
	}
	
	function get_pop_menu_id($suffix,$capability){
		if(1==$this->get_option('enable_wlb_dashboard'))$this->pop_menu_done =true;
		if($this->pop_menu_done)return $this->id.$suffix;
		if(current_user_can($capability)){
			$this->main_cap = $capability;
			$this->pop_menu_done =true;
			return $this->id;
		}
		return $this->id.$suffix;
	}
	
	function is_wlb_network_admin(){
		return ( $this->multisite && function_exists('is_super_admin')&&function_exists('is_multisite') && is_super_admin() && is_multisite() );
	}
	
	function admin_menu(){
		$capability = false===$this->main_cap?'wlb_dashboard_tool':$this->main_cap;
		add_menu_page( __("WLB Settings",'wlb'), __("WLB Settings",'wlb'), $capability, $this->id, null, WLB_URL.'images/wlb.png' );
	}
}
?>