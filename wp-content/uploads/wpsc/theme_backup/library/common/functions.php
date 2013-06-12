<?php
/**
 * Regular functions to be used throughout Headway.  This file has absolutely no organizational pattern.
 **/


/**
 * Generates the URL for the image resizer.
 * 
 * @param string $url URL to original image.
 * @param int $w Width to resize to.
 * @param int $h Height to resize to.
 * @param int $zc Determines whether or not to zoom/crop the image.
 *
 * @return string The URL to the image.
 **/
function headway_thumbnail($url, $w = false, $h = false, $zc = 1) {
		
	if ( !$w && !$h ) {
		$w = 90;
		$h = 90;
	}

	if ( $w && $h ) {
		return home_url() . '/?headway-trigger=thumbnail&src=' . urlencode($url) . '&q=90&w=' . $w . '&h=' . $h . '&zc=' . $zc;
	} elseif ( $w && !$h ) {
		return home_url() . '/?headway-trigger=thumbnail&src=' . urlencode($url) . '&q=90&w=' . $w . '&zc=' . $zc;
	} elseif ( !$w && $h ) {
		return home_url() . '/?headway-trigger=thumbnail&src=' . urlencode($url) . '&q=90&h=' . $h . '&zc=' . $zc;
	} else {
		return false;
	}
	
}

	
/**
 * Detects if the browser is Internet Explorer.  Will also check if a specific version of MSIE.
 * 
 * @param int $version
 *
 * @return bool
 **/
function headway_is_ie($version_check = false) {
	
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	preg_match('/MSIE\s([\d.]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
	
	if ( count($matches) === 0 || !is_array($matches) )
		return false;

	/* The user agent has a version with a decimal in it so it needs to be changed to an integer so it's 9 rather than 9.0 */
	$version = (int)$matches[1];

	if ( $version_check !== false )
		return $version_check == $version;
		
	return $version;
	
}


/**
 * Parses PHP using eval.
 *
 * @param string $content PHP to be parsed.
 * 
 * @return mixed PHP that has been parsed.
 **/
function headway_parse_php($content) {
	
	/* If Headway PHP parsing is disabled, then return the content now. */
	if ( defined('HEADWAY_DISABLE_PHP_PARSING') && HEADWAY_DISABLE_PHP_PARSING === true )
		return $content;
	
	/* If it's a WordPress Network setup and the current site being viewed isn't the main site, 
	   then don't parse unless HEADWAY_ALLOW_NETWORK_PHP_PARSING is true. */
	if ( !is_main_site() && (!defined('HEADWAY_ALLOW_NETWORK_PHP_PARSING') || HEADWAY_ALLOW_NETWORK_PHP_PARSING === false) )
		return $content;
	
	ob_start();
	eval("?>$content<?php ");
	$parsed = ob_get_contents();
	ob_end_clean();
	return $parsed;
	
}


function headway_convert_bytes($size) {
	
	$unit=array('B','KB','MB','GB','TB','PB');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).''.$unit[$i];
	
}


function wp_enqueue_multiple_scripts($scripts, $in_footer = true) {
	
	if ( !is_array($scripts) || count($scripts) === 0 )
		return false;
	
	foreach ($scripts as $script => $src) {

		if ( is_string($script) && is_string($src) ) {
			wp_enqueue_script($script, $src, false, false, $in_footer);
		} else {
			wp_enqueue_script($src, false, false, false, $in_footer);
		}

	}
	
}


function wp_enqueue_multiple_styles($styles) {
	
	if ( !is_array($styles) || count($styles) === 0 )
		return false;
	
	foreach ($styles as $style => $src) {

		if ( is_string($style) && is_string($src) ) {
			wp_enqueue_style($style, $src);
		} else {
			wp_enqueue_style($src);
		}

	}
	
}


function headway_in_numeric_range($check, $begin, $end, $allow_equals = true) {

	if ( $allow_equals && ($begin <= $check && $check <= $end) )
		return true;

	if ( !$allow_equals && ($begin < $check && $check < $end) )
		return true;

	return false;
	
}


function headway_remove_from_array(array &$array, $value) {
	
	$array = array_diff($array, array($value));
	
	return $array;
	
}


