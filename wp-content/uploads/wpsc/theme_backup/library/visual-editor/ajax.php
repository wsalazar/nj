<?php
class HeadwayVisualEditorAJAX {
	
	
	public static function ajax() {
		
		//Check the nonce of course
		check_ajax_referer('headway-visual-editor-ajax', 'security');
		
		//Make sure user can edit options or use the visual editor
		if ( !HeadwayCapabilities::can_user_visually_edit() )
			die(-1);
				
		//Call method if it exists		
		$method = str_replace('-', '_', Headway::post('method'));
		
		if ( method_exists(__CLASS__, 'method_' . $method) ) {
			
			return call_user_func(array(__CLASS__, 'method_' . $method));
			
		//Method doesn't exist
		} else {
			
			echo 'Method ' . $method . ' does not exist.';
			
			return false;
			
		}
				
	}

	
	/* Saving methods */
	public static function method_save_options() {
		
		$current_layout = Headway::post('layout');
		$mode = Headway::post('mode');
		
		//Set up options
		parse_str(Headway::post('options'), $options);
		
		//Handle triple slash bullshit
		if ( get_magic_quotes_gpc() === 1 )
			$options = array_map('stripslashes_deep', $options);
								
		$blocks = isset($options['blocks']) ? $options['blocks'] : null;
		$options_inputs = isset($options['options']) ? $options['options'] : null;
		$design_editor_inputs = isset($options['design-editor']) ? $options['design-editor'] : null;
		
		//Set the current layout to customized if it's the grid mode
		if ( $mode == 'grid' )
			HeadwayLayoutOption::set($current_layout, 'customized', true); 
						
		//Handle blocks
		if ( $blocks ) {
			
			foreach ( $blocks as $id => $methods ) {
			
				foreach ( $methods as $method => $value ) {
				
					switch ( $method ) {
					
						case 'new':
					
							if ( HeadwayBlocksData::get_block($id) )
								continue;
								
							$dimensions = explode(',', $blocks[$id]['dimensions']);	
							$position = explode(',', $blocks[$id]['position']);		
							
							$settings = isset($blocks[$id]['settings']) ? $blocks[$id]['settings'] : array();
								
							$args = array(
								'id' => $id,
								'type' => $value,
								'position' => array(
									'left' => $position[0],
									'top' => $position[1]
								),
								'dimensions' => array(
									'width' => $dimensions[0],
									'height' => $dimensions[1]
								),
								'settings' => $settings
							);
								
							HeadwayBlocksData::add_block($current_layout, $args);
					
						break;
					
						case 'delete':
					
							HeadwayBlocksData::delete_block($current_layout, $id);
					
						break;
					
						case 'dimensions':
						
							$dimensions = explode(',', $value);	
						
							$args = array(
								'dimensions' => array(
									'width' => $dimensions[0],
									'height' => $dimensions[1]
								)
							);
							
							HeadwayBlocksData::update_block($current_layout, $id, $args);
														
						break;
					
						case 'position':
						
							$position = explode(',', $value);	
						
							$args = array(
								'position' => array(
									'left' => $position[0],
									'top' => $position[1]
								)
							);
							
							HeadwayBlocksData::update_block($current_layout, $id, $args);
					
						break;
					
						case 'settings':
													
							//Retrieve all blocks from layout
							$layout_blocks = HeadwayBlocksData::get_blocks_by_layout($current_layout);
							
							//Get the block from the layout
							$block = Headway::get($id, $layout_blocks);
							
							//If block doesn't exist, we can't do anything.
							if ( !$block )
								continue;
								
							//If there aren't any options, then don't do anything either	
							if ( !is_array($value) || count($value) === 0 )
								continue;	
								
							$block['settings'] = array_merge($block['settings'], $value);
							
							HeadwayBlocksData::update_block($current_layout, $id, $block);
						
						break;
					
					}
				
				}
			
			}
			
		}
		
		//Handle options
		if ( $options_inputs ) {
			
			foreach ( $options_inputs as $group => $options ) {

				foreach ( $options as $option => $value ) {							
					HeadwayOption::set($option, $value, $group);
				}

			}
			
		}
		
		//Handle design editor inputs
		if ( $design_editor_inputs ) {			
			
			//Loop through to get every element and its properties
			foreach ( $design_editor_inputs as $element_id => $element_types ) {
								
				//Loop through element types and get the children meta
				foreach ( $element_types as $element_type => $element_type_metas ) {
					
					//Loop through the metas (ID of state, instance, etc)
					foreach ( $element_type_metas as $element_type_meta => $properties ) {
						
						//Go through properties now
						foreach ( $properties as $property_id => $value ) {
							
							//If the element_type is regular, then use the traditional method
							if ( $element_type == 'regular' )
								HeadwayElementsData::set_property($element_id, $property_id, $value);
							else
								HeadwayElementsData::set_special_element_property($element_id, $element_type, $element_type_meta, $property_id, $value);
							
						}
						
					}
					
				}
				
			}
			
		}
		
		//Let's flush the compiler cache just to make things easier
		HeadwayCompiler::flush_cache();
		
		//Allow plugins to perform functions upon visual editor save
		do_action('headway_visual_editor_save');
		
		echo 'success';
		
	}
	
	
	/* Block methods */
	public static function method_get_available_block_id() {

		$block_id_blacklist = Headway::post('block_id_blacklist', array());

		echo HeadwayBlocksData::get_available_block_id($block_id_blacklist);

	}


