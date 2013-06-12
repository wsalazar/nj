(function($) {

visualEditorModeDesign = function() {
	
	
	this.init = function() {
		
		editorTabInstance = new designEditorTabEditor();
		defaultsTabInstance = new designEditorTabDefaults();
		
		designEditorBindPropertyInputs();
		
	}
	
	
	this.iframeCallback = function() {
		
		addBlockControls(true, false);

		/* Reset editor for layout switch */
		editorTabInstance.switchLayout();
		
	}

	
}


/* DESIGN EDITOR TABS */
	designEditorTabEditor = function() {
	
		this.context = 'div#editor-tab';
	
		this._init = function() {
		
			this.setupBoxes();
			this.setupElementSelector();
			this.bindDesignEditorInfo();
		
		}
	
		this.setupBoxes = function() {
								
			designEditorBindPropertyBoxToggle(this.context);
		
		}
	
		this.setupElementSelector = function() {
		
			var self = this;
		
			/* Setup properties box */
			$('div.design-editor-options', this.context).masonry({
				itemSelector:'div.design-editor-box',
				columnWidth: 240
			});

			$('div.design-editor-options-container', this.context).scrollbarPaper();
			/* End properties */


			/* Retrieve the main groups and put them in */
			createCog($('ul#design-editor-element-groups', this.context), true);
			

			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'get_element_groups',
				security: Headway.security,
				layout: Headway.currentLayout
			}, function(groups) {

				$('ul#design-editor-element-groups', self.context).html(groups);

			});
			/* End main groups */


			/* Bind the element clicks */
			$('ul.element-selector li span', this.context).live('click', function(event) {

				var link = $(this).parent();

				if ( link.hasClass('element-group') ) {
					self.processGroupClick(link);
				} else {
					self.processElementClick(link);				
				}

				link.siblings('.ui-state-active').removeClass('ui-state-active');
				link.addClass('ui-state-active');

			});
			/* End binding */

			/* Add scrollbars to groups, main elements, and sub elements */
			$('ul.element-selector', this.context).scrollbarPaper();
		
		}
	
		this.processGroupClick = function(link) {
		
			var self = this;
						
			var group = link.attr('id').replace('element-group-', '');

			if ( $('ul#design-editor-main-elements', this.context).data('group') === group ) {
				return false;
			}

			/* Add notice back to design editor options since there is no element selected */
			$('div.design-editor-options-container', this.context).data({main_element: false, sub_element: false});

			designEditorShowInstructions(this.context);

			/* Add cog to main elements */
			$('ul#design-editor-main-elements', this.context).show();
			createCog($('ul#design-editor-main-elements', this.context), true);

			/* Hide sub elements panel and its scrollbar */
			$('ul#design-editor-sub-elements', this.context).hide().data('main_element', false);
			$('div#scrollbarpaper-design-editor-sub-elements', this.context).hide();
		
			/* Refresh scrollbar for main elements */
			$('ul#design-editor-main-elements', this.context).scrollbarPaper();

			/* Load elements for second panel */
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'get_main_elements',
				group: group,
				security: Headway.security,
				layout: Headway.currentLayout
			}).success(function(mainElements){
			
				$('ul#design-editor-main-elements', self.context).html(mainElements);
				$('ul#design-editor-main-elements', self.context).data('group', group);
			
			})

		}

		this.processElementClick = function(link) {
		
			var self = this;

			/* Set up variables */
			var elementType = link.hasClass('main-element') ? 'main' : 'sub';
			var element_name = link.text();
			var element = link.attr('id').replace(/^(.*)\-element\-/ig, '');

			/* If it is a main element has children, display them.  Otherwise hide them */
			if ( link.hasClass('has-children') && elementType == 'main' ) {

				/* If we're selecting a new main element, display the new sub elements */
				if ( $('ul#design-editor-sub-elements', this.context).data('main_element') !== element ) {

					/* Add cog to sub elements */
					$('ul#design-editor-sub-elements', this.context).show();
					createCog($('ul#design-editor-sub-elements', this.context), true);
					
					/* Get the elements */			
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_sub_elements',
						element: element,
						security: Headway.security,
						layout: Headway.currentLayout
					}).success(function(subElements) {

						$('ul#design-editor-sub-elements', self.context).html(subElements);
						$('ul#design-editor-sub-elements', self.context).data('main_element', element);
					
						/* Refresh scrollbar for sub elements */
						$('ul#design-editor-sub-elements', self.context).scrollbarPaper();

					});

				/* Else the sub elements are already visible and we're just going back to the main element, just remove the selected element from sub	*/						
				} else {

					$('ul#design-editor-sub-elements li.ui-state-active', this.context).removeClass('ui-state-active');		

				}

			/* There are no children, hide them. */
			} else if ( elementType == 'main' ) {

				/* Hide sub elements panel and scrollbar */
				$('ul#design-editor-sub-elements', this.context).hide().data('main_element', false);
				$('div#scrollbarpaper-design-editor-sub-elements', this.context).hide();

			}

			/* LOAD INPUTS, INSTANCES, AND STATES */
				designEditorShowCog(this.context);

				$.when(

					/* Inputs */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_inputs',
						element: element,
						unsavedValues: designEditorGetUnsavedValues(element),
						security: Headway.security
					}).success(function(inputs) {
					
						var options = $('div.design-editor-options', self.context);
						var previousElement = options.data('element') || false;
						var previousElementSpecialElementType = options.data('specialElementType') || false;

						$('div.design-editor-options', self.context).html(inputs);
													
						/* Set the flags */
						$('div.design-editor-options', self.context).data({'element': element, 'specialElementType': false, 'specialElementMeta': false});
					
					}),

					/* Instances */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_instances',
						element: element,
						security: Headway.security
					}).success(function(instances) {

						if ( instances.length === 0 ) {

							$('div.design-editor-info select.instances', self.context).hide();

						} else {

							$('div.design-editor-info select.instances', self.context).show();

							var instanceOptions = '<option value="">&mdash; Instances &mdash;</option>' + instances;
							$('div.design-editor-info select.instances', self.context).html(instanceOptions);

						}

					}),

					/* States */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_states',
						element: element,
						security: Headway.security
					}).success(function(states) {

						if ( states.length === 0 ) {

							$('div.design-editor-info select.states', self.context).hide();

						} else {

							$('div.design-editor-info select.states', self.context).show();

							var statesOptions = '<option value="">&mdash; States &mdash;</option>' + states;
							$('div.design-editor-info select.states', self.context).html(statesOptions);

						}

					}),
				
					/* Element name and inherit location */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_inherit_location_name',
						element: element,
						security: Headway.security
					}).success(function(inheritLocation) {

						/* Add element name to info box */					
						$('div.design-editor-info h4 span', self.context).text(element_name);
					
						/* Reset layout element button */
						$('span.customize-element-for-layout').text('Customize For Current Layout');
					
						/* Show and fill inherit location if it exists and hide it if not */
						if ( inheritLocation.length > 0 ) {
						
							$('div.design-editor-info h4 strong', self.context)
								.text('(Inheriting From ' + inheritLocation + ')')
								.show();
						
						} else {
						
							$('div.design-editor-info h4 strong', self.context).hide();
						
						}

					})

				/* Everything is done, we can hide cog and show options now */
				).then(function() {
					
					designEditorShowContent(self.context, true);
					
				});			
			/* END LOAD INPUTS */

		}
	
		this.bindDesignEditorInfo = function() {
		
			var self = this;
		
			$('span.customize-element-for-layout', this.context).click(function() {
			
				var options = $('div.design-editor-options', self.context);
			
				var currentElement = self.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^(.*)\-element\-/ig, '');
				var currentElementName = currentElement.text();
								
				/* Hide everything and show the cog */
				designEditorShowCog(self.context);
			
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', self.context).html(currentElementName + '<em> on ' + Headway.currentLayoutName + ' Layout</em>');
				$('div.design-editor-info h4 strong', self.context)
					.html('(Inheriting From ' + currentElementName + ')')
					.show();
				
				/* Hide current button, states, instances, and show the button to return to the regular element */
				$(this).hide();
			
				$('div.design-editor-info select.instances', self.context).hide();
				$('div.design-editor-info select.states', self.context).hide();
			
				$('div.design-editor-info span.customize-for-regular-element', self.context).show();
			
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'layout',
					specialElementMeta: Headway.currentLayout,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'layout', Headway.currentLayout),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', self.context).html(inputs);

					designEditorShowContent(self.context);

				});
			
				/* Set the flags */
				$('div.design-editor-options', self.context).data({'element': currentElementID, 'specialElementType': 'layout', 'specialElementMeta': Headway.currentLayout});
								
			}); /* Customize for layout button */
		
			$('span.customize-for-regular-element', this.context).click(function() {
			
				var currentElement = self.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^(.*)\-element\-/ig, '');
				var currentElementName = currentElement.text();
								
				currentElement.find('span').trigger('click');
			
				/* Hide the current button and bring back the layout-specific element button */
				$(this).hide();
				$('div.design-editor-info span.customize-element-for-layout', self.context).show();
			
			}); /* Customize for regular element button */
		
			$('select.instances', this.context).bind('change', function() {
			
				var options = $('div.design-editor-options', self.context);
			
				var currentElement = self.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^(.*)\-element\-/ig, '');
				var currentElementName = currentElement.text();
			
				var instanceID = $(this).val();
				var instanceName = $(this).find(':selected').text();
			
				/* Hide everything and show the cog */
				designEditorShowCog(self.context);
			
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', self.context).html(instanceName);
				$('div.design-editor-info h4 strong', self.context)
					.html('(Inheriting From ' + currentElementName + ')')
					.show();
				
				/* Hide states, layout-specific button, and show the button to return to the regular element */					
				$('div.design-editor-info select.states', self.context).hide();
				$('div.design-editor-info span.customize-element-for-layout', self.context).hide();
			
				$('div.design-editor-info span.customize-for-regular-element', self.context).show();
			
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'instance',
					specialElementMeta: instanceID,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'instance', instanceID),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', self.context).html(inputs);

					designEditorShowContent(self.context);

				});
			
				/* Set the flags */
				$('div.design-editor-options', self.context).data({'element': currentElementID, 'specialElementType': 'instance', 'specialElementMeta': instanceID});
			
			}); /* Instances select */
		
			$('select.states', this.context).bind('change', function() {
			
				var options = $('div.design-editor-options', self.context);
			
				var currentElement = self.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^(.*)\-element\-/ig, '');
				var currentElementName = currentElement.text();
			
				var stateID = $(this).val();
				var stateName = $(this).find(':selected').text();
			
				/* Hide everything and show the cog */
				designEditorShowCog(self.context);
			
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', self.context).html(currentElementName + ' &ndash; ' + stateName);
				$('div.design-editor-info h4 strong', self.context)
					.html('(Inheriting From ' + currentElementName + ')')
					.show();
				
				/* Hide instances, layout-specific button, and show the button to return to the regular element */					
				$('div.design-editor-info select.instances', self.context).hide();
				$('div.design-editor-info span.customize-element-for-layout', self.context).hide();
			
				$('div.design-editor-info span.customize-for-regular-element', self.context).show();
								
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'state',
					specialElementMeta: stateID,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'state', stateID),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', self.context).html(inputs);

					designEditorShowContent(self.context);

				});
			
				/* Set the flags */
				$('div.design-editor-options', self.context).data({'element': currentElementID, 'specialElementType': 'state', 'specialElementMeta': stateID});
			
			}); /* Instances select */
		
		}
	
		this.getCurrentElement = function() {
		
			/* Check against sub elements then main elements. */
			if ( $('ul#design-editor-sub-elements li.ui-state-active', this.context).length === 1 ) {
			
				return $('ul#design-editor-sub-elements li.ui-state-active', this.context);
			
			} else if ( $('ul#design-editor-main-elements li.ui-state-active', this.context).length === 1 ) {
			
				return $('ul#design-editor-main-elements li.ui-state-active', this.context);
			
			} else {
			
				return null;
			
			}
		
		}
	
		this.switchLayout = function() {
		
			/* If editing layout-specific element, switch back to normal element. */
			var currentElement = this.getCurrentElement();
						
			if ( !currentElement || currentElement.length === 0 )
				return false;
		
			currentElement.find('span').trigger('click');
		
		}
	
		this._init();
	
	}

	designEditorTabDefaults = function() {
	
		this.context = 'div#defaults-tab';
	
		this._init = function() {
		
			this.setupBoxes();
			this.setupElementSelector();
		
		}
	
		this.setupBoxes = function() {
								
			designEditorBindPropertyBoxToggle(this.context);
		
		}
	
		this.setupElementSelector = function() {
		
			var self = this;
		
			/* Setup properties box */
			$('div.design-editor-options', this.context).masonry({
				itemSelector:'div.design-editor-box',
				columnWidth: 240
			});

			$('div.design-editor-options-container', this.context).scrollbarPaper();
			/* End properties */


			/* Retrieve the default elements and put them in */
			createCog($('ul#design-editor-element-groups', this.context), true);

			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'get_default_elements',
				security: Headway.security,
				layout: Headway.currentLayout
			}, function(defaultElements) {

				$('ul#design-editor-default-elements', self.context).html(defaultElements);

			});
			/* End main groups */


			/* Bind the element clicks */
			$('ul.element-selector li span', this.context).live('click', function(event) {

				var link = $(this).parent();

				self.processDefaultElementClick(link);				

				link.siblings('.ui-state-active').removeClass('ui-state-active');
				link.addClass('ui-state-active');

			});
			/* End binding */

			/* Add scrollbars to groups, main elements, and sub elements */
			$('ul.element-selector', this.context).scrollbarPaper();
		
		}
	
		this.processDefaultElementClick = function(link) {
		
			var self = this;

			/* Set up variables */
			var elementType = link.hasClass('main-element') ? 'main' : 'sub';
			var element_name = link.text();
			var element = link.attr('id').replace(/^(.*)\-element\-/ig, '');

			/* LOAD INPUTS, INSTANCES, AND STATES */
				designEditorShowCog(this.context);

				$.when(

					/* Inputs */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_inputs',
						element: element,
						specialElementType: 'default',
						unsavedValues: designEditorGetUnsavedValues(element, 'default'),
						security: Headway.security
					}).success(function(inputs) {

						$('div.design-editor-options', self.context).html(inputs);

					})
				
				/* Everything is done, we can hide cog and show options now */
				).then(function() {

					/* Add element name to info box */					
					$('div.design-editor-info h4 span', self.context).text(element_name);

					/* Show everything and hide cog */
					designEditorShowContent(self.context);

				});			
			/* END LOAD INPUTS */

		}
	
		this._init();
	
	}