function headway_array_insert(array &$array, array $insert, $position) {
	
	settype($position, 'int');
 
	//if pos is start, just merge them
	if ( $position === 0 ) {
		
	    $array = array_merge($insert, $array);
	
	} else {
		
	    //if pos is end just merge them
	    if( $position >= (count($array)-1) ) {
		
	        $array = array_merge($array, $insert);
	
	    } else {
		
	        //split into head and tail, then merge head+inserted bit+tail
	        $head = array_slice($array, 0, $position);
	        $tail = array_slice($array, $position);
	        $array = array_merge($head, $insert, $tail);
	
	    }
	
	}
	
	return $array;
	
}


function headway_array_key_neighbors($array, $findKey, $valueOnly = true) {
	
	if ( ! array_key_exists($findKey, $array))
		return FALSE;

	$select = $previous = $next = NULL;

	foreach($array as $key => $value) {
		
		$thisValue = $valueOnly ? $value : array($key => $value);
		
		if ($key === $findKey) {
			$select = $thisValue;
			continue;
		}
		
		if ($select !== NULL) {
			$next = $thisValue;
			break;
		}
		
		$previous = $thisValue;

	}

	return array(
		'prev' => $previous,
		'current' => $select,
		'next' => $next
	);

}


/**
 * http://www.php.net/manual/en/function.array-merge-recursive.php#104145
 **/
function headway_array_merge_recursive_simple() {

    if ( func_num_args() < 2 ) {
	
        trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);

        return;

    }

    $arrays = func_get_args();
    $merged = array();

    while ( $arrays ) {
	
        $array = array_shift($arrays);

        if ( !is_array($array) ) {
	
            trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
            return;

        }

        if (!$array)
            continue;

        foreach ($array as $key => $value) {
	
            if (is_string($key)) {
	
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
                else
                    $merged[$key] = $value;

           } else {
	
                $merged[] = $value;

			}

		}

    }

    return $merged;

}


/**
 * http://www.php.net/manual/en/function.get-browser.php#101125
 **/
function headway_get_browser() {

	$u_agent = $_SERVER['HTTP_USER_AGENT']; 
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version = '';

	/* First get the platform */
	if ( preg_match('/linux/i', $u_agent) )
		$platform = 'linux';
		
	elseif ( preg_match('/macintosh|mac os x/i', $u_agent) )
		$platform = 'mac';
		
	elseif ( preg_match('/windows|win32/i', $u_agent) )
		$platform = 'windows';

	/* Next get the name of the useragent yes seperately and for good reason */
	if ( preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent) ) { 
		
		$bname = 'Internet Explorer'; 
		$ub = 'MSIE'; 
	
	} elseif ( preg_match('/Firefox/i', $u_agent) ) { 
		
		$bname = 'Mozilla Firefox'; 
		$ub = 'Firefox'; 
		
	} elseif ( preg_match('/Chrome/i', $u_agent) ) { 
		
		$bname = 'Google Chrome'; 
		$ub = 'Chrome'; 
		
	} elseif ( preg_match('/Safari/i', $u_agent) ) { 
		
		$bname = 'Apple Safari'; 
		$ub = 'Safari'; 
		
	} elseif ( preg_match('/Opera/i', $u_agent) ) { 
		
		$bname = 'Opera'; 
		$ub = 'Opera'; 
		
	} elseif ( preg_match('/Netscape/i', $u_agent) ) {
		 
		$bname = 'Netscape'; 
		$ub = 'Netscape'; 
		
	} 

	/* Get the correct version number */
	$known = array('Version', $ub, 'other');
	$pattern = '#(?P<browser>' . join('|', $known) . ')[/ ]+(?P<version>[0-9.|a-zA-Z.]*)#';
	
	if  ( !preg_match_all($pattern, $u_agent, $matches) ) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	
	if ($i != 1) {
		
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if ( strripos($u_agent, 'Version') < strripos($u_agent, $ub) )
		   $version = $matches['version'][0];
		else
		   $version = $matches['version'][1];
		
	} else {
		$version = $matches['version'][0];
	}

	//Check if we have a number
	if ( $version == null || $version == '' )
		$version = '?';

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'    => $pattern
	);
	
}