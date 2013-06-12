<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class admin_menu_sort {
	var $id;
	var $menu_order = array();
	function admin_menu_sort(){
		global $wlb_plugin;
		$this->id = $wlb_plugin->id.'-nav';	
		add_filter("pop-options_{$this->id}",array(&$this,'wlb_options'),10,1);	
		add_action('pop_admin_head_'.$this->id,array(&$this,'admin_head'),10);		
		add_action('pop_handle_save',array(&$this,'pop_handle_save'),50,1);
		//--- 
		if( '1'==$wlb_plugin->get_option('use_admin_sort_menu',false) ){
			add_filter('menu_order',array(&$this,'menu_order'),10,1);
			add_filter('custom_menu_order',create_function('','return true;'));		
		}
	}
	
	function pop_handle_save($pop){
		global $wlb_plugin;
		if($wlb_plugin->options_varname!=$pop->options_varname)return;
		if(isset($_REQUEST['admin_menu_order'])&&is_array($_REQUEST['admin_menu_order'])){
			$existing_options = get_option($pop->options_varname);
			$existing_options = is_array($existing_options)?$existing_options:array();
			$existing_options['admin_menu_order'] = $_REQUEST['admin_menu_order'];
			update_option($pop->options_varname,$existing_options);		
		}
	}
	
	function wlb_options($t){
		$i = count($t);
		//-----
		$i++;
		@$t[$i]->id 			= 'admin_sort_menu'; 
		$t[$i]->label 		= __('Sort Admin Menu','wlb');//title on tab
		$t[$i]->right_label	= __('Sort Admin Menu','wlb');//title on tab
		$t[$i]->page_title	= __('Sort Admin Main Menu','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Sort wp-admin main menu','wlb')	
			),
			(object)array(
				'id'		=> 'use_admin_sort_menu',
				'label'		=> __('Enable custom admin menu order.','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Choose yes if you want to customize the order of the WordPress admin main menu.','wlb'),
				'el_properties'	=> array(),
				'hidegroup'	=> '#admin_sort_menu_group',
				'save_option'=>true,
				'load_option'=>true
			),			
			(object)array(
				'type'=>'description',
				'label'=>__('Customize the WordPress admin menu.  Drag and drop the main menu item to their new custom position.','wlb')
			),
			(object)array(
				'type'=>'clear'
			),
			(object)array(
				'id'	=> 'admin_sort_menu_group',
				'type'=>'div_start'
			),
			(object)array('label'=>__('Save Changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' ),
			(object)array(
				'type'=>'clear'
			),
			(object)array('description'=>__('Drag and drop a menu item to its new custom positions.  After reorganizing the menu items click "Save Changes"','wlb'),'type'=>'description'),
			(object)array(
				'id'=>'admin_menu_sortable',
				'type'=>'callback',
				'callback'=>array(&$this,'pop_menu_sort')
			),	
			(object)array('type'=>'div_end'),
			(object)array('label'=>__('Save Changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' )								
		);		
		return $t;
	}
	
	function admin_head(){
		wp_print_scripts( 'jquery-ui-sortable' );	
?>
<script type='text/javascript'>
jQuery(document).ready(function($){ 
	$('#admin_menu_sortable').sortable({
		placeholder:'sortable-placeholder',
		revert:true
	});
});
</script>
<?php	
	}
	
	function pop_menu_sort($tab,$i,$o,$save_fields){
		global $menu,$wlb_plugin;
		$admin_menu = $menu;
//echo "<PRE>";
//print_r($menu);
//echo "</PRE>";	
		$this->default_menu_order = array();
		foreach ( $menu as $menu_item ) {
			$this->default_menu_order[] = $menu_item[2];
		}	
		$this->menu_order = $wlb_plugin->get_option('admin_menu_order',false);
		//debugging:$this->menu_order = array("white-label-branding","separator1","index.php","edit.php","edit.php?post_type=echelp","upload.php","link-manager.php","edit.php?post_type=page","edit-comments.php","edit.php?post_type=book","edit.php?post_type=background","separator2","themes.php","plugins.php","users.php","tools.php","options-general.php","separator-last","edit.php?post_type=csshortcode");		
		if(is_array($this->menu_order)&&count($this->menu_order)>0){
			$this->menu_order = array_flip($this->menu_order);
			usort($admin_menu, array(&$this,'sort_menu') );
		}
		//---
		echo sprintf('<div id="%s" class="pt-option pop-menu-order-cont">',$o->id);
		foreach($admin_menu as $i => $item){
			echo sprintf('<div id="pop-menu-order-item-%s" class="pop-menu-order-item menu-item-handle %s"><span class="item-title">%s</span><div class="wlb-menu-order-item-right-icon"></div><input type="hidden" name="admin_menu_order[]" value="%s" /></div>', $i, $item[4], ($item[0]==''?$item[2]:$item[0]) ,$item[2]);
		}
		echo '</div>';
	}
	
	function sort_menu($a, $b) {
		//from the wordpress core
		$menu_order 			= $this->menu_order; 
		$default_menu_order		= $this->default_menu_order;
		$a = $a[2];
		$b = $b[2];
		if ( isset($menu_order[$a]) && !isset($menu_order[$b]) ) {
			return -1;
		} elseif ( !isset($menu_order[$a]) && isset($menu_order[$b]) ) {
			return 1;
		} elseif ( isset($menu_order[$a]) && isset($menu_order[$b]) ) {
			if ( $menu_order[$a] == $menu_order[$b] )
				return 0;
			return ($menu_order[$a] < $menu_order[$b]) ? -1 : 1;
		} else {
			return ($default_menu_order[$a] <= $default_menu_order[$b]) ? -1 : 1;
		}
	}
	
	function admin_footer(){

	}
	
	function menu_order($menu_order){
		global $wlb_plugin;
		$menu_order = $wlb_plugin->get_option('admin_menu_order',array());
		return $menu_order;
	}
}

?>