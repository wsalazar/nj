<?php
class HeadwayCompiler {
	
	
	/**
	 * @param string
	 * @param string
	 * @param mixed
	 * @param bool
	 * 
	 * @uses HeadwayCompiler::enqueue_file()
	 * 
	 * @return bool
	 **/
	public static function register_file($args) {
		
		$defaults = array(
			'name' => null,
			'format' => null,
			'fragments' => array(),
			'dependencies' => array(),
			'enqueue' => true
		);
		
		extract(array_merge($defaults, $args));
		
		$cache = HeadwayOption::get('cache', false, array());
						
		//If file is not registered or fragments are not the same, add it to the DB.
		if ( 
			!isset($cache[$name]) 
			|| $cache[$name]['fragments'] !== array_map(array('Headway', 'change_to_unix_path'), $fragments)
			|| $cache[$name]['format'] !== $format 
			|| $cache[$name]['dependencies'] !== array_map(array('Headway', 'change_to_unix_path'), $dependencies)
		){
			
			$cache[$name] = array(
				'fragments' => array_map(array('Headway', 'change_to_unix_path'), $fragments),
				'dependencies' => array_map(array('Headway', 'change_to_unix_path'), $dependencies),
				'format' => $format,
				'hash' => null,
				'cached' => false
			);
				
			//Update cache option
			if ( !HeadwayOption::set('cache', $cache) )
				return false;

		}

		//Enqueue script
		if ( $enqueue )
			return self::enqueue_file($name);

		return true;

	}
	
	
	/**
	 * @param string
	 * 
	 * @return string
	 **/
	public static function enqueue_file($file) {
				
		$cache = HeadwayOption::get('cache');
				
		if ( $cache[$file]['format'] === 'js' )
			return wp_enqueue_script('headway-' . $file, self::get_url($file), false, false, false, true);
		elseif ( $cache[$file]['format'] === 'css' )
			return wp_enqueue_style('headway-' . $file, self::get_url($file));
			
		return false;	
			
	}
	
	
	/**
	 * @param string
	 * 
	 * @return string
	 **/
	public static function get_url($file) {
				
		$cache = HeadwayOption::get('cache');
										
		//If the file isn't in the DB at all								
		if ( !isset($cache[$file]) )
			return false;
													
		//If cache exists
		if ( 
			$cache[$file]['cached'] === true 
			&& file_exists(HEADWAY_CACHE . '/' . $cache[$file]['hash'] . '.' . $cache[$file]['format']) 
			&& self::can_cache() 
		) {
									
			return HEADWAY_CACHE_URL . '/' . $cache[$file]['hash'] . '.' . $cache[$file]['format'];
		
		//Cache doesn't exist	
		} else {
			
			//If file doesn't exist, but we can still cache, let's cache the damn thing.
			if ( self::can_cache() ) {
												
				self::cache_file($file);				
								
				return self::get_url($file);
			
			//No caching available, now we have to use fallback method.
			} else {
				
				$rand = '&rand=' . rand();
				
				$visual_editor_open = HeadwayRoute::is_visual_editor_iframe() ? '&visual-editor-open=true' : null;
				
				return home_url() . '/?headway-trigger=compiler&file=' . $file . $visual_editor_open . '&layout-in-use=' . HeadwayLayout::get_current_in_use(). $rand;
				
			}
						
		}
		
	}
	
	
	/**
	 * @param string
	 * 
	 * @return bool
	 **/
	public static function cache_file($file) {
				
		$cache = HeadwayOption::get('cache', false, array());
		
		//Get the current layout here directly and set is as GET since the output trigger can use POST, but this cannot.
		$_GET['layout-in-use'] = HeadwayLayout::get_current_in_use(); 
		
		$content = self::combine_fragments($cache[$file]);
		
		//If existing cache file exists, delete it.		
		self::delete_cache_file($cache[$file]['hash'], $cache[$file]['format']);
		
		//MD5 the contents that way we can check for differences down the road
		$cache[$file]['hash'] = md5($content);
		
		//Build file
		$file_handle = @fopen(HEADWAY_CACHE . '/' . $cache[$file]['hash'] . '.' . $cache[$file]['format'], 'w');
		
		if ( !@fwrite($file_handle, $content) )
			return false;
	
		@chmod(HEADWAY_CACHE . '/' . $cache[$file]['hash'] . '.' . $cache[$file]['format'], 0755);
			
		@fclose($file_handle);

		//All is done
		$cache[$file]['cached'] = true;
		
		HeadwayOption::set('cache', $cache);
				
		return true;		
				
	}
	

