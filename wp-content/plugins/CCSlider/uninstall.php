<?php
// Uninstall script for CCSlider

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit ();
}
        
// Delete option from options table
delete_option( 'ccslider_options' );
delete_option( 'ccslider_ids' );
delete_option( 'ccslider_default' );

// delete image upload folder alongwith all images inside it
remove_dir( WP_CONTENT_DIR.'/ccslider-upload' );

function remove_dir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    remove_dir($dir.'/'.$object);
                }
                else {
                    unlink($dir.'/'.$object);
                }
            }
        }
     reset($objects);
     rmdir($dir);
    }
}
        
?> 