/* END DESIGN EDITOR TABS */


/* CONTENT TOGGLING */
	designEditorShowCog = function(context) {
					
		$('p.design-editor-options-instructions', context).hide();
		$('div.design-editor-options', context).hide();
		$('div.design-editor-info', context).hide();
		
		createCog($('div.design-editor-options-container', context), true, true);
		
	}

	designEditorShowContent = function(context, refreshInfoButtons) {
		
		refreshInfoButtons = typeof refreshInfoButtons == 'undefined' ? false : true;
	
		/* Show info/options and hide cog/instructions */
		$('div.design-editor-info', context).show();
		$('div.design-editor-options', context).show();
	
		$('p.design-editor-options-instructions', context).hide();
		$('div.design-editor-options-container', context).find('.cog-container').remove();

		/* Run Masonry after everything is visible */
		$('div.design-editor-options', context).masonry('reload');
		
		/* Reset the Customize Regular Element/For Current Layout buttons */
		if ( refreshInfoButtons ) {
			
			$('div.design-editor-info span.customize-element-for-layout', context).show();
			$('div.design-editor-info span.customize-for-regular-element', context).hide();
		
		}
	
		/* Refresh Tooltips */
		setupTooltips();
	
	}

	designEditorShowInstructions = function(context) {
	
		$('div.design-editor-options-container div.cog-container', context).remove();
		$('div.design-editor-options', context).hide();
		$('div.design-editor-info', context).hide();

		$('p.design-editor-options-instructions', context).show();
	
	}
