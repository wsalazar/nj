<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
if(!class_exists('PluginOptionsPanelModule')):

class PluginOptionsPanelModule {
	var $id;
	var $capability;
	var $page_title;
	var $menu_text;
	var $option_menu_parent;
	var $notification;
	var $description_rowspan=0;
	var $version = '2.0.2';
	var $rangeinput;
	var $colorpicker;
	var $registration = true;
	var $tdom;
	var $path;
	var $url;
	var $uid;
	function PluginOptionsPanelModule($args=array()){
		$defaults = array(
			'id'					=> 'plugin-options-panel',
			'tdom'					=> 'pop',			
			'section'				=> null,
			'capability'			=> 'manage_pop',
			'options_varname'		=> 'pop_options',
			'menu_id'				=> 'pop-options',
			'page_title'			=> __('Plugin Options Panel','pop'),
			'menu_text'				=> __('Plugin Options Panel','pop'),
			'option_menu_parent'	=> 'options-general.php',
			'notification'			=> (object)array(
				'plugin_version'=> '1.0.0',
				'plugin_code' 	=> 'POP',
				'message'		=> __('Plugin update %s is available!','pop').'<a href="%s">'.__('Please update now','pop').'</a>'
			),
			'theme'			=> false,
			'registration'	=> false,
			'downloadables'	=> false,
			'import_export'	=> false,
			'stylesheet'	=> 'default-pop-options',
			'extracss'		=> false,
			'colorpicker'	=> 'jquery-colorpicker',
			'rangeinput'	=> true,
			'farbtastic'	=> true,
			'fileuploader'	=> false,
			'arrowslider'	=> false,
			'dc_options'	=> array(),
			'import_export_options' => true,
			'bundles'=>false,
			'classes'		=> array(
				'panel_head'=>'sidebar-name',
				'panel_body'=>'inside'
			),
			'pluginurl'		=> ''
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
		$this->path = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);
		//---
		add_action('admin_menu',array(&$this,'admin_menu'));
		add_action('init',array(&$this,'init'));
		add_action('init',array(&$this,'pop_handle_save'));
		add_action('wp_ajax_notifications-'.$this->id, array(&$this,'pop_notifications'));
		add_action('wp_ajax_pop_uploader-'.$this->id, array(&$this,'pop_uploader'));
		//---
		add_filter('pop-existing-options_'.$this->id,array(&$this,'filter_loaded_options'),10,1);
		//---
		if(false!==$this->bundles){
			if(!class_exists('pop_bundles')) require_once 'class.pop_bundles.php';
			new pop_bundles(array('plugin_id'=>$this->id,'open'=>false,'tdom'=>'pop','plugin_code'=>$this->notification->plugin_code,'options_varname'=>$this->options_varname));	
		}
		if($this->registration){
			if(!class_exists('pop_plugin_registration')) require_once 'class.plugin_registration.php';
			new pop_plugin_registration(array('plugin_id'=>$this->id,'open'=>false,'tdom'=>'pop','plugin_code'=>$this->notification->plugin_code,'options_varname'=>$this->options_varname));		
		}
		
		if($this->import_export){
			if(!class_exists('pop_import_export')) require_once 'class.pop_import_export.php';
			new pop_import_export(array('plugin_id'=>$this->id,'tdom'=>'pop','plugin_code'=>$this->notification->plugin_code,'options_varname'=>$this->options_varname,'capability'=>$this->capability,'load_options'=>$this->import_export_options));
		}
		
		if($this->downloadables){
			if(!class_exists('pop_downloadable_content')) require_once 'class.pop_downloadable_content.php';
			new pop_downloadable_content($this->dc_options);		
		}
	}
	
	function init(){
		if(is_admin()){
			wp_register_style( $this->stylesheet, $this->url.'css/pop.css', array(),'1.0.0');	
			wp_register_script( 'pop', $this->url.'js/pop.js', array(),'1.0.0');	
			//--
			if($this->rangeinput)wp_register_script( 'pop-rangeinput', $this->url.'js/rangeinput.js', array(),'1.2.5');	
			if($this->arrowslider){
				wp_register_script( 'jquery-arrowslider', $this->url.'js/arrowslider.js', array(),'1.0.0');
				wp_register_style( 'jquery-arrowslider', $this->url.'css/arrowslider.css', array(),'1.0.0');
			}	
			if(false!==$this->colorpicker){
				wp_register_style( 'jquery-colorpicker', $this->url.'colorpicker/css/colorpicker.css', array(),'1.0.0');
				wp_register_script( 'jquery-colorpicker', $this->url.'colorpicker/js/colorpicker.js', array(),'1.0.0');	
			}	
			if(false!==$this->fileuploader){
				wp_register_style( 'valums-fileuploader', $this->url.'fileuploader/fileuploader.css', array(),'1.0.0');
				wp_register_script( 'valums-fileuploader', $this->url.'fileuploader/fileuploader.js', array(),'1.0.0');	
			}
			//--
			global $wp_scripts;
			if ( is_a($wp_scripts, 'WP_Scripts') && !isset($wp_scripts->registered['farbtastic']) ){
				$this->farbtastic = false;//disable farbtastic if the script is not registered.
			}			
		}
	}
	