	/**
	 * @return void
	 **/
	public static function output_trigger() {
		
		$file = Headway::get('file');
		
		//No GET parameter set		
		if ( !$file )
			return false;
		
		$cache = HeadwayOption::get('cache');
		
		//File does not exist
		if ( !isset($cache[$file]))
			return;
			
		$format = $cache[$file]['format'];
		$expires = 60 * 60 * 24 * 30;
		
		Headway::start_gzip();
		
		header("Pragma: public");
		header("Cache-Control: maxage=".$expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		
		if ( $format == 'css' )
			header("Content-type: text/css");
		elseif ( $format == 'js' )
			header("content-type: application/x-javascript");
										
		echo self::combine_fragments($cache[$file]);
		
 	}


	/**
	 * @param array
	 * @param string
	 **/
	public static function combine_fragments($file) {
				
		extract($file);		

		$num_fragments = (int)count($fragments);

		$data = '';
		
		//Load dependencies if there are dependents
		if ( is_array($dependencies) && count($dependencies) > 0 ) {
			
			foreach ( $dependencies as $dependent ) {
				
				if ( !is_file($dependent) )
					continue;
					
				include_once $dependent;
				
			}
			
		}

		//Go through and merge the fragments
		foreach ( $fragments as $fragment ) {
					
			//Determine if it's a function or file
			if ( !is_array($fragment) && strpos($fragment, '.') !== false && strpos($fragment, '()') === false && file_exists($fragment) ) {
				
				if ( filesize($fragment) === 0 ) 
					continue;

				$temp_handler = fopen($fragment, 'r');
				$data .= fread($temp_handler, filesize($fragment));
				fclose($temp_handler);
				
			//It's a function	
			} else {
				
				//Remove unneeded paratheses if is a string
				if ( is_string($fragment) )
					$fragment = str_replace('()', '', $fragment);
				
				//Check if method or function
				if ( !is_callable($fragment) ) 
					continue;
					
				$data .= call_user_func($fragment);
				
			}	
					
			if ( $format == 'js' && count($fragments) > 1 )
				$data .= "\n\n;";
			else
				$data .= "\n\n";
			
		}
		
		return self::format_content($data, $file);
		
	}
	
	
	/**
	 * @param string
	 * @param string
	 **/
	public static function format_content($content, $file) {
		
		extract($file);
		
		//Remove whitespace if CSS
		if ( $format == 'css' ) {
			
			if ( isset($less) && $less === true ) {
				//We'll make an exception and use require_once here
				require_once 'lessc.inc.php';
			
				$less = new lessc();
			
				$content = $less->parse($content);
			}
			
			//Strip whitespace if set to do so
			if ( !defined('HEADWAY_COMPILER_STRIP_WHITESPACE') || HEADWAY_COMPILER_STRIP_WHITESPACE === true ) {
				
				$replace = array(
					"#/\*.*?\*/#s" => '',  // Strip comments.
					"#\s\s+#"      => ' ', // Strip excess whitespace.
				);
			
				$search = array_keys($replace);
				$content = preg_replace($search, $replace, $content);

				$replace = array(
					": "  => ":",
					"; "  => ";",
					" {"  => "{",
					" }"  => "}",
					", "  => ",",
					"{ "  => "{",
					";}"  => "}", // Strip optional semicolons.
					",\n" => ",", // Don't wrap multiple selectors.
					"\n}" => "}", // Don't wrap closing braces.
					"} "  => "}\n", // Put each rule on it's own line.
					"\n" => "" //Take out all line breaks
				);

				$search = array_keys($replace);
				$content = trim(str_replace($search, $replace, $content));
				
			}
			
		}
	
		//Time to replace variables
		$search = array(
			'%HEADWAY_URL%',
			'%HEADWAY_LIBRARY_URL%',
			'%VISUALEDITOR%',
			'%SITE_URL%',
			'%HOME_URL%'
		);
		
		$replace = array(
			HEADWAY_URL,
			HEADWAY_URL . '/library',
			HEADWAY_URL . '/library/visual-editor',
			site_url(),
			home_url()
		);
		
		return str_replace($search, $replace, $content);
		
	}

	
	/**
	 * @return bool
	 **/
	public static function can_cache(){
		
		//VE iframe, do not cache
		if ( HeadwayRoute::is_visual_editor_iframe() )
			return false;
			
		//If cache is disabled from a constant, then return false
		if ( defined('HEADWAY_DISABLE_CACHE') && HEADWAY_DISABLE_CACHE === true )
			return false;
			
		//Force cache set, try it no matter what
		if ( defined('HEADWAY_FORCE_CACHE') && HEADWAY_FORCE_CACHE === true ) 
			return true;
		
		//WP_DEBUG is true, don't allow caching
		if ( defined('WP_DEBUG') && WP_DEBUG === true ) 
			return false;
			
		//Caching is disabled... don't cache.
		if ( HeadwayOption::get('disable-caching') ) 
			return false;

		//Cache folder doesn't exist or isn't writable, don't cache
		if ( !is_dir(HEADWAY_CACHE) || !is_writable(HEADWAY_CACHE) ) 
			return false;

		return true;
		
	}
	
	
	/**
	 * @return bool
	 **/
	public static function flush_cache() {
		
		//Flush Headway cache if it is active.
		if ( self::can_cache() ) {
			
			//Delete the Headway cache option
			HeadwayOption::delete('cache');
			
			//Set do not delete list
			$no_delete = array(
				'..',
				'.'
			);
				
			if ( $handle = opendir(HEADWAY_CACHE) ) {
			
			    while (false !== ($file = readdir($handle)) ) {
		       		
					if ( in_array($file, $no_delete) )
						continue;
					
					@unlink(HEADWAY_CACHE . '/' . $file);
		
			    }
		
			    closedir($handle);
		
			}
			
		}
		
		//Flush plugin caches
		self::flush_plugin_caches();
		
		return true;
		
	}
	
	
	/**
	 * @param string
	 * @param string
	 * 
	 * @return bool
	 **/
	public static function delete_cache_file($hash, $format) {
		
		if ( !file_exists(HEADWAY_CACHE . '/' . $hash . ' . ' . $format) )
			return false;
		
		return @unlink(HEADWAY_CACHE . '/' . $hash . ' . ' . $format);
		
	}
	
	
	/**
	 * Check if W3 Total Cache or if WP Super Cache are running.
	 *
	 * @return bool
	 **/
	public static function is_plugin_caching() {
		
		if ( class_exists('W3_Plugin_TotalCache') )
			return 'W3 Total Cache';
		
		elseif ( function_exists('prune_super_cache'))
			return 'WP Super Cache';
			
		else
			return false;
		
	}
	
	
	/**
	 * Flush Super Cache and W3 Total Cache
	 * 
	 * @return void
	 **/
	public static function flush_plugin_caches(){
		
		if ( function_exists('prune_super_cache') ) {
			
			global $cache_path;
			prune_super_cache($cache_path . 'supercache/', true );
			prune_super_cache($cache_path, true );
			
		}

		if ( class_exists('W3_Plugin_TotalCache') ) {
			
			if ( function_exists('w3_instance') )
				$w3_plugin_totalcache =& w3_instance('W3_Plugin_TotalCache');
			elseif ( is_callable(array('W3_Plugin_TotalCache', 'instance')) )
				$w3_plugin_totalcache =& W3_Plugin_TotalCache::instance();

			if ( method_exists($w3_plugin_totalcache, 'flush') )
				$w3_plugin_totalcache->flush();
			elseif ( method_exists($w3_plugin_totalcache, 'flush_all') )
				$w3_plugin_totalcache->flush_all();
		
		}
		
	}
	
	
}