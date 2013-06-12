<?php
/*
Plugin Name: CCSlider WP
Plugin URI: http://codecanyon.net/user/cosmocoder
Description: Add CCSlider to your WP theme
Version: 2.0.2
Author: Nilok Bose (CosmoCoder)
Author URI: http://codecanyon.net/user/cosmocoder
*/



//check the WP version (should be version 3.0 or greater)
register_activation_hook( __FILE__, 'ccslider_install' );
        
function ccslider_install() {
    if ( version_compare( get_bloginfo( 'version' ), '3.0', ' < ' ) ) {
        deactivate_plugins( basename( __FILE__ ) ); // Deactivate ccslider plugin
    }
    
    // create the default list of ccslider options
    $default_params = array(
        'effectType'=> '3d',
        'effect'=> 'cubeUp',
        'imageWidth'=> 600,
        'imageHeight'=> 300,
        'wrapperWidth'=> 700,
        'wrapperHeight'=> 400,
        'transparentImg' => false,
        'innerSideColor'=> '#444',
        'makeShadow'=> true,
        'shadowColor'=> 'rgba(0, 0, 0, 0.7)',
        'slices'=> 3,
        'rows' => 3,
        'columns' => 3,
        'delay'=> 200,
        'delayDir' => 'first-last',
        'depthOffset' => 400,
        'sliceGap' => 20,
        'easing'=> 'easeInOutCubic',
        'fallBack'=> 'fadeSlide',
        'fallBackSpeed'=> 1200,
        'animSpeed'=> 1200,
        'startSlide'=> 0,
        'directionNav'=> true,
        'controlLinks'=> true,
        'controlLinkThumbs'=> false,
        'controlThumbLocation'=> content_url( 'ccslider-upload' ).'/',
        'autoPlay'=> true,
        'pauseTime'=> 3000,
        'pauseOnHover'=> true,
        'captions'=> true,
        'captionAnimation'=> 'slide',
        'captionAnimationSpeed'=> 600 
    );
    
    update_option( 'ccslider_default', $default_params );
    
    // insert missing values for options introduced in the new version for previously created slideshows in older versions
    $slider_ids = get_option( 'ccslider_ids' );
    $ccslider_options = get_option('ccslider_options' );
    
    if( $slider_ids ) {
        foreach( $slider_ids as $slider_id ) {
            foreach( $default_params as $key => $val ) {
                if( !isset( $ccslider_options[ $slider_id ][ $key ] ) || $ccslider_options[ $slider_id ][ $key ] === '' ) {
                    $ccslider_options[ $slider_id ][ $key ] = $val;
                }
            }
        }
        
        update_option( 'ccslider_options', $ccslider_options );
    }
}


// replace Wordpress's version of jQuery with that from Google
add_action( 'init', 'jquery_google' );

if( !function_exists( 'jquery_google' ) ) {
    function jquery_google() {
        if( !is_admin() ) {
            wp_deregister_script( 'jquery' );
            wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js', array(), '1.7.1' );
        }        
    }
}



// URL to plugin folder
define( 'ccslider_path', plugin_dir_url( __FILE__), true );

// URL to the /js directory of the plugin
define( 'ccslider_js_path', plugin_dir_url( __FILE__).'js', true );

// URL to the /css directory of the plugin
define( 'ccslider_css_path', plugin_dir_url( __FILE__).'css', true );

// create the folder where uploaded images for ccslider will be stored
define( 'upload_folder', WP_CONTENT_DIR.'/ccslider-upload', true );

if( !file_exists( upload_folder ) ) {
    if( !mkdir( upload_folder, 0777, true ) ) {
        wp_die('Cannot create upload folder!');   
    }
}



//create menus for CCSlider in admin page
add_action( 'admin_menu', 'ccslider_create_menu' );

function ccslider_create_menu() {
    //create CCSlider top level menu
    add_menu_page( 'CCSlider', 'CCSlider', 'manage_options', 'ccslider_manager', 'ccslider_manager_page' );
    
    //create sub-menu items
    $manager_page = add_submenu_page( 'ccslider_manager', 'CCSlider - Slideshow Manager', 'Slideshow Manager', 'manage_options', 'ccslider_manager', ccslider_manager_page );
    $options_page = add_submenu_page( 'ccslider_manager', 'CCSlider - Add/Edit Slideshow', 'Create New Slideshow', 'manage_options', 'ccslider_options', ccslider_options_page );
    
    // hook to load scripts in ccslider options page
    add_action( 'load-'.$options_page, 'ccslider_admin_scripts' );
}