/* END CONTENT TOGGLING */


/* DESIGN EDITOR OPTIONS/INPUTS */
	designEditorGetUnsavedValues = function(element, specialElementType, specialElementMeta) {
		
		if ( typeof specialElementType == 'undefined' )
			var specialElementType = false;
		
		if ( typeof specialElementMeta == 'undefined' )
			var specialElementMeta = false;
		
		var inputs = $('input[element="' + element + '"]', 'div#visual-editor-hidden-inputs');
		var properties = {};
		
		/* Filter by special elements if those are set */
		if ( specialElementType )
			inputs = inputs.filter('[specialElementType="' + specialElementType + '"]');
		else
			inputs = inputs.filter('[specialElementType="false"]');
			
		if ( specialElementMeta )
			inputs = inputs.filter('[specialElementMeta="' + specialElementMeta + '"]');
		else
			inputs = inputs.filter('[specialElementMeta="false"]');
			
		/* Construct the object to be outputted */
		inputs.each(function() {
		
			properties[$(this).attr('property')] = $(this).val();
			
		});
								
		return Object.keys(properties).length > 0 ? properties : null;
		
	}

	designEditorBindPropertyBoxToggle = function(context) {
		
		$('div.design-editor-options', context).delegate('span.design-editor-box-toggle, span.design-editor-box-title', 'click', function(){

			var box = $(this).parents('div.design-editor-box');

			box.toggleClass('design-editor-box-minimized');

			$('div.design-editor-options', context).masonry('reload');

		});
	}

	designEditorBindPropertyInputs = function() {
		
		/* Customize Buttons */
		$('div#panel').delegate('div.customize-property', 'click', function() {
			
			var property = $(this).parents('li');
			var hidden = property.find('input.property-hidden-input');
			
			$(this).parents('li').removeClass('uncustomized-property', 150);
			$(this).fadeOut(150);
			
			setTimeout(function() {

				designEditorUpdateInputHidden(hidden, hidden.val());

				allowSaving();
				
			}, 160);
			
		});
		
		/* Uncustomize Button */
		$('div#panel').delegate('span.uncustomize-property', 'click', function() {
			
			if ( !confirm('Are you sure you wish to uncustomize this property?  The value will be reset.') )
				return false;
			
			var property = $(this).parents('li');
			var hidden = property.find('input.property-hidden-input');
			
			property.find('div.customize-property')
				.fadeIn(150);
				
			property.addClass('uncustomized-property', 150);
			
			designEditorUpdateInputHidden(hidden, null);
			
			/* Remove the CSS declaration */
			var selector = hidden.attr('element_selector') || false;
			var property = hidden.attr('property').toLowerCase();
							
			if ( selector && property )
				stylesheet.delete_rule_property(selector, property);
							
			allowSaving();
			
		});
		
		/* Select */
		$('div#panel').delegate('div.property-select select', 'change', designEditorInputSelect);
		
		/* Font Select */
		$('div#panel').delegate('div.property-font-family-select select', 'change', designEditorInputFontSelect);
		
		/* Integer */
		$('div#panel').delegate('div.property-integer input', 'focus', designEditorInputIntegerFocus);
		
		$('div#panel').delegate('div.property-integer input', 'keyup blur', designEditorInputIntegerChange);
		
		/* Image Uploaders */
		$('div#panel').delegate('div.property-image span.button', 'click', designEditorInputImageUpload);

		$('div#panel').delegate('div.property-image span.delete-image', 'click', designEditorInputImageUploadDelete);

		/* Color Inputs */
		$('div#panel').delegate('div.property-color div.colorpicker-box', 'click', designEditorInputColor);
		
	}
