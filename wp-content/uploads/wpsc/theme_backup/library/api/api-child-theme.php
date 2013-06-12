<?php
class HeadwayChildThemeAPI {
	
	
	public static $id;
	
	
	public static $block_styles = array();
	
	
	public static function init() {
		
		//If the constant has been set for the child theme ID, use it.  Otherwise find it below.
		if ( defined('HEADWAY_CHILD_THEME_ID') )
			self::$id = HEADWAY_CHILD_THEME_ID;
			
		//Get the child theme ID from the theme directory
		else
			self::$id = strtolower(str_replace(' ', '-', trim(end(explode('/', HEADWAY_CHILD_THEME_URL)), '/ ')));
							
		self::$id = apply_filters('headway_child_theme_id', self::$id);
				
	}

	
	public static function register_block_style(array $args) {
				
		$defaults = array(
			'id' => null,
			'name' => null,
			'class' => null,
			'block-types' => 'all'
		);
		
		$block_style = array_merge($defaults, $args);
		
		//Add the block style to the main $block_styles property
		self::$block_styles[$block_style['id']] = $block_style;
		
		return true;
				
	}
	
	
	public static function get_block_style_classes() {
		
		$block_style_classes = array();
		
		foreach ( self::$block_styles as $block_style_id => $block_style )
			$block_style_classes[$block_style_id] = $block_style['class'];
			
		return $block_style_classes;
		
	}
		
	
}