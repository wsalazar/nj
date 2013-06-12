jQuery(window).load(function(){
    jQuery('#slider_' + sliderParams['id']).ccslider({
        effectType: sliderParams['effectType'], 
        effect: sliderParams['effect'], 
        _3dOptions: { 
                      imageWidth: parseInt(sliderParams['imageWidth']),
                      imageHeight: parseInt(sliderParams['imageHeight']),
                      innerSideColor: sliderParams['innerSideColor'],
                      transparentImg: sliderParams['transparentImg'],
                      makeShadow: sliderParams['makeShadow'],
                      shadowColor: sliderParams['shadowColor'],
                      slices: parseInt(sliderParams['slices']),
                      rows: parseInt(sliderParams['rows']),
                      columns: parseInt(sliderParams['columns']),
                      delay: parseInt(sliderParams['delay']),
                      delayDir: sliderParams['delayDir'],
                      depthOffset: parseInt(sliderParams['depthOffset']),
                      sliceGap: parseInt(sliderParams['sliceGap']),
                      easing: sliderParams['easing'],
                      fallBack: sliderParams['fallBack'],
                      fallBackSpeed: parseInt(sliderParams['fallBackSpeed'])
                    },		
        animSpeed: parseInt(sliderParams['animSpeed']),
        startSlide: parseInt(sliderParams['startSlide']),	
        directionNav: sliderParams['directionNav'],
        controlLinks: sliderParams['controlLinks'],
        controlLinkThumbs: sliderParams['controlLinkThumbs'],
        controlThumbLocation: sliderParams['controlThumbLocation'],
        autoPlay: sliderParams['autoPlay'],
        pauseTime: parseInt(sliderParams['pauseTime']),
        pauseOnHover: sliderParams['pauseOnHover'],
        captions: sliderParams['captions'],
        captionAnimation: sliderParams['captionAnimation'],
        captionAnimationSpeed: parseInt(sliderParams['captionAnimationSpeed']) 
    });
});