/* END DESIGN EDITOR INPUTS */


/* INPUT FUNCTIONALITY */
	/* Select */
	designEditorInputSelect = function(event) {
		
		var hidden = $(this).siblings('input.property-hidden-input');
						
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */
		
		designEditorUpdateInputHidden(hidden, $(this).val());

		allowSaving();
		
	}

	/* Font Select */
	designEditorInputFontSelect = function(event) {
		
		var hidden = $(this).siblings('input.property-hidden-input');
						
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */
		
		designEditorUpdateInputHidden(hidden, $(this).val());
		
		/* Change the font of the select to the selected option */
		$(this).css('fontFamily', $(this).val());

		allowSaving();
		
	}

	/* Integer */
	designEditorInputIntegerFocus = function(event) {
	
		if ( typeof originalValues !== 'undefined' ) {
			delete originalValues;
		}
		
		originalValues = new Object;
		
		var hidden = $(this).siblings('input.property-hidden-input');
		var id = hidden.attr('selector') + '-' + hidden.attr('property');
		
		originalValues[id] = $(this).val();
		
	}
	
	designEditorInputIntegerChange = function(event) {
		
		var hidden = $(this).siblings('input.property-hidden-input');
		var value = $(this).val();
		
		if ( event.type == 'keyup' && value == '-' )
			return;
		
		/* Validate the value and make sure it's a number */
		if ( isNaN(value) ) {
			
			/* Take the nasties out to make sure it's a number */
			value = value.replace(/[^0-9]*/ig, '');
			
			/* If the value is an empty string, then revert back to the original value */
			if ( value === '' ) {
				
				var id = hidden.attr('selector') + '-' + hidden.attr('property');
				var value = originalValues[id];
										
			}
			
			/* Set the value of the input to the sanitized value */
			$(this).val(value);
			
		}
		
		/* Remove leading zeroes */
		if ( value.length > 1 && value[0] == 0 ) {
			
			value = value.replace(/^[0]+/g, '');
			
			/* Set the value of the input to the sanitized value */
			$(this).val(value);
			
		}
		
		
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */

		designEditorUpdateInputHidden(hidden, $(this).val());

		allowSaving();
		
	}

	/* Image Uploaders */
	designEditorInputImageUpload = function(event) {
		
		var self = this;
		
		openImageUploader(function(url, filename) {
			
			var hidden = $(self).siblings('input');

			hidden.val(url);

			$(self).siblings('.image-input-controls-container').find('span.src').text(filename);
			$(self).siblings('.image-input-controls-container').show();

			designEditorUpdateInputHidden(hidden, url);

			/* Call developer-defined callback */
			var callback = eval(hidden.attr('callback'));
			callback($(self), hidden, {method: 'add', value: url});
			/* End Callback */
			
		});
		
	}
	
	designEditorInputImageUploadDelete = function(event) {
		
		if ( !confirm('Are you sure you wish to remove this image?') ) {
			return false;
		}

		$(this).parent('.image-input-controls-container').hide();
		$(this).hide();
		
		var hidden = $(this).parent().siblings('input');

		hidden.val('');

		designEditorUpdateInputHidden(hidden, '');	

		/* Call developer-defined callback */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden, {method: 'delete'});
		/* End Callback */

		allowSaving();
		
	}
	
	/* Color Inputs */
	designEditorInputColor = function(event) {
		
		var offset = $(this).offset();
		
		var colorpickerWidth = 356;
		var colorpickerHeight = 196;
		
		var colorpickerLeft = offset.left;
		var colorpickerTop = offset.top - colorpickerHeight + $(this).outerHeight();
										
		//If the colorpicker is bleeding to the right of the window, flip it to the left
		if ( (offset.left + colorpickerWidth) > $(window).width() ) {
			
			//6 pixels at end is just for a precise adjustment.  Color picker width and color picker box outer width don't get it to the precise position.
			var colorpickerLeft = offset.left - colorpickerWidth + $(this).outerWidth() + 6;
			
		}
		
		/* Keep the design editor options container from scrolling */
		$('div.design-editor-options-container').css('overflow-y', 'hidden');

		//If the colorpicker exists, just show it
		if ( $(this).data('colorpickerId') ) {
			
			var colorpicker = $('div#' + $(this).data('colorpickerId'));
														
			$(this).colorPickerShow();
			
			//Put the CSS after showing so it actually applies
			colorpicker.css({
				top: colorpickerTop + 'px',
				left: colorpickerLeft + 'px '
			});
			
			return true;
			
		}

		//Colorpicker doesn't exist, we have to create and bind stuff
		$(this).colorPicker({
			position: {
				top: colorpickerTop,
				left: colorpickerLeft,
				position: 'fixed'
			},
			eventName: false, /* Make it so it doesn't bind the colorpicker-box click event */
			onChange: function(hsb, hex, rgb, el) {	

				//this refers to colorpicker object
				
				if ( hex == 'transparent' ) {
					var color = 'transparent';
				} else {
					var color = '#' + hex;
				}

				var input = $(el).siblings('input');
				var colorpickerColor = $(el).children('.colorpicker-color');

				/* Call developer-defined callback */
				var callback = eval(input.attr('callback'));
				callback($(el), input, {value: color});
				/* End Callback */

				//Update the color of the original element
				colorpickerColor.css('background-color', color);
				
				//If the color is transparent, add the transparent class to the colorpicker color.  Otherwise, remove the class.
				if ( color == 'transparent' ) {
					colorpickerColor.addClass('colorpicker-color-transparent');
				} else {
					colorpickerColor.removeClass('colorpicker-color-transparent');
				}

				//Update the input
				input.val(color);
				
				//Update the hidden flag
				designEditorUpdateInputHidden(input, color.replace('#', ''));

				allowSaving();

			},
			onSubmit: function(hsb, hex, rgb, el) {

				//this refers to colorpicker object
				if ( hex == 'transparent' ) {
					var color = 'transparent';
				} else {
					var color = '#' + hex;
				}

				var input = $(el).siblings('input');
				var colorpickerColor = $(el).children('.colorpicker-color');

				/* Call developer-defined callback */
				var callback = eval(input.attr('callback'));
				callback($(el), input, {value: color});
				/* End Callback */

				//Update the color of the original element
				colorpickerColor.css('background-color', color);
				
				//If the color is transparent, add the transparent class to the colorpicker color.  Otherwise, remove the class.
				if ( color == 'transparent' ) {
					colorpickerColor.addClass('colorpicker-color-transparent');
				} else {
					colorpickerColor.removeClass('colorpicker-color-transparent');
				}

				//Update the input
				input.val(color);
				
				//Update the hidden flag
				designEditorUpdateInputHidden(input, color.replace('#', ''));

				//Hide the colorpicker
				$(el).colorPickerHide();
				
				/* Allow design editor options container to scroll again */
				$('div.design-editor-options-container').css('overflow-y', 'auto');

				allowSaving();	

			},
			onBeforeShow: function() {	

				//this refers to colorpicker box
				var input = $(this).siblings('input');

				$(this).colorPickerSetColor(input.val());

			},
			onHide: function() {
				
				/* Allow design editor options container to scroll again */
				$('div.design-editor-options-container').css('overflow-y', 'auto');
				
			}
		});

		return $(this).colorPickerShow();
		
	}
