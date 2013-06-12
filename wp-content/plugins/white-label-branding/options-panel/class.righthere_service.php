<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class righthere_service {
	function righthere_service(){
	
	}
	
	function rh_service($url){
		$request = wp_remote_post( $url , array('timeout'=>10) );
		if ( is_wp_error($request) ){
			$this->last_error_str = $request->get_error_message();
			return false;
		}else{
			$r = json_decode($request['body']);
			if(is_object($r)&&property_exists($r,'R')){
				return $r;
			}else{
				$this->last_error_str = $request['body'];
				return false;
			}	
		}
		return false;
	}
}
?>