	public static function method_get_available_block_id_batch() {
		
		$block_id_blacklist = Headway::post('block_id_blacklist', array());
		$number_of_ids = Headway::post('number_of_ids', 10);
		
		if ( !is_numeric($number_of_ids) )
			$number_of_ids = 10;

		$block_ids = array();

		for ( $i = 1; $i <= $number_of_ids; $i++ ) {

			$available_block_id = HeadwayBlocksData::get_available_block_id($block_id_blacklist);

			$block_ids[] = $available_block_id;
			$block_id_blacklist[] = $available_block_id;

		}

		echo json_encode($block_ids);

	}
	
	
	public static function method_get_layout_blocks_in_json() {
		
		$layout = Headway::post('layout', false);
		$layout_status = HeadwayLayout::get_status($layout);
		
		if ( $layout_status['customized'] != true )
			return false;
		
		echo json_encode(HeadwayBlocksData::get_blocks_by_layout($layout));
		
	}
	
	
	public static function method_load_block_content() {
		
		$layout = Headway::post('layout');
		$block_origin = Headway::post('block_origin');
		$block_default = Headway::post('block_default', false);
		
		$unsaved_block_settings = Headway::post('unsaved_block_settings', false);
		
		/* If the block origin is a string or ID, then get the object from DB. */
		if ( is_numeric($block_origin) || is_string($block_origin) )
			$block = HeadwayBlocksData::get_block($block_origin);
			
		/* Otherwise use the object */
		else
			$block = $block_origin;
									
		/* If the block doesn't exist, then use the default as the origin.  If the default doesn't exist... We're screwed. */
		if ( !$block && $block_default )
			$block = $block_default;
						
		/* If the block settings is an array, merge that into the origin.  But first, make sure the settings exists for the origin. */
		if ( !isset($block['settings']) )
			$block['settings'] = array();
		
		if ( is_array($unsaved_block_settings) )
			$block = headway_array_merge_recursive_simple($block, $unsaved_block_settings);	
			
		/* If the block is set to mirror, then get that block. */
		if ( $mirrored_block = HeadwayBlocksData::is_block_mirrored($block) )
			$block = $mirrored_block;
					
		/* Add a flag into the block so we can check if this is coming from the visual editor. */
		$block['ve-live-content-query'] = true;
		
		/* Show the content */		
		do_action('headway_block_content_' . $block['type'], $block);
		
	}
	
	
	public static function method_load_block_options() {
		
		$layout = Headway::post('layout');
		$block_id = Headway::post('block_id');
	
		$block = HeadwayBlocksData::get_block($block_id);
		
		//If block is new, set the bare basics up
		if ( !$block ) {
			
			$block = array(
				'type' => Headway::post('block_type'),
				'new' => true,
				'id' => $block_id,
				'layout' => $layout
			);
			
		}
		
		do_action('headway_block_options_' . $block['type'], $block, $layout);
		
	}
	
	
	/* Box methods */
	public static function method_load_box_ajax_content() {
		
		$layout = Headway::post('layout');
		$box_id = Headway::post('box_id');
				
		do_action('headway_visual_editor_ajax_box_content_' . $box_id);
		
	}
	
	
	/* Layout methods */
	public static function method_get_layout_name() {
				
		$layout = Headway::post('layout');
		
		echo HeadwayLayout::get_name($layout);
		
	}
	
	
	public static function method_revert_layout() {
		
		$layout = Headway::post('layout_to_revert');
		
		//Delete the blocks
		HeadwayBlocksData::delete_by_layout($layout);
		
		//Remove the customized flag
		HeadwayLayoutOption::set($layout, 'customized', false);
		
		echo 'success';
		
	}


