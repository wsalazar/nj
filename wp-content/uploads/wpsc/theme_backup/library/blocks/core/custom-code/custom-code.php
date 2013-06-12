<?php
headway_register_block('HeadwayCustomCodeBlock', HEADWAY_URL . '/library/blocks/core/custom-code');

class HeadwayCustomCodeBlock extends HeadwayBlockAPI {
	
	
	public $id = 'custom-code';
	
	public $name = 'Custom Code';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayCustomCodeBlockOptions';
	
	
	function content($block) {
		
		$content = parent::get_setting($block, 'content');	
			
		if ( $content != null )
			echo headway_parse_php(do_shortcode(stripslashes($content)));
		else
			echo '<p>There is no custom code to display.</p>';
		
	}
	
	
}


class HeadwayCustomCodeBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'content' => 'Content'
	);

	public $inputs = array(
		'content' => array(
			'content' => array(
				'type' => 'textarea',
				'name' => 'content',
				'label' => 'Content',
				'default' => null
			)
		)
	);
	
}