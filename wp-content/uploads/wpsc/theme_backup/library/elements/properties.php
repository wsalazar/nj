<?php
class HeadwayElementProperties {
	
	
	protected static $properties = array(

			'font-family' => array(
				'group' => 'Fonts',
				'name' => 'Font Family',
				'type' => 'font-family-select',
				'js-callback' => 'propertyInputCallbackFontFamily(selector, value);'
			),

			'font-size' => array(
				'group' => 'Fonts',
				'name' => 'Font Size',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"font-size": value + "px"});',
				'unit' => 'px',
				'options' => 'HeadwayElementInputs::font_size_options()'
			),

			'color' => array(
				'group' => 'Fonts',
				'name' => 'Color',
				'type' => 'color',
				'js-callback' => 'stylesheet.update_rule(selector, {"color": value});',
				'default' => 'transparent'
			),

			'line-height' => array(
				'group' => 'Fonts',
				'name' => 'Line Height',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"line-height": value + "%"});',
				'unit' => '%',
				'options' => 'HeadwayElementInputs::line_height_options()'
			),

			'font-styling' => array(
				'group' => 'Fonts',
				'name' => 'Font Styling',
				'type' => 'select',
				'options' => array(
					'normal' => 'Normal',
					'bold' => 'Bold',
					'italic' => 'Italic',
					'bold-italic' => 'Bold Italic'
				),
				'js-callback' => 'propertyInputCallbackFontStyling(selector, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_font_styling'
			),
			
			'text-align' => array(
				'group' => 'Fonts',
				'name' => 'Alignment',
				'type' => 'select',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right'
				),
				'js-callback' => 'stylesheet.update_rule(selector, {"text-align": value});'
			),

			'capitalization' => array(
				'group' => 'Fonts',
				'name' => 'Capitalization',
				'type' => 'select',
				'options' => array(
					'none' => 'Normal',
					'uppercase' => 'Uppercase',
					'lowercase' => 'Lowercase',
					'small-caps' => 'Small Caps',
				),
				'js-callback' => 'propertyInputCallbackCapitalization(selector, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_capitalization'
			),
			
			'letter-spacing' => array(
				'group' => 'Fonts',
				'name' => 'Letter Spacing',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"letter-spacing": value + "px"});',
				'unit' => 'px',
				'options' => array(
					'0' => '0',
					'1' => '1px',
					'2' => '2px',
					'3' => '3px',
					'-1' => '-1px',
					'-2' => '-2px',
					'-3' => '-3px'
				)
			),
			
			'text-decoration' => array(
				'group' => 'Fonts',
				'name' => 'Underline',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"text-decoration": value});',
				'options' => array(
					'none' => 'No Underline',
					'underline' => 'Underline',
				)
			),

			'background-color' => array(
				'group' => 'Background',
				'name' => 'Color',
				'type' => 'color',
				'js-callback' => 'stylesheet.update_rule(selector, {"background-color": value});',
				'default' => 'transparent'
			),

			'background-image' => array(
				'group' => 'Background',
				'name' => 'Image',
				'type' => 'image',
				'js-callback' => 'propertyInputCallbackBackgroundImage(selector, params, value);'
			),

			'background-repeat' => array(
				'group' => 'Background',
				'name' => 'Image Repeat',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"background-repeat": value});',
				'options' => array(
					'repeat' => 'Tile',
					'no-repeat' => 'No Tiling',
					'repeat-x' => 'Tile Horizontally',
					'repeat-y' => 'Tile Vertically'
				)
			),
			
			'border-color' => array(
				'group' => 'Borders',
				'name' => 'Border Color',
				'type' => 'color',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-color": value});',
				'default' => 'transparent'
			),

			'border-top-width' => array(
				'group' => 'Borders',
				'name' => 'Top Border Width',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-top-width": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),
			
			'border-right-width' => array(
				'group' => 'Borders',
				'name' => 'Right Border Width',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-right-width": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),
			
			'border-bottom-width' => array(
				'group' => 'Borders',
				'name' => 'Bottom Border Width',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-bottom-width": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),
			
			'border-left-width' => array(
				'group' => 'Borders',
				'name' => 'Left Border Width',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-left-width": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),
			
			'border-style' => array(
				'group' => 'Borders',
				'name' => 'Border Style',
				'type' => 'select',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-style": value});',
				'options' => array(
					'none' => 'Hidden',
					'solid' => 'Solid',
					'dashed' => 'Dashed',
					'dotted' => 'Dotted',
					'double' => 'Double'
				)
			),

			'border-top-left-radius' => array(
				'group' => 'Rounded Corners',
				'name' => 'Top Left',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-top-left-radius": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),

			'border-top-right-radius' => array(
				'group' => 'Rounded Corners',
				'name' => 'Top Right',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-top-right-radius": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),

			'border-bottom-left-radius' => array(
				'group' => 'Rounded Corners',
				'name' => 'Bottom Left',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-bottom-left-radius": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),

			'border-bottom-right-radius' => array(
				'group' => 'Rounded Corners',
				'name' => 'Bottom Right',
				'type' => 'integer',
				'js-callback' => 'stylesheet.update_rule(selector, {"border-bottom-right-radius": value + "px"});',
				'unit' => 'px',
				'default' => 0
			),

			'box-shadow-horizontal-offset' => array(
				'group' => 'Box Shadow',
				'name' => 'Horizontal Offset',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'box-shadow-vertical-offset' => array(
				'group' => 'Box Shadow',
				'name' => 'Vertical Offset',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'box-shadow-blur' => array(
				'group' => 'Box Shadow',
				'name' => 'Blur',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'box-shadow-color' => array(
				'group' => 'Box Shadow',
				'name' => 'Color',
				'type' => 'color',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 'transparent'
			),

			'text-shadow-horizontal-offset' => array(
				'group' => 'Text Shadow',
				'name' => 'Horizontal Offset',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'text-shadow-vertical-offset' => array(
				'group' => 'Text Shadow',
				'name' => 'Vertical Offset',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'text-shadow-blur' => array(
				'group' => 'Text Shadow',
				'name' => 'Blur',
				'type' => 'integer',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 0
			),

			'text-shadow-color' => array(
				'group' => 'Text Shadow',
				'name' => 'Color',
				'type' => 'color',
				'js-callback' => 'propertyInputCallbackShadow(selector, property_id, value);',
				'complex-property' => 'HeadwayElementProperties::complex_property_shadow',
				'default' => 'transparent'
			)

	);
	
	
	public static function get_property($property) {
				
		return isset(self::$properties[$property]) ? self::$properties[$property] : null;
		
	}
	
	
	public static function get_properties_by_group($group) { 
		
		//Filter though all of the properties to make sure they are in the selected group
		$filtered_properties = array_filter(self::$properties, create_function('$property', 'return ($property[\'group\'] === \'' . $group . '\');'));
		
		if ( !is_array($filtered_properties) || count($filtered_properties) === 0 )
			return null;
		else
			return $filtered_properties;
	
	}
	
	
	public static function output_css($selector, $properties = array()) {
				
		if ( !isset($selector) || $selector == false )
			return null;
			
		if ( !is_array($properties) || count($properties) === 0 )
			return null;
			
		global $headway_fonts;
		
		$output = '';
		
		$output .= $selector . ' {' . "\n";
	
			//Loop through properties
			foreach ( $properties as $property_id => $value ) {
			
				//If the value is an empty string, false, or null, don't attempt to put anything.
				if ( !isset($value) || $value === '' || $value === false )
					continue;
			
				//Look up the property to figure out how to handle it
				$property = self::get_property($property_id);
				
				//If the property does not exist, skip it.
				if ( !$property )
					continue;
							
				//If it's a complex property, pass everything through it.
				if ( Headway::get('complex-property', $property) && is_callable(Headway::get('complex-property', $property)) ) {
					$output .= call_user_func(Headway::get('complex-property', $property), array(
						'selector' => $selector, 
						'property_id' => $property_id, 
						'value' => $value, 
						'properties' => $properties, 
						'property' => $property
					));
					
					continue;
				}
				
				//Format the $value by adding the unit or hex indicator if it's a color
				if ( Headway::get('unit', $property) !== null )
					$value = $value . $property['unit'];
				
				if ( Headway::get('type', $property) === 'color' && $value != 'transparent' )
					$value = '#' . trim($value, '#');
				
				if ( Headway::get('type', $property) === 'image' )
					$value = 'url(' . $value . ')';
					
				if ( Headway::get('type', $property) === 'font-family-select' )
					$value = isset($headway_fonts[$value]) ? $headway_fonts[$value] : $value;
			
				$output .= $property_id . ': ' . $value . ';' . "\n";
			
			} //foreach: Regular Properties
	
		$output .= '}' . "\n";
		
		return $output;
		
	}
	
	
	public static function complex_property_shadow($args) {
		
		extract($args);
												
		$shadow_type = (strpos($property_id, 'box-shadow') !== false) ? 'box-shadow' : 'text-shadow';		
		
		global $headway_complex_property_check;
		
		//If the complex property check isn't even set, make it an empty array.
		if ( !is_array($headway_complex_property_check) )
			$headway_complex_property_check = array($shadow_type => array());
						
		//Since the complex property is a combination of a bunch of properties, we only want it to output once.
		if ( isset($headway_complex_property_check[$shadow_type][$selector]) && $headway_complex_property_check[$shadow_type][$selector] == true )
			return;
			
		$headway_complex_property_check[$shadow_type][$selector] = true;
		
		$shadow_color = isset($properties[$shadow_type . '-color']) ? $properties[$shadow_type . '-color'] : 'rgba(0,0,0,0)';
		$shadow_hoffset = isset($properties[$shadow_type . '-horizontal-offset']) ? $properties[$shadow_type . '-horizontal-offset'] : '0';
		$shadow_voffset = isset($properties[$shadow_type . '-vertical-offset']) ? $properties[$shadow_type . '-vertical-offset'] : '0';
		$shadow_blur = isset($properties[$shadow_type . '-blur']) ? $properties[$shadow_type . '-blur'] : '0';
		$shadow_inset = ($shadow_type == 'text-shadow') ? null : null;
				
		return $shadow_type . ': #' . $shadow_color . ' ' . $shadow_hoffset . 'px ' . $shadow_voffset . 'px ' . $shadow_blur . 'px ' . $shadow_inset . ';';
		
	}
			
		
	public static function complex_property_capitalization($args) {
		
		extract($args);
		
		$data = '';
		
		if ( $value == 'none' ) {
			
			$data .= 'text-transform: none;';
			$data .= 'font-variant: normal;';
			
		} elseif ( $value == 'small-caps' ) {
			
			$data .= 'text-transform: none;';
			$data .= 'font-variant: small-caps;';

		} else {
			
			$data .= 'text-transform: ' . $value . ';';
			$data .= 'font-variant: normal;';
			
		}
		
		return $data;
		
	}
	
	
	public static function complex_property_font_styling($args) {
		
		extract($args);
		
		$data = '';
		
		if ( $value == 'normal' ) {
			
			$data .= 'font-style: normal;';
			$data .= 'font-weight: normal;';
			
		} elseif ( $value == 'bold' ) {
			
			$data .= 'font-style: normal;';
			$data .= 'font-weight: bold;';

		} elseif ( $value == 'italic' ) {

			$data .= 'font-style: italic;';
			$data .= 'font-weight: normal;';

		} elseif ( $value == 'bold-italic' ) {
			
			$data .= 'font-style: italic;';
			$data .= 'font-weight: bold;';
			
		}
		
		return $data;
		
	}		

	
}