/* END INPUT FUNCTIONALITY */


/* DESIGN EDITOR SAVING */
	designEditorUpdateInputHidden = function(input, value) {

		var input = $(input);
		
		/* If it's an uncustomized property and the user somehow tabs to the input, DO NOT send the stuff to the DB. */
		if ( input.parents('li.uncustomized-property').length == 1 )
			return false;
		
		/* Get all vars */
		var element = input.attr('element').toLowerCase();
		var property = input.attr('property').toLowerCase();
		var selector = input.attr('element_selector') || false;
		var specialElementType = input.attr('special_element_type').toLowerCase() || false;
		var specialElementMeta = input.attr('special_element_meta').toLowerCase() || false;

		/* Build name and ID */
		var hiddenInputID = 'input-' + element + '-' + property;
		var hiddenInputName = 'design-editor[' + element + ']';
		
		/* Add layout, instance, or state to the name/ID.  Otherwise just say that it's a default element type */
		if ( specialElementType != false && specialElementMeta != false ) {
			hiddenInputID = hiddenInputID + '-' + specialElementType + '_' + specialElementMeta;
			hiddenInputName = hiddenInputName + '[' + specialElementType + '][' + specialElementMeta + ']';
		} else {
			hiddenInputName = hiddenInputName + '[regular][]';				
		}
		
		/* Add the property to the end of the property input name */
		hiddenInputName = hiddenInputName + '[' + property + ']';
		
		/* Finish by adding '-hidden' to the ID */
		hiddenInputID = hiddenInputID + '-hidden';
		
		/* Create input if it doesn't exist—otherwise, update it. */
		if ( $('input#' + hiddenInputID, 'div#visual-editor-hidden-inputs').length === 0 ) {

			var hiddenInput = $('<input type="hidden" />');
			
			hiddenInput.attr({
				id: hiddenInputID,
				name: hiddenInputName,
				element: element,
				property: property,
				specialElementType: specialElementType,
				specialElementMeta: specialElementMeta
			});		

			/* Finish setting up input */
			hiddenInput
				.val(value)
				.appendTo('div#visual-editor-hidden-inputs');

		} else {

			$('input#' + hiddenInputID, 'div#visual-editor-hidden-inputs').val(value);

		}

	}