//load the manager page
function ccslider_manager_page() {
    // Check that the user is allowed to update options
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    require_once('CCSlider-Manager.php');
}


//load the options page
function ccslider_options_page() {    
    // Check that the user is allowed to update options
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    require_once('CCSlider-Options.php');
}

// load scripts in ccslider options page
function ccslider_admin_scripts() {
    wp_enqueue_style( 'ccslider_upload_css', ccslider_css_path.'/fileuploader.css', array(), '2.0.1' );    
    wp_enqueue_style( 'ccslider_css', ccslider_css_path.'/ccslider.css', array(), '2.0.1' );
    wp_enqueue_style( 'ccslider_admin_css', ccslider_css_path.'/admin.css', array('ccslider_css'), '2.0.1' );
    
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery_easing', ccslider_js_path.'/jquery.easing.1.3.js', array('jquery'), '1.3' );
    wp_enqueue_script( 'ccslider_js', ccslider_js_path.'/jquery.ccslider-2.0.1.min.js', array('jquery', 'jquery_easing'), '2.0.1' );
    wp_enqueue_script( 'ccslider_upload_js', ccslider_js_path.'/fileuploader.js', array(), '2.0.1' );
    wp_enqueue_script( 'ccslider_admin_js', ccslider_js_path.'/admin.js', array('jquery', 'jquery_easing', 'ccslider_js'), '2.0.1' );
    
    
    // Get current page protocol
    $protocol = isset( $_SERVER['HTTPS']) ? 'https://' : 'http://';
    
    // get url to admin-ajax.php
    $ajaxurl = admin_url( 'admin-ajax.php', $protocol );
    
    
    $upload_params = array(
        'upload_php' => ccslider_path.'includes/upload.php',
        'upload_folder' => upload_folder,
        'upload_url' => content_url( 'ccslider-upload' ),
        'ajaxurl' => $ajaxurl
    );
    
    wp_localize_script( 'ccslider_admin_js', 'uploadParams', $upload_params );
}


//load ccslider jquery plugin and css file when the Wordpress loads the required theme
add_action( 'template_redirect', 'ccslider_insert_scripts_blog' );

