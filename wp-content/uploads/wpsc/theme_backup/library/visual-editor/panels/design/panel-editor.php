<?php
class EditorPanel extends HeadwayVisualEditorPanelAPI {
	
	
	public $id = 'editor';
	public $name = 'Editor';
	public $mode = 'design';
	
	
	function panel_content() {
		
		echo '
			<div class="design-editor-element-selector-container">
				<ul id="design-editor-element-groups" class="sub-tabs element-selector">
				</ul><!-- #design-editor-element-groups -->
				
				<ul id="design-editor-main-elements" class="sub-tabs element-selector element-selector-hidden">
				</ul><!-- #design-editor-main-elements -->
				
				<ul id="design-editor-sub-elements" class="sub-tabs element-selector element-selector-hidden">
				</ul><!-- #design-editor-sub-elements -->
			</div><!-- .design-editor-element-selector-container -->
			
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