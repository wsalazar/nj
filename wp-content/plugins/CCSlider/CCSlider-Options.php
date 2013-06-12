<?php
/*
* This file handles the CCSlider options in the admin page
*/

// First determine whether the page is opened to create a new slideshow or edit an existing one.
// Depending on the that choice all the form fields are filled up accordingly
$action = '';
if( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
    check_admin_referer( 'ccslider_edit_id'.$_GET['slider_id'] );
    
    $action = 'edit';
    $ccslider_options = get_option( 'ccslider_options' );
    $formFields = $ccslider_options[ $_GET['slider_id'] ];
}
else {
    $action = 'add';
    $formFields = get_option( 'ccslider_default' );
}



// Save the submitted form data
if( isset( $_POST['save'] ) ) {
    if( isset( $_POST['captionText'] ) ) {
        $_POST['captionText'] = array_map( 'force_balance_tags', $_POST['captionText'] );
    }    
    
    $formFields = $_POST;
    
    
    if( $_POST['name'] === '' ) {
        $error = 1;
    }
    else {    
        $new_id = 0;
        
        // prevent the creation of a new entry if the user again saves the displayed data of $_POST
        if( isset( $_POST['slider_id'] ) && $_POST['slider_id'] !== '' ) {
            $new_id = $_POST['slider_id'];
        }
        else {
            if( $action == 'add' ) {
                $slider_ids = array();
                $slider_ids = get_option( 'ccslider_ids' );
                
                if( $slider_ids ) {
                   foreach( $slider_ids as $slider_id ) {
                        if( $new_id < $slider_id ) {
                            $new_id = $slider_id;
                        }
                    } 
                }            
                
                $new_id = $new_id + 1;
                
                $slider_ids[ $new_id ] = $new_id;
                update_option( 'ccslider_ids', $slider_ids );
            }
            else {
                $new_id = $_GET['slider_id'];
            }
        }        
        
        $formFields['slider_id'] = $new_id;
        
        $formData = $_POST;
        $formData['slider_id'] = $new_id;
        $formData['date'] = date('d/M/Y');
        $formData['shortcode'] = '[ccslider id="'.$new_id.'"]';
        
        
        
        $ccslider_options = get_option( 'ccslider_options' );
        if( !$ccslider_options ) {
            $ccslider_options = array();
        }
        $ccslider_options[ $new_id ] = $formData;
        update_option( 'ccslider_options', $ccslider_options );
    }
}
?>

