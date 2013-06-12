jQuery(function($){
    
    // hide and show 3d or 2d slider options depending on which effect type is chosen
    var $effectType = $('#effectType');
	$effectType.change(function(){
        if( $(this).val() === '3d' ) {
            $('#options3d').slideDown(600);
            $('#options2d').slideUp(600);
        }
        else {
            $('#options3d').slideUp(600);
            $('#options2d').slideDown(600);
        }
    });
    
    // hide and disable caption animation options when caption option is not enabled
    $('#captions').change(function(){
	if( $(this)[0].checked ) {
	    $('#captionAnimation, #captionAnimationSpeed').attr('disabled', false);
	}
	else {
	    $('#captionAnimation, #captionAnimationSpeed').attr('disabled', true);
	}
    })
    
    
    
    // handle image upload
    var $slidesList = $('#slidesList');
    
    var uploader = new qq.FileUploader({
        element: $('#upload_btn')[0],
        action: uploadParams.upload_php,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        params: {
            folder: uploadParams.upload_folder
        },
        onComplete: function( id, fileName, response ) {
            if( response.error ) {
                alert( response.error );
                return;
            }
            
            var thumbUrl = uploadParams.upload_url + '/' + response.filename;
            $('li.qq-upload-success').eq(id).hide();
            var imghtml = '<li>'
                        + '<img src="'+thumbUrl+'" data-name="'+response.filename+'" />'
						+ '<input type="hidden" name="images[]" value="'+ thumbUrl +'" />'
						+ '<label>Enter Caption (can contain HTML):</label>'
                        + '<textarea class="caption" name="captionText[]"></textarea>'
						+ '<label>Image Link</label>'
						+ '<input class="slideLink" type="text" name="links[]" value="" />'
						+ '<span class="description">for e.g. http://somesite.com</span>'
                        + '<a class="delete" title="Delete this image"></a>'
                        + '</li>';
            $slidesList.append(imghtml);
			
			var $customCheck = $('input[name="enableCustom"]');
			if( ($customCheck[0].id === 'enableCustom' && $customCheck[0].checked) || ( $customCheck[0].id === 'disableCustom') ) {
				if( $customSettings.find('li').eq(0).length === 0 ) {
					$customSettings.append( $customLiHtml );
				}
				else {
					$customSettings.append( $customSettings.find('li').eq(0).clone() );
				}				
				$customSettings.find('img:last')[0].src = thumbUrl;
			}
        }
    });
    
    $('div.qq-upload-button').addClass('button-secondary');
    
    
    // make the image thumbnail list sortable
    var sorting = false;
    
    $slidesList.sortable({ revert: true, cursor: 'move', axis: 'y', containment: 'parent' });
    
    $slidesList.bind('sortstart', function(e, ui){
        sorting = true;
        $(ui.item).find('a.delete').hide();
    })
    .bind('sortstop', function(){
        sorting = false;
    });
    
    //Show the delete button on images on hover but not when sorting is in progress	
    $slidesList.delegate('li', 'mouseenter mouseleave', function(e){
        if( !sorting ) {
            if( e.type === 'mouseenter' ){
                $(this).find('a.delete').show();
            }
            else {
                $(this).find('a.delete').hide();
            }
        }
    });
    
    // handle image deletion
	var customLiHtml;
    $slidesList.delegate('a.delete', 'click', function(){
        var li  = $(this).parent(),
            imageName = li.find('img').data('name'),
            data = {
                action: 'delete_image',
                name: imageName 
            };
        
		if( $slidesList.find('li').length === 1 ) {
			customLiHtml = $customSettings.find('li').eq(0).clone();
		}
		
        li.slideUp(600, function(){
			li.remove();
		});
	
        $.post( uploadParams.ajaxurl, data, function(data){console.log(data);});
		
		// also remove corresponding item from custom settings list
		$customSettings.find('li').eq( li.index() ).remove();
    });
	
	
	// custom transition settings
	var $customSettings = $('#customSettings'),
		$enableCustom = $('#enableCustom'),
		$disableCustom = $('#disableCustom')
	
	$enableCustom.change(function(){
		if( this.checked ) {
			var slideimgs = $slidesList.find('img'),
				slidenum = slideimgs.length;
				
			$customSettings.find('img').eq(0)[0].src = slideimgs.eq(0)[0].src;
			for( var i = 1; i < slidenum; i++ ) {
				$customSettings.append( $customSettings.find('li').eq(0).clone() );
				$customSettings.find('img')[i].src = slideimgs[i].src;
			}
			
			if( $effectType.val() === '3d' ) {
				$customSettings.find('li').removeClass('customEffects2d');
			}
			else {
				$customSettings.find('li').addClass('customEffects2d');
			}
			
			$customSettings.slideDown(600);
		}
		else {
			$customSettings.find('li').not(':first').remove();
			$customSettings.slideUp(600);
		}
	});
	
	$disableCustom.change(function(){
		if( this.checked ) {
			$customSettings.slideDown(600);
		}
		else {
			$customSettings.slideUp(600);
		}
	});
	
	$slidesList.bind('sortstart', function(e, ui){
		$slidesList.find('li').each(function(){
			$(this).data('index', $slidesList.find('li').not(ui.placeholder).index(this) );
		});		
	})
	.bind('sortupdate', function(e, ui){
		if( $enableCustom.length !== 0 && $customSettings.is(':visible') ) {
			$customSettings.find('li').not(':first').remove();
			$enableCustom.trigger('change');
		}
		else {
			var sortorder = [];
			$slidesList.find('li').each(function(){
				sortorder.push( $(this).data('index') );
			});
			var customLi = $customSettings.find('li').get();
			$customSettings.empty();
			for( var i = 0; i < sortorder.length; i++ ) {
				$customSettings.append( customLi[ sortorder[i] ] );
			}
		}
	});
	
	$effectType.change(function(){
        if( $effectType.val() === '3d' ) {
			$customSettings.find('li').removeClass('customEffects2d');
		}
		else {
			$customSettings.find('li').addClass('customEffects2d');
		}
    });
    

    
    // handle preview of slideshow
    var $overlay = $('<div id="overlay"/>').appendTo('body'),
		$overlayContent = $('<div id="overlayContent"/>').appendTo($overlay),
		$slider = $('<div class="ccslider"/>').appendTo( $overlayContent ),
		$close = $('<a id="close"/>').appendTo($overlay),
		$win = $(window);
        
    
    $('#slider-preview').click(function(){
		var settings = $('#ccslider-settings').serializeObject(),
			imgs = '',
			imgUrls = [],
			resizedUrls = [],
			resizeWidth = settings['effectType'] === '3d' ? parseInt(settings['imageWidth']) : parseInt(settings['slideWidth']),
			resizeHeight = settings['effectType'] === '3d' ? parseInt(settings['imageHeight']) : parseInt(settings['slideHeight']),
			captionData = '',
			captions = '',
			linkData = '',
			top = $win.scrollTop() + $win.height()/2;
			
		resizeWidth = resizeWidth || 600;
		resizeHeight = resizeHeight || 300;
			
		$slidesList.find('li').each(function(){
			imgUrls.push( $(this).find('img')[0].src );
		});
		
		var ajaxDone1 = $.ajax({
			type: 'post',
			url: uploadParams.ajaxurl,
			dataType: 'json',
			data: {action: 'preview_image_resize', urls: imgUrls, width: resizeWidth, height: resizeHeight},
			success: function(data) {
				$.each(data, function( i, val ) {
					resizedUrls.push( unescape(val) );
				});
			}
		});
		
		if( settings['controlLinkThumbs'] ) {
			var thumbUrls = [];
			var ajaxDone2 = $.ajax({
				type: 'post',
				url: uploadParams.ajaxurl,
				dataType: 'json',
				data: {action: 'preview_image_resize', urls: imgUrls, width: parseInt(settings['controlThumbWidth']), height: parseInt(settings['controlThumbHeight'])},
				success: function(data) {
					$.each(data, function( i, val ) {
						thumbUrls.push( unescape(val) );
					});
				}
			});
		}		
			
		settings['effect'] = settings['effectType'] === '3d' ? settings['effect3d'] : settings['effect2d'];
		settings['transparentImg'] = settings['transparentImg'] ? true : false;
		settings['makeShadow'] = settings['makeShadow'] ? true : false;
		settings['directionNav'] = settings['directionNav'] ? true : false;
		settings['controlLinks'] = settings['controlLinks'] ? true : false;
		settings['controlLinkThumbs'] = settings['controlLinkThumbs'] ? true : false;
		settings['autoPlay'] = settings['autoPlay'] ? true : false;
		settings['pauseOnHover'] = settings['pauseOnHover'] ? true : false;
		settings['captions'] = settings['captions'] ? true : false;
		
		if( ajaxDone2 ) {
			$.when(ajaxDone1, ajaxDone2).then(createSlider);
		}
		else {
			$.when(ajaxDone1).then(createSlider);
		}
	
        function createSlider(){
			$slidesList.find('li').each(function(i){
				var $this = $(this);
				captionData = '';
				linkData = '';
				customData = '';
				thumbData = '';
				
				if( $this.find('textarea').val() !== '' ) {
					captionData = ' data-captionelem="#cc-slideCaption'+ i +'"';
					captions += '<div class="cc-caption" id="cc-slideCaption'+ i +'">'+ $this.find('textarea').val() +'</div>';
				}
				
				if( $this.find('input.slideLink').val() !== '' ) {
					linkData = ' data-href="'+ $this.find('input.slideLink').val() +'"';
				}
				
				if( $('input[name="enableCustom"]')[0].checked ) {
					customData = ' data-transition=\'{';
					
					if( settings['effectType'] === '3d' && $customSettings.find('select[name="custom3d[]"]').eq(i).val() ) {
						customData += '"effect":"'+$customSettings.find('select[name="custom3d[]"]').eq(i).val()+'", ';
					}
					else if( settings['effectType'] === '2d' && $customSettings.find('select[name="custom2d[]"]').eq(i).val() ) {
						customData += '"effect":"'+$customSettings.find('select[name="custom2d[]"]').eq(i).val()+'", ';
					}
					
					if( $customSettings.find('input[name="customSlices[]"]').eq(i).val() ) {
						customData += '"slices": '+$customSettings.find('input[name="customSlices[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('input[name="customRows[]"]').eq(i).val() ) {
						customData += '"rows": '+$customSettings.find('input[name="customRows[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('input[name="customColumns[]"]').eq(i).val() ) {
						customData += '"columns": '+$customSettings.find('input[name="customColumns[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('input[name="customDelay[]"]').eq(i).val() ) {
						customData += '"delay": '+$customSettings.find('input[name="customDelay[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('select[name="customDelayDir[]"]').eq(i).val() ) {
						customData += '"delayDir": "'+$customSettings.find('select[name="customDelayDir[]"]').eq(i).val()+'", ';
					}
					
					if( $customSettings.find('input[name="customDepthOffset[]"]').eq(i).val() ) {
						customData += '"depthOffset": '+$customSettings.find('input[name="customDepthOffset[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('select[name="customSliceGap[]"]').eq(i).val() ) {
						customData += '"sliceGap": '+$customSettings.find('select[name="customSliceGap[]"]').eq(i).val()+', ';
					}
					
					if( $customSettings.find('select[name="customEasing[]"]').eq(i).val() ) {
						customData += '"easing": "'+$customSettings.find('select[name="customEasing[]"]').eq(i).val()+'", ';
					}
					
					if( $customSettings.find('input[name="customAnimSpeed[]"]').eq(i).val() ) {
						customData += '"animSpeed": '+$customSettings.find('input[name="customAnimSpeed[]"]').eq(i).val()+', ';
					}
					
					// remove the last two characters consisting of a comma and a space
					customData = customData.substr(0, customData.length - 2);
					
					customData += '}\'';
				}
				
				if( settings['controlLinkThumbs'] ) {
					thumbData = ' data-thumbname="'+ (thumbUrls[i]).split('/').pop() +'"';
				}
				
				imgs += '<img src="'+ resizedUrls[i] +'"'+ captionData + linkData + customData + thumbData +' alt="" />';
			});
			
			$slider.html( imgs );
			$slider.after( captions );
		
			var width = 0, height = 0;
			
			if( settings['effectType'] === '3d' ) {
				width = parseInt(settings.wrapperWidth);
				height = parseInt(settings.wrapperHeight);
				$slider.css({ width: width, height: height });
			}
			else {			
				width = parseInt(settings.slideWidth);
				height = parseInt(settings.slideHeight);
			}		
		
			$overlay.css({ top: top, width: width + 100, height: height + 100, marginTop: -(height + 100)/2, marginLeft: -(width + 100)/2 })
			.fadeIn(600, function(){
				$slider.ccslider({
				effectType: settings['effectType'], 
				effect: settings['effect'], 
				_3dOptions: { 
						  imageWidth: parseInt(settings['imageWidth']),
						  imageHeight: parseInt(settings['imageHeight']),
						  innerSideColor: settings['innerSideColor'],
						  transparentImg: settings['transparentImg'],
						  makeShadow: settings['makeShadow'],
						  shadowColor: settings['shadowColor'],
						  slices: parseInt(settings['slices']),
						  rows: parseInt(settings['rows']),
						  columns: parseInt(settings['columns']),
						  delay: parseInt(settings['delay']),
						  delayDir: settings['delayDir'],
						  depthOffset: parseInt(settings['depthOffset']),
						  sliceGap: parseInt(settings['sliceGap']),
						  easing: settings['easing'],
						  fallBack: settings['fallBack'],
						  fallBackSpeed: parseInt(settings['fallBackSpeed'])
						},		
				animSpeed: parseInt(settings['animSpeed']),
				startSlide: parseInt(settings['startSlide']),	
				directionNav: settings['directionNav'],
				controlLinks: settings['controlLinks'],
				controlLinkThumbs: settings['controlLinkThumbs'],
				controlThumbLocation: uploadParams.upload_url+'/',
				autoPlay: settings['autoPlay'],
				pauseTime: parseInt(settings['pauseTime']),
				pauseOnHover: settings['pauseOnHover'],
				captions: settings['captions'],
				captionAnimation: settings['captionAnimation'],
				captionAnimationSpeed: settings['captionAnimationSpeed']
				});
			});
			
			if( settings['controlLinkThumbs'] ) {
				$overlay.css({ height: $overlay.height() + parseInt(settings.controlThumbHeight), marginTop: -($overlay.height() + parseInt(settings.controlThumbHeight))/2 });
			}
		}  // end createSlider()
	
    });
    
    $close.click(function(){
		$overlay.fadeOut(function(){
			$slider.data('ccslider').destroy();
			$slider.children().remove();
			$slider.siblings('div.cc-caption').remove();
		});
    });
    
    
    // this function helps convert form data to json
    $.fn.serializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
			} else {
			o[this.name] = this.value || '';
			}
		});
		return o;
    };

    
});

