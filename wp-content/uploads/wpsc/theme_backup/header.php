<?php 
/* Prevent direct access to this file */
if ( !defined('WP_CONTENT_DIR') )
	die('Please do not access this file directly.');

HeadwayDisplay::html_open();

/* WordPress and a lot of plugins require this file, so I guess we have to use it :-(. */
wp_head();

HeadwayDisplay::body_open();