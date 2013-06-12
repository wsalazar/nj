(function($) {
/* IFRAME FUNCTIONS */
	$i = function(element) {
		return Headway.iframe.contents().find(element);
	}
	
	
	loadIframe = function(callback) {
	
		iframeURL = Headway.homeURL + '?ve-iframe=true&ve-iframe-layout=' + Headway.currentLayout + '&ve-iframe-mode=' + Headway.mode + '&rand=' + Math.floor(Math.random() * 100000001);
				
		//Since the default iframe load function is used for all modes, we can just pack it in with the normal callback				
		var callback_with_default = function() {
			
			setLoadingBar(85, 'iframe nearly complete');
						
			if ( typeof callback === 'function' ) {
				callback();
			}
			
			defaultIframeLoad();
						
		}						
								
		/* Use iframe plugin so it can detect a timeout.  If there's a timeout, refresh the entire page. */
		Headway.iframe.src(iframeURL, callback_with_default, {
			timeout: function(duration) {
														
				iframeTimeout = true;	
				
				stopTitleActivityIndicator();

				changeTitle('Visual Editor: Error!');					
										
				$('div#loading div.loading-message p.tip').html('<strong>ERROR:</strong> There was a problem while loading the visual editor.<br /><br />Your browser will automatically refresh in 4 seconds to attempt loading again.');
				
				$('div#loading div.loading-bar').css('borderColor', '#D8000C');	
				$('div#loading div.loading-bar div.loading-bar-inside').stop(true).css({background: '#D8000C', width: '100%'});	
				$('div#loading div.loading-message p.tip, div#loading div.loading-message p.tip strong').css('color', '#D8000C');
				
				setTimeout(function(){
					window.location.href = unescape(window.location);
				}, 4000);
			
			},
			timeoutDuration: 40000
		});
		
		stopIFrameLoadingIndicator();
	
	}


	/* Default function to be called when iframe finishes loading. */
	defaultIframeLoad = function() {
		
		stopTitleActivityIndicator();
	
		changeTitle('Visual Editor: ' + Headway.currentLayoutName);
		$('div#current-layout strong span').text(Headway.currentLayoutName);
	
		/* Set up tooltips */
		setupTooltips();
		setupTooltips('iframe');
		/* End Tooltips */
		
		/* Handle layout selector cookie */
		if ( $.cookie('hide-layout-selector') === 'true' ) {
			toggleLayoutSelector(true);
		}
	
		setLoadingBar(100, 'Complete', function(){
			
			jQuery('div.loading-message').animate({marginTop: -1300}, 800, 'easeInOutExpo');	
			
			setTimeout(function(){
				
				$('div#loading').animate({opacity: 0}, 400, function(){ 
					$(this).remove(); 
				});
				
			}, 400);
			
		});
		
		stylesheet = new ITStylesheet({document: Headway.iframe.contents()[0], href: Headway.homeURL + '/?headway-trigger=compiler&file=general'}, 'find');
		
		/* Add the template notice if it's layout mode and a template is active */
		if ( Headway.currentLayoutTemplate ) {
			$i('body').prepend('<div id="template-notice"><h1>To edit this layout, remove the template from this layout.</h1></div>');
		}
		
		/* Clear out hidden inputs */
		clearHiddenInputs();
		
		/* Disallow certain keys so user doesn't accidentally leave the VE */
		disableBadKeys();
		
		/* Bind visual editor key shortcuts */
		bindKeyShortcuts();
		
		/* Deactivate all links and buttons */
		Headway.iframe.contents().find('body').delegate('a, input[type="submit"], button', 'click', function(event) {
			event.preventDefault();
			
			return false;
		});
		
		/* Show the load message */
		if ( typeof headwayIframeLoadNotification !== 'undefined' ) {
			showNotification(headwayIframeLoadNotification);
			
			delete headwayIframeLoadNotification;
		}
		
		/* Remove the tabs that are set to close on layout switch */
		removeLayoutSwitchPanels();
		
		/* Show the grid wizard if the current layout isn't customized and not using a tmeplate */
		var layoutNode = $('div#layout-selector span.layout[layout_id="' + Headway.currentLayout + '"]');
		var layoutLi = layoutNode.parent();
				
		if ( !layoutNode.hasClass('layout-template') && !layoutLi.hasClass('layout-item-customized') && !layoutLi.hasClass('layout-item-template-used')  ) {
		
			hidePanel();
			
			openBox('grid-wizard');
			
		} else {
			
			closeBox('grid-wizard');
			
		}
		
		/* Clear out and disable iframe loading indicator */
		$('div#iframe-loading-overlay').fadeOut(500).html('');
		
	}
	

	stopIFrameLoadingIndicator = function() {
		
		//http://www.shanison.com/2010/05/10/stop-the-browser-%E2%80%9Cthrobber-of-doom%E2%80%9D-while-loading-comet-forever-iframe/
		if ( /Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent) ) {
			
			var fake_iframe;

			if ( fake_iframe == null ){
				fake_iframe = document.createElement('iframe');
				fake_iframe.style.display = 'none';
			}

			document.body.appendChild(fake_iframe);
			document.body.removeChild(fake_iframe);
			
		}
	
	}
/* END IFRAME FUNCTIONS */


/* TOOLTIPS */
	setupTooltips = function(location) {
		
		if ( typeof location === 'undefined' )
			location = false;
			
		if ( Headway.disableTooltips == 1 ) {
			
			$('div.tooltip-button').hide();
			
			return false;
			
		}
		
		var tooltipOptions = {
			style: {
				classes: 'ui-tooltip-headway'
			},
			show: {
				delay: 10
			},
			position: {
				my: 'bottom left',
				at: 'top center',
				viewport: $(window),
				effect: false
			}
		}
		
		if ( location == 'iframe' ) {
			
			tooltipOptions.position.container = Headway.iframe.contents().find('body'); 
			tooltipOptions.position.viewport = Headway.iframe; 
						
			var tooltipElement = $i;
			
		} else {
			
			var tooltipElement = $;
			
		}
						
		tooltipElement('div.tooltip-button, .tooltip').qtip(tooltipOptions);
		
		tooltipElement('.tooltip-bottom-right').qtip($.extend({}, tooltipOptions, { 
		   position: {
				my: 'bottom right',
				at: 'top center'
		   }
		}));
		
		tooltipElement('.tooltip-top-right').qtip($.extend({}, tooltipOptions, { 
		   position: {
				my: 'top right',
				at: 'bottom center'
		   }
		}));
		
		tooltipElement('.tooltip-top-left').qtip($.extend({}, tooltipOptions, { 
		   position: {
				my: 'top left',
				at: 'bottom center'
		   },
		   show: {
		   		delay: 750
		   }
		}));
		
		tooltipElement('.tooltip-left').qtip($.extend({}, tooltipOptions, { 
		   position: {
				my: 'left center',
				at: 'right center'
		   }
		}));
		
		tooltipElement('.tooltip-right').qtip($.extend({}, tooltipOptions, { 
		   position: {
				my: 'right center',
				at: 'left center'
		   }
		}));
		
		
		var iframeScrollTooltipReposition = function() {
			
			/* Flood Control */
			if ( $i('.qtip:visible').length === 0 || typeof iframeScrollTooltipRepositionFloodTimeout != 'undefined' )
				return;
			
			iframeScrollTooltipRepositionFloodTimeout = setTimeout(function() {
				
				$i('.qtip:visible').qtip('reposition');
				
				delete iframeScrollTooltipRepositionFloodTimeout;
				
			}, 400);
						
		}

		Headway.iframe.contents().unbind('scroll', iframeScrollTooltipReposition);		
		Headway.iframe.contents().bind('scroll', iframeScrollTooltipReposition);
		
	}
	

	repositionTooltips = function() {
		
		$i('.qtip:visible').qtip('reposition');
		
	}


	hideTooltipsIframeBlur = function() {

		$i('.ui-tooltip').each(function() {
			$(this).qtip('api').hide();
		});

	}
/* END TOOLTIPS */


/* LAYOUT FUNCTIONS */
	switchToLayout = function(layoutNode, reloadIframe, showSwitchNotification) {
						
		if ( typeof layoutNode == 'object' && !layoutNode.hasClass('layout') )
			layoutNode = layoutNode.find('> span.layout');
			
		if ( layoutNode.length !== 1 )
			return false;
				
		changeTitle('Visual Editor: Loading');
		startTitleActivityIndicator();
	
		var layout = layoutNode;
		var layout_id = layout.attr('layout_id');
		var layout_name = layout.find('strong').text();
				
		//Flip classes around
		$('.layout-selected', 'div#layout-selector').removeClass('layout-selected');
		layout.parent('li').addClass('layout-selected');
		
		//Set global variables, these will be used in the next function to switch the iframe
		Headway.currentLayout = layout_id;
		Headway.currentLayoutName = layout_name;
		Headway.currentLayoutTemplate = false;
				
		//Check if the layout node has a template assigned to it.  
		var possibleTemplateID = layout.find('.status-template').attr('template_id');
						
		if ( typeof possibleTemplateID != 'undefined' && possibleTemplateID != 'none' )
			Headway.currentLayoutTemplate = possibleTemplateID;
		
		//Add the hash of the layout to the URL
		window.location.hash = '#layout=' + Headway.currentLayout;
		
		//Reload iframe and new layout right away
		if ( typeof reloadIframe == 'undefined' || reloadIframe == true ) {
			
			if ( typeof showSwitchNotification == 'undefined' || showSwitchNotification == true )
				headwayIframeLoadNotification = 'Switched to <em>' + Headway.currentLayoutName + '</em>';
			
			loadIframe(Headway.instance.iframeCallback);
			
		}
					
		return true;
		
	}

/* END LAYOUT FUNCTIONS */


/* SHARED INPUT FUNCTIONS */
	openImageUploader = function(callback) {
		
		if ( !boxExists('input-image') ) {
			
			/* iframe load event function */
			var iframeLoad = function(event){

				var iframe = $(event.target);

				var content = iframe.contents();
				var iframe_window = iframe[0].contentWindow; 

				/* CSS changes */
					var stylesheet = new ITStylesheet({document: content[0], href: Headway.homeURL + '/wp-includes/js/imgareaselect/imgareaselect.css'}, 'find');

					stylesheet.update_rule('p.howto', {display:'none'});
					stylesheet.update_rule('tr.post_title', {display:'none'});
					stylesheet.update_rule('tr.image_alt', {display:'none'});
					stylesheet.update_rule('tr.post_excerpt', {display:'none'});
					stylesheet.update_rule('tr.post_content', {display:'none'});
					stylesheet.update_rule('tr.align', {display:'none'});
					stylesheet.update_rule('tr.url button, tr.url p', {display:'none'});
					stylesheet.update_rule('tr.image-size', {display:'none'});
					stylesheet.update_rule('p.ml-submit', {display:'none !important'});

					stylesheet.update_rule('td.savesend input', {opacity:'0'});
					stylesheet.update_rule('input.urlfield', {opacity:'0'});
					stylesheet.update_rule('tr.url th.label span.alignleft', {opacity:'0'});
				/* End CSS changes */
				
				/* Function to bind to the submit button */
					var useImage = function(event) {
					
						if ( event.data.useThumbnailForSrc === true ) {
							var url = $(this).parents('table').find('img.thumbnail').attr('src').replace(/(-)(\d+)(x)(\d+)(\.)/i, '.');
						} else {
							var url = $(this).parents('table').find('#src').val();								
						}
						
						var filename = url.split('/')[url.split('/').length-1];
					
						callback(url, filename);
						
						allowSaving();

						closeBox('input-image', true);		

						event.preventDefault();
					
					}
				/* End function to bind to the submit button */

				/* Set up URL tab */
					if ( content.find('ul#sidemenu li#tab-type_url a.current').length === 1 ) {

						//Remove all other rows
						content.find('#src').parents('tr').siblings('tr').remove();

						//Add a submit button
						content.find('#src')
							.parents('tbody')
							.append('<tr class="submit"><td></td><td class="savesend-url"><input type="submit" value="Use Image" class="button image-input-fix" id="go_button" name="go_button" style="color: #bbb;" /></td></tr>');

						content.find('tr.submit input#go_button').bind('click', {useThumbnailForSrc: false}, useImage);

					}
				/* End URL tab setup */

				/* Handle all other tabs */
					var imageUploaderInputFix = function(){

						content.find('td.savesend input:not(.input-input-fix)')
							.css('opacity', 1)
							.addClass('image-input-fix')
							.addClass('button-primary')
							.val('Use Image')
							.unbind('click')
							.bind('click', {useThumbnailForSrc: true}, useImage);

						content.find('input.urlfield:not(.image-input-fix)').css('opacity', 1).addClass('image-input-fix').attr('readonly', true);

						content.find('tr.url th.label span.alignleft:not(.image-input-fix)').css('opacity', 1).addClass('image-input-fix').text('Image URL');

					}
				
					/* Call fix function right away before the interval is started */
					imageUploaderInputFix();

					if ( typeof imageUploaderInputFixInterval !== 'undefined' ) {
						iframe_window.clearInterval(imageUploaderInputFixInterval);
					}		

					imageUploaderInputFixInterval = iframe_window.setInterval(imageUploaderInputFix, 1000);
				/* End all other tabs */

			}
			/* End iframe load event function */


			var iframePostID = Headway.currentLayout;

			if ( isNaN(Headway.currentLayout) )
				iframePostID = 0;
			
			var settings = {
				id: 'input-image',
				title: 'Select an image',
				description: 'Upload or select an image',
				src: Headway.adminURL + '/media-upload.php?type=image&amp;TB_iframe=true&amp;post_id=' + iframePostID,
				load: iframeLoad,
				width: 670,
				height: 500,
				center: true,
				draggable: false,
				deleteWhenClosed: true,
				blackOverlay: true
			};

			var box = createBox(settings);

		}

		openBox('input-image');
		
	}
/* END SHARED INPUT FUNCTIONS */


/* ANNOYANCE FIXER FUNCTIONS */
	prohibitVEClose = function () {	
		window.onbeforeunload = function(){
			return 'You have unsaved changes.  Are you sure you wish to leave the Visual Editor?';
		}
	
		allowVECloseSwitch = false;
	}


	allowVEClose = function() {
		window.onbeforeunload = function(){
			return null;
		}
	
		allowVECloseSwitch = true;
	}


	disableBadKeys = function() {
	
		//Disable backspace for normal frame but still keep backspace functionality in inputs.  Also disable enter.
		$(document).bind('keypress', disableBadKeysCallback);
		$(document).bind('keydown', disableBadKeysCallback);
	
		//Disable backspace and enter for iframe
		$i('html').bind('keypress', disableBadKeysCallback);
		$i('html').bind('keydown', disableBadKeysCallback);
		
	}
	
	
	disableBadKeysCallback = function(event) {
		
		//8 = Backspace
		//13 = Enter
	
		var element = $(event.target); 
	
		if ( event.which === 8 && !element.is('input') && !element.is('textarea') ) {
			event.preventDefault();
			
			return false;
		}
	
		if ( event.which == 13 && !element.is('textarea') ) {
			event.preventDefault();
			
			return false;
		}
		
	}
/* END ANNOYANCE FIXER FUNCTIONS */


/* KEY SHORTCUTS */
	bindKeyShortcuts = function() {
		
		$(document).bind('keyup', keyUpShortcutsCallback);
		$i('html').bind('keyup', keyUpShortcutsCallback);
								
		$(document).bind('keypress', keyPressShortcutsCallback);
		$i('html').bind('keypress', keyPressShortcutsCallback);
		
		return true;
		
	}
	
	
	keyUpShortcutsCallback = function(event) {
			
		/* Escape key doesn't work well with keypress so we must use keyup event. */
								
		/* Bind escape key to close out of block type selector and any task */
		if ( event.which === 27 ) {
									
			if ( typeof jQuery().grid == 'function' && $i('#block-type-popup').is(':visible') )
				Headway.iframe.grid('hideBlockTypePopupAndNewBlock', {});
			
			if ( $('div#task-notification').length > 0 )
				hideTaskNotification($('div#task-notification').data('closeCallback'));
			
			event.preventDefault();
			
		}
		
	}
	
	
	keyPressShortcutsCallback = function(event) {

		/* FF || WebKit */
				
		/* Bind save to Ctrl + S */
		if ( (event.which === 115 && event.ctrlKey === true) || (event.which === 19 && event.ctrlKey === true) ) {
			
			save();
			
			event.preventDefault();
			event.stopPropagation();
			
		}
		
		/* Bind panel toggle to Ctrl + P */
		if ( (event.which === 112 && event.ctrlKey === true) || (event.which === 16 && event.ctrlKey === true) ) {
			
			togglePanel();
			
			event.preventDefault();
			event.stopPropagation();
			
		}
		
		/* Bind layout selector toggle to Ctrl + L */
		/* FF || WebKit */
		if ( (event.which === 108 && event.ctrlKey === true) || (event.which === 12 && event.ctrlKey === true) ) {
			
			toggleLayoutSelector();
			
			event.preventDefault();
			event.stopPropagation();
			
		}
				
		/* Bind Live CSS toggle to Ctrl + E */
		/* WebKit */
		if ( event.which === 5 && event.ctrlKey === true ) {
			
			if ( !boxOpen('live-css') ) {
								
				openBox('live-css');
				
			} else {
								
				closeBox('live-css');
				
			}
			
			event.preventDefault();
			event.stopPropagation();
			
		}

		/* Bind Inspector Toggle to Ctrl + I  */
		if ( event.which === 9 && event.ctrlKey === true ) {
				
			if ( typeof toggleInspector != 'function' )
				return;

			toggleInspector();
			
			event.preventDefault();
			event.stopPropagation();
			
		}
		
	}
/* END KEY SHORTCUTS */


/* BLOCK FUNCTIONS */
	getBlock = function(element) {
		//If invalid selector, do not go any further
		if ( $(element).length === 0 ) {
			return $;
		}
		
		//Find the actual block node
		if ( $(element).hasClass('block') ) {
			block = $(element);
		} else if ( $(element).parents('.block').length === 1 ) {
			block = $(element).parents('.block');
		} else {
			block = false;
		}
		
		return block;
	}


	getBlockID = function(element) {
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
		
		//Pull out ID
		return block.attr('id').replace('block-', '');
	}
	
	
	getBlockType = function(element) {
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
		
		var classes = block.attr('class').split(' ');
	    
		for(i = 0; i <= classes.length - 1; i++){
			if(classes[i].indexOf('block-type-') !== -1){
				var blockType = classes[i].replace('block-type-', '');
			}
		}	
		
		return blockType;	
	}
	
	
	getBlockTypeNice = function(type) {
		
		if ( typeof type != 'string' ) {
			return false;
		}
		
		return type.replace('-', ' ').capitalize();
		
	}
	
	
	getBlockTypeIcon = function(blockType, blockInfo) {
		
		if ( typeof blockInfo == 'undefined' )
			blockInfo = false;
			
		if ( typeof Headway.allBlockTypes[blockType] != 'object' )
			return null;
			
		if ( blockInfo === true )
			return Headway.blockTypeURLs[blockType] + '/icon-white.png';
			
		return Headway.blockTypeURLs[blockType] + '/icon.png';
		
	}
	
	
	getBlockTypeObject = function(blockType) {
		
		var blockTypes = Headway.allBlockTypes;
		
		if ( typeof blockTypes[blockType] === 'undefined' )
			return {'fixed-height': false};
		
		return blockTypes[blockType];
		
	}


	getBlockGridWidth = function(element) {
		
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
			    
		//Cycle through classes to find width
		var classes = block.attr('class').split(' ');
		
		for(i = 0; i <= classes.length - 1; i++){
			if(classes[i].indexOf('grid-width-') !== -1){
				var width = classes[i].replace('grid-width-', '');
			}
		}	
		
		//If not block width still, try the column
		if ( typeof width === 'undefined' ) {
			var column = block.parents('.column');
			
			//If no column, then we can't do anything
			if ( column.length === 0 ) {
				return false;
			}
			
			var classes = column.attr('class').split(' ');
			
			for(i = 0; i <= classes.length - 1; i++){
				if(classes[i].indexOf('grid-width-') !== -1){
					var width = classes[i].replace('grid-width-', '');
				}
			}	
		}
		
		return width;
		
	}
	
	
	getBlockGridLeft = function(element) {
		
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
			    
		//Cycle through classes to find left
		var classes = block.attr('class').split(' ');
		
		for(i = 0; i <= classes.length - 1; i++){
			if(classes[i].indexOf('grid-left-') !== -1){
				var left = classes[i].replace('grid-left-', '');
			}
		}	
		
		//If not block width still, try the column
		if ( typeof left === 'undefined' ) {
			var column = block.parents('.column');
			
			//If no column, then we can't do anything
			if ( column.length === 0 ) {
				return false;
			}
			
			var classes = column.attr('class').split(' ');
			
			for(i = 0; i <= classes.length - 1; i++){
				if(classes[i].indexOf('grid-left-') !== -1){
					var left = classes[i].replace('grid-left-', '');
				}
			}	
		}
		
		return left;
		
	}

	
	getBlockDimensions = function(element) {
		
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
		
		return {
			width: getBlockGridWidth(block),
			height: block.height()
		}
		
	}
	
	
	getBlockWidthPixels = function(element) {
		
		var block = getBlock(element);
		
		return block.width();
		
	}
	
	
	getBlockHeightPixels = function(element) {
		
		var block = getBlock(element);
		
		return block.height();
		
	}

	
	getBlockPosition = function(element) {
		
		var block = getBlock(element);
		
		if ( !block ) {
			return false;
		}
		
		return {
			left: getBlockGridLeft(block),
			top: block.position().top
		}
		
	}
	
	
	getAvailableBlockID = function(async) {
		
		if ( typeof async == 'undefined' )
			var async = true;
		
		/* Get the ready block ID */
		var readyBlockID = Headway.availableBlockID;
		
		/* Retrieve the block ID that can be used. */
			/* Blacklist IDs in the grid already */
			var blockIDBlacklist = [readyBlockID];
		
			$i('.block').each(function() {
			
				blockIDBlacklist.push(getBlockID($(this)));
			
			});
										
			$.ajax(Headway.ajaxURL, {
				type: 'POST',
				async: async,
				data: {
					action: 'headway_visual_editor',
					method: 'get_available_block_id',
					security: Headway.security,
					block_id_blacklist: blockIDBlacklist
				},
				success: function(response) {

					if ( isNaN(response) )
						return;

					Headway.availableBlockID = response;

				}
			});
				
		/* Return the ID stored before. */
		return readyBlockID;
		
	}
	
	
	getAvailableBlockIDBatch = function(numberOfIDs) {

		/* Add any blocks in the layout to the blacklist since the PHP/AJAX won't know about unsaved blocks */
		var blockIDBlacklist = [];

		$i('.block').each(function() {

			blockIDBlacklist.push(getBlockID(this));

		});

		if ( typeof numberOfIDs == 'undefined' || isNaN(numberOfIDs) )
			numberOfIDs = 10;
		
		/* Do the request */
		var request = $.ajax(Headway.ajaxURL, {
			type: 'POST',
			async: false,
			data: {
				action: 'headway_visual_editor',
				method: 'get_available_block_id_batch',
				security: Headway.security,
				block_id_blacklist: blockIDBlacklist,
				number_of_ids: numberOfIDs
			}
		});
		
		return $.parseJSON(request.responseText.replace(/&quot;/g, '"'));

	}
	
	
	isBlockMirrored = function(element) {
		
		var block = getBlock(element);
		
		return block.hasClass('block-mirrored');
		
	}
	
	
	getBlockMirrorOrigin = function(element) {
		
		var block = getBlock(element);
		
		if ( !isBlockMirrored(block) )
			return false;
			
		//Cycle through classes to find the origin
		var classes = block.attr('class').split(' ');

		for(i = 0; i <= classes.length - 1; i++){
			if(classes[i].indexOf('block-mirroring-') !== -1){
				var origin = classes[i].replace('block-mirroring-', '');
			}
		}
		
		return ( typeof origin == 'string' ) ? origin : false;
		
	}

	
	loadBlockContent = function(args) {

		var settings = {};
		
		var defaults = {
			blockElement: false,
			blockSettings: {},
			blockOrigin: false,
			blockDefault: false,
			callback: function(args){},
			callbackArgs: null
		};
		
		$.extend(settings, defaults, args);
				
		var blockContent = settings.blockElement.find('div.block-content');
		var blockType = getBlockType(settings.blockElement);
		
		if ( Headway.mode == 'grid' && !getBlockTypeObject(blockType)['show-content-in-grid'] )
			return blockContent.html('<p class="hide-content-in-grid-notice"><strong>Notice:</strong> <em>' + getBlockTypeNice(blockType) + '</em> blocks do not display in the Grid Mode.  Please switch to either the Manage or Design mode to see the content in this block.</p>');
			
		createCog(blockContent, true, true, Headway.iframe.contents(), 1);
		blockContent.siblings('h3.block-type').hide();

		return blockContent.load(Headway.ajaxURL, {
			action: 'headway_visual_editor',
			method: 'load_block_content',
			unsaved_block_settings: settings.blockSettings,
			block_origin: settings.blockOrigin,
			block_default: settings.blockDefault,
			layout: Headway.currentLayout,
			mode: Headway.mode,
			security: Headway.security
		}, function(){
			
			if ( typeof settings.callback == 'function' )
				settings.callback(settings.callbackArgs);
			
			blockContent.siblings('h3.block-type').show();
			
		});
		
	}


	addBlockControls = function(showOptions, showDelete) {

		if ( typeof showOptions == 'undefined' )
			var showOptions = false;
		
		if ( typeof showDelete == 'undefined' )
			var showDelete = false;
		
		var blocks = $i('.block');
		
		blocks.each(function() {
			
			var id = getBlockID(this);
			var type = getBlockType(this);	
			var typeNice = getBlockTypeNice(type);
				
			var tooltipID = 'This is the ID for the block.  The ID of the block is displayed in the WordPress admin panel if it is a widget area or navigation block.  Also, this can be used with advanced developer functions.';
			var tooltipType = 'Click to change the block type.';
			var tooltipMirror = 'This block is set to mirror another block.  A mirrored block is always set to clone the origin block.  If the block that this block is mirroring is updated, then so will this one.  You can unmirror the block at any time by opening the options for this block (top right of block) then going to Config.'
			var tooltipOptions = 'Show the options for this block.';
			var tooltipDelete = 'Delete this block.';

			var blockTypeIconURL = getBlockTypeIcon(type, true);
			var blockTypeIconStyle = blockTypeIconURL ? ' style="background-image:url(' + blockTypeIconURL + ');"' : null;

			$(this).append('\
				<div class="block-info">\
					<span class="id tooltip" title="' + tooltipID + '">' + id + '</span>\
					<span class="type type-' + type + ' tooltip" title="' + tooltipType + '"' + blockTypeIconStyle + '>' + typeNice + '</span>\
				</div>');
				
			if ( isBlockMirrored($(this)) ) {
				
				var mirrorOrigin = getBlockMirrorOrigin($(this));
				
				$(this).find('.block-info').append('<span class="mirroring tooltip" title="' + tooltipMirror + '">Mirroring ' + mirrorOrigin + '</span>');
				
			}

			/* Make sure at least one of the buttons in block controls is going to be shown.  If both are hidden, don't add the block controls <div>. */
			if ( !(showOptions == false && showDelete == false) ) {
				
				var optionsButton = ( showOptions == true ) ? '<span class="options tooltip" title="' + tooltipOptions + '">Options</span>' : '';
				var deleteButton = ( showDelete == true ) ? '<span class="delete tooltip" title="' + tooltipDelete + '">Delete</span>' : '';
				
				$(this).append('\
					<div class="block-controls">\
						' + optionsButton + '\
						' + deleteButton + '\
					</div>');
					
			}
				
		});
		
		bindBlockControls();
		
		setupTooltips('iframe');
				
	}
	
	
	getUnsavedBlockOptionValues = function(blockID) {
				
		var inputs = $('input[block_id="' + blockID + '"][name*="[settings]"]', 'div#visual-editor-hidden-inputs');
		var options = {};
					
		/* Construct the object to be outputted */
		inputs.each(function() {
		
			options[$(this).attr('option')] = $(this).val();
			
		});
								
		return Object.keys(options).length > 0 ? options : null;
		
	}
	
	
	bindBlockControls = function() {
				
		/* Block Type Icon */
		$i('body').delegate('.block div.block-info span.type', 'click', function(event) {
			
			var block = getBlock($(this));
			var blockInfo = $(this).parents('.block-info');
			
			var type = getBlockType(block);
			
			//If the block info is shown then hide it if they click the same button.  Otherwise show the block info.
			if ( !block.hasClass('block-info-show') ) {
			
				//Force the ID and block type icon to stay visible
				block.addClass('block-info-show');
			
				//Keep track of this block so we can remove the block-info-show class later.
				Headway.blockTypeSwitchBlock = block;
						
				showBlockTypePopup({top: block.position().top + 36, left: block.position().left + 5}, true);
			
				//Hide the current block type from the list
				Headway.blockTypePopup.find('li#block-' + type).addClass('block-type-hidden');
			
			} else {
								
				Headway.blockTypeSwitchBlock.removeClass('block-info-show');
				
				hideBlockTypePopup();
				
				delete Headway.blockTypeSwitchBlock;
				
			}
						
			event.preventDefault();
			
		});
		
		/* Options */
		$i('body').delegate('.block div.block-controls span.options', 'click', function(event) {
			
			var block = getBlock(this);
			
			var blockID = getBlockID(block);		    
			var blockType = getBlockType(block);		
			var blockTypeName = getBlockTypeNice(blockType);
									
			var readyTabs = function() {
				
				var tab = $('div#block-' + blockID + '-tab');
				
				/* Ready tab, sliders, and inputs */
				tab.tabs();
				setUpPanelInputs('div#block-' + blockID + '-tab');
				
				/* Refresh tooltips */
				setupTooltips();
				
				/* Call the open callback for the box panel */
				var callback = eval(tab.find('ul.sub-tabs').attr('open_js_callback'));
				callback({
					block: block,
					blockID: blockID,
					blockType: blockType
				});
				
				/* If it's a mirrored block, then hide the other tabs */
				if ( $('div#block-' + blockID + '-tab').find('select#input-' + blockID + '-mirror-block').val() != '' ) {
					
					$('div#block-' + blockID + '-tab ul.sub-tabs li:not(#sub-tab-config)').hide();
					$('div#block-' + blockID + '-tab').tabs('select', 'sub-tab-config-content');
					
				}
				
			}						
			
			var blockIDForTab = isNaN(blockID) ? ': ' + blockID : ' #' + blockID;
						
			addPanelTab('block-' + blockID, blockTypeName + ' Block' + blockIDForTab, {
				url: Headway.ajaxURL, 
				data: {
					action: 'headway_visual_editor',
					method: 'load_block_options',
					block_type: blockType,
					block_id: blockID,
					unsaved_block_options: getUnsavedBlockOptionValues(blockID),
					layout: Headway.currentLayout,
					security: Headway.security
				}, 
				callback: readyTabs}, true, true, 'block-type-' + blockType);
			
			$('div#panel').tabs('select', 'block-' + blockID + '-tab');
						
		});
		
		/* Delete */
		$i('body').delegate('.block div.block-controls span.delete', 'click', function(event) {
			
			if(!confirm('Are you sure you want to delete this block?')){
				return false;
			}	
			
			Headway.iframe.grid('deleteBlock', $(this));
			
		});
	
		/* Block Dimensions */
		$i('body').delegate('.block', 'mouseenter', function(event) {
				
			var self = this;	
			var firstSetup = typeof $(this).data('qtip') == 'undefined' ? true : false;

			if ( typeof disableBlockDimensions !== 'undefined' && disableBlockDimensions )
				return false;
				
			if ( firstSetup ) {
					
				$(this).qtip({
					style: {
						classes: 'ui-tooltip-headway ui-tooltip-block-dimensions'
					},
					position: {
						my: 'top center',
						at: 'bottom center',
						container: Headway.iframe.contents().find('body'),
						viewport: Headway.iframe,
						adjust: {
							method: 'shift'
						}
					},
					show: {
						delay: 600
					},
					hide: {
						delay: 25
					},
					content: {
						text: function(api) {

							var block = getBlock(this);

							var blockWidth = getBlockWidthPixels(block);	
							var blockHeight = getBlockHeightPixels(block);					
							var blockType = getBlockType(block);

							if ( getBlockTypeObject(blockType)['fixed-height'] ) {
							
								var blockHeight = blockHeight;
								var heightText = 'Height';
							
							} else {
							
								var blockHeight = Headway.mode == 'grid' ? blockHeight : block.css('minHeight').replace('px', '');
								var heightText = 'Min. Height';
							
							}
						
							var height = '<span class="block-height"><strong>' + heightText + ':</strong> ' + blockHeight + '<small>px</small></span>';
							var width = '<span class="block-width"><strong>Width:</strong> ' + blockWidth + '<small>px</small></span>';

							//Show different width info if it's responsive
							if ( $('#input-enable-responsive-grid label.checkbox-checked').length == 1 || (Headway.mode != 'grid' && Headway.responsiveGrid) )
								var width = '<span class="block-width"><strong>Max Width:</strong> <small>~</small>' + blockWidth + '<small>px</small></span>';

							var fluidMessage = !getBlockTypeObject(blockType)['fixed-height'] ? '<span class="block-fluid-height-message">Height will auto-expand</span>' : '';

							return width + ' <span class="block-dimensions-separator">&#9747;</span> ' + height + fluidMessage;

						}
					}
				});
				
				$(this).data('hoverWaitTimeout', setTimeout(function() {

					$(self).qtip('show');

				}, 600));
				
			}
						
		});
		
		$i('body').delegate('.block', 'mouseleave', function(event) {
			
			clearTimeout($(this).data('hoverWaitTimeout'));
						
		});
		
		/* Hide block dimensions if hover over a control or info icon */
		$i('body').delegate('.block-controls, .block-info', 'mouseenter', function(event) {
						
			var block = getBlock($(this));	
			
			block.qtip('hide');		
			clearTimeout(block.data('hoverWaitTimeout'));
			
		});
		
		$i('body').delegate('.block-controls, .block-info', 'mouseleave', function(event) {
						
			var block = getBlock($(this));	
			
			block.data('hoverWaitTimeout', setTimeout(function() {
				
				block.qtip('show');
				
			}, 300));
			
		});

	}


	initBlockTypePopup = function() {
				
		Headway.blockTypePopup = $('div#block-type-popup').clone();

		Headway.blockTypePopup.appendTo($i('.grid-container'));
		
		$i('#block-type-popup').delegate('li:not(#more-blocks)', 'click', function(event){			
			
			var blockType = $(this).attr('id').replace('block-type-', '');
			
			//Either create a new block or switch the type of the selected block
			if ( Headway.blockTypeSwitch === 'undefined' || Headway.blockTypeSwitch === false ) {
				
				Headway.iframe.grid('setupBlankBlock', blockType);
				
			} else {
				
				if ( !confirm('Are you sure you wish to switch block types?  All settings for this block will be lost.') ) {
					hideBlockTypePopup();
					
					return false;
				}
				
				Headway.iframe.grid('switchBlockType', Headway.blockTypeSwitchBlock, blockType);
				
			}
			
			//Keep it from bubbling
			event.stopPropagation();
			
		});
		
		
	}
	
	
	showBlockTypePopup = function(position, blockTypeSwitch) {
				
		if ( typeof blockTypeSwitch === 'undefined' || blockTypeSwitch === false ) {
			Headway.blockTypeSwitch = false;
		} else {
			Headway.blockTypeSwitch = true;
		}
				
		var blockTypePopupWidth = Headway.blockTypePopup.width();
		var blockTypePopupHeight = Headway.blockTypePopup.height();
				
		var bodyWidth = $i('body').width();
		var bodyHeight = $i('body').height();
		
		var iframeLeft = parseInt(Headway.iframe.css('paddingLeft').replace('px', ''));
				
		//If the position is a block object, figure it out from that.
		if ( typeof position.hasClass == 'function' && position.hasClass('block') ) {
			
			var block = position;
			
			var rightCutoffOffset = 20;
			var bottomCutoffOffset = 25;
			
			var blockTypePopupCSS = {
				top: block.position().top
			}
		
			//If block type popup runs over right edge, then flip the y-axis that the block type popup sits on			
			if ( block.offset().left + block.width() + blockTypePopupWidth + rightCutoffOffset > bodyWidth ) {
				blockTypePopupCSS.left = block.position().left + block.width() - blockTypePopupWidth - 10;
			} else {
				blockTypePopupCSS.left = block.position().left + block.width() + 10;
			}

			var iframeTop = parseInt(Headway.iframe.css('paddingTop').replace('px', ''));
				
			//iframeOffset has to be in both of these to offset itself
			var absoluteBottomOfSelector = block.position().top + blockTypePopupHeight + bottomCutoffOffset - Headway.iframe.contents().scrollTop();
			var screenBottom = Headway.iframe.height() - iframeTop;
		
			if ( absoluteBottomOfSelector >= screenBottom ) {
			
				var difference = absoluteBottomOfSelector - screenBottom;
						
				blockTypePopupCSS.top = block.position().top - difference;
			
			}
			
		//We have a pre-defined position
		} else {
			
			var blockTypePopupCSS = {
				top: position.top,
				left: position.left
			}
						
		}

		//Show all block types again
		Headway.blockTypePopup.find('.block-type-hidden').removeClass('block-type-hidden');

		Headway.blockTypePopup.show().css(blockTypePopupCSS);
				
		$(document).bind('mousedown', {hideBlock: true}, hideBlockTypePopup);
		Headway.iframe.contents().bind('mousedown', {hideBlock: true}, hideBlockTypePopup);
		
	}

	
	hideBlockTypePopup = function(event) {
		
		if ( typeof event == 'undefined' )
			event = {data: {hideBlock: false}};
		
		if ( event.data.hideBlock ) {
			
			//If clicking box, do not hide
			if ( $(event.target).parents('.block').length === 1 )
				return false;
			
			//If the popup isn't visible, don't try to hide
			if ( !Headway.blockTypePopup.is(':visible') )
				return false;
			
			//If clicking a block type option, do not let this function run
			if ( $(event.target).parents('#block-type-popup')[0] === Headway.blockTypePopup[0] )
				return false;
				
		}
			
		//Commence hiding
		Headway.blockTypePopup.hide();
		
		//Delete the block if it exists
		if ( event.data.hideBlock && typeof Headway.blankBlock !== 'undefined' )
			Headway.blankBlock.remove();
						
		if ( Headway.blockTypeSwitch ) {
			Headway.blockTypeSwitchBlock.removeClass('block-info-show');
			
			delete Headway.blockTypeSwitch;
		}
		
		$(document).unbind('mousedown', hideBlockTypePopup);		
		Headway.iframe.contents().unbind('mousedown', hideBlockTypePopup);
		
		return true;
		
	}
/* END BLOCK FUNCTIONS */


/* NOTIFICATIONS */
	showTaskNotification = function(message, closeCallback, noClose, opacity) {

		if ( $('#task-notification:visible').length )
			return;
				
		if ( typeof closeCallback == 'undefined' )
			var closeCallback = null;

		if ( typeof opacity == 'undefined' )
			var opacity = 1;

		if ( typeof noClose == 'undefined' || !noClose ) {
			var closeButton = '<span class="close">Close</span>';
		} else {
			var closeButton = '';
		}

		$('<div id="task-notification" class="notification"><p>' + message + '</p>' + closeButton + '</div>')
			.hide()
			.appendTo('body')
			.css('opacity', opacity)
			.fadeIn(350)
			.data('closeCallback', closeCallback);
			
		$('.close', 'div#task-notification').bind('click', function() {
			hideTaskNotification(closeCallback);
		});
		
	}
	
	
	hideTaskNotification = function(closeCallback) {
				
		if ( typeof closeCallback === 'function' ) {
			closeCallback();
		}		
				
		$('div#task-notification').fadeOut(350, function() {
			$(this).remove();
		});
		
	}
	
	
	showNotification = function(message, timer, error, id) {
								
		if ( typeof timer === 'undefined' )
			var timer = 3000;
		
		if ( typeof error === 'undefined' )
			var error = false;
		
		//Close out all other notifications
		$('div.notifcation:not(#task-notification)').remove();
		
		var notification = $('<div class="notification"><p>' + message + '</p></div>');
		
		if ( typeof id != 'undefined' )
			notification.attr('id', 'notification-' + id);

		if ( error )
			notification.addClass('notification-error');

		notification.hide()
					.appendTo('body')
					.fadeIn(350);
					
		notificationGlowAnimationLoop();
			
		setTimeout(function() {
			notification.fadeOut(1500, function() {
				$(this).remove();
			});
		}, timer);
		
		return notification;
		
	}
	
	
	notificationGlowAnimationLoop = function() {
	
		var notification = $('div.notification');
		var shadowColor = (!notification.hasClass('notification-error')) ? '00ffde' : 'C43C35';
	
		notification.animate({boxShadow: '0 0 15px #' + shadowColor}, 750, function() { 
		
			notification.animate({boxShadow: '0 0 0 #' + shadowColor}, 750, function(){ 
				notificationGlowAnimationLoop();
			});
		
		});
	
	}
/* END NOTIFICATIONS */


/* LOADING FUNCTIONS */
	/* Simple function to change loading bar. */
	setLoadingBar = function(percent, message, callback) {
		
		if ( (typeof loadingComplete != 'undefined' && loadingComplete == true) || (typeof iframeTimeout != 'undefined' && iframeTimeout == true) ) {
						
			/* Stop all animations */
			$('div.loading-bar-inside').stop(true);
			
			/* Don't animate again */
			return false;
			
		} 
		
		if ( typeof callback !== 'function' )
			callback = function(){};
			
		width = $('div.loading-bar').width() * (percent/100);
		
		$('div.loading-bar-inside').animate({'width': width}, 120, 'linear', callback);
		
		if ( percent == 100 )
			loadingComplete = true;

	}
/* END LOADING FUNCTIONS */


/* TITLE FUNCTIONS */
	/* Simple title change function */
	changeTitle = function(title) {

		return $('title').text(title);

	}


	startTitleActivityIndicator = function() {
		
		//If the title activity indicator has already been started, don't try to again.
		if ( typeof titleActivityIndicatorInstance === 'number' )
			return false;

		titleActivityIndicatorInstance = window.setInterval(titleActivityIndicator, 500);
		titleActivityIndicatorSavedTitle = $('title').text();

		return true;

	}


	stopTitleActivityIndicator = function() {

		if ( typeof titleActivityIndicatorInstance !== 'number' ) {

			return false;

		}

		window.clearInterval(titleActivityIndicatorInstance);

		changeTitle(titleActivityIndicatorSavedTitle);

		delete titleActivityIndicatorCounter;
		delete titleActivityIndicatorSavedTitle;
		delete titleActivityIndicatorInstance;

		return true;

	}


	/* Title indicator callback function */
	titleActivityIndicator = function() {

		/* Set up variables */
		if ( typeof titleActivityIndicatorCounter == 'undefined' ) {
			titleActivityIndicatorCounter = 0;
			titleActivityIndicatorCounterPos = true;
		}	


		/* Increase/decrease periods */
		if ( titleActivityIndicatorCounterPos === true ) {
			++titleActivityIndicatorCounter;
		} else {
			--titleActivityIndicatorCounter;
		}

		/* Flippy da switch */
		if ( titleActivityIndicatorCounter === 3) {
			titleActivityIndicatorCounterPos = false;
		} else if ( titleActivityIndicatorCounter === 0) {
			titleActivityIndicatorCounterPos = true;
		}

		var title = titleActivityIndicatorSavedTitle + '.'.repeatStr(titleActivityIndicatorCounter);

		changeTitle(title);

	}
/* END TITLE FUNCTIONS */


/* BOX FUNCTIONS */
	createBox = function(args) {
		var settings = {};
		
		var defaults = {
			id: null,
			title: null,
			description: null,
			content: null,
			src: null,
			load: null,
			width: 500,
			height: 300,
			center: true,
			closable: true,
			resizable: false,
			draggable: true,
			deleteWhenClosed: false,
			blackOverlay: false,
			blackOverlayOpacity: .6,
			blackOverlayIframe: false
		};
		
		$.extend(settings, defaults, args);
				
		/* Create box */
			var box = $('<div class="box" id="box-' + settings.id + '"><div class="box-top"></div><div class="box-content-bg"><div class="box-content"></div></div></div>');
			
			box.attr('black_overlay', settings.blackOverlay);
			box.attr('black_overlay_opacity', settings.blackOverlayOpacity);
			box.attr('black_overlay_iframe', settings.blackOverlayIframe);
			box.attr('load_with_ajax', false);
				
		/* Move box into document */
			box.appendTo('div#boxes');
					
		/* Inject everything */
			/* If regular content and not iframe, just put it in */
			if ( typeof settings.src !== 'string' ) {
								
				box.find('.box-content').html(settings.content);
			
			/* Else use iframe */	
			} else {
				
				box.find('.box-content').html('<iframe src="' + settings.src + '" style="width: ' + settings.width + 'px; height: ' + parseInt(settings.height - 50) + 'px;"></iframe>');
								
				if ( typeof settings.load === 'function' ) {
					
					box.find('.box-content iframe').bind('load', settings.load);
					
				}
				
			}
		
			box.find('.box-top').append('<strong>' + settings.title + '</strong>');
			
			if ( typeof settings.description === 'string' ) {
				box.find('.box-top').append('<span>' + settings.description + '</span>');
			}
		
		/* Setup box */
			setupBox(settings.id, settings);
					
		return box;
	}
	
	
	setupBox = function(id, args) {
		
		var settings = {};
		
		var defaults = {
			width: 600,
			height: 300,
			center: true,
			closable: true,
			deleteWhenClosed: false,
			draggable: false,
			resizable: false
		};
				
		$.extend(settings, defaults, args);		
				
		var box = $('div#box-' + id);
				
		/* Handle draggable */
		if ( settings.draggable ) {
			
			box.draggable({
				handle: box.find('.box-top'),
				start: showIframeOverlay,
				stop: hideIframeOverlay
			});
			
			box.find('.box-top').css('cursor', 'move');
			
		}
		
		/* Make box closable */
		if ( settings.closable ) {
			
			/* If close button doesn't exist, create it. */
			box.find('.box-top').append('<span class="box-close"></span>');
			
			box.find('.box-close').bind('click', function(){
				closeBox(id, settings.deleteWhenClosed);
			});
			
		}
		
		/* Make box resizable */
		if ( settings.resizable ) {
			
			/* If close button doesn't exist, create it. */
			box.resizable({
				start: showIframeOverlay,
				stop: hideIframeOverlay,
				handles: 'n, e, s, w, ne, se, sw, nw',
				minWidth: settings.minWidth,
				minHeight: settings.minHeight
			});
			
		}
		
		/* Set box dimensions */
		box.css({
			width: settings.width,
			height: settings.height
		});

		/* Center Box */
		if ( settings.center ) {
			
			var marginLeft = -(box.width() / 2);
			var marginTop = -(box.height() / 2);
			
			box.css({
				top: '50%',
				left: '50%',
				marginLeft: marginLeft,
				marginTop: marginTop,
			});
			
		}
		
	}
	
	
	showIframeOverlay = function() {
		
		var overlay = $('div#iframe-overlay');
		var iframe = Headway.iframe;
		
		var iframeWidth = iframe.width();
		var iframeHeight = iframe.height();
				
		overlay.css({
			top: iframe.css('paddingTop'),
			left: iframe.css('paddingLeft'),
			width: iframeWidth,
			height: iframeHeight
		});
		
		overlay.show();
		
	}
	
	
	hideIframeOverlay = function() {
		
		/* Add a timeout for intense draggers */
		setTimeout(function(){
			$('div#iframe-overlay').hide();
		}, 250);
		
	}
	
	
	setupStaticBoxes = function() {
				
		$('div.box').each(function() {
		
			/* Fetch settings */
			var draggable = $(this).attr('draggable').toBool();
			var closable = $(this).attr('closable').toBool();
			var resizable = $(this).attr('resizable').toBool();
			var center = $(this).attr('center').toBool();
			var width = $(this).attr('width');
			var height = $(this).attr('height');
			var minWidth = $(this).attr('min_width');
			var minHeight = $(this).attr('min_height');			
						
			var id = $(this).attr('id').replace('box-', '');
																		
			setupBox(id, {
				draggable: draggable,
				closable: closable,
				resizable: resizable,
				center: center,
				width: width,
				height: height,
				minWidth: minWidth,
				minHeight: minHeight
			});
			
			/* Remove settings attributes */
			$(this).attr('draggable', null);
			$(this).attr('closable', null);
			$(this).attr('resizable', null);
			$(this).attr('center', null);
			$(this).attr('width', null);
			$(this).attr('height', null);
			$(this).attr('min_width', null);
			$(this).attr('min_height', null);
			
		});
		
	}
	
	
	openBox = function(id) {
		
		var id = id.replace('box-', '');
		var box = $('div#box-' + id);
		
		if ( box.length === 0 )
			return false;
		
		var blackOverlay = box.attr('black_overlay').toBool();
		var blackOverlayOpacity = box.attr('black_overlay_opacity');
		var blackOverlayIframe = box.attr('black_overlay_iframe').toBool();
		var loadWithAjax = box.attr('load_with_ajax').toBool();
		
		if ( blackOverlay && !boxOpen(id) ) {

			var overlay = $('<div class="black-overlay"></div>')
				.hide()
				.attr('id', 'black-overlay-box-' + id)
				.appendTo('body');

			if ( blackOverlayIframe === true )
				overlay.css('zIndex', 4);

			if ( !isNaN(blackOverlayOpacity) )
				overlay.css('background', 'rgba(0, 0, 0, ' + blackOverlayOpacity + ')');

			overlay.fadeIn(100);

		}
			
		if ( loadWithAjax) {
			
			createCog(box.find('.box-content'), true);
						
			box.find('.box-content').load(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'load_box_ajax_content',
				box_id: id,
				layout: Headway.currentLayout,
				security: Headway.security
			}, function() {
									
				var loadWithAjaxCallback = eval(box.attr('load_with_ajax_callback'));
								
				loadWithAjaxCallback.call();
				
			});
			
		}
			
		return box.fadeIn(100);
		
	}
	
	
	closeBox = function(id, deleteWhenClosed) {
		
		var id = id.replace('box-', '');
		var box = $('div#box-' + id);
		
		box.fadeOut(300, function(){
			
			if ( typeof deleteWhenClosed != 'undefined' && deleteWhenClosed == true )
				$(this).remove();
			
		});
		
		$('div#black-overlay-box-' + id).fadeOut(300, function() {
			$(this).remove();
		});
		
		return true;
		
	}
	
	
	boxOpen = function(id) {
		
		return $('div#box-' + id).is(':visible');
		
	}
	
	
	boxExists = function(id) {
		
		if ( $('div#box-' + id).length === 1 ) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
/* END BOX FUNCTIONS */


/* LAYOUT SELECTOR FUNCTIONS */
	layoutSelectorRevertCheck = function() {
		
		if ( $('.layout-item-customized').length > 1 ) {
			$('div#layout-selector-pages').removeClass('layout-selector-disallow-revert');
		} else {
			$('div#layout-selector-pages').addClass('layout-selector-disallow-revert');
		}
		
	}

	toggleLayoutSelector = function(noAnimation) {
		
		if ( typeof noAnimation === 'undefined' ){
			noAnimation = false;
		}
		
		var layout_selector = $('div#layout-selector-offset');

		if ( layout_selector.hasClass('open') ) {
			
			if ( !noAnimation ) {
				
				$('div#layout-selector-offset').animate({left: '-350px'}, 750, 'easeOutExpo').removeClass('open');

				$('iframe.content').animate({paddingLeft: '0'}, 750, 'easeOutExpo');
				
			} else {
				
				$('div#layout-selector-offset').css({left: '-350px'}).removeClass('open');

				$('iframe.content').css({paddingLeft: '0'});
				
			}
			
			$('body').addClass('layout-selector-hidden');
			
			$('span#layout-selector-toggle').text('Show Layout Selector');
			
			$.cookie('hide-layout-selector', true);

		} else {
			
			if ( !noAnimation ) {
				
				$('div#layout-selector-offset').animate({left: '-60px'}, 750, 'easeOutExpo').addClass('open');

				$('iframe.content').animate({paddingLeft: '295px'}, 750, 'easeOutExpo');
				
			} else {
				
				$('div#layout-selector-offset').css({left: '-60px'}).addClass('open');

				$('iframe.content').css({paddingLeft: '295px'});
				
			}
			
			$('body').removeClass('layout-selector-hidden');
			
			$('span#layout-selector-toggle').text('Hide Layout Selector');
			
			$.cookie('hide-layout-selector', false);

		}

	}
/* END LAYOUT SELECTOR FUNCTIONS */


/* PANEL FUNCTIONS */
	/* Tab Functions */
	$('ul#panel-top li span.close').live('click', function(){
				
		var tab = $(this).siblings('a').attr('href').replace('#', '').replace('-tab', '');
				
		return removePanelTab(tab);
		
	});
	
	
	addPanelTab = function(name, title, content, closable, closeOnLayoutSwitch, panelClass) {
		
		/* If the tab name already exists, don't try making it */
		if ( $('ul#panel-top li a[href="#' + name + '-tab"]').length !== 0 )
			return false;
		
		/* Set up default variables */
		if ( typeof closable == 'undefined' ) {
			var closable = false;
		}
		
		if ( typeof closeOnLayoutSwitch == 'undefined' ) {
			var closeOnLayoutSwitch = false;
		}
		
		if ( typeof panelClass == 'undefined' ) {
			var panelClass = false;
		}
		
		/* Add the tab */
		var tab = $('div#panel').tabs('add', '#' + name + '-tab', title);
		var panel = $('div#panel div#' +  name + '-tab');
		var tabLink = $('ul#panel-top li a[href="#' + name + '-tab"]');
		
		$(tabLink).bind('click', showPanel);
		
		showPanel();
		
		/* Add the panel class to the panel */
		panel.addClass('panel');
		
		/* If the content is static, just throw it in.  Otherwise get the content with AJAX */
		if ( typeof content == 'string' ) {
			
			panel.html(content);
			
		} else {
			
			var loadURL = content.url; 
			var loadData = content.data || false;
			
			var loadCallback = function() {
				
				if ( typeof content.callback == 'function' )
					content.callback.call();
			
				addPanelScrolling();
				
			};
			
			createCog(panel, true);
						
			$('div#panel div#' +  name + '-tab').load(loadURL, loadData, loadCallback);
			
		}
		
		if ( panelClass )
			panel.addClass('panel-' + panelClass);

		/* Add delete to tab link if the tab is closable */
		if ( closable ) {
					
			tabLink.parent().append('<span class="close">X</span>');
			
		}
		
		/* If the panel is set to close on layout switch, add a class to the tab itself so we can target it down the road */
		tabLink.parent().addClass('tab-close-on-layout-switch');
				
		return tab;
		
	}
	
	
	removePanelTab = function(name) {
		
		/* If tab doesn't exist, don't try to delete any tabs */
		if ( $('#' + name + '-tab').length === 0 ) {
			return false;
		}
		
		return $('div#panel').tabs('remove', name + '-tab');
		
	}
	
	
	removeLayoutSwitchPanels = function() {
		
		$('li.tab-close-on-layout-switch').each(function(){
			var id = $(this).find('a').attr('href').replace('#', '');
			
			$('div#panel').tabs('remove', id);
		});
		
	}


	/* Toggle visibility of visual editor panel */
	togglePanel = function(noAnimation) {

		if ( typeof noAnimation === 'undefined' )
			noAnimation = false;

		if ( $('div#panel').hasClass('panel-hidden') ) {

			showPanel(noAnimation);

		} else {

			hidePanel(noAnimation);

		}

	}
	
	
	hidePanel = function(noAnimation) {
		
		//If the panel is already hidden, don't go through any trouble.
		if ( $('div#panel').hasClass('panel-hidden') )
			return false;
		
		if ( typeof noAnimation === 'undefined' )
			noAnimation = false;
									
		var panelCSS = {'bottom': -$('div#panel').height()};
		var iframeCSS = {'paddingBottom': $('ul#panel-top').outerHeight()};
		var layoutSelectorCSS = {paddingBottom: $('ul#panel-top').outerHeight() + $('div#layout-selector-tabs').height()};
			
		if ( !noAnimation ) {

			$('div#panel').animate(panelCSS, 750, 'easeOutExpo').addClass('panel-hidden');
			$('iframe.content').animate(iframeCSS, 750, 'easeOutExpo');
			$('div#layout-selector-offset').animate(layoutSelectorCSS, 750, 'easeOutExpo');
						
			setTimeout(repositionTooltips, 400);

		} else {

			$('div#panel').css(panelCSS).addClass('panel-hidden');
			$('iframe.content').css(iframeCSS);
			$('div#layout-selector-offset').css(layoutSelectorCSS);

		}

		$('body').addClass('panel-hidden');

		/* Add class to button */
		$('ul#panel-top li#minimize span').addClass('active');
		
		/* Hide the panel top handle to disallow resizing while it's hidden */
		$('div#panel-top-handle').fadeOut(200);
		
		/* De-select the selected block while the panel is hidden */
		$i('.block-selected').removeClass('block-selected block-hover');

		$.cookie('hide-panel', true);
		
		return true;
		
	}
	
	
	showPanel = function(noAnimation) {
				
		//If the panel is already visible, don't go through any trouble.
		if ( !$('div#panel').hasClass('panel-hidden') )
			return false;
		
		if ( typeof noAnimation === 'undefined' )
			noAnimation = false;
			
		var panelCSS = {'bottom': 0};
		var iframeCSS = {'paddingBottom': $('div#panel').outerHeight()};
		var layoutSelectorCSS = {paddingBottom: $('div#panel').outerHeight() + $('div#layout-selector-tabs').height()};
					
		if ( noAnimation !== true ) {

			$('div#panel').animate(panelCSS, 750, 'easeOutExpo').removeClass('panel-hidden');
			$('iframe.content').animate(iframeCSS, 750, 'easeOutExpo');
			$('div#layout-selector-offset').animate(layoutSelectorCSS, 750, 'easeOutExpo');
			
			setTimeout(repositionTooltips, 400);

		} else {

			$('div#panel').css(panelCSS).removeClass('panel-hidden');
			$('iframe.content').css(iframeCSS);
			$('div#layout-selector-offset').css(layoutSelectorCSS);

		}

		$('body').removeClass('panel-hidden');

		/* Remove class from button */
		$('ul#panel-top li#minimize span').removeClass('active');
		
		/* Show the panel top handle to allow resizing again */
		$('div#panel-top-handle').fadeIn(200);
		
		/* Re-select the the block if a block optiosn panel tab is open. */
		$i('#' + $('ul#panel-top > li.ui-state-active a').attr('href').replace('#', '').replace('-tab', '')).addClass('block-selected block-hover');
		
		$.cookie('hide-panel', false);
		
		return true;
		
	}

	
	/* Scrolling */
	addPanelScrolling = function() {
		
		$('ul.sub-tabs').scrollbarPaper();
		$('div.sub-tabs-content-container').scrollbarPaper();
		
	}
/* END PANEL FUNCTIONS */


/* COMPLEX INPUTS ACROSS ALL MODES */
	updateBlockMirrorStatus = function(input, block, value, updateTooltips) {
		
		/* If there is no input provided, then create an empty jQuery so no errors show up */
		if ( typeof input == 'undefined' || input == false )
			input = $();
			
		if ( typeof updateTooltips == 'undefined' )
			updateTooltips = true;
			
		var block = getBlock(block);
		
		if ( value == '' ) { 
							
			block.find('.block-info span.mirroring').remove();
			
			input.parents(".panel").find("ul.sub-tabs li:not(#sub-tab-config)").show();
			
		} else { 
			
			var tooltipMirror = 'This block is set to mirror another block.  A mirrored block is always set to clone the origin block.  If the block that this block is mirroring is updated, then so will this one.  You can unmirror the block at any time by opening the options for this block (top right of block) then going to Config.'
			
			block.find('.block-info').append('<span class="mirroring tooltip" title="' + tooltipMirror + '">Mirroring ' + value + '</span>');
			
			/* Since we added the Mirroring status, the tooltips have to be refreshed. */
			if ( updateTooltips )
				setupTooltips('iframe');
			
			input.parents(".panel").find("ul.sub-tabs li:not(#sub-tab-config)").hide();
			
		}
		
	}
/* END COMPLEX INPUTS */


/* SAVE FUNCTIONS */
	save = function() {
		
		/* If saving isn't allowed, don't try to save. */
		if ( typeof isSavingAllowed === 'undefined' || isSavingAllowed === false ) {
			return false;
		}
		
		/* If currently saving, do not do it again. */
		if ( typeof currentlySaving !== 'undefined' && currentlySaving === true ) {
			return false;
		}
	
		currentlySaving = true;
		
		savedTitle = $('title').text();
		saveButton = $('span#save-button');
	
		saveButton
			.text('Saving...')
			.addClass('active')
			.css('cursor', 'wait');
		
		/* Change the title */
		changeTitle('Visual Editor: Saving');
		startTitleActivityIndicator();
	
		saveAnimationLoop();
		
		/* Serialize options */
		var options = $('div#visual-editor-hidden-inputs input').serialize();

		/* Do the stuff */
		$.post(Headway.ajaxURL, {
			action: 'headway_visual_editor',
			method: 'save_options',
			options: options,
			security: Headway.security,
			layout: Headway.currentLayout,
			mode: Headway.mode
		}, function(response) {
			
			delete currentlySaving;
			
			/* If it's not a successful save, revert the save button to normal and display an alert. */
			if ( response !== 'success' ) {
								
				saveButton.stop(true);
			
				saveButton.text('Save');
				saveButton.removeClass('active');

				saveButton.css('cursor', 'pointer');
							
				return showNotification('Error: Could not save!  Please try again.', 6000, true);
				
			/* Successful Save */
			} else {
				
				saveButton.animate({boxShadow: '0 0 0 #00ffde'}, 350);
				
				setTimeout(function() {

					saveButton.css('boxShadow', '');
					saveButton.stop(true);

					saveButton.text('Save');
					saveButton.removeClass('active');

					saveButton.css('cursor', 'pointer');

					/* Clear out hidden inputs */
					clearHiddenInputs();

					/* Set the current layout to customized after save */
					$('li.layout-selected').addClass('layout-item-customized');
					
					layoutSelectorRevertCheck();

					/* Fade back to inactive save button. */
					disallowSaving();				

					/* Reset the title and show the saving complete notification */
					setTimeout(function() {

						stopTitleActivityIndicator();
						changeTitle(savedTitle);

						showNotification('Saving complete!', 3500);

					}, 150);

				}, 350);

				allowVEClose(); //Do this here in case we have some speedy folks who want to close VE ultra-early after a save.
				
			}

		});
	
	}


	updatePanelInputHidden = function(args) {
				
		if ( typeof args.input !== 'undefined' && $(args.input).length === 1 ) {
			
			var originalInput = $(args.input);
			
			var optionID = originalInput.attr('name').toLowerCase();
			var optionGroup = originalInput.attr('group').toLowerCase();
			
			var optionValue = args.value;

			var isBlock = originalInput.attr('is_block');
			var blockID = originalInput.attr('block_id');

			var callback = eval(originalInput.attr('callback'));
			
		} else {
			
			var optionID = args.id.toLowerCase();
			var optionGroup = (typeof args.group != 'undefined') ? args.group.toLowerCase() : false;

			var optionValue = args.value;
			
			var isBlock = args.isBlock;
			var blockID = args.blockID;

			var callback = (typeof args.callback === 'function') ? args.callback : false;
			
		}
			
		/* Prepare the name and class for the input(s) */
		if ( isBlock == 'true' ) {
			
			var hiddenInputClass = 'input-' + blockID + '-' + optionID + '-hidden';
			var hiddenInputName = 'blocks[' + blockID + '][settings][' + optionID + ']';
			
		} else {
			
			var hiddenInputClass = 'input-' + optionGroup + '-' + optionID + '-hidden';
			var hiddenInputName = 'options[' + optionGroup + '][' + optionID + ']';
			
		}
		
		/* Remove the existing inputs to keep it simple. */
		$('input.' + hiddenInputClass, 'div#visual-editor-hidden-inputs').remove();
					
		/* If the value is anything but an object, then one input will do. */
		if ( typeof optionValue != 'object' ) {
			
			$('<input type="hidden" class="' + hiddenInputClass + '" name="' + hiddenInputName + '" />')
				.val(optionValue)
				.appendTo('div#visual-editor-hidden-inputs');

		/* If the value is an object/array, then create multiple hidden inputs. */	
		} else {
			
			if ( optionValue !== null ) {
				
				$.each(optionValue, function(index, propertyValue) {

					$('<input type="hidden" class="' + hiddenInputClass + '" name="' + hiddenInputName + '[]" />')
						.val(propertyValue)
						.attr('array_input', 'true')
						.appendTo('div#visual-editor-hidden-inputs');

				});
				
			} else {
				
				$('<input type="hidden" class="' + hiddenInputClass + '" name="' + hiddenInputName + '" />')
					.val('')
					.appendTo('div#visual-editor-hidden-inputs');
				
			}		
			
		}
		
		/* Retrieve the hidden inputs again so they can be manipulated. */
		var hiddenInputs = $('input.' + hiddenInputClass);
						
		/* If it's a block hidden input, add option ID and block IDs for updating block content next. */
		if ( isBlock == 'true' ) {
			
			hiddenInputs.attr('option', optionID);
			hiddenInputs.attr('block_id', blockID);
			
		}
			
 		/* If it's a block input then update the block content then run the callback */
		if ( isBlock == 'true' ) {
			
			/* Flood Control */
			if ( typeof updateBlockContentFloodTimeout != 'undefined' )
				return;
				
			var blockElement = $i('#block-' + blockID);

			var newBlockSettings = {};

			$('input[block_id="' + blockID + '"]', 'div#visual-editor-hidden-inputs').each(function() {

				/* Handle regular inputs. */
				if ( typeof $(this).attr('array_input') == 'undefined' || $(this).attr('array_input') != 'true' ) {

					newBlockSettings[$(this).attr('option')] = $(this).val();

				/* Multi-selects and multi-image inputs. */	
				} else {

					if ( typeof newBlockSettings[$(this).attr('option')] == 'undefined' )
						newBlockSettings[$(this).attr('option')] = [];

					newBlockSettings[$(this).attr('option')].push($(this).val());

				}

			});
			
			/* Update the block content */
			loadBlockContent({
				blockElement: blockElement,
				blockSettings: {
					settings: newBlockSettings,
					dimensions: getBlockDimensions(blockElement),
					position: getBlockPosition(blockElement)
				},
				blockOrigin: blockID,
				blockDefault: {
					type: getBlockType(blockElement),
					id: 0,
					layout: Headway.currentLayout
				},
				callback: callback,
				callbackArgs: args
			});
			
			updateBlockContentFloodTimeout = setTimeout(function() {
				
				delete updateBlockContentFloodTimeout;
				
			}, 500);
			
		/* Else if it's not a block input (just a regular panel input), then run the callback right away. */
		} else {

			if ( typeof callback == 'function' )
				callback(args);	
					
		}
		
		
	}
	
	
	updateBlockPositionHidden = function(id, position) {
		
		if ( typeof id === 'string' && id.indexOf('block-') !== -1 ) {
			var id = id.replace('block-', '');
		}
		
		var hidden_input_class = 'block-' + id + '-position';
		var position = position.left + ',' + position.top;

		/* Create input if it doesn't exist—otherwise, update it. */
		if ( $('input.' + hidden_input_class, 'div#visual-editor-hidden-inputs').length === 0 ) {
			
			$('<input type="hidden" class="' + hidden_input_class + '" name="blocks[' + id + '][position]" value="' + position + '"  />')
				.appendTo('div#visual-editor-hidden-inputs');
			
		} else {
			
			$('input.' + hidden_input_class, 'div#visual-editor-hidden-inputs').val(position);
			
		}
		
	}
	
	
	updateBlockDimensionsHidden = function(id, dimensions) {
		
		if ( typeof id === 'string' && id.indexOf('block-') !== -1 ) {
			var id = id.replace('block-', '');
		}
		
		var hidden_input_class = 'block-' + id + '-dimensions';
		var dimensions = dimensions.width + ',' + dimensions.height;

		/* Create input if it doesn't exist—otherwise, update it. */
		if ( $('input.' + hidden_input_class, 'div#visual-editor-hidden-inputs').length === 0 ) {
			
			$('<input type="hidden" class="' + hidden_input_class + '" name="blocks[' + id + '][dimensions]" value="' + dimensions + '"  />')
				.appendTo('div#visual-editor-hidden-inputs');
			
		} else {
			
			$('input.' + hidden_input_class, 'div#visual-editor-hidden-inputs').val(dimensions);
			
		}
		
	}
	
	
	addDeleteBlockHidden = function(id) {
		
		if ( typeof id === 'string' && id.indexOf('block-') !== -1 ) {
			var id = id.replace('block-', '');
		}
		
		var hidden_input_class = 'block-' + id + '-delete';
		
		$('<input type="hidden" class="' + hidden_input_class + '" name="blocks[' + id + '][delete]" value="true"  />')
			.appendTo('div#visual-editor-hidden-inputs');
			
		//Remove the hidden input flags that may exist for the block
		$('div#visual-editor-hidden-inputs input.block-' + id + '-new').remove();
		$('div#visual-editor-hidden-inputs input.block-' + id + '-position').remove();
		$('div#visual-editor-hidden-inputs input.block-' + id + '-dimensions').remove();
		
	}
	
	
	addNewBlockHidden = function(id, type) {
		
		if ( typeof id === 'string' && id.indexOf('block-') !== -1 ) {
			var id = id.replace('block-', '');
		}
		
		var hidden_input_class = 'block-' + id + '-new';
		
		$('<input type="hidden" class="' + hidden_input_class + '" name="blocks[' + id + '][new]" value="' + type + '"  />')
			.appendTo('div#visual-editor-hidden-inputs');
			
		//Remove the delete hidden if it exists
		$('div#visual-editor-hidden-inputs input.block-' + id + '-delete').remove();
		
	}
	
	
	clearHiddenInputs = function() {
		
		$('div#visual-editor-hidden-inputs').html('');
		
	}


	allowSaving = function() {
						
		//If it's the layout mode and there no blocks on the page, then do not allow saving.
		if ( Headway.mode == 'grid' && $i('.block').length === 0 ) {
			disallowSaving();
			
			return false;
		}				
		/* If saving is already allowed, don't do anything else	*/
		if ( typeof isSavingAllowed !== 'undefined' && isSavingAllowed === true ) {
			return;
		}		
				
		//Put animation in timeout so the animation actually happens instead of a jump to the end.  Still haven't figured out why this happens.
		setTimeout(function(){
			$('span#save-button').stop(true).show().animate({opacity: 1}, 350);
		}, 1);
		
		isSavingAllowed = true;
		
		
		/* Set reminder whne trying to leave that there are changes. */
		prohibitVEClose();
		
		return true;
		
	}
	
	
	disallowSaving = function() {
		
		isSavingAllowed = false;
		
		setTimeout(function(){
			
			$('span#save-button').stop(true).animate({opacity: 0}, 350, function() {
				$(this).hide();
			});
			
		}, 1);
		
		/* User can safely leave VE now--changes are saved. */
		allowVEClose();

		return true;
		
	}
/* END SAVE BUTTON FUNCTIONS */


/* TOUR FUNCTIONS */
	startTour = function() {
		
		$('<div class="black-overlay"></div>')
			.hide()
			.attr('id', 'black-overlay-tour')
			.css('zIndex', 15)
			.appendTo('body')
			.show();
		
		var steps = [
			{ 
				beginning: true, 
				title: 'Welcome to the Headway Visual Editor!', 
				content: 'If this is your first time in the Headway visual editor, we recommend you get a feel for things by following this tour.<br /><br/>If you\'re experienced or want to jump in right away, just click the close button in the top right at any time.' 
			},
			
			{ 
				target: $('li#mode-grid'), 
				title: 'Grid Mode', 
				content: 'The grid mode is the heart and soul to Headway 3.0.  Inside it you\'ll find Headway\'s revolutionary Grid system.<br /><br />If you can think of a layout, you can make it with the Grid!  As long as it\'s within reason, of course :-).' 
			},
			
			{ 
				target: $('li#mode-manage'), 
				title: 'Manage Mode', 
				content: 'The manage mode is a convenient location to change the settings for blocks, upload your header image, and more.' 
			},
						
			{ 
				target: $('li#mode-design'), 
				title: 'Design Mode', 
				content: 'Once you\'re satisfied with what you created with the grid, jump to the Design Mode to add colors, fonts, backgrounds, rounded corners, and other nifty design elements to your website.<br /><br />Please note, you don\'t have to use the modes in any particular order.' 
			},
			
			{ 
				target: $('div#layout-selector'), 
				title: 'Layout Selector', 
				content: 'Besides LayoutGrid, the layout selector will allow you to customize any layout with paramount granularity.  Or, if you don\'t want to customize every page, you can customize its parents or assign each layout a template to speed things up.<br /><br />The layout selector will allow you to be as precise or broad as you wish.  It\'s completely up to you!', 
				position: {
					my: 'left center',
					at: 'right center',
					vertical: true
				}
			},
			
			{ 
				target: $('li#options span'), 
				title: 'Panel Options', 
				content: 'In here you can open the Live CSS editor, re-run this tour, and more!', 
				position: {
					my: 'left center',
					at: 'right center',
					vertical: true
				}
			},
			
			{ 
				target: $('li#search span'), 
				title: 'Visual Editor Search', 
				content: 'Looking for a specific option or tab?  Type it in here and it\'ll get you going straight to where you need to go!', 
				position: {
					my: 'right center',
					at: 'left center',
					vertical: true
				},
				end: true 
			}
		];

		$(document.body).qtip({
			id: 'tour', // Give it an ID of ui-tooltip-tour so we an identify it easily
			content: {
				text: '<p>' + steps[0].content + '</p><span id="tour-next" class="tour-button">Next<span class="arrow"></span></span>', // Use first steps content...
				title: {
					text: steps[0].title, // ...and title
					button: 'Skip Tour'
				}
			},
			style: {
				classes: 'ui-tooltip-tour',
				tip: {
					width: 18,
					height: 10,
					mimic: 'center'
				}
			},
			position: {
				my: 'center',
				at: 'center',
				target: $(window), // Also use first steps position target...
				viewport: $(window), // ...and make sure it stays on-screen if possible
				adjust: {
					y: 5
				}
			},
			show: {
				event: false, // Only show when show() is called manually
				ready: true // Also show on page load
			},
			hide: false, // Don't hide unless we call hide()
			events: {
				render: function(event, api) {
					
					// Grab tooltip element
					var tooltip = api.elements.tooltip;

					// Track the current step in the API
					api.step = 0;

					// Bind custom custom events we can fire to step forward/back
					tooltip.bind('next prev', function(event) {
						// Increase/decrease step depending on the event fired
						api.step += event.type === 'next' ? 1 : -1;
						api.step = Math.min(steps.length - 1, Math.max(0, api.step));

						// Set new step properties
						var current = steps[api.step];
						
						//Make sure the target really exists
						while ( current.target.length === 0 ) {
							
							// Increase/decrease step depending on the event fired
							api.step += event.type === 'next' ? 1 : -1;
							api.step = Math.min(steps.length - 1, Math.max(0, api.step));

							// Set new step properties
							var current = steps[api.step];
							
						}
						
						//Fade the overlay out if it's visible
						if ( $('div#black-overlay-tour').length === 1 ) {
							
							$('<div id="tour-overlay"></div>')
								.insertAfter('div#black-overlay-tour')
								.css({
									width: '100%',
									height: '100%',
									position: 'fixed',
									zIndex: 6,
									display: 'block',
									background: 'transparent'
								})
							
							$('div#black-overlay-tour').fadeOut(100, function() {
								$(this).remove();
							});
							
						}
							
						//Switch the tooltip's content and position
						if ( current ) {
							
							//Run the callback if it exists
							if ( typeof current.callback === 'function' ) {
								current.callback.apply(api);
							}
							
							//Set the position
							if ( typeof current.position !== 'undefined' ) {
																
								api.set('position.my', current.position.my);
								api.set('position.at', current.position.at);
								
								if ( typeof current.position.vertical !== 'undefined' && current.position.vertical === true ) {

									api.set('style.tip.width', 10);
									api.set('style.tip.height', 18);

								} else {

									api.set('style.tip.width', 18);
									api.set('style.tip.height', 10);

								}
								
							} else {
								
								api.set('position.my', 'top center');
								api.set('position.at', 'bottom center');
								
							}
							
							if ( typeof current.end !== 'undefined' && current.end === true ) {
								var button = '<span id="tour-finish" class="tour-button">Finish Tour<span class="arrow"></span>';
							} else {
								var button = '<span id="tour-next" class="tour-button">Next<span class="arrow"></span>';
							}
														
							//Set the content
							api.set('content.text', '<p>' + current.content + '</p>' + button + '</span>');
							api.set('content.title.text', current.title);
							api.set('position.target', current.target);
														
						}
												
					});
				},

				// Destroy the tooltip after it hides as its no longer needed
				hide: function(event, api) { 
					
					$('div#tour-overlay').remove();
					
					$('div#black-overlay-tour').fadeOut(100, function() {
						$(this).remove();
					});

					$('#ui-tooltip-tour').fadeOut(100, function(){
						$(this).qtip('destroy');
					});

					//Tell the DB that the tour has been ran
					if ( Headway.ranTour == false ) {

						$.post(Headway.ajaxURL, {
							action: 'headway_visual_editor',
							method: 'ran_tour',
							security: Headway.security
						});

					}

				}
			}
		});
		
	}
	
	// Bind the buttons
		$('span#tour-next, #prev').live('click', function(event) {
			// Trigger the tooltip event
			$('#ui-tooltip-tour').triggerHandler(this.id.replace('tour-', ''));

			// Stop the link from actually clicking
			event.preventDefault();
		});
	
		$('span#tour-finish').live('click', function(event) {
		
			$('#ui-tooltip-tour').qtip('hide');
		
		});
	//End tour button bindings
/* END TOUR FUNCTIONS */


/* COG FUNCTIONS */
	createCog = function(element, animate, append, context, opacity) {
		
		if ( $(element).length === 0 )
			return false;
		
		var append = typeof append == 'undefined' ? false : append;
		var animate = typeof animate == 'undefined' ? false : animate;

		var cogString = '<div class="cog-container"><div class="cog-bottom-left"></div><div class="cog-top-right"></div></div>';
						
		if ( append ) {
			
			element.append(cogString);
						
		} else {
			
			element.html(cogString);
			
		}
		
		if ( typeof opacity != 'undefined' )
			element.find('.cog-container').css({opacity: opacity});
		
		if ( animate )
			animateCog(element, context);
			
		return true;
		
	}
	

	animateCog = function(element, context) {
		
		if ( typeof context == 'undefined' )
			context = $('body');
		
		var element = element.find('.cog-container');
				
		var bottomLeftCogAngle = 0;
		var topRightCogAngle = 0;

		var interval = setInterval(function() {

			var domElement = context.find(element);	
									
			//If the element no longer exists, then remove the interval for garbage disposal.
			if ( domElement.length === 0 || !domElement.is(':visible') )
				return clearInterval(interval);
						
			var bottomLeftValue = 'rotate(' + bottomLeftCogAngle + 'deg)';		
			var topRightValue = 'rotate(' + topRightCogAngle + 'deg)';		

			element.find('div.cog-bottom-left').css({'-webkit-transform': bottomLeftValue, '-moz-transform': bottomLeftValue});
			element.find('div.cog-top-right').css({'-webkit-transform': topRightValue, '-moz-transform': topRightValue});
			
			bottomLeftCogAngle += 2;
			topRightCogAngle -= 3.01;

		}, 20);
		
	}
/* END COG FUNCTIONS */


/* MISCELLANEOUS FUNCTIONS */
	/* Simple rounding function */
	Number.prototype.toNearest = function(num){
		return Math.round(this/num)*num;
	}
	
	
	/* Nifty little function to repeat a string n times */
	String.prototype.repeatStr = function(n) {
		if ( n <= 0 ) {
			return '';
		}

	    return Array.prototype.join.call({length:n+1}, this);
	};
	
	
	/* Function to capitalize every word in string */
	String.prototype.capitalize = function(){
		return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
	}
	
	
	/* Change integer 1 and integer 0 to boolean values */
	Number.prototype.toBool = function(){
	
		if ( this === 1 ) {
			
			return true;
			
		} else if ( this === 0  ) {
			
			return false;
			
		} else {
			
			return null;
			
		}
		
	}
	
	
	/* Change string 1, 0, true, and false to boolean values */
	String.prototype.toBool = function(){
		
		/* I'm still confused about this, but this changes the weird object of letters into an array of words */
		var string = this.split(/\b/g);
		
		if ( string[0] === '1' || string[0] === 'true' ) {
			
			return true;
			
		} else if ( string[0] === '0' || string[0] === 'false' ) {
			
			return false;
			
		} else {
			
			return null;
			
		}
		
	}
/* END MISCELLANEOUS FUNCTIONS */
})(jQuery);