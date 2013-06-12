/*
 * Headway Grid 0.0.1
 *
 * Copyright 2011, Headway Themes, LLC
 *
 * http://headwaythemes.com
 */
(function( $, undefined ) {

$.widget("ui.grid", $.ui.mouse, {
	options: {
		columns: null,
		columnWidth: null,
		gutterWidth: null,
		yGridInterval: 10,
		minBlockHeight: 40,
		selectedBlocksContainerClass: 'selected-blocks-container',
		defaultBlockClass: 'block',
		defaultBlockContentClass: 'block-content'
	},
	
	_create: function() {
		
		grid = this;
		
		if ( !this.options.columns || !this.options.columnWidth || this.options.gutterWidth === null ) {
			return console.error('The grid widget was not supplied all of the required arguments.', this.element, this.options);
		}
		
		this.container = $(this.element).contents().find(this.options.container);
		this.contents = $(this.element).contents();
									
		this.focused = false;					
		this.dragged = false;
		this.helper = $("<div class='ui-grid-helper block'></div>");
		this.offset = this.container.offset();
								
		this.container.addClass("ui-grid");
		this.container.disableSelection();

		this._initResizable(this.container.children('.' + this.options.defaultBlockClass.replace('.', '')));
		this._initDraggable(this.container.children('.' + this.options.defaultBlockClass.replace('.', '')));
								
		this._bindDoubleClick();
								
		this._bindIFrameMouse();
		
	},
	
	destroy: function() {
		
		this.element
			.removeClass("ui-grid ui-grid-disabled")
			.removeData("grid")
			.unbind(".grid");
		this._mouseDestroy();
		
		this.contents.unbind('mousedown', this._iFrameMouseDown);
		this.contents.unbind('mouseup', this._iFrameMouseUp);
		this.contents.unbind('mousemove', this._iFrameMouseMove);
		
		/* Focus/unfocus mechanism */
		this.element.unbind('mouseleave', this._iFrameMouseUp);
				
		$.Widget.prototype.destroy.apply(this, arguments); // default destroy
		
		return this;
		
	},

	iframeElement: function(selector) {
		
		return $(this.element).contents().find(selector);
		
	},	
	
	resetDraggableResizable: function() {
		
		this._initResizable(this.container.children('.' + this.options.defaultBlockClass.replace('.', '')));
		this._initDraggable(this.container.children('.' + this.options.defaultBlockClass.replace('.', '')));
		
	},
	
	_bindIFrameMouse: function() {
						
		this.contents.bind('mousedown', this._iFrameMouseDown);
		this.contents.bind('mouseup', this._iFrameMouseUp);
		
		/* Focus/unfocus mechanism */
		this.element.bind('mouseleave', this._iFrameMouseUp);
		
	},
	
	_iFrameMouseDown: function(event) {
		
		//If anything but left click, then don't trigger this.
		if ( event.which !== 1 )
			return false;
		
		grid = Headway.iframe.data('grid');
				
		//Focus iframe so things like keyup work
		grid.element.focus();	
						
		grid.mouseEventDown = event;
		grid.mouseEventElement = $(grid.mouseEventDown.originalEvent.target);
																							
		if(typeof grid.bindMouseMove === 'undefined'){	
											
			grid.contents.mousemove(grid._iFrameMouseMove);

			grid.bindMouseMove = true;
			
		}
						
		//If it's resizable handle
		if ( grid.mouseEventElement.hasClass('ui-resizable-handle') ) {
			
			getBlock(grid.mouseEventElement).data('resizable')._mouseDown(event);

		//If it's the block or block content
		} else if ( getBlock(grid.mouseEventElement) && getBlock(grid.mouseEventElement).hasClass(grid.options.defaultBlockClass.replace('.', '')) ) {
						
			if ( getBlock(grid.mouseEventElement).data('draggable') ){

				getBlock(grid.mouseEventElement).data('draggable')._mouseDown(event);

			}
			
		//If the mouse is sitting on the container, then we can create new blocks
		} else if ( 
			grid.element.data('grid') && 
			(grid.mouseEventElement[0] == grid.container[0] || grid.mouseEventElement[0] == grid.container.parents('div.wrapper')[0])
		) {
						
			grid.element.data('grid')._mouseDown(event);
			
		}

	},
	
	_iFrameMouseMove: function(event) {
						
		if ( typeof grid.mouseEventDown !== 'undefined' ) {	
			
			//If it's resizable handle, then go to parent block
			if ( grid.mouseEventElement.hasClass('ui-resizable-handle') )  {

				getBlock(grid.mouseEventElement).data('resizable')._mouseMove(event);

			//If it's the block or block content
			} else if ( getBlock(grid.mouseEventElement) && getBlock(grid.mouseEventElement).hasClass(grid.options.defaultBlockClass.replace('.', '')) ) {

				if ( getBlock(grid.mouseEventElement).data('draggable') ){

					getBlock(grid.mouseEventElement).data('draggable')._mouseMove(event);

				}

			//If the mouse is sitting on the container, then we can create new blocks
			} else if (
				grid.element.data('grid') && 
				(grid.mouseEventElement[0] == grid.container[0] || grid.mouseEventElement[0] == grid.container.parents('div.wrapper')[0])
			) {

				grid.element.data('grid')._mouseMove(event);

			}
			
		}
		
	},
	
	_iFrameMouseUp: function(event) {
				
		if ( typeof grid.mouseEventDown !== 'undefined' ) {
			
			var block = getBlock(grid.mouseEventElement);
			var container = grid.element;
						
			if ( block && typeof block.data('resizable') != 'undefined' )
				block.data('resizable')._mouseUp(event);
				
			if ( block && typeof block.data('draggable') != 'undefined' )
				block.data('draggable')._mouseUp(event);
			
			if ( typeof container != 'undefined' && typeof container.data('grid') != 'undefined' )
				container.data('grid')._mouseUp(event);

			delete grid.mouseEventDown;
			
		}
		
	},
	
	_mouseStart: function(event) {
		
		if ( !event || grid.container.hasClass('grouping-active') )
			return;
															
		this.mouseStartPosition = [event.pageX - this.container.offset().left, event.pageY - this.container.offset().top];
		
		this._trigger("start", event);

		$(this.container).append(this.helper);
			
		//Add the minimum column width to the helper	
		this.helper.css({
			width: this.options.columnWidth,
			height: 0,
			top: 0,
			left: 0,
			display: 'none'
		});
								
		return true;
		
	},

	_mouseDrag: function(event) {
				
		if ( !event || grid.container.hasClass('grouping-active') )
			return;
			
		this.dragged = true;

		var x1 = this.mouseStartPosition[0];
		var y1 = this.mouseStartPosition[1];
		
		var x2 = event.pageX - $(this.container).offset().left;
		var y2 = event.pageY - $(this.container).offset().top;
		
		if (x1 > x2) { var tmp = x2; x2 = x1; x1 = tmp; }
		if (y1 > y2) { var tmp = y2; y2 = y1; y1 = tmp; }
		
		var containerLeft = $(this.container).offset().left;
		var containerTop = $(this.container).offset().top;
		var containerHeight = $(this.container).height();	
		var containerWidth = $(this.container).width();
			
		/* Handle Padding */
			
			/* If both start and end points of block are inside right padding, don't draw the block. */
			if ( x2 >= $(this.container).width() && x1 >= $(this.container).width() )
				return;

			/* If both start and end points of block are inside bottom padding, don't draw the block. */
			if ( y2 >= $(this.container).height() && y1 >= $(this.container).height() )
				return;
						
			/* If they're starting the drag from the wrapper left padding, start at 0. */
			if ( x1 < 0 )
				x1 = 0;
				
			/* If they're starting the drag from the wrapper top padding, start at 0. */
			if ( y1 < 0 )
				y1 = 0;
				
			/* If start point is inside bottom padding, move it to absolute bottom */			
			if ( y2 > containerHeight ) {
				y2 = containerHeight;
			}			

		/* End Padding Conditionals */
		
		var blockLeft = x1.toNearest(this.options.columnWidth + this.options.gutterWidth);
		var blockTop = y1.toNearest(this.options.yGridInterval);
		var blockWidth = x2.toNearest(this.options.columnWidth + this.options.gutterWidth) - blockLeft - this.options.gutterWidth;
		var blockHeight = y2.toNearest(this.options.yGridInterval) - y1.toNearest(this.options.yGridInterval);	
				
		Headway.blankBlockOptions = {
			display: 'block',
			left: blockLeft, 
			top: blockTop, 
			width: blockWidth,
			height: blockHeight
		};	
				
		/* Maxes */
		
			/* Width Max */
			if ( blockLeft + blockWidth > (this.options.columns * (this.options.columnWidth + this.options.gutterWidth)) )
				Headway.blankBlockOptions.width = containerWidth - Headway.blankBlockOptions.left;

			/* If block bleeds out bottom, put a damper there. */
			if ( event.pageY > (containerTop + containerHeight)  ) {
				Headway.blankBlockOptions.height = containerHeight - blockTop;
			}
			
		/* End Maxes */
		
		this.helper.css(Headway.blankBlockOptions);
		
		/* Make block red if it is not big enough */
		if ( Headway.blankBlockOptions.height < this.options.minBlockHeight ) {
			this.helper.addClass('block-error');
		} else if ( this.helper.hasClass('block-error') ) {
			this.helper.removeClass('block-error');
		}
				
		this._trigger("drag", event);
		
		return false;
		
	},

	_mouseStop: function(event) {
		
		if ( !event || grid.container.hasClass('grouping-active') )
			return;
		
		this.dragged = false;

		this._trigger("stop", event);
		
		Headway.blankBlockOptions = {
			width: this.helper.width(),
			height: this.helper.height(),
			top: this.helper.position().top,
			left: this.helper.position().left
		}
		
		this.helper.remove();
		
		//Check to make sure the block is big enough
		if ( Headway.blankBlockOptions.width < this.options.columnWidth || Headway.blankBlockOptions.height < this.options.minBlockHeight )
			return false;
						
		//Set max drag
		if(Headway.blankBlockOptions.left + Headway.blankBlockOptions.width > this.options.columns * (this.options.columnWidth + this.options.gutterWidth) + 20){
			var overage = (Headway.blankBlockOptions.left + Headway.blankBlockOptions.width) - (this.options.columns * (this.options.columnWidth + this.options.gutterWidth) - 20);

			Headway.blankBlockOptions.width = Headway.blankBlockOptions.width - overage;
		}

		//If the width is below the minimum size, then change the width to the minimum size
		if ( Headway.blankBlockOptions.width < this.options.columnWidth )
			Headway.blankBlockOptions.width = this.options.columnWidth;
									
		this.addBlankBlock(Headway.blankBlockOptions);
								
		this.mouseStartPosition = false;
		
		return false;
		
	},
	
	_mouseUp: function(event) {
		
		if ( !event || grid.container.hasClass('grouping-active') )
			return;
		
		grid = this;
			
		$(document)
			.unbind('mousemove.' + this.widgetName, this._mouseMoveDelegate)
			.unbind('mouseup.' + this.widgetName, this._mouseUpDelegate);
					
		if ( this._mouseStarted ) {
			
			this._mouseStarted = false;
		
			if ( event.target == this._mouseDownEvent.target ) {
			    $.data(event.target, this.widgetName + '.preventClickEvent', true);
			}
		
			this._mouseStop(event);
			
		}
		
		return false;
		
	},

	_initResizable: function(element) {
		
		grid = this;
		
		if(typeof element == 'string'){
			element = $(element);
		}

		if ( typeof element.resizable === 'function' ) {
			element.resizable('destroy');
		}

		element.resizable({
			handles: 'n, e, s, w, ne, se, sw, nw',
			grid:[this.options.columnWidth + this.options.gutterWidth, this.options.yGridInterval], 
			containment: this.container,
			minHeight: this.options.minBlockHeight, 
			maxWidth: this.options.columns * (this.options.columnWidth + this.options.gutterWidth),
			start: this._resizableStart,
			resize: this._resizableResize,
			stop: this._resizableStop
		});
		
	},
	
	_resizableStart: function(event, ui) {
		
		//this variable refers to resizabable
		
		var block = getBlock(ui.element);
		
		var minBlockHeight = parseInt(block.css('minHeight').replace('px', ''));
		var height = block.height();
				
		//Remove min-height
		if ( minBlockHeight <= height ) {			
			block.css('minHeight', 0);
		}
		
		//Add the block hover class that keeps the controls, info, and glow visible during resizing
		block.addClass('block-hover');
		
		//Show the dimensions tooltip
		block.qtip('option', 'hide.delay', 10000);

		block.qtip('show');
		block.qtip('reposition');		
		
	},
	
	_resizableResize: function(event, ui) {
		
		var block = getBlock(ui.element);
		
		//Show the dimensions tooltip
		block.qtip('show');
		block.qtip('reposition');
		
		
	},
	
	_resizableStop: function(event, ui) {
		
		//this variable refers to resizable
		
		var block = getBlock(ui.element); 
		var newGridWidth = Math.ceil(block.width() / (grid.options.columnWidth + grid.options.gutterWidth));
		var newGridLeft = Math.ceil(block.position().left / (grid.options.columnWidth + grid.options.gutterWidth));
		
		//Find width
		var oldGridWidth = getBlockGridWidth(block);
		
		//Find left
		var oldLeftPosition = getBlockGridLeft(block);
		
		//Update classes and CSS
		block.removeClass('grid-width-' + oldGridWidth);
		block.addClass('grid-width-' + newGridWidth);
		
		block.removeClass('grid-left-' + oldLeftPosition);
		block.addClass('grid-left-' + newGridLeft);
		
		block.css('width', '');
		block.css('left', '');		
		
		//Add hidden input
		updateBlockDimensionsHidden(getBlockID(block), getBlockDimensions(block));
		updateBlockPositionHidden(getBlockID(block), getBlockPosition(block));
		
		//Check for intersectors and allow saving if possible
		grid.blockIntersectCheck(block) ? allowSaving() : disallowSaving();
				
		//Show the dimensions tooltip
		block.qtip('option', 'hide.delay', 25);

		block.qtip('show');
		block.qtip('reposition');
		
		//Remove the block hover class that keeps the controls, info, and glow visible during resizing
		block.removeClass('block-hover');	
								
	},
	
	_initDraggable: function(element) {
		
		if(typeof element == 'string'){
			element = $(element);
		}
		
		if ( typeof element.draggable === 'function' ) {
			element.draggable('destroy');
		}
		
		grid = this;
		
		element.css('cursor', 'move').draggable({
			grid: [this.options.columnWidth + this.options.gutterWidth, this.options.yGridInterval],
			containment: this.iframeElement(this.options.container),
			scrollSpeed: 40,
			start: this._draggableStart,
			stop: this._draggableStop,
			drag: this._draggableDrag
		});
		
	},
	
	_draggableStart: function(event, ui) {
		
		//this variable refers to draggable
		
		//Keep draggable from accidentally happening
		if ( $(event.originalEvent.target).parents('.block-controls').length === 1 || $(event.originalEvent.target).parents('.block-info').length === 1 ) {
			
			$(this).draggable('stop');
			
			return false;
			
		}
		
		$(this).data('dragging', true);
		
		//Grouping Code
		posTopArray = [];
		posLeftArray = [];
		
		//If it's a grouped block, move group, otherwise reset group
		if ( $(this).hasClass('grouped-block') ) {
			
			grid.container.find('.grouped-block').each(function(i) {
				
				if ( event.srcElement == this ) //Skip the pimary block
					return;
				
				posTopArray[i] = parseInt($(this).css('top').replace('px', '')) || 0;
				posLeftArray[i] = parseInt($(this).css('left').replace('px', '')) || 0;
												
			});
			
		} else {
			
			grid.container.removeClass('grouping-active');
			grid.container.find('.grouped-block').removeClass('grouped-block');
			
			hideTaskNotification();
			
		}

		beginTop = $(this).offset().top; //Dragged element top position
		beginLeft = $(this).offset().left;
		//End Grouping Code

		//Hide dimensions tooltip	
		$(getBlock(ui.helper)).qtip('hide');
		
	},
	
	_draggableDrag: function(event, ui) {
		
		//this variable refers to draggable
				
		//Grouping Code
		var topDiff = $(this).offset().top - beginTop;	//Current distance dragged element has traveled vertically
		var leftDiff = $(this).offset().left - beginLeft;

		//If it's a grouped block, move group, otherwise reset group
		if ( $(this).hasClass('grouped-block') ) {
			
			grid.container.find('.grouped-block').each(function(i) {
				
				if ( event.srcElement == this ) //Skip the primary block
					return;
								
				$(this).css('top', posTopArray[i] + topDiff);
				$(this).css('left', posLeftArray[i] + leftDiff); 
				
			});
			
		} else {
			
			grid.container.find('.grouped-block').removeClass('grouped-block');
			
		}
		//End Grouping Code	
		
		//Hide dimensions tooltip	
		$(getBlock(ui.helper)).qtip('hide');

	},
	
	_draggableStop: function(event, ui) {
		
		//this variable refers to draggable
				
		$(this).data('dragging', false);
		
		//Build the list of blocks that need to be updated, if there are grouped blocks then update them (which will include the one dragged)
		if ( grid.container.find('.grouped-block').length ) {
			
			var blocks = grid.container.find('.grouped-block');
			
		//Else we just have the one block to update
		} else {
			
			var blocks = getBlock(ui.helper);
			
		}
		
		//Loop through each block now
		blocks.each(function(){
			
			var block = $(this); 
			var newGridLeft = Math.ceil(block.position().left / (grid.options.columnWidth + grid.options.gutterWidth));

			//Find left
			var oldLeftPosition = getBlockGridLeft(block);

			//Update classes and CSS
			block.removeClass('grid-left-' + oldLeftPosition);
			block.addClass('grid-left-' + newGridLeft);

			block.css('left', '');

			//Add hidden inputs
			updateBlockPositionHidden(getBlockID(block), getBlockPosition(block));

			//Check for intersectors and allow saving if possible		
			if ( grid.blockIntersectCheck(block) ) {
				allowSaving();
			} else {
				disallowSaving();
			}
			
		});

		$(document).focus();
		
		//Reposition dimensions tooltip	
		$(this).data('hoverWaitTimeout', setTimeout(function() {
			
			$(getBlock(ui.helper)).qtip('reposition');
			$(getBlock(ui.helper)).qtip('show');
			
		}, 300));
		
	},

	_bindDoubleClick: function() {
		
		grid = this;
		
		this.container.delegate('.' + this.options.defaultBlockClass.replace('.', ''), 'dblclick', function(event) {
			
			//Do not do the double click stuff if they're clicking the block info or block controls.
			if ( $(event.target).parents('.block-info').length == 1 || $(event.target).parents('.block-controls').length == 1 )
				return false;
			
			//If there's only one grouped block and it's being toggled off, remove all grouping
			if ( $(this).hasClass('grouped-block') && grid.container.find('.grouped-block').length === 1 ) {
				
				$(this).removeClass('grouped-block');
				grid.container.removeClass('grouping-active');
				
				hideTaskNotification();
				
			//Else if the block is grouped, remove its class only
			} else if ( $(this).hasClass('grouped-block') ) {
				
				$(this).removeClass('grouped-block');
			
			//Else there's no grouping and we need to start it	
			} else {
			
				$(this).addClass('grouped-block');
				grid.container.addClass('grouping-active');
				
				showTaskNotification('Mass Block Selection Mode', function(){					
					$i('.grouped-block').removeClass('grouped-block');
					Headway.iframe.data('grid').container.removeClass('grouping-active');
				});
			
			}
			
		});
		
	},

	addBlankBlock: function(args, usePixels, usingAddBlock) {
		
		var defaults = {
			top: 0,
			left: 0,
			width: 140,
			height: this.options.minBlockHeight,
			id: null
		}
		
		var args = $.extend({}, defaults, args);
		
		if ( typeof usePixels == 'undefined' )
			var usePixels = true;
			
		if ( typeof usingAddBlock == 'undefined' )
			var usingAddBlock = false;
	
		var blockID = ( args.id == false || args.id == null ) ? getAvailableBlockID() : args.id;	
				
		/* In case the available block ID batch system gets overloaded or errors out, do not add the block AT ALL. */
		if ( typeof blockID === 'undefined' || !blockID )
			return false;
				
		Headway.blankBlock = $('<div><div class="block-content-fade block-content"></div><h3 class="block-type" style="display: none;"><span></span></h3></div>')
			.attr('id', 'block-' + blockID)
			.addClass(this.options.defaultBlockClass.replace('.', ''));

		var tooltipID = 'This is the ID for the block.  The ID of the block is displayed in the WordPress admin panel if it is a widget area or navigation block.  Also, this can be used with advanced developer functions.';
		var tooltipType = 'Click to change the block type.';
		var tooltipOptions = 'Show the options for this block.';
		var tooltipDelete = 'Delete this block.';
		
		Headway.blankBlock.addClass('blank-block');
		
		Headway.blankBlock.append('\
			<div class="block-info">\
				<span class="id tooltip" title="' + tooltipID + '">' + blockID + '</span>\
				<span class="type type-unknown tooltip" title="' + tooltipType + '">Unknown</span>\
			</div>');
			
		Headway.blankBlock.append('\
			<div class="block-controls">\
				<span class="options tooltip" title="' + tooltipOptions + '">Options</span>\
				<span class="delete tooltip" title="' + tooltipDelete + '">Delete</span>\
			</div>');		

		var block = Headway.blankBlock;

		block.css({
			width: parseInt(args.width),
			height: parseInt(args.height),
			top: parseInt(args.top),
			left: parseInt(args.left),
			position: 'absolute',
			visibility: 'hidden'
		});
		
		block.appendTo(this.container);
						
		//Fix with and column position
		if ( usePixels ) {
			
			var width = String(block.width()).replace('px', '');
			var widthGridNum = Math.ceil(width/(grid.options.columnWidth + grid.options.gutterWidth));

			var left = String(block.position().left).replace('px', '');
			var leftGridNum = Math.ceil(left/(grid.options.columnWidth + grid.options.gutterWidth));
			
		} else {
			
			widthGridNum = parseInt(args.width);
			leftGridNum = parseInt(args.left);
			
		}

		block.css('width', '').addClass('grid-width-' + widthGridNum);
		block.css('left', '').addClass('grid-left-' + leftGridNum);
		block.css('visibility', 'visible');
		
		//Initiate stuff
		this._initResizable(block);
		this._initDraggable(block);
		
		//Show the red right off the bat if the block is touching/overlapping other blocks
		this.blockIntersectCheck(block);
		
		//Add this conditional in so addBlock doesn't take as long
		if ( usingAddBlock == false ) {
			
			/* Refresh tooltips */
			setupTooltips('iframe');

			showBlockTypePopup($(Headway.blankBlock));
			
		}
						
		return block;
		
	},

	setupBlankBlock: function(blockType, usingAddBlock) {
		
		if ( typeof usingAddBlock == 'undefined' )
			var usingAddBlock = false;
					
		var blockTypeIconURL = getBlockTypeIcon(blockType, true);
		
		Headway.blankBlock.removeClass('blank-block');

		Headway.blankBlock.addClass('block-type-' + blockType);
												
		Headway.blankBlock.find('.block-info span.type')
			.attr('class', '')
			.addClass('type')
			.addClass('type-' + blockType)
			.html(getBlockTypeNice(blockType))
			.css('backgroundImage', 'url(' + blockTypeIconURL + ')');

		loadBlockContent({
			blockElement: Headway.blankBlock,
			blockOrigin: {
				type: blockType,
				id: 0,
				layout: Headway.currentLayout
			},
			blockSettings: {
				dimensions: getBlockDimensions(Headway.blankBlock),
				position: getBlockPosition(Headway.blankBlock)
			},
		});
		
		//Set the fluid/fixed height class so the fluid height message is shown correctly
		if ( getBlockTypeObject(blockType)['fixed-height'] === true ) {
			
			Headway.blankBlock.addClass('block-fixed-height');
			
		} else {
			
			Headway.blankBlock.addClass('block-fluid-height');
			
		}
		
		//Set the hide-content-in-grid depending on the block type
		if ( !getBlockTypeObject(blockType)['show-content-in-grid'] )
			Headway.blankBlock.addClass('hide-content-in-grid');

		//Add the block type to the block type readout
		Headway.blankBlock.find('h3.block-type span').text(getBlockTypeNice(blockType));
		Headway.blankBlock.find('h3.block-type').show();
		
		//Add this conditional in so addBlock doesn't take as long
		if ( usingAddBlock == false ) {
				
			//Hide the block type popup
			hideBlockTypePopup();
			
		}
		
		//Add the hidden input flag
		addNewBlockHidden(getBlockID(Headway.blankBlock), getBlockType(Headway.blankBlock));
		updateBlockPositionHidden(getBlockID(Headway.blankBlock), getBlockPosition(Headway.blankBlock));
		updateBlockDimensionsHidden(getBlockID(Headway.blankBlock), getBlockDimensions(Headway.blankBlock));
		
		//Check for intersectors and allow saving if possible		
		if ( this.blockIntersectCheck(Headway.blankBlock) ) {
			allowSaving();
		} else {
			disallowSaving();
		}
		
		//Save block variable to return it at the end
		var block = Headway.blankBlock;
		
		//Clear variable
		delete Headway.blankBlock;
		delete Headway.blankBlockOptions;

		//Add this conditional in so addBlock doesn't take as long
		if ( usingAddBlock == false ) {
			
			/* Refresh tooltips */
			setupTooltips('iframe');
			
		}
		
		return block;

	},
	
	addBlock: function(args) {
		
		var defaults = {
			top: 0,
			left: 0,
			width: 1,
			height: this.options.minBlockHeight,
			type: null,
			id: null,
			settings: []
		}
		
		var args = $.extend({}, defaults, args);
		
		if ( this.addBlankBlock(args, false, true) ) {
			
			var block = this.setupBlankBlock(args.type, true);
			var blockID = block.attr('id').replace('block-', '');
									
			$.each(args.settings, function(key, value) {
								
				updatePanelInputHidden({
					id: key,
					value: value,
					group: 'general',
					isBlock: 'true',
					blockID: blockID
				});
				
				if ( key == 'mirror-block' ) {
					updateBlockMirrorStatus(false, block, value, false);
				}
				
			});
			
		} else {
			
			return false;
			
		}
		
	},
	
	switchBlockType: function(block, blockType) {
		
		var blockTypeIconURL = getBlockTypeIcon(blockType, true);
		
		var oldType = getBlockType(block);
		var blockID = getBlockID(block);
		
		block.removeClass('block-type-' + oldType);
		block.addClass('block-type-' + blockType);

		block.find('.block-info span.type')
			.attr('class', '')
			.addClass('type')
			.addClass('type-' + blockType)
			.html(getBlockTypeNice(blockType))
			.css('backgroundImage', 'url(' + blockTypeIconURL + ')');
			
		block.find('h3.block-type span').text(getBlockTypeNice(blockType));
						
		loadBlockContent({
			blockElement: block,
			blockOrigin: {
				type: blockType,
				id: 0,
				layout: Headway.currentLayout
			},
			blockSettings: {
				dimensions: getBlockDimensions(block),
				position: getBlockPosition(block)
			},
		});
		
		//Set the fluid/fixed height class so the fluid height message is shown correctly
		if ( getBlockTypeObject(blockType)['fixed-height'] === true ) {
			
			block.removeClass('block-fluid-height');
			block.addClass('block-fixed-height');
			
		} else {
			
			block.removeClass('block-fixed-height');
			block.addClass('block-fluid-height');
			
		}
		
		//Set the hide-content-in-grid depending on the block type
		if ( !getBlockTypeObject(blockType)['show-content-in-grid'] ) {
			
			block.addClass('hide-content-in-grid');
			
		} else {
			
			block.removeClass('hide-content-in-grid');
			
		}

		//Hide the block type popup
		hideBlockTypePopup();
		
		//Prepare for hiddens
		var newBlockID = getAvailableBlockID();
		var oldBlockID = blockID;
		
		//Delete the old block optiosn tab if it exists
		removePanelTab('block-' + oldBlockID);
		
		//Add hiddens to delete old block and add new block in its place
		addDeleteBlockHidden(oldBlockID);
		addNewBlockHidden(newBlockID, blockType);
		updateBlockPositionHidden(newBlockID, getBlockPosition(block));
		updateBlockDimensionsHidden(newBlockID, getBlockDimensions(block));
		
		//Update the ID on the block
		block.attr('id', 'block-' + newBlockID);
		block.find('div.block-info span.id').text(newBlockID);
		
		//Allow saving now that the type has been switched
		allowSaving();
		
		/* Refresh tooltips */
		setupTooltips('iframe');
		
	},

	deleteBlock: function(element) {
	
		var block = getBlock(element);
	
		var block_id = block.attr('id');
		
		//Remove the block!
		block.remove();
		
		//Remove block options tab from panel
		removePanelTab(block_id);
		
		//Hide block type popup if they're in the process of making a new block and want to scrap it
		hideBlockTypePopup();
		
		//Add the hidden input flag
		addDeleteBlockHidden(block_id);
		
		//Set block to false for the intersect chec
		var block = false;
		this.blockIntersectCheck(block);
		
		allowSaving();	
		
	},
		
	blockIntersectCheck: function(originBlock) {
		
		var intersectors = grid.blockIntersectCheckCallback(originBlock, grid.iframeElement('.block'));

		//If there are two elements in the intersection array (the original one will be included since we're doing a general '.block' search), then we throw an error
		if ( intersectors.length > 1 ) {	
			
			intersectors.addClass('block-error');

			var output = false;
			
		} else {
			
			//Set up variable for next loop
			var blockErrorCount = 0;

			//Since there could still be errors after this one if fixed, we must loop through all other blocks that have errors
			grid.iframeElement('.block-error').each(function(){
				var intersectors = grid.blockIntersectCheckCallback(this, grid.iframeElement('.block'));

				if ( intersectors.length === 1) {
					$(this).removeClass('block-error');
				} else {
					blockErrorCount++;
				}
			});

			//If there aren't any touching blocks, then we can save.  Otherwise, we cannot.
			var output = ( blockErrorCount === 0 ) ? true : false;
			
		}

		return output;
	
	},

	blockIntersectCheckCallback: function(targetSelector, intersectorsSelector) {
		
		if ( targetSelector == false || intersectorsSelector == false ) {
			return false;
		}
		
	    var intersectors = [];

	    var $target = $(targetSelector);
	    var tAxis = $target.offset();
	    var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
	    var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];

	    $(intersectorsSelector).each(function() {
	          var $this = $(this);
	          var thisPos = $this.offset();
	          var i_x = [thisPos.left, thisPos.left + $this.outerWidth()]
	          var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

	          if ( t_x[0] < i_x[1] && t_x[1] > i_x[0] &&
	               t_y[0] < i_y[1] && t_y[1] > i_y[0]) {
	              intersectors.push(this);
	          }
	    });
	
	    return $(intersectors);
	
	}

	
});

$.extend($.ui.grid, {
	version: "0.7"
});

})(jQuery);