<?php
class EditorPanel extends HeadwayVisualEditorPanelAPI {
	
	
	public $id = 'editor';
	public $name = 'Editor';
	public $mode = 'design';
	
	
	function panel_content() {
		
		echo '
			<div class="design-editor-element-selector-container">';

				echo '<ul id="design-editor-element-groups" class="sub-tabs element-selector">';

					foreach ( HeadwayElementAPI::get_groups() as $id => $name)
						echo '<li id="element-group-' . $id . '" class="has-children element-group"><span>' . $name . '</span></li>';
						
				echo '</ul><!-- #design-editor-element-groups -->';
				
				echo '<ul id="design-editor-main-elements" class="sub-tabs element-selector element-selector-hidden">';
						
					$sub_elements = array();

					foreach ( HeadwayElementAPI::get_all_elements() as $group => $elements ) {

						if ( $group == 'default-elements' )
							continue;

						foreach ( $elements as $id => $settings ) {

							$classes =  array(
								'main-element',
								'group-' . $group
							);

							if ( is_array($settings['children']) && count($settings['children']) > 0 )
								$classes[] = 'has-children';

							$sub_elements = array_merge($sub_elements, $settings['children']);
							
							echo '<li id="main-element-' . $id . '" class="' . implode(' ', $classes) . '"><span>' . $settings['name'] . '</span></li>';

						}
						
					}

				echo '</ul><!-- #design-editor-main-elements -->';
				
				echo '<ul id="design-editor-sub-elements" class="sub-tabs element-selector element-selector-hidden">';
							
					foreach ( $sub_elements as $id => $settings )
						echo '<li id="sub-element-' . $id . '" class="sub-element parent-element-' . $settings['parent'] . '"><span>' . $settings['name'] . '</span></li>';
											
				echo '</ul><!-- #design-editor-sub-elements -->';

			echo '</div><!-- .design-editor-element-selector-container -->
			
			<div class="design-editor-options-container">
			
				<div class="design-editor-info" style="display: none;">
					<h4>Editing: <span></span> <strong></strong></h4>
					
					<div class="design-editor-info-right">
						<select class="instances">
						</select>
						
						<select class="states">
						</select>
						
						<span class="button button-small design-editor-info-button customize-element-for-layout">Customize For Current Layout</span>
						<span class="button button-small design-editor-info-button customize-for-regular-element">Customize Regular Element</span>
					</div>
				</div><!-- .design-editor-info -->
				
				<div class="design-editor-options" style="display:none;"></div><!-- .design-editor-options -->
				
				<p class="design-editor-options-instructions sub-tab-notice">' . __('Please select an element to the left.') . '</p>
				
			</div><!-- .design-editor-options-container -->
		';

	}
	
}
headway_register_visual_editor_panel('EditorPanel');