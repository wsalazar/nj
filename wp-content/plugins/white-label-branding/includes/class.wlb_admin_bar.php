<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class wlb_admin_bar {
	var $admin_bar_nodes;
	function wlb_admin_bar(){
		global $wlb_plugin;
		$this->id = $wlb_plugin->id.'-nav';	
		add_filter("pop-options_{$this->id}",array(&$this,'wlb_options'),10,1);	
		add_action('pop_handle_save',array(&$this,'pop_handle_save'),50,1);
		//--
		add_action('wp_before_admin_bar_render',array(&$this,'store_nodes'),9);
		add_action('wp_before_admin_bar_render', array(&$this,'wp_before_admin_bar_render'), 10);
		add_action('admin_head',array(&$this,'admin_head'));
		add_action('init',array(&$this,'init'),9);
	}
		
	function store_nodes(){
		global $wp_admin_bar,$wp_version;
		if($wp_version<3.4)return;//not needed on 3.3
		$this->admin_bar_nodes = $wp_admin_bar->get_nodes();
	}	
		
	function flagged(){
		global $flagged;
		$flagged=true;
	}
		
	function init(){
		global $wlb_plugin;
		if(!function_exists('show_admin_bar'))
			return;
			
		if(!$wlb_plugin->is_wlb_administrator()){
			if(1==$wlb_plugin->get_option('hide_admin_bar')){
				show_admin_bar(false);
				remove_action('wp_head','_admin_bar_bump_cb');
				add_filter('show_admin_bar',create_function('$a','return false;'));
			}
		}
	}
	
	function admin_head(){
		global $wlb_plugin;
		if(!$wlb_plugin->is_wlb_administrator()){
?><style><?php
			if(1==$wlb_plugin->get_option('hide_admin_bar_profile')){
?>#profile-page .show-admin-bar {display:none !important;}<?php		
			}
			if(''!=$wlb_plugin->get_option('sm_profile_php')){
?>#wp-admin-bar-edit-profile {display:none;}#wp-admin-bar-my-account ul{min-height:84px;}}<?php			
			}
?></style><?php
		}
	}
	function wp_before_admin_bar_render(){
		global $wlb_plugin,$wp_admin_bar;
		//if(!$wlb_plugin->is_wlb_administrator()){
		if(true){//for simplicity, just hide the items for everybody.
			$hidden_ids = $wlb_plugin->get_option('wlb_hidden_admin_bar_ids');
			$hidden_ids = is_array($hidden_ids)&&count($hidden_ids)>0?$hidden_ids:false;
			if(false!==$hidden_ids){
				foreach($hidden_ids as $id){
					$wp_admin_bar->remove_menu( $id );
				}		
			}
			
			//--
			if('1'==$wlb_plugin->get_option('wlb_admin_bar_logout_in_root')){
				$logout_node = $wp_admin_bar->get_node('logout');
				$wp_admin_bar->remove_node('logout');
				
				$logout_node->parent = 'root';
				$logout_node->group = false;
				$logout_node->meta['class'] = 'ab-top-secondary';
				
				$wp_admin_bar->add_node($logout_node);			
			}
		}
	}
	function wlb_options($t){
		$i = count($t);
		
		$i++;
		@$t[$i]->id 			= 'admin_bar'; 
		$t[$i]->label 		= __('Admin Bar','wlb');//title on tab
		$t[$i]->right_label	= __('Admin Bar','wlb');//title on tab
		$t[$i]->page_title	= __('Admin Bar','wlb');//title on content
		$t[$i]->options = array();	
		
		$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=>__('Admin bar settings','wlb')	
			);

		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_admin_bar',
				'label'		=> __('Hide Admin Bar','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Select yes to completely remove the top Admin Bar (wp 3.1)','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	

		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_admin_bar_profile',
				'label'		=> __('Hide Admin Bar settings on profile','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Select yes to hide the admin bar settings on user profile.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	

		$t[$i]->options[] =	(object)array(
				'id'		=> 'wlb_admin_bar_logout_in_root',
				'label'		=> __('Move the logout link to the admin bar.','wlb'),
				'type'		=> 'yesno',
				'description'=> __('By default, the logout link is inside a submenu in the right of the admin bar.  Check yes if you want to move the logout link outside, if you for example want to hide the profile to the non-admin user.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	
		
		$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=>__('Hide admin bar items','wlb')	
			);
		
		$t[$i]->options[]=(object)array(
				'type'=>'callback',
				'callback'=>array(&$this,'output'),
				'description'=>__('Check individual Admin Bar items that you want to hide. Choosing a parent item will hide all children.  You most have the admin bar enabled to be able to use this option.','wlb')
			);
		$t[$i]->options[]=(object)array('type'=>'clear');
		$t[$i]->options[]=(object)array('label'=>__('Save Changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' );	
		return $t;
	}
	
	function pop_handle_save($pop){
		global $wlb_plugin;
		if($wlb_plugin->options_varname!=$pop->options_varname)return;
		if(!isset($_POST['wlb_save_admin_bar']))return;
		if(!current_user_can('wlb_navigation'))return;
		$existing_options = get_option($pop->options_varname);
		$existing_options = is_array($existing_options)?$existing_options:array();
		$existing_options['wlb_hidden_admin_bar_ids'] = (is_array($_POST['ADMINBAR'])&&count($_POST['ADMINBAR'])>0)? $_POST['ADMINBAR'] : array() ;
		update_option($pop->options_varname,$existing_options);		
	}
		
	function output($menu_item){
		global $wp_admin_bar,$wlb_plugin;

		if ( !is_object( $wp_admin_bar ) )
			return __('Admin bar items not available','wlb');
		
		if(method_exists($wp_admin_bar,'load_user_locale_translations'))$wp_admin_bar->load_user_locale_translations();
		do_action_ref_array( 'admin_bar_menu', array( &$wp_admin_bar ) );

		$hidden_ids = $wlb_plugin->get_option('wlb_hidden_admin_bar_ids');
		$hidden_ids = is_array($hidden_ids)?$hidden_ids:array();

		//--- build a tree -- like how the menu was built prewp33.
		
		$wp_admin_bar->get_nodes();
		if( !method_exists($wp_admin_bar,'get_nodes') ){
			return __('Please update your WordPress installation to a WP3.3+ release version. (wp_admin_bar::get_nodes is not defined, wp3.3 release version)','wlb');
		}
		
		$nodes = $this->build_nodes();				
		ob_start();
?>
			<div class="pt-option admin-quicklinks">
				<ul>
					<input type="hidden" name="wlb_save_admin_bar" value="1" />
					<?php foreach ( $nodes as $id => $menu_item ) : ?>
						<?php 	$this->recursive_render( $id, $menu_item, $hidden_ids ) ?>
					<?php endforeach; ?>
				</ul>
			</div>
<?php		
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
		
	}
	
	function get_label($menu_item){
		if($menu_item->id=='wp-logo')return __('WP Logo','wlb');
		if($menu_item->id=='updates')return __('Updates','wlb');
		if($menu_item->id=='comments')return __('Comments','wlb');
		$title = trim(strip_tags($menu_item->title));
		return ''==$title?$menu_item->id:$title;
	}
	
	function recursive_render( $id, &$menu_item, $hidden_ids ) { ?>
		<?php
		$is_parent =  ! empty( $menu_item->children );
		?>
		
		<li>
			<input type="checkbox" name="ADMINBAR[]" <?php echo in_array($id,$hidden_ids)?'checked="checked"':''?> value="<?php echo $id?>"/>
			<?php
			if ( $is_parent ) :
				?><span><?php
			endif;
	
			echo $this->get_label($menu_item);

			if ( $is_parent ) :
				?></span><?php
			endif;

			?>

			<?php if ( $is_parent ) : ?>
			<ul>
				<?php foreach ( $menu_item->children as $child_id => $child_menu_item ) : ?>
					<?php $this->recursive_render( $child_id, $child_menu_item, $hidden_ids ); ?>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php if ( ! empty( $menu_item->meta['html'] ) ) : ?>
				<?php echo $menu_item->meta['html']; ?>
			<?php endif; ?>
		</li><?php
	}	
	
	function build_nodes(){
		global $wp_admin_bar,$wp_version;
		$nodes = array();
		if($wp_version<3.4){
			$list  = $wp_admin_bar->get_nodes();		
		}else{
			$list = $this->admin_bar_nodes;
		}
		foreach($list as $id => $node){
			if(''!=trim($node->parent))continue;
			$nodes[$id] = $this->add_children($node,$list);
		}
		return $nodes;
	}
	
	function add_children($node,&$list){
		$node->children = array();
		$nodes = array();
		foreach($list as $id => $n){
			if($n->parent==$node->id){
				$node->children[$n->id] = $this->add_children($n,$list); 
			}
		}
		//--------	
		return $node;
	}
}

?>