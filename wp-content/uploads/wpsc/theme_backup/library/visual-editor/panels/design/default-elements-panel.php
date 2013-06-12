<?php
class DefaultsDesignEditorPanel extends HeadwayVisualEditorPanelAPI {
	
	public $id = 'defaults';
	public $name = 'Defaults';
	public $mode = 'design';
	
	function panel_content() {
		
		echo '
			<div class="design-editor-element-selector-container">
				<ul id="design-editor-default-elements" class="sub-tabs element-selector">
				</ul><!-- #design-editor-default-elements -->
			</div><!-- .design-editor-default-element-selector-container -->
			
			<div class="design-editor-options-container">
			
				<div class="design-editor-info" style="display: none;">
					<h4>Editing: <span></span> <strong>(Default Element)</h4>
				</div><!-- .design-editor-info -->
				
				<div class="design-editor-options" style="display:none;"></div><!-- .design-editor-options -->
				
				<p class="design-editor-options-instructions sub-tab-notice">' . __('Please select a default element to the left.') . '</p>
				
				<div class="cog-container" style="display:none;">
					<div class="cog-bottom-left"></div>
					<div class="cog-top-right"></div>
				</div><!-- .cog-container -->
				
			</div><!-- .design-editor-options-container -->
		';

	}
	
	
}
headway_register_visual_editor_panel('DefaultsDesignEditorPanel');