function ccslider_insert_scripts_blog() {
    wp_enqueue_style( 'ccslider_css', ccslider_css_path.'/ccslider.css', array(), '2.0.1' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery_easing', ccslider_js_path.'/jquery.easing.1.3.js', array('jquery'), '1.3', true );
    wp_enqueue_script( 'ccslider_js', ccslider_js_path.'/jquery.ccslider-2.0.1.min.js', array('jquery', 'jquery_easing'), '2.0.1', true  );
    wp_enqueue_script( 'ccslider_init_js', ccslider_js_path.'/slider-init.js', array('jquery', 'jquery_easing', 'ccslider_js' ), '2.0.1', true );
}



// shortcode for CCSlider
add_shortcode('ccslider', ccslider_slideshow );

global $ccslider_insert_js;

function ccslider_slideshow( $attr ) {
    require_once('includes/vt_resize.php');
    
    global $ccslider_insert_js;
    $ccslider_insert_js = true;
    
    $slider_id = $attr['id'];
    $ccslider_options = get_option( 'ccslider_options' );
    $slider_option = $ccslider_options[ $slider_id ];
    
    if( !is_numeric($slider_option['imageWidth']) ) {
        $slider_option['imageWidth'] = 600;
    }
    
    if( !is_numeric($slider_option['imageHeight']) ) {
        $slider_option['imageHeight'] = 300;
    }
    
    if( !is_numeric($slider_option['slideWidth']) ) {
        $slider_option['slideWidth'] = 600;
    }
    
    if( !is_numeric($slider_option['slideHeight']) ) {
        $slider_option['slideHeight'] = 300;
    }
    
    if( !is_numeric($slider_option['controlThumbWidth']) ) {
        $slider_option['controlThumbWidth'] = 100;
    }
    
    if( !is_numeric($slider_option['controlThumbHeight']) ) {
        $slider_option['controlThumbHeight'] = 50;
    }
    
    $slider_option['effect'] = $slider_option['effectType'] === '3d' ? $slider_option['effect3d'] : $slider_option['effect2d'];
    
    $slider_params = array(
        'id'=> $slider_id,
        'effectType'=> $slider_option['effectType'],
        'effect'=> $slider_option['effect'],
        'imageWidth'=> $slider_option['imageWidth'],
        'imageHeight'=> $slider_option['imageHeight'],
        'transparentImg'=> $slider_option['transparentImg'] ? true : false,
        'innerSideColor'=> $slider_option['innerSideColor'],
        'makeShadow'=> $slider_option['makeShadow'] ? true : false,
        'shadowColor'=> $slider_option['shadowColor'],
        'slices'=> $slider_option['slices'],
        'rows'=> $slider_option['rows'],
        'columns'=> $slider_option['columns'],
        'delay'=> $slider_option['delay'],
        'delayDir'=> $slider_option['delayDir'],
        'depthOffset'=> $slider_option['depthOffset'],
        'sliceGap'=> $slider_option['sliceGap'],
        'easing'=> $slider_option['easing'],
        'fallBack'=> $slider_option['fallBack'],
        'fallBackSpeed'=> $slider_option['fallBackSpeed'],
        'animSpeed'=> $slider_option['animSpeed'],
        'startSlide'=> $slider_option['startSlide'],
        'directionNav'=> $slider_option['directionNav'] ? true : false,
        'controlLinks'=> $slider_option['controlLinks'] ? true : false,
        'controlLinkThumbs'=> $slider_option['controlLinkThumbs'] ? true : false,
        'controlThumbLocation'=> content_url( 'ccslider-upload' ).'/',
        'autoPlay'=> $slider_option['autoPlay'] ? true : false,
        'pauseTime'=> $slider_option['pauseTime'],
        'pauseOnHover'=> $slider_option['pauseOnHover'] ? true : false,
        'captions'=> $slider_option['captions'] ? true : false,
        'captionAnimation'=> $slider_option['captionAnimation'],
        'captionAnimationSpeed'=> $slider_option['captionAnimationSpeed'] 
    );
    
    wp_localize_script( 'ccslider_init_js', 'sliderParams', $slider_params );
    
    
    if( $slider_option['effectType'] === '3d' ) {
        $width = $slider_option['wrapperWidth'];        
        $height = $slider_option['wrapperHeight'];
        
        $slider_html = '<div id="slider_'.$slider_id.'" class="ccslider" style="width:'.$width.'px; height:'.$height.'px;">';
    }
    else {
        $slider_html = '<div id="slider_'.$slider_id.'" class="ccslider">';
    }    
    
    $imagenum = count( $slider_option['images'] );
    $imageHtml = '';
    $captionData = '';
    $captionHtml = '';
    $linkData = '';
    $customData = '';
    $thumbData = '';
    
    for( $i = 0; $i < $imagenum; $i++ ) {
        $captionData = '';
        $linkData = '';
        
        if( $slider_option['captionText'][$i] !== '' ) {
            $captionData = ' data-captionelem="#cc-slideCaption'.$i.'"';
            $captionHtml .= '<div class="cc-caption" id="cc-slideCaption'.$i.'">'. stripcslashes( $slider_option['captionText'][$i] ) .'</div>';
        }
        
        if( $slider_option['links'][$i] !== '' ) {
            $linkData = ' data-href="'.$slider_option['links'][$i].'"';
        }
        
        if( $slider_option['enableCustom'] ) {
            $customData = ' data-transition=\'{';
            
            if( $slider_option['effectType'] === '3d' && isset($slider_option['custom3d'][$i]) && $slider_option['custom3d'][$i] !== '' ) {
                $customData .= '"effect": "'.$slider_option['custom3d'][$i].'", ';
            }
            else if( $slider_option['effectType'] === '2d' && isset($slider_option['custom2d'][$i]) && $slider_option['custom2d'][$i] !== '' ) {
                $customData .= '"effect": "'.$slider_option['custom2d'][$i].'", ';
            }
            
            if( isset($slider_option['customSlices'][$i]) && $slider_option['customSlices'][$i] !== '' ) {
                $customData .= '"slices": '.$slider_option['customSlices'][$i].', ';
            }
            
            if( isset($slider_option['customRows'][$i]) && $slider_option['customRows'][$i] !== '' ) {
                $customData .= '"rows": '.$slider_option['customRows'][$i].', ';
            }
            
            if( isset($slider_option['customColumns'][$i]) && $slider_option['customColumns'][$i] !== '' ) {
                $customData .= '"columns": '.$slider_option['customColumns'][$i].', ';
            }
            
            if( isset($slider_option['customDelay'][$i]) && $slider_option['customDelay'][$i] !== '' ) {
                $customData .= '"delay": '.$slider_option['customDelay'][$i].', ';
            }
            
            if( isset($slider_option['customDelayDir'][$i]) && $slider_option['customDelayDir'][$i] !== '' ) {
                $customData .= '"delayDir": "'.$slider_option['customDelayDir'][$i].'", ';
            }
            
            if( isset($slider_option['customDepthOffset'][$i]) && $slider_option['customDepthOffset'][$i] !== '' ) {
                $customData .= '"depthOffset": '.$slider_option['customDepthOffset'][$i].', ';
            }
            
            if( isset($slider_option['customSliceGap'][$i]) && $slider_option['customSliceGap'][$i] !== '' ) {
                $customData .= '"sliceGap": '.$slider_option['customSliceGap'][$i].', ';
            }
            
            if( isset($slider_option['customEasing'][$i]) && $slider_option['customEasing'][$i] !== '' ) {
                $customData .= '"easing": "'.$slider_option['customEasing'][$i].'", ';
            }
            
            if( isset($slider_option['customAnimSpeed'][$i]) && $slider_option['customAnimSpeed'][$i] !== '' ) {
                $customData .= '"animSpeed": '.$slider_option['customAnimSpeed'][$i].', ';
            }
            
            // remove the last two characters consisting of a comma and a space
            $customData = substr($customData, 0, -2);
            
            $customData .= '}\'';
        }
        
        
        $src = $slider_option['effectType'] === '3d' ? vt_resize(null, $slider_option['images'][$i], $slider_option['imageWidth'], $slider_option['imageHeight'], true)
                                                     : vt_resize(null, $slider_option['images'][$i], $slider_option['slideWidth'], $slider_option['slideHeight'], true);
                                                     
        $thumburl = vt_resize(null, $slider_option['images'][$i], $slider_option['controlThumbWidth'], $slider_option['controlThumbHeight'], true);
        $thumbname = basename( $thumburl['url'] );
        $thumbData = ' data-thumbname="'.$thumbname.'"';
        
        if( is_wp_error($src) ) {
            echo '<p>Error: '. $src->get_error_message() .'</p>';
            $imageHtml .= '<img src=" "'.$captionData.$linkData.$customData.$thumbData.' alt="" />';
        }
        else {
            $imageHtml .= '<img src="'. $src['url'] .'"'.$captionData.$linkData.$customData.$thumbData.' alt="" />';    
        }
        
    }
    
    $slider_html .= $imageHtml;
    $slider_html .= '</div>';
    $slider_html .= $captionHtml;
    
    
    return $slider_html;
}


// remove scripts if the shortcode is not present
add_action( 'wp_print_footer_scripts', 'ccslider_remove_scripts', 1 );

function ccslider_remove_scripts() {
    global $ccslider_insert_js;
    if( !$ccslider_insert_js ) {
        wp_deregister_script( 'jquery_easing' );
        wp_deregister_script( 'ccslider_js' );
        wp_deregister_script( 'ccslider_init_js' );
    }
}



// function to delete images on ajax call
add_action( 'wp_ajax_delete_image', 'delete_uploaded_image' );

function delete_uploaded_image() {
    $img_name = pathinfo($_REQUEST['name']);
    $allimgs = glob( upload_folder.'/'.$img_name['filename'].'*');
    foreach( $allimgs as $img ) {
        if( pathinfo( $img, PATHINFO_EXTENSION) == $img_name['extension']) {
            unlink($img);    
        }        
    }
    //unlink( WP_CONTENT_DIR.'/ccslider-upload/'.$img_name );
}



// function to resize images for live preview
add_action( 'wp_ajax_preview_image_resize', 'preview_image_resize' );

function preview_image_resize() {
    require_once('includes/vt_resize.php');
    
    $imgurls = $_REQUEST['urls'];
    $width = $_REQUEST['width'];
    $height = $_REQUEST['height'];
    $resizeUrls = array();
    
    foreach( $imgurls as $url ) {
        $newimg = vt_resize(null, $url, $width, $height, true);
        array_push( $resizeUrls, $newimg['url'] );
    }
    
    echo json_encode($resizeUrls);
    exit();
}

?>