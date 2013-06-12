<?php
class HeadwayAJAX {
	

	public static function init() {
		
		add_action('wp_ajax_headway_visual_editor', array(__CLASS__, 'ajax_visual_editor'));
		
	}
	
	
	public static function ajax_visual_editor() {
		
		Headway::load('visual-editor', true);
		
		HeadwayVisualEditor::ajax();
		
		die();
		
	}
	
	
}