<div class="wrap">
    <?php screen_icon( 'options-general' ); ?>
    <h2>CCSlider - Slideshow Options</h2>
    
    <?php
        if( isset( $_POST['save'] ) ) {
            
            if( !$error ) {
                echo '<div class="updated" id="message"><p>Settings saved successfully</p></div>';
            }
            else {
               echo '<div class="error" id="message"><p>You must enter a name for the slider</p></div>'; 
            }
            
        }
    ?>
    
    <style type="text/css">
        a.showhelp {
            float: right;
            margin-bottom: 20px;
        }
        
        #ccslider-settings {
            clear: both;
        }
        
        #cc-help {
            position: absolute;
            top: 100px;
            left: 50%;
            width: 860px;
            height: 1000px;
            margin-left: -400px;
            z-index: 1000;
            display: none;
            background: #fff;
            -moz-box-shadow: 10px 10px 50px rgba(0, 0, 0, 0.7);
            -webkit-box-shadow: 10px 10px 50px rgba(0, 0, 0, 0.7);
            box-shadow: 10px 10px 50px rgba(0, 0, 0, 0.7);
        }
        
        #cc-closehelp {
            position: absolute;
            top: -15px;
            right: -15px;
            width: 36px;
            height: 36px;
            cursor: pointer;
            background: url(<?php echo plugin_dir_url( __FILE__);?>/images/close.png);
        }
        
        <?php if( $formFields['effectType'] === '3d' ) { ?>
            #options3d { display: block; }
            #options2d { display: none; }
        <?php
        }
        else { ?>
           #options3d { display: none; }
           #options2d { display: block; }
        <?php
        } ?>
    </style>
    
    <script type="text/javascript">
        jQuery(function($){
            var $helpContainer = $('<div id="cc-help"/>').appendTo('body'),
                $helpIframe = $('<iframe width="100%" height="100%" frameborder="1" />').appendTo($helpContainer),
                $closehelp = $('<a id="cc-closehelp"/>').appendTo($helpContainer),
                helpurl = "<?php echo plugin_dir_url( __FILE__).'documentation/index.html' ;?>" ;
            
            $helpIframe[0].src = helpurl;
            
            $('a.showhelp').click(function(){
               $helpContainer.slideDown(600); 
            });
            
            $closehelp.click(function(){
                $helpContainer.slideUp(600);
            });
        });
    </script>
    
    <a class="showhelp button-secondary">View Documentation</a>

    <form id="ccslider-settings" method="post" action="">
        <h3>Slideshow name</h3>
        
        <input type="hidden" name="slider_id" value="<?php echo $formFields['slider_id']; ?>" />
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"> <label for="name">Unique slideshow name (required)</label> </th>
                <td> <input type="text" name="name" id="name" class="regular-text" value="<?php echo $formFields['name'] ; ?>" /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="description">Slider description (optional)</label> </th>
                <td> <input type="text" name="description" id="description" class="regular-text" value="<?php echo $formFields['description'] ; ?>" /> </td>
            </tr>
        </table>
        
        
        
        <h3>Slide images and captions</h3>
        <!--<input type="button" id="dummy_button" value="Upload Images" class="button-secondary"/>-->
        <div id="upload_btn"></div>
        
        <ul id="slidesList">
            <?php
                if( $formFields['images'] ) {
                    $slidenum = count( $formFields['images'] );
                    for( $i = 0; $i < $slidenum; $i++ ) { ?>
                        <li><img src="<?php echo $formFields['images'][$i]; ?>" data-name="<?php echo basename( $formFields['images'][$i] ); ?>" alt="" />
                            <input type="hidden" name="images[]" value="<?php echo $formFields['images'][$i]; ?>" />
                            
                            <label>Image Caption (can contain HTML):</label>
                            <textarea class="caption" name="captionText[]"><?php echo stripcslashes( $formFields['captionText'][$i] ); ?></textarea>
                            
                            <label>Image Link</label>
                            <input class="slideLink" type="text" name="links[]" value="<?php echo $formFields['links'][$i]; ?>" />
                            <br/>
                            <span class="description">for e.g. http://somesite.com</span>
                             
                            <a class="delete" title="Delete this image"></a>
                        </li>
                    <?php }
                }
            ?>
        </ul>
        
        
        
        
        <h3>Slideshow Type</h3>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"> <label for="effectType">Type of Effect</label> </th>
                <td>
                    <select name="effectType" id="effectType">
                        <option <?php selected( '3d', $formFields['effectType'] ); ?>
                                          value="3d">3d</option>
                                          
                        <option <?php selected( '2d', $formFields['effectType'] ); ?>
                                          value="2d">2d</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <div id="options3d">
            <h3>Options for 3d Slideshow</h3>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"> <label for="effect3d">Effect Name</label> </th>
                    <td>
                        <select name="effect3d" id="effect3d">
                            <option <?php selected( 'random', $formFields['effect3d'] ); ?>
                                              value="random">Random</option>
                                              
                            <option <?php selected( 'cubeUp', $formFields['effect3d'] ); ?>
                                              value="cubeUp">Cube Up</option>
                                              
                            <option <?php selected( 'cubeDown', $formFields['effect3d'] ); ?>
                                              value="cubeDown">Cube Down</option>
                                              
                            <option <?php selected( 'cubeRight', $formFields['effect3d'] ); ?>
                                              value="cubeRight">Cube Right</option>
                                              
                            <option <?php selected( 'cubeLeft', $formFields['effect3d'] ); ?>
                                              value="cubeLeft">Cube Left</option>
                                              
                            <option <?php selected( 'flipUp', $formFields['effect3d'] ); ?>
                                              value="flipUp">Flip Up</option>
                                              
                            <option <?php selected( 'flipDown', $formFields['effect3d'] ); ?>
                                              value="flipDown">Flip Down</option>
                                              
                            <option <?php selected( 'flipRight', $formFields['effect3d'] ); ?>
                                              value="flipRight">Flip Right</option>
                                              
                            <option <?php selected( 'flipLeft', $formFields['effect3d'] ); ?>
                                              value="flipLeft">Flip Left</option>
                                              
                            <option <?php selected( 'blindsVertical', $formFields['effect3d'] ); ?>
                                              value="blindsVertical">Blinds Vertical</option>
                                              
                            <option <?php selected( 'blindsHorizontal', $formFields['effect3d'] ); ?>
                                              value="blindsHorizontal">Blinds Horizontal</option>
                            
                            <option <?php selected( 'gridBlocksUp', $formFields['effect3d'] ); ?>
                                              value="gridBlocksUp">Grid Blocks Up</option>
                                              
                            <option <?php selected( 'gridBlocksDown', $formFields['effect3d'] ); ?>
                                              value="gridBlocksDown">Grid Blocks Up</option>
                                              
                            <option <?php selected( 'gridBlocksLeft', $formFields['effect3d'] ); ?>
                                              value="gridBlocksLeft">Grid Blocks Left</option>
                                              
                            <option <?php selected( 'gridBlocksRight', $formFields['effect3d'] ); ?>
                                              value="gridBlocksRight">Grid Blocks Right</option>
                        </select>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="imageWidth">Image Width</label> </th>
                    <td> <input type="text" id="imageWidth" name="imageWidth" value="<?php echo $formFields['imageWidth'] ; ?>" />
                         <span class="description">in px</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="imageHeight">Image Height</label> </th>
                    <td> <input type="text" id="imageHeight" name="imageHeight" value="<?php echo $formFields['imageHeight'] ; ?>" />
                         <span class="description">in px</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="wrapperWidth">Width of the slider wrapper</label> </th>
                    <td> <input type="text" id="wrapperWidth" name="wrapperWidth" value="<?php echo $formFields['wrapperWidth'] ; ?>" />
                         <span class="description">in px and should be greater than image width</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="wrapperHeight">Height of the slider wrapper</label> </th>
                    <td> <input type="text" id="wrapperHeight" name="wrapperHeight" value="<?php echo $formFields['wrapperHeight'] ; ?>" />
                         <span class="description">in px and should be greater than image height</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="innerSideColor">Color of the inner sides of the slices</label> </th>
                    <td> <input type="text" id="innerSideColor" name="innerSideColor" value="<?php echo $formFields['innerSideColor'] ; ?>" />
                         <span class="description">Applicable only for 'cube' 3d effects</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="transparentImg">Do the slide images have transparent regions ?</label> </th>
                    <td> <input type="checkbox" id="transparentImg" name="transparentImg" <?php if( $formFields['transparentImg'] ) { ?> checked="checked" <?php } ?> />
                         <span class="description">enable this if you are using transparent png images</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="makeShadow">Enable shadow ?</label> </th>
                    <td> <input type="checkbox" id="makeShadow" name="makeShadow" <?php if( $formFields['makeShadow'] ) { ?> checked="checked" <?php } ?> />
                         <span class="description">Applicable only for 'cube' 3d effects</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="shadowColor">Color of the shadow</label> </th>
                    <td> <input type="text" id="shadowColor" name="shadowColor" value="<?php echo $formFields['shadowColor'] ; ?>" />
                         <span class="description">Applicable only if shadow is enabled for 'cube' 3d effects</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="slices">Number of slices</label> </th>
                    <td> <input type="text" id="slices" name="slices" value="<?php echo $formFields['slices'] ; ?>" /> </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="rows">Number of rows</label> </th>
                    <td> <input type="text" id="rows" name="rows" value="<?php echo $formFields['rows'] ; ?>" />
                         <span class="description">Only required for "grid" effects</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="columns">Number of columns</label> </th>
                    <td> <input type="text" id="columns" name="columns" value="<?php echo $formFields['columns'] ; ?>" />
                         <span class="description">Only required for "grid" effects</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="delay">Delay between the animation of each slice</label> </th>
                    <td> <input type="text" id="delay" name="delay" value="<?php echo $formFields['delay'] ; ?>" />
                         <span class="description">in ms</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="delayDir">Direction of propagation of the delay</label> </th>
                    <td> <select id="delayDir" name="delayDir">
                            <option <?php selected( 'first-last', $formFields['delayDir'] ); ?>
                                              value="first-last">First to Last</option>
                                              
                            <option <?php selected( 'last-first', $formFields['delayDir'] ); ?>
                                              value="last-first">Last to First</option>
                                              
                            <option <?php selected( 'fromCentre', $formFields['delayDir'] ); ?>
                                              value="fromCentre">Away from the Centre</option>
                                              
                            <option <?php selected( 'toCentre', $formFields['delayDir'] ); ?>
                                              value="toCentre">Towards the Centre</option>
                         </select>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="depthOffset">Offset during the transition along the z-axis</label> </th>
                    <td> <input type="text" id="depthOffset" name="depthOffset" value="<?php echo $formFields['depthOffset'] ; ?>" />
                         <span class="description">this value dictates how far it will go along the z-axis (away from the screen) when animating the 3d effect</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="sliceGap">Distance between the slices during the transition</label> </th>
                    <td> <input type="text" id="sliceGap" name="sliceGap" value="<?php echo $formFields['sliceGap'] ; ?>" />
                         <span class="description">in ms</span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="easing">Type of easing</label> </th>
                    <td>
                        <select id="easing" name="easing">
                            <option <?php selected( 'easeInQuad', $formFields['easing'] ); ?>
                                              value="easeInQuad">easeInQuad</option>
                                              
                            <option <?php selected( 'easeOutQuad', $formFields['easing'] ); ?>
                                              value="easeOutQuad">easeOutQuad</option>
                            
                            <option <?php selected( 'easeInOutQuad', $formFields['easing'] ); ?>
                                              value="easeInOutQuad">easeInOutQuad</option>
                                              
                            <option <?php selected( 'easeInCubic', $formFields['easing'] ); ?>
                                              value="easeInCubic">easeInCubic</option>
                            
                            <option <?php selected( 'easeOutCubic', $formFields['easing'] ); ?>
                                              value="easeOutCubic">easeOutCubic</option>
                                              
                            <option <?php selected( 'easeInOutCubic', $formFields['easing'] ); ?>
                                              value="easeInOutCubic">easeInOutCubic</option>
                                              
                            <option <?php selected( 'easeInQuart', $formFields['easing'] ); ?>
                                              value="easeInQuart">easeInQuart</option>
                            
                            <option <?php selected( 'easeOutQuart', $formFields['easing'] ); ?>
                                              value="easeOutQuart">easeOutQuart</option>
                                              
                            <option <?php selected( 'easeInOutQuart', $formFields['easing'] ); ?>
                                              value="easeInOutQuart">easeInOutQuart</option>
                                              
                            <option <?php selected( 'easeInQuint', $formFields['easing'] ); ?>
                                              value="easeInQuint">easeInQuint</option>
                                              
                            <option <?php selected( 'easeOutQuint', $formFields['easing'] ); ?>
                                              value="easeOutQuint">easeOutQuint</option>
                                              
                            <option <?php selected( 'easeInOutQuint', $formFields['easing'] ); ?>
                                              value="easeInOutQuint">easeInOutQuint</option>
                                              
                            <option <?php selected( 'easeInSine', $formFields['easing'] ); ?>
                                              value="easeInSine">easeInSine</option>
                                              
                            <option <?php selected( 'easeOutSine', $formFields['easing'] ); ?>
                                              value="easeOutSine">easeOutSine</option>
                                              
                            <option <?php selected( 'easeInOutSine', $formFields['easing'] ); ?>
                                              value="easeInOutSine">easeInOutSine</option>
                                              
                            <option <?php selected( 'easeInExpo', $formFields['easing'] ); ?>
                                              value="easeInExpo">easeInExpo</option>
                                              
                            <option <?php selected( 'easeOutExpo', $formFields['easing'] ); ?>
                                              value="easeOutExpo">easeOutExpo</option>
                                              
                            <option <?php selected( 'easeInOutExpo', $formFields['easing'] ); ?>
                                              value="easeInOutExpo">easeInOutExpo</option>
                                              
                            <option <?php selected( 'easeInCirc', $formFields['easing'] ); ?>
                                              value="easeInCirc">easeInCirc</option>
                                              
                            <option <?php selected( 'easeOutCirc', $formFields['easing'] ); ?>
                                              value="easeOutCirc">easeOutCirc</option>
                                              
                            <option <?php selected( 'easeInOutCirc', $formFields['easing'] ); ?>
                                              value="easeInOutCirc">easeInOutCirc</option>
                                              
                            <option <?php selected( 'easeInElastic', $formFields['easing'] ); ?>
                                              value="easeInElastic">easeInElastic</option>
                                              
                            <option <?php selected( 'easeOutlastic', $formFields['easing'] ); ?>
                                              value="easeOutElastic">easeOutElastic</option>
                                              
                            <option <?php selected( 'easeInOutElastic', $formFields['easing'] ); ?>
                                              value="easeInOutElastic">easeInOutElastic</option>
                                              
                            <option <?php selected( 'easeInBack', $formFields['easing'] ); ?>
                                              value="easeInBack">easeInBack</option>
                                              
                            <option <?php selected( 'easeOutBack', $formFields['easing'] ); ?>
                                              value="easeOutBack">easeOutBack</option>
                                              
                            <option <?php selected( 'easeInOutBack', $formFields['easing'] ); ?>
                                              value="easeInOutBack">easeInOutBack</option>
                                              
                            <option <?php selected( 'easeInBounce', $formFields['easing'] ); ?>
                                              value="easeInBounce">easeInBounce</option>
                                              
                            <option <?php selected( 'easeOutBounce', $formFields['easing'] ); ?>
                                              value="easeOutBounce">easeOutBounce</option>
                                              
                            <option <?php selected( 'easeInOutBounce', $formFields['easing'] ); ?>
                                              value="easeInOutBounce">easeInOutBounce</option>
                        </select>                                              
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="fallBack">Name of 2d animation fallback for older browsers</label> </th>
                    <td>
                        <select name="fallBack" id="fallBack">
                            <option <?php selected( 'random', $formFields['fallBack'] ); ?>
                                              value="random">Random</option>
                                              
                            <option <?php selected( 'fade', $formFields['fallBack'] ); ?>
                                              value="fade">Fade</option>
                                              
                            <option <?php selected( 'horizontalOverlap', $formFields['fallBack'] ); ?>
                                              value="horizontalOverlap">Horizontal Overlap</option>
                                              
                            <option <?php selected( 'verticalOverlap', $formFields['fallBack'] ); ?>
                                              value="verticalOverlap">Vertical Overlap</option>
                                              
                            <option <?php selected( 'horizontalSlide', $formFields['fallBack'] ); ?>
                                              value="horizontalSlide">Horizontal Slide</option>
                                              
                            <option <?php selected( 'verticalSlide', $formFields['fallBack'] ); ?>
                                              value="verticalSlide">Vertical Slide</option>
                                              
                            <option <?php selected( 'horizontalWipe', $formFields['fallBack'] ); ?>
                                              value="horizontalWipe">Horizontal Wipe</option>
                                              
                            <option <?php selected( 'verticalWipe', $formFields['fallBack'] ); ?>
                                              value="verticalWipe">Vertical Wipe</option>
                                              
                            <option <?php selected( 'verticalSplit', $formFields['fallBack'] ); ?>
                                              value="verticalSplit">Vertical Split</option>
                                              
                            <option <?php selected( 'horizontalSplit', $formFields['fallBack'] ); ?>
                                              value="horizontalSplit">Horizontal Split</option>
                                              
                            <option <?php selected( 'fadeSlide', $formFields['fallBack'] ); ?>
                                              value="fadeSlide">Fade Slide</option>
                                              
                            <option <?php selected( 'circle', $formFields['fallBack'] ); ?>
                                              value="circle">Circle</option>
                                              
                            <option <?php selected( 'fadeZoom', $formFields['fallBack'] ); ?>
                                              value="fadeZoom">Fade Zoom</option>
                                              
                            <option <?php selected( 'clock', $formFields['fallBack'] ); ?>
                                              value="clock">Clock</option>
                                              
                            <option <?php selected( 'zoomInOut', $formFields['fallBack'] ); ?>
                                              value="zoomInOut">Zoom In Out</option>
                                              
                            <option <?php selected( 'spinFade', $formFields['fallBack'] ); ?>
                                              value="spinFade">Spin Fade</option>
                                              
                            <option <?php selected( 'rotate', $formFields['fallBack'] ); ?>
                                              value="rotate">Rotate</option>
                        </select>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"> <label for="fallBackSpeed">Speed of transition for fallback animation</label> </th>
                    <td> <input type="text" id="fallBackSpeed" name="fallBackSpeed" value="1200" />
                         <span class="description">in ms</span>
                    </td>
                </tr>
            </table>
        </div>  <!-- end #options3d -->
        
        
        
        <div id="options2d">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"> <label for="effect2d">Effect Name</label> </th>
                    <td>
                        <select name="effect2d" id="effect2d">
                            <option <?php selected( 'random', $formFields['effect2d'] ); ?>
                                              value="random">Random</option>
                                              
                            <option <?php selected( 'fade', $formFields['effect2d'] ); ?>
                                              value="fade">Fade</option>
                                              
                            <option <?php selected( 'horizontalOverlap', $formFields['effect2d'] ); ?>
                                              value="horizontalOverlap">Horizontal Overlap</option>
                                              
                            <option <?php selected( 'verticalOverlap', $formFields['effect2d'] ); ?>
                                              value="verticalOverlap">Vertical Overlap</option>
                                              
                            <option <?php selected( 'horizontalSlide', $formFields['effect2d'] ); ?>
                                              value="horizontalSlide">Horizontal Slide</option>
                                              
                            <option <?php selected( 'verticalSlide', $formFields['effect2d'] ); ?>
                                              value="verticalSlide">Vertical Slide</option>
                                              
                            <option <?php selected( 'horizontalWipe', $formFields['effect2d'] ); ?>
                                              value="horizontalWipe">Horizontal Wipe</option>
                                              
                            <option <?php selected( 'verticalWipe', $formFields['effect2d'] ); ?>
                                              value="verticalWipe">Vertical Wipe</option>
                                              
                            <option <?php selected( 'verticalSplit', $formFields['effect2d'] ); ?>
                                              value="verticalSplit">Vertical Split</option>
                                              
                            <option <?php selected( 'horizontalSplit', $formFields['effect2d'] ); ?>
                                              value="horizontalSplit">Horizontal Split</option>
                                              
                            <option <?php selected( 'fadeSlide', $formFields['effect2d'] ); ?>
                                              value="fadeSlide">Fade Slide</option>
                                              
                            <option <?php selected( 'circle', $formFields['effect2d'] ); ?>
                                              value="circle">Circle</option>
                                              
                            <option <?php selected( 'fadeZoom', $formFields['effect2d'] ); ?>
                                              value="fadeZoom">Fade Zoom</option>
                                              
                            <option <?php selected( 'clock', $formFields['effect2d'] ); ?>
                                              value="clock">Clock</option>
                                              
                            <option <?php selected( 'zoomInOut', $formFields['effect2d'] ); ?>
                                              value="zoomInOut">Zoom In Out</option>
                                              
                            <option <?php selected( 'spinFade', $formFields['effect2d'] ); ?>
                                              value="spinFade">Spin Fade</option>
                                              
                            <option <?php selected( 'rotate', $formFields['effect2d'] ); ?>
                                              value="rotate">Rotate</option>
                        </select>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><label>Slider Size: </label></th>
                    <td> <input type="text" name="slideWidth" id="slideWidth" value="<?php echo $formFields['slideWidth'] ; ?>" /> &times;
                         <input type="text" name="slideHeight" id="slideHeight" value="<?php echo $formFields['slideHeight'] ; ?>" />
                         <span class="description">in px (the slide images will be cropped to these dimensions)</span>
                    </td>
                </tr>
            </table>
        </div>  <!-- end #options2d -->
        
        
        
        <h3>General Options</h3>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"> <label for="animSpeed">Animation speed</label> </th>
                <td> <input type="text" name="animSpeed" id="animSpeed"  value="<?php echo $formFields['animSpeed'] ; ?>" />
                     <span class="description">in ms</span>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="startSlide">Slide number from which slideshow will start</label> </th>
                <td> <input type="text" name="startSlide" id="startSlide"  value="<?php echo $formFields['startSlide'] ; ?>" />
                     <span class="description">Zero based index</span>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="directionNav">Enable next/prev buttons</label> </th>
                <td> <input type="checkbox" name="directionNav" id="directionNav" <?php if( $formFields['directionNav'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="controlLinks">Enable control links to each slide</label> </th>
                <td> <input type="checkbox" name="controlLinks" id="controlLinks" <?php if( $formFields['controlLinks'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="controlLinkThumbs">Enable thumbnails as control links to each slide</label> </th>
                <td> <input type="checkbox" name="controlLinkThumbs" id="controlLinkThumbs" <?php if( $formFields['controlLinkThumbs'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label>Size of thumbnails</label> </th>
                <td> <input type="text" name="controlThumbWidth" id="controlThumbWidth" value="<?php echo $formFields['controlThumbWidth'] ; ?>" /> &times;
                     <input type="text" name="controlThumbHeight" id="controlThumbHeight" value="<?php echo $formFields['controlThumbHeight'] ; ?>" />
                     <span class="description">in px (the width and height of the thumbnails used for control links)</span>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="autoPlay">Enable Auto Play</label> </th>
                <td> <input type="checkbox" name="autoPlay" id="autoPlay" <?php if( $formFields['autoPlay'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="pauseTime">Time interval for which each slide will be visible</label> </th>
                <td> <input type="text" name="pauseTime" id="pauseTime" value="<?php echo $formFields['pauseTime'] ; ?>" />
                     <span class="description">in ms</span>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="pauseOnHover">Pause slideshow on hover</label> </th>
                <td> <input type="checkbox" name="pauseOnHover" id="pauseOnHover" <?php if( $formFields['pauseOnHover'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="captions">Enable captions</label> </th>
                <td> <input type="checkbox" name="captions" id="captions" <?php if( $formFields['captions'] ) { ?> checked="checked" <?php } ?> /> </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="captionAnimation">Animated effect for captions</label> </th>
                <td>
                    <select name="captionAnimation" id="captionAnimation" <?php if( $formFields['captions'] ) { ?> diabled="diabled" <?php } ?> >
                        <option <?php selected( 'fade', $formFields['captionAnimation'] ); ?>
                                          value="fade">Fade</option>
                                          
                        <option <?php selected( 'slide', $formFields['captionAnimation'] ); ?>
                                          value="slide">Slide</option>
                                          
                        <option <?php selected( 'none', $formFields['captionAnimation'] ); ?>
                                          value="none">None</option>
                    </select>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"> <label for="captionAnimationSpeed">Animation speed for captions</label> </th>
                <td> <input type="text" name="captionAnimationSpeed" id="captionAnimationSpeed"  value="<?php echo $formFields['captionAnimationSpeed'] ; ?>" <?php if( $formFields['captions'] ) { ?> diabled="diabled" <?php } ?> />
                     <span class="description">in ms</span>
                </td>
            </tr>
            
        </table>
        
        
        
        <h3>Custom Transitions for Each Slide</h3>
        
        <table class="form-table" id="customTransition">
            <tr valign="top">
                <th scope="row"> <label for="enableCustom">Enable custom transitions per slide:</label> </th>
                <td> <input type="checkbox" name="enableCustom" <?php if( $formFields['enableCustom'] ) { ?> id="disableCustom" checked="checked" <?php } else { ?> id="enableCustom" <?php } ?> /> </td>  <!-- id is kept different than name -->
            </tr>
        </table>
        
        
        
        <ul id="customSettings" <?php if( $formFields['enableCustom'] ) {?> style="display: block;" <?php } ?>>
            <?php
            $customnum = 1;
            if( $formFields['enableCustom'] ) {
                $customnum = $slidenum;
            }
            for( $i = 0; $i < $customnum; $i++ ) { ?>
            <li <?php if( $formFields['effectType'] === '2d' ) {?> class="customEffects2d" <?php } ?> >
                <p class="slideThumb"><img src="<?php echo $formFields['images'][$i]; ?>" alt="" /></p>
                
                <p class="custom3d">
                    <label>Effect name</label>
                    <select name="custom3d[]">
                        <option value="">Select one</option>
                        
                        <option <?php selected( 'cubeUp', $formFields['custom3d'][$i] ); ?>
                                          value="cubeUp">Cube Up</option>
                                          
                        <option <?php selected( 'cubeDown', $formFields['custom3d'][$i] ); ?>
                                          value="cubeDown">Cube Down</option>
                                          
                        <option <?php selected( 'cubeRight', $formFields['custom3d'][$i] ); ?>
                                          value="cubeRight">Cube Right</option>
                                          
                        <option <?php selected( 'cubeLeft', $formFields['custom3d'][$i] ); ?>
                                          value="cubeLeft">Cube Left</option>
                                          
                        <option <?php selected( 'flipUp', $formFields['custom3d'][$i] ); ?>
                                          value="flipUp">Flip Up</option>
                                          
                        <option <?php selected( 'flipDown', $formFields['custom3d'][$i] ); ?>
                                          value="flipDown">Flip Down</option>
                                          
                        <option <?php selected( 'flipRight', $formFields['custom3d'][$i] ); ?>
                                          value="flipRight">Flip Right</option>
                                          
                        <option <?php selected( 'flipLeft', $formFields['custom3d'][$i] ); ?>
                                          value="flipLeft">Flip Left</option>
                                          
                        <option <?php selected( 'blindsVertical', $formFields['custom3d'][$i] ); ?>
                                          value="blindsVertical">Blinds Vertical</option>
                                          
                        <option <?php selected( 'blindsHorizontal', $formFields['custom3d'][$i] ); ?>
                                          value="blindsHorizontal">Blinds Horizontal</option>
                        
                        <option <?php selected( 'gridBlocksUp', $formFields['custom3d'][$i] ); ?>
                                          value="gridBlocksUp">Grid Blocks Up</option>
                                          
                        <option <?php selected( 'gridBlocksDown', $formFields['custom3d'][$i] ); ?>
                                          value="gridBlocksDown">Grid Blocks Down</option>
                                          
                        <option <?php selected( 'gridBlocksLeft', $formFields['custom3d'][$i] ); ?>
                                          value="gridBlocksLeft">Grid Blocks Left</option>
                                          
                        <option <?php selected( 'gridBlocksRight', $formFields['custom3d'][$i] ); ?>
                                          value="gridBlocksRight">Grid Blocks Right</option>
                    </select>
                </p>
                
                <p class="custom2d">
                    <label>Effect name</label>
                    <select name="custom2d[]">
                        <option value="">Select one</option>
                        
                        <option <?php selected( 'fade', $formFields['custom2d'][$i] ); ?>
                                          value="fade">Fade</option>
                                          
                        <option <?php selected( 'horizontalOverlap', $formFields['custom2d'][$i] ); ?>
                                          value="horizontalOverlap">Horizontal Overlap</option>
                                          
                        <option <?php selected( 'verticalOverlap', $formFields['custom2d'][$i] ); ?>
                                          value="verticalOverlap">Vertical Overlap</option>
                                          
                        <option <?php selected( 'horizontalSlide', $formFields['custom2d'][$i] ); ?>
                                          value="horizontalSlide">Horizontal Slide</option>
                                          
                        <option <?php selected( 'verticalSlide', $formFields['custom2d'][$i] ); ?>
                                          value="verticalSlide">Vertical Slide</option>
                                          
                        <option <?php selected( 'horizontalWipe', $formFields['custom2d'][$i] ); ?>
                                          value="horizontalWipe">Horizontal Wipe</option>
                                          
                        <option <?php selected( 'verticalWipe', $formFields['custom2d'][$i] ); ?>
                                          value="verticalWipe">Vertical Wipe</option>
                                          
                        <option <?php selected( 'verticalSplit', $formFields['custom2d'][$i] ); ?>
                                          value="verticalSplit">Vertical Split</option>
                                          
                        <option <?php selected( 'horizontalSplit', $formFields['custom2d'][$i] ); ?>
                                          value="horizontalSplit">Horizontal Split</option>
                                          
                        <option <?php selected( 'fadeSlide', $formFields['custom2d'][$i] ); ?>
                                          value="fadeSlide">Fade Slide</option>
                                          
                        <option <?php selected( 'circle', $formFields['custom2d'][$i] ); ?>
                                          value="circle">Circle</option>
                                          
                        <option <?php selected( 'fadeZoom', $formFields['custom2d'][$i] ); ?>
                                          value="fadeZoom">Fade Zoom</option>
                                          
                        <option <?php selected( 'clock', $formFields['custom2d'][$i] ); ?>
                                          value="clock">Clock</option>
                                          
                        <option <?php selected( 'zoomInOut', $formFields['custom2d'][$i] ); ?>
                                          value="zoomInOut">Zoom In Out</option>
                                          
                        <option <?php selected( 'spinFade', $formFields['custom2d'][$i] ); ?>
                                          value="spinFade">Spin Fade</option>
                                          
                        <option <?php selected( 'rotate', $formFields['custom2d'][$i] ); ?>
                                          value="rotate">Rotate</option>
                    </select>
                </p>
                
                <p class="customSlices>
                    <label for="customSlices">Number of slices</label>
                    <input type="text" name="customSlices[]" value="<?php echo $formFields['customSlices'][$i]; ?>" />
                </p>
                
                <p class="customRows">
                    <label for="customRows">Number of rows</label>
                    <input type="text" name="customRows[]" value="<?php echo $formFields['customRows'][$i] ; ?>" />
                    <span class="description">Only required for "grid" effects</span>                    
                </p>
                
                <p class="customColumns">
                    <label for="customColumns">Number of columns</label>
                    <input type="text" name="customColumns[]" value="<?php echo $formFields['customColumns'][$i] ; ?>" />
                    <span class="description">Only required for "grid" effects</span>                    
                </p>
                
                <p class="customDelay">
                    <label for="customDelay">Delay between slices</label>
                    <input type="text" name="customDelay[]" value="<?php echo $formFields['customDelay'][$i] ; ?>" />
                    <span class="description">in ms</span>
                </p>
                
                <p class="customDelayDir">
                    <label for="customDelayDir">Delay direction</label>
                    <select name="customDelayDir[]">
                        <option value="">Select one</option>
                        
                        <option <?php selected( 'first-last', $formFields['customDelayDir'][$i] ); ?>
                                          value="first-last">First to Last</option>
                                          
                        <option <?php selected( 'last-first', $formFields['customDelayDir'][$i] ); ?>
                                          value="last-first">Last to First</option>
                                          
                        <option <?php selected( 'fromCentre', $formFields['customDelayDir'][$i] ); ?>
                                          value="fromCentre">Away from the Centre</option>
                                          
                        <option <?php selected( 'toCentre', $formFields['customDelayDir'][$i] ); ?>
                                          value="toCentre">Towards the Centre</option>
                     </select>
                </p>
                
                <p class="customDepthOffset">
                    <label for="customDepthOffset">Offset along z-axis</label> </th>
                    <input type="text" name="customDepthOffset[]" value="<?php echo $formFields['customDepthOffset'][$i]; ?>" />
                </p>
                
                <p class="customSliceGap">
                    <label for="customSliceGap">Distance between slices</label>
                    <input type="text" name="customSliceGap[]" value="<?php echo $formFields['customSliceGap'][$i]; ?>" />
                    <span class="description">in ms</span>
                </p>
                
                <p class="customEasing">
                    <label for="customEasing">Type of easing</label>
                    <select name="customEasing[]">
                        <option value="">Select one</option>
                        
                        <option <?php selected( 'easeInQuad', $formFields['customEasing'][$i] ); ?>
                                          value="easeInQuad">easeInQuad</option>
                                          
                        <option <?php selected( 'easeOutQuad', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutQuad">easeOutQuad</option>
                        
                        <option <?php selected( 'easeInOutQuad', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutQuad">easeInOutQuad</option>
                                          
                        <option <?php selected( 'easeInCubic', $formFields['customEasing'][$i] ); ?>
                                          value="easeInCubic">easeInCubic</option>
                        
                        <option <?php selected( 'easeOutCubic', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutCubic">easeOutCubic</option>
                                          
                        <option <?php selected( 'easeInOutCubic', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutCubic">easeInOutCubic</option>
                                          
                        <option <?php selected( 'easeInQuart', $formFields['customEasing'][$i] ); ?>
                                          value="easeInQuart">easeInQuart</option>
                        
                        <option <?php selected( 'easeOutQuart', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutQuart">easeOutQuart</option>
                                          
                        <option <?php selected( 'easeInOutQuart', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutQuart">easeInOutQuart</option>
                                          
                        <option <?php selected( 'easeInQuint', $formFields['customEasing'][$i] ); ?>
                                          value="easeInQuint">easeInQuint</option>
                                          
                        <option <?php selected( 'easeOutQuint', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutQuint">easeOutQuint</option>
                                          
                        <option <?php selected( 'easeInOutQuint', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutQuint">easeInOutQuint</option>
                                          
                        <option <?php selected( 'easeInSine', $formFields['customEasing'][$i] ); ?>
                                          value="easeInSine">easeInSine</option>
                                          
                        <option <?php selected( 'easeOutSine', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutSine">easeOutSine</option>
                                          
                        <option <?php selected( 'easeInOutSine', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutSine">easeInOutSine</option>
                                          
                        <option <?php selected( 'easeInExpo', $formFields['customEasing'][$i] ); ?>
                                          value="easeInExpo">easeInExpo</option>
                                          
                        <option <?php selected( 'easeOutExpo', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutExpo">easeOutExpo</option>
                                          
                        <option <?php selected( 'easeInOutExpo', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutExpo">easeInOutExpo</option>
                                          
                        <option <?php selected( 'easeInCirc', $formFields['customEasing'][$i] ); ?>
                                          value="easeInCirc">easeInCirc</option>
                                          
                        <option <?php selected( 'easeOutCirc', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutCirc">easeOutCirc</option>
                                          
                        <option <?php selected( 'easeInOutCirc', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutCirc">easeInOutCirc</option>
                                          
                        <option <?php selected( 'easeInElastic', $formFields['customEasing'][$i] ); ?>
                                          value="easeInElastic">easeInElastic</option>
                                          
                        <option <?php selected( 'easeOutlastic', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutElastic">easeOutElastic</option>
                                          
                        <option <?php selected( 'easeInOutElastic', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutElastic">easeInOutElastic</option>
                                          
                        <option <?php selected( 'easeInBack', $formFields['customEasing'][$i] ); ?>
                                          value="easeInBack">easeInBack</option>
                                          
                        <option <?php selected( 'easeOutBack', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutBack">easeOutBack</option>
                                          
                        <option <?php selected( 'easeInOutBack', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutBack">easeInOutBack</option>
                                          
                        <option <?php selected( 'easeInBounce', $formFields['customEasing'][$i] ); ?>
                                          value="easeInBounce">easeInBounce</option>
                                          
                        <option <?php selected( 'easeOutBounce', $formFields['customEasing'][$i] ); ?>
                                          value="easeOutBounce">easeOutBounce</option>
                                          
                        <option <?php selected( 'easeInOutBounce', $formFields['customEasing'][$i] ); ?>
                                          value="easeInOutBounce">easeInOutBounce</option>
                    </select>                                              
                </p>
                
                <p class="customAnimSpeed">
                    <label for="customAnimSpeed">Animation speed</label>
                    <input type="text" name="customAnimSpeed[]" value="<?php echo $formFields['customAnimSpeed'][$i] ; ?>" />
                    <span class="description">in ms</span>
                </p>
            </li>
            <?php } ?>
        </ul>
        
        
        
        <p id="formButtons">
            <input type="button" name="preview" id="slider-preview" value="Preview Slideshow" class="button-secondary" />
            <input type="submit" name="save" value="Save Options" class="button-primary" />
        </p>
        
    </form> 
</div>  <!-- end #wrap -->

