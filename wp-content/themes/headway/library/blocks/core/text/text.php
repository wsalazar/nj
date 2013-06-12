<?php
headway_register_block('HeadwayTextBlock', headway_url() . '/library/blocks/core/text');

class HeadwayTextBlock extends HeadwayBlockAPI {
	
	
	public $id = 'text';
	
	public $name = 'Text';
	
	public $core_block = true;
	
	protected $show_content_in_grid = true;
	
	
	function content() {
				
		echo '<h1>This will be a text block.</h1>';
		
	}
	
	
}