	function pop_handle_save(){
		if(!isset($_POST[$this->id.'_options']))
			return;
		
		if(!current_user_can($this->capability))
			return;
		
		if(isset($_POST['pop_preview'])){
			return;
		}
			
		$options = explode(',', stripslashes( $_POST[ 'page_'.$this->id ] ));
		if ( $options ) {
			$existing_options = $this->get_options();
			foreach ( $options as $option ) {
				$option = trim($option);
				$value = null;
				if ( isset($_POST[$option]) )
					$value = $_POST[$option];
				if ( !is_array($value) ) $value = trim($value);
				$value = stripslashes_deep($value);
				$existing_options[$option]=$value;
			}		
			update_option($this->options_varname, $existing_options);
		}

		do_action('pop_handle_save',$this);
		//------------------------------	
		$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
		/*if(isset($_REQUEST['tabs_selected_tab'])&&$_REQUEST['tabs_selected_tab']!=''){
			$goback = add_query_arg( 'tabs_selected_tab', $_REQUEST['tabs_selected_tab'], $goback );
		}*/
		$goback = add_query_arg( 'pop_open_tabs', (isset($_REQUEST['pop_open_tabs'])?$_REQUEST['pop_open_tabs']:''), $goback );
		wp_redirect( $goback );
	}
	
	
	function admin_menu(){
		$page_id = add_submenu_page( $this->option_menu_parent,$this->page_title ,$this->menu_text,$this->capability,$this->menu_id,array(&$this,'body'));
		add_action( 'admin_head-'. $page_id, array(&$this,'head') );
	}
	//admin_enqueue_scripts
	function head(){
		wp_print_styles( $this->stylesheet );
		wp_print_scripts( 'pop' );
		if(false!==$this->extracss)wp_print_styles($this->extracss);
		wp_print_scripts( 'jquery-ui-tabs' );
		if($this->rangeinput)wp_print_scripts( 'pop-rangeinput');
		if(false!==$this->arrowslider){
			wp_print_styles( 'jquery-arrowslider' );
			wp_print_scripts( 'jquery-arrowslider' );		
		}
		if(false!==$this->colorpicker){
			wp_print_styles( $this->colorpicker );
			wp_print_scripts( $this->colorpicker );	
		}
		if(false!==$this->farbtastic){
			wp_print_styles( 'farbtastic' );
			wp_print_scripts( 'farbtastic' );	
		}
		if(false!==$this->fileuploader){
			wp_print_styles( 'valums-fileuploader' );
			wp_print_scripts( 'valums-fileuploader' );	
		}
?>
  <script>
 jQuery(document).ready(function($){ 
 	$("#pop-options-cont .option-title").unbind('click').click(function(e){
		$(this).toggleClass('open').next().slideToggle();
	});
 
 	$("#btn-open-all").click(function(){
		$('#pop-options-cont .option-title').addClass('open').next().slideDown();
	});
	
	$('#pop-options-form').submit(function(){
		var pop_open_tabs = [];
		$('.toggle-option h3.open').each(function(i,inp){
			pop_open_tabs.push($(inp).parent().attr('id'));
		});
		$('#pop_open_tabs').val(pop_open_tabs.join(','));
	});
	
	if(location.hash){
		var sel = '#'+location.hash.slice(1)+'.toggle-option .option-title';
		if($(sel).length>0){
			$(sel).click();
		}
	}
		
	var args = {
		action: 'notifications-<?php echo $this->id ?>'
	};	
	$.post(ajaxurl,args,function(data){
		if(data.R=='OK'){
			$('#notifications-<?php echo $this->id ?>').html(data.MSG);	
		}
	},'json');		
<?php if($this->rangeinput): ?>	
	/* range input fields */
	$('.pop_rangeinput').pop_rangeinput();
	$('.pt-option-range .handle').mousedown(function(e){$(this).parent().parent().find('.pop_rangeinput').focus();});
<?php endif; ?>
<?php if($this->arrowslider): ?>	
	$('.arrow-slider').ArrowSlider();
<?php endif; ?>
	function get_contrast_font_color(hex,color1,color2){
		function giveHex(s){
			s=s.toUpperCase();
			return parseInt(s,16);
		}	
		if(hex.length<6||hex.length>6){
			return color1;
		}
		r=giveHex(hex.slice(0,2));
		g=giveHex(hex.slice(2,4));
		b=giveHex(hex.slice(4,6));
		if ( ((r*299 + g*587 + b*114) / 1000) > 125) {
		    return color1;
		} else {
		    return color2;
		}
	}
<?php if(false!==$this->colorpicker):?>	 
	$('.pop-colorpicker').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex).change();
			$(el).ColorPickerHide();
			//$(el).css('background-color', '#'+hex );
			//$(el).css('color', '#'+get_contrast_font_color(hex,'000000','ffffff') );
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	}).bind('keyup', function(e){
		$(this).ColorPickerSetColor(this.value);
		 if (e.keyCode == 27) { $(this).ColorPickerHide(); }
	}).bind('change',function(e){
		$(this).css('background-color', '#'+this.value );
		$(this).css('color', '#'+get_contrast_font_color(this.value,'000000','ffffff') );	
	}).change();
<?php endif;?>	
<?php if(false!==$this->farbtastic):?>
	$('.pop-farbtastic').each(function(i,o){
		$(this).farbtastic($(this).attr('rel')).hide();
	});	
	$('.farbtastic-choosecolor').click(function(e){
		var helper = $(this).parent().find('.pop-farbtastic');
		if(helper.is(':visible')){
			helper.slideUp();
			$(this).addClass('show-colorpicker').removeClass('hide-colorpicker');
		}else{
			helper.slideDown();
			$(this).addClass('hide-colorpicker').removeClass('show-colorpicker');
		}
		var tmp = $(this).attr('rel');
		$(this).attr('rel',$(this).attr('title'));
		$(this).attr('title',tmp);		
	});
	$('.farbtastic-choosecolor').mousedown(function(e){$(this).parent().find('input').trigger('focus');});
<?php endif;?>	
<?php if(false!==$this->fileuploader):?>
	var pop_uploader = new Array();
	$('.pop-fileuploader').each(function(i,inp){
		var id = $(inp).attr('rel');
		pop_uploader[pop_uploader.length] = new qq.FileUploader({
		    element: $(inp)[0],
		    action: ajaxurl,
			params: {
				action: 'pop_uploader-<?php echo $this->id ?>',
				id: id
			},
			onSubmit: function(_id, fileName){
				$(id+'_msg').addClass('loading');
			},
			onComplete: function(_id, fileName, responseJSON){
				if(responseJSON.success){					
					$(responseJSON.id).val(responseJSON.url).trigger('change');
				}
				$(id+'_msg').removeClass('loading');
			},
			onProgress: function(){
			
			},
			showMessage: function(message){
				$(id + '_msg').html(message);
			}
		});		
	});
	$('.pop-input-fileuploader').change(function(e){
		var sel =  $(this).attr('rel');
		var src = $(this).val();
		var dcurl_sel = '#' + $(this).attr('id') + '_dcurl';
		$(sel).html('');
		if(src!=''){
		 src = src.replace('{pluginurl}','<?php echo $this->pluginurl?>');
		 src = src.replace('{dcurl}', $(dcurl_sel).val() );
		 $(sel).append( $('<img />').attr('src',src ).addClass('pop-uploader-preview') );
		}
	}).trigger('change');
<?php endif;?>	
	//console.log( $('.pop_groupcontrol:checked').length );
	$('.pop_groupcontrol:checked').trigger('click');
	$('select.pop_groupcontrol').trigger('change');
	