/* END DESIGN EDITOR SAVING */


/* COMPLEX JS CALLBACKS */
	propertyInputCallbackFontFamily = function(selector, value) {
		
		$.post(Headway.ajaxURL, {
			action: 'headway_visual_editor',
			method: 'get_font_stack',
			security: Headway.security,
			font: value
		}, function(response) {
			
			if ( typeof response != 'undefined' && response != false ) {
				var fontStack = response;
			} else {
				var fontStack = value;
			}
			
			stylesheet.update_rule(selector, {"font-family": fontStack});
		
		});
		
	}

	propertyInputCallbackBackgroundImage = function(selector, params, value) {
		
		if ( params.method === 'add' ) {
			
			stylesheet.update_rule(selector, {"background-image": 'url(' + value + ')'});
			
		} else if ( params.method === 'delete' ) {
			
			stylesheet.update_rule(selector, {"background-image": null});
			
		}
		
	}

	propertyInputCallbackFontStyling = function(selector, value) {
		
		if ( value === 'normal' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'normal',
				'font-weight': 'normal'
			});
			
		} else if ( value === 'bold' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'normal',
				'font-weight': 'bold'
			});
			
		} else if ( value === 'italic' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'italic',
				'font-weight': 'normal'
			});
			
		} else if ( value === 'bold-italic' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'italic',
				'font-weight': 'bold'
			});
			
		}
		
	}

	propertyInputCallbackCapitalization = function(selector, value) {
		
		if ( value === 'none' ) {
			
			stylesheet.update_rule(selector, {
				'text-transform': 'none',
				'font-variant': 'normal'
			});
			
		} else if ( value === 'small-caps' ) {
			
			stylesheet.update_rule(selector, {
				'text-transform': 'none',
				'font-variant': 'small-caps'
			});
			
		} else {
			
			stylesheet.update_rule(selector, {
				'text-transform': value,
				'font-variant': 'normal'
			});
			
		}
		
	}

	propertyInputCallbackShadow = function(selector, property_id, value) {
		
		var shadowType = ( property_id.indexOf('box-shadow') === 0 ) ? 'box-shadow' : 'text-shadow';
											
		var currentShadow = $i(selector).css(shadowType) || false;
								
		//If the current shadow isn't set, then create an empty template to work off of.
		if ( currentShadow == false || currentShadow == 'none' )
			currentShadow = 'rgba(0, 0, 0, 0) 0 0 0';
		
		//Remove all spaces inside rgba, rgb, and hsb colors and also remove all px
		var shadowFragments = currentShadow.replace(/, /g, ',').replace(/px/g, '').split(' ');
		
		var shadowColor = shadowFragments[0];
		var shadowHOffset = shadowFragments[1];
		var shadowVOffset = shadowFragments[2];
		var shadowBlur = shadowFragments[3];
		var shadowInset = ( typeof shadowFragments[4] != 'undefined' && shadowFragments[4] == 'inset' ) ? 'inset' : '';
		
		switch ( property_id ) {
			
			case shadowType + '-horizontal-offset':
				shadowHOffset = value;
			break;
			
			case shadowType + '-vertical-offset':
				shadowVOffset = value;
			break;
			
			case shadowType + '-blur':
				shadowBlur = value;
			break;
			
			case shadowType + '-inset':
				shadowInset = value;
			break;
			
			case shadowType + '-color':
				shadowColor = value;
			break;
			
		}
		
		var shadow = shadowColor + ' ' + shadowHOffset + 'px ' + shadowVOffset + 'px ' + shadowBlur + 'px' + shadowInset;
					
		var properties = {};
		
		//Use this syntax so the shadow type can feed from variable.
		properties[shadowType] = shadow;
					
		stylesheet.update_rule(selector, properties);
		
	}
/* END COMPLEX JS CALLBACKS */


})(jQuery);