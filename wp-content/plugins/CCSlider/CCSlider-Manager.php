<?php

/*
 * This file handles the management of CCSlider slideshows
 */


    $slider_ids = get_option( 'ccslider_ids' );
    $ccslider_options = get_option('ccslider_options' );
    
    if( isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {
        check_admin_referer( 'ccslider_delete_id'.$_GET['slider_id'] );
        
        unset( $slider_ids[ $_GET['slider_id'] ] );
        update_option( 'ccslider_ids', $slider_ids );
        
        $slider_options = $ccslider_options[ $_GET['slider_id'] ];
        $slider_images = $slider_options['images'];
        
        // delete the associated images
        if( $slider_images ) {
            foreach( $slider_images as $slideimg ) {
                $img_name = pathinfo( $slideimg );
                $allimgs = glob( upload_folder.'/'.$img_name['filename'].'*');
                foreach( $allimgs as $img ) {
                    if( pathinfo( $img, PATHINFO_EXTENSION) == $img_name['extension']) {
                        unlink($img);    
                    }        
                }
                //unlink( WP_CONTENT_DIR.'/ccslider-upload/'.$img_name );
            }    
        }        
        
        unset( $ccslider_options[ $_GET['slider_id'] ] );
        update_option( 'ccslider_options', $ccslider_options );
        
        $deleted = true;
        $delete_msg = 'The slider ( id = "'.$_GET['slider_id'].'" ) was deleted';
    }
?>

<div class="wrap">
    <?php screen_icon( 'plugins' ); ?>
    <h2>CCSlider - Slideshow Manager</h2>
    
    <?php
        if( $deleted ) {
            echo '<div class="updated" id="message"><p>'.$delete_msg.'</p></div>';
        }
    ?>
    
    <style type="text/css">
        a.showhelp {
            float: right;
            margin-bottom: 20px;
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
        
        .manager-thumbs {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .manager-thumbs li {
            display: inline-block;
            width: 32px;
            height: 32px;
            margin: 0 10px 0 0;
            padding: 2px;
            border: 1px solid #aaa;
        }
        
        .manager-thumbs li img {
            width: 100%;
            height: 100%;
        }
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
    
    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Images</th>
                <th>Shortcode</th>
                <th>Action</th>
                <th>Date (Last Modified)</th>
            </tr>
        </thead>
        
        <tfoot>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Images</th>
                <th>Shortcode</th>
                <th>Action</th>
                <th>Date (Last Modified)</th>
            </tr>
        </tfoot>
        
        <tbody>
            
            <?php
            if( $slider_ids ) {
                foreach( $slider_ids as $slider_id ) {
                    $edit_query = add_query_arg( array( 'page' => 'ccslider_options', 'action' => 'edit', 'slider_id' => $slider_id ), admin_url( 'admin.php' ) );
                    $edit_url = wp_nonce_url( $edit_query, 'ccslider_edit_id'.$slider_id );
                    
                    $delete_query = add_query_arg( array( 'action' => 'delete', 'slider_id' => $slider_id ) );
                    $delete_url = wp_nonce_url( $delete_query, 'ccslider_delete_id'.$slider_id );
                    
                    $imagenum = count( $ccslider_options[ $slider_id ]['images'] );
                    if( $imagenum > 5 ) $imagenum = 5;
                    $slideThumbs = '<ul class="manager-thumbs">';
                    for( $i = 0; $i < $imagenum; $i++ ) {
                        $slideThumbs .= '<li><img src="'. $ccslider_options[ $slider_id ]['images'][$i] .'" alt="" /></li>';
                    }
                    $slideThumbs .= '</ul>';
                    ?>
                    <tr>
                        <th><?php echo $slider_id; ?></th>
                        <th><?php echo $ccslider_options[ $slider_id ]['name']; ?></th>
                        <th><?php echo $ccslider_options[ $slider_id ]['description']; ?></th>
                        <th><?php echo $slideThumbs; ?></th>
                        <th><?php echo $ccslider_options[ $slider_id ]['shortcode']; ?></th>
                        <th> <a href="<?php echo $edit_url; ?>">Edit</a>
                            /
                            <a href="<?php echo $delete_url; ?>" onclick="return confirm('Are you sure that you want to delete this slider?');">Delete</a></th>
                        <th><?php echo $ccslider_options[ $slider_id ]['date']; ?></th>
                    </tr>
                <?php
                }
            }
            else { ?>
                <tr>
                    <td align="center" colspan="5"> No entries found </td>
                </tr>
            <?php
            }
            ?>
            
        </tbody>
    </table>
    
</div>