	$('.pop-preview-item').each(function(i,inp){
		var item = this;
		var arr = $(this).attr('rel').split("|");
		var sel = arr[0];
		if(arr[0]!='')$(arr[0]).focus(function(e){$(item).parent().find('.pop-preview-item').hide();$(item).show();});
	});
	$('.pop-preview-items .pop-preview-item:first-child').show();
 });	
</script>
<?php

		add_action( 'admin_notices', array(&$this,'pop_update_notice') );	
		do_action('pop_admin_head_'.$this->id);		
	}
	
	function pop_update_notice(){
		echo sprintf("<div id=\"notifications-%s\"></div>",$this->id);
	}
	
	function pop_notifications(){
		$url = sprintf('http://plugins.righthere.com/?rh_latest_version=%s&site_url=%s&license_key=%s',$this->notification->plugin_code,urlencode(site_url('/')),urlencode($this->get_license_key()));
		if($this->theme){$url.="&theme=1";}
		
		if(!class_exists('righthere_service'))require_once 'class.righthere_service.php';
		$rh = new righthere_service();
		$r = $rh->rh_service($url);		
		
		if(false!==$r){
			if(!$this->theme){
				if(is_object($r)&&property_exists($r,'version')){
					if($r->version>$this->notification->plugin_version){
						$message = sprintf("<div class=\"updated fade\"><p><strong>%s</strong></p></div>",$this->notification->message);
						$response = (object)array(
							'R'		=> 'OK',
							'MSG'	=> sprintf($message,$r->version,$r->url)
						);
						die(json_encode($response));
					}else{
						die(json_encode((object)array('R'=>'ERR','MSG'=> __('Plugin is latest version.','pop') )));
					}
				}else{
					die(json_encode((object)array('R'=>'ERR','MSG'=> __('Invalid response format.','pop') )));
				}			
			}
		}
		die(json_encode((object)array('R'=>'ERR','MSG'=> __('Notification service is not available.','pop') )));
	}
	
	function get_saved_options(){
		$var = $this->options_varname.'_saved';
		$options = get_option($var);
		return is_array($options)?$options:array();
	}	
	
	function filter_loaded_options($options){
		if(isset($_REQUEST['pop_preview'])){
			$index = intval($_REQUEST['pop_preview']);
			$saved = $this->get_saved_options();
			$saved = array_reverse($saved);
			if(isset($saved[$index]) && is_object($saved[$index]) && property_exists($saved[$index],'options') && is_array($saved[$index]->options) && count($saved[$index]->options)>0){
				foreach($saved[$index]->options as $option_name => $option_value){
					$options[$option_name]=$option_value;
				}
			}	
		}
		return $options;
	}
	
	function body(){
		if(!class_exists('pop_input'))require_once 'class.pop_input.php';
		$pop_input = new pop_input(array('farbtastic'=>$this->farbtastic));
		$options = apply_filters('pop-options_'.$this->id,array(),$this->section);
		$existing_options = $this->get_options();
		$existing_options = is_array($existing_options)?$existing_options:array();		
		$existing_options = apply_filters('pop-existing-options_'.$this->id,$existing_options);	
?>
<div class="wrap">
<?php screen_icon('options-general'); ?>
<h2><?Php echo $this->menu_text?></h2>
<?php echo isset($_REQUEST['updated'])?'<div class="updated">'.__('Options updated.','pop').'</div>':'' ?>
<div id="sys_msg"></div>
<div id="pop-options-cont" class="<?php echo do_action('pop-options-cont-class')?>">
<?php		
		if(count($options)>0){
			$open_tabs = isset($_REQUEST['pop_open_tabs'])?$_REQUEST['pop_open_tabs']:false;
			$open_tabs = false===$open_tabs?false:(''==trim($open_tabs)?array():explode(',',$open_tabs));
			$map_save_fields = array();
			$save_fields = array();
			echo "<form id=\"pop-options-form\" enctype=\"multipart/form-data\" method=\"post\" action=\"\">";
			echo '<input type="hidden" id="pop_plugin_id" name="pop_plugin_id" value="'.$this->id.'" />';
			echo '<input type="hidden" name="'.$this->id.'_options" value="1" />';
			//echo '<input type="hidden" id="tabs_selected_tab" name="tabs_selected_tab" value="" />';
			echo sprintf('<input type="hidden" id="pop_open_tabs" name="pop_open_tabs" value="%s" />',isset($_REQUEST['pop_open_tabs'])?$_REQUEST['pop_open_tabs']:'');
			wp_nonce_field($this->id);
			foreach($options as $tab){
				if(false===$open_tabs){
					$open = property_exists($tab,'open')&&$tab->open?'open':'';
				}else{
					$open = in_array($tab->id,$open_tabs)?'open':'';
				}
				$starting_save_fields = $save_fields;
				$tab->theme_option = property_exists($tab,'theme_option')? $tab->theme_option : true;
				$tab->plugin_option = property_exists($tab,'plugin_option')? $tab->plugin_option : true;
				if($this->theme&&!$tab->theme_option){
					continue;
				}else if(!$tab->plugin_option){
					continue;
				}
				
				echo sprintf("<div id=\"%s\" class=\"toggle-option\">",$tab->id);
				echo sprintf("<h3 class=\"option-title %s %s\"><span class=\"pop-option-title-icon\"></span><span class=\"pop-option-title\">%s</span><span class=\"pop-right\">%s</span></h3>", $open, (isset($this->classes['panel_head'])?$this->classes['panel_head']:''),$tab->label, @$tab->right_label );
				echo sprintf("<div class=\"option-content %s %s\">",$open,(isset($this->classes['panel_body'])?$this->classes['panel_body']:''));
				if(count($tab->options)>0){
					foreach($tab->options as $i => $o){			
						$o->theme_option = property_exists($o,'theme_option')? $o->theme_option : true;
						$o->plugin_option = property_exists($o,'plugin_option')? $o->plugin_option : true;					
						if($this->theme&&!$o->theme_option){
							continue;
						}else if(!$o->plugin_option){
							continue;
						}
						//----------
						$method = "_".$o->type;
						if(!method_exists($pop_input,$method))
							continue;
							
						if(true===@$o->load_option){
							$option_varname = $pop_input->get_option_name($tab,$i,$o);
							$o->value = isset($existing_options[$option_varname])?$existing_options[$option_varname]:(property_exists($o,'default')?$o->default:'');
						}
						
						echo $pop_input->translucent_description(@$o->description);
						if(in_array($o->type,array('description',))){
						
						}else if(in_array($o->type,array('callback','div_start','div_end','preview'))){
							$o->existing_options = $existing_options;
							echo $pop_input->$method($tab,$i,$o,$save_fields);
						}else{	
							$class = property_exists($o,'ptclass')?$o->ptclass:'';
							echo sprintf("<div class=\"pt-option pt-option-%s %s\">",$o->type,$class);
							if(in_array($o->type,array('checkbox'))){
								echo sprintf("%s",$pop_input->$method($tab,$i,$o,$save_fields));	
								echo sprintf("<span class=\"pt-checkbox-label\">%s</span>",$o->label);
							}else{
								if(property_exists($o,'label')&&!in_array($o->type,array('label','subtitle','hr','submit','range','textarea'))){
									echo sprintf("<span class=\"pt-label pt-type-%s\">%s</span>",$o->type,$o->label);	
								}
								echo sprintf("%s",$pop_input->$method($tab,$i,$o,$save_fields));						
							}
							//------------
							//echo "<div class=\"pt-clear\"></div>";
							echo "</div>";//close pt-option						
						}
					}
				}
				echo "</div>";//close option-content
				echo "</div>";//close toggle-option
				$added_save_fields = array_diff($save_fields,$starting_save_fields);
				$map_save_fields[$tab->id]=$added_save_fields;
			}
			
			echo "<div class=\"bottom-controls\">";
			echo "<input id=\"btn-open-all\" class=\"button-secondary\" type=\"button\" value=\"".__('Open all','pop')."\" />";
			echo $pop_input->_submit(null,null,(object)array('class'=>'button-primary','label'=>__('Save all','pop')));
			echo "</div>";
			
			echo '<input type="hidden" name="action" value="update" />';
			echo sprintf('<input type="hidden" name="page_%s" value="%s" />',$this->id,$this->get_save_fields($save_fields));
			
			do_action('pop_main_controls');
			do_action('pop_main_controls_'.$this->id);
			
			echo "</form>";
		}
?>
</div>
</div>
<?php	
		global $rh_php_commons;
		echo sprintf("<div class=\"pop-version\"><i>%s (%s)</i></div>",
			sprintf(__('Options panel version %s','pop'),
				isset($rh_php_commons['options-panel']['included'])?$rh_php_commons['options-panel']['included']->version:$this->version
				),
			$this->id
		);
		
		do_action('pop_body_'.$this->id);
	}
	
	function get_save_fields($save_fields){
		if(is_array($save_fields)&&count($save_fields)>0){
			$tmp = array();
			foreach($save_fields as $field){
				if(in_array($field,$tmp))continue;
				$tmp[]=str_replace("[]",'',$field);
			}
			return implode(",",$tmp);
		}else{
			return '';
		}
	}
	
	function get_options(){
		$options = get_option($this->options_varname);
		return is_array($options)?$options:array();
	}
	
	function get_option($name){
		$options = $this->get_options();
		return isset($options[$name])?$options[$name]:'';
	}
	
	function get_license_key(){
		$licenses = $this->get_option('license_keys');
		if(is_array($licenses)&&count($licenses)>0){
			foreach($licenses as $license){
				if(in_array($license->item_type,array('plugin','theme'))){
					return $license->license_key;
				}
			}
		}
		$license_key = $this->get_option('license_key');
		if(trim($license_key)!=''){
			return $license_key;
		}
		return '';
	}
	
	function pop_uploader(){
		require_once('class.pop_uploader.php');
		$allowedExtensions = array();
		$sizeLimit = 10 * 1024 * 1024;
		$uploader = new pop_qqFileUploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload();
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);		
		die();
	}		
}
endif;
?>