	/* Design editor methods */
	public static function method_get_element_groups() {
		
		foreach(HeadwayElementAPI::get_groups() as $id => $name) {
			
			echo '<li id="element-group-' . $id . '" class="has-children element-group"><span>' . $name . '</span></li>';
			
		}
		
	}
	
	
	public static function method_get_main_elements() {
		
		$group = Headway::post('group');
	
		$elements = HeadwayElementAPI::get_main_elements($group);
				
		foreach($elements as $id => $settings) {
			
			$classes = array();
			
			if ( is_array($settings['children']) && count($settings['children']) > 0 )
				$classes[] = 'has-children';
				
			$classes[] = 'main-element';
			
			echo '<li id="main-element-' . $id . '" class="' . implode(' ', $classes) . '"><span>' . $settings['name'] . '</span></li>';
			
		}
		
	}
	
	
	public static function method_get_sub_elements() {

		$element = Headway::post('element');
	
		$elements = HeadwayElementAPI::get_sub_elements($element);
				
		foreach($elements as $id => $settings) {
								
			echo '<li id="sub-element-' . $id . '" class="sub-element"><span>' . $settings['name'] . '</span></li>';
			
		}
		
	}
	
	
	public static function method_get_default_elements() {
		
		$elements = HeadwayElementAPI::get_default_elements();
				
		foreach($elements as $id => $settings) {
								
			echo '<li id="default-element-' . $id . '" class="default-element"><span>' . $settings['name'] . '</span></li>';
			
		}
		
	}
	
	
	public static function method_get_element_inputs() {
		
		$element = Headway::post('element');
		$special_element_type = Headway::post('specialElementType', false);
		$special_element_meta = Headway::post('specialElementMeta', false);
		
		$unsaved_values = Headway::post('unsavedValues', false);
		
		//Make sure that the library is loaded
		Headway::load('visual-editor/panels/design/element-inputs');
	
		//Display the appropriate inputs and values depending on the element
		HeadwayElementInputs::display($element, $special_element_type, $special_element_meta, $unsaved_values);
	
	}
	
	
	public static function method_get_element_instances() {
		
		$element = Headway::post('element');
		
		$instances = HeadwayElementAPI::get_instances($element);
				
		if ( !is_array($instances) )	
			return false;
		
		foreach ( $instances as $instance ) {
			
			//Get the layout so we can append that to the instance name
			$layout = (isset($instance['layout'])) ? '  &ndash;  ' . HeadwayLayout::get_name($instance['layout']) : null;
			
			echo '<option value="' . $instance['id'] . '">' . $instance['name'] . $layout . '</option>' . "\n";
			
		}
		
	}
	
	
	public static function method_get_element_states() {
		
		$element = Headway::post('element');
		
		$states = HeadwayElementAPI::get_states($element);
				
		if ( !is_array($states) )	
			return false;
		
		foreach ( $states as $name => $selector ) {
						
			$id = strtolower($name);		
						
			echo '<option value="' . $id . '">' . $name . '</option>' . "\n";
			
		}
		
	}
	
	
	public static function method_get_element_inherit_location_name() {
		
		$element = Headway::post('element');
		
		$inherit_location = HeadwayElementAPI::get_inherit_location($element);
		
		//Check if element has a default element set
		if ( $inherit_location !== null ) {
			
			$inherit_location = HeadwayElementAPI::get_element($inherit_location);
			
			echo $inherit_location['name'];
			
		}		
		
		//Return nothing if it doesn't
		return false;
		
	}
	

	/* Template methods */
	public static function method_add_template() {
		
		//Get the stuff from DB
		$templates = HeadwayOption::get('list', 'templates', array());
		$last_template_id = HeadwayOption::get('last-id', 'templates', 0);
		
		//Build name
		$id = $last_template_id + 1;
		$template_name = 'Template ' . $id;
		
		//Add to array
		$templates[$id] = $template_name;
		
		//Send array to DB
		HeadwayOption::set('list', $templates, 'templates');
		HeadwayOption::set('last-id', $id, 'templates');
		
		//Send the template ID back to JavaScript so it can be added to the list
		echo $id;
		
	}
	
	
	public static function method_delete_template() {
		
		//Retreive templates
		$templates = HeadwayOption::get('list', 'templates', array());
		
		//Unset the deleted ID
		$id = Headway::post('template_to_delete');
		
		//Delete template if it exists and send array back to DB
		if ( isset($templates[$id]) ) {
			
			unset($templates[$id]);
			
			//Delete the blocks from the template
			HeadwayBlocksData::delete_by_layout('template-' . $id);
			
			HeadwayOption::set('list', $templates, 'templates');
			
			echo 'success';
			
		} else {
			
			echo 'failure';
			
		}
		
	}
	
	
	public static function method_assign_template() {
		
		$layout = Headway::post('layout');
		$template = str_replace('template-', '', Headway::post('template'));
		
		//Add the template flag
		HeadwayLayoutOption::set($layout, 'template', $template);
		
		echo HeadwayLayout::get_name('template-' . $template);
		
	}
	
	
	public static function method_remove_template_from_layout() {
		
		$layout = Headway::post('layout');
		
		//Remove the template flag
		if ( !HeadwayLayoutOption::set($layout, 'template', false) ) {
			echo 'failure';
			
			return;
		}
		
		if ( HeadwayLayoutOption::get($layout, 'customized', false) === true ) {
			echo 'customized';
			
			return;
		}
			
		echo 'success';
		
	}
	
	
	/* Micellaneous methods */
	public static function method_clear_cache() {
		
		if ( HeadwayCompiler::flush_cache() )
			echo 'success';
		else
			echo 'failure';
		
	}

	
	public static function method_ran_tour() {
		
		HeadwayOption::set('ran-tour', true);
		
	}
	
	
	public static function method_change_grid_height() {
		
		$grid_height = Headway::post('grid_height');		
		
		//Make sure the grid height is numeric and at least 800px
		if ( !is_numeric($grid_height) || $grid_height < 800 )
			return false;
						
		HeadwayOption::set('grid-height', $grid_height);
		
	}
	
	
	public static function method_get_font_stack() {
		
		global $headway_fonts;
		
		$requested_font = Headway::post('font');
						
		echo isset($headway_fonts[$requested_font]) ? $headway_fonts[$requested_font] : null;
		
	}
	
	
}