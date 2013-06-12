<?php
class HeadwayLayout {
	
	
	/**
	 * Returns current layout
	 * 
	 * @return mixed
	 **/
	public static function get_current() {
		
		//If the user is viewing the site through the iframe and the mode is set to Layout, then display that exact layout.
		if ( Headway::get('ve-iframe-layout') && HeadwayRoute::is_visual_editor_iframe() ) 
			return Headway::get('ve-iframe-layout');
		
		return end(self::get_current_hierarchy());
		
	}
	
	
	/**
	 * Traverses up the hierarchy tree to figure out which layout is being used.
	 * 
	 * @return mixed
	 **/
	public static function get_current_in_use() {
		
		//If the user is viewing the site through the iframe and the mode is set to Layout, then display that exact layout.
		if ( Headway::get('ve-iframe-layout') && HeadwayRoute::is_visual_editor_iframe() ) 
			return Headway::get('ve-iframe-layout');
		
		//Get hierarchy
		$hierarchy = array_reverse(self::get_current_hierarchy());
				
		//Loop through entire hierarchy to find which one is customized or has a template
		foreach ( $hierarchy as $layout ) {
			
			$status = self::get_status($layout);
			
			//If the layout isn't customized or using a template, skip to next, otherwise we return the current layout in the next line.
			if ( $status['customized'] === false && $status['template'] === false )
				continue;
				
			//If the layout has a template assigned to it, use the template.  Templates will take precedence over customized status.
			if ( $status['template'] )
				return 'template-' . $status['template'];	
				
			//If it's a customized layout, then use the layout itself after making sure there are blocks on the layout
			if ( $status['customized'] && count(HeadwayBlocksData::get_blocks_by_layout($layout)) > 0 )
				return $layout;
			
		}
		
		//If there's still not a customized layout, loop through the top-level layouts and find the first one that's customized.
		$top_level_layouts = array(
			'index',
			'single',
			'archive',
			'four04'
		);
				
		if ( get_option('show_on_front') == 'page' )
			$top_level_layouts[] = 'front_page';

		foreach ( $top_level_layouts as $top_level_layout ) {
						
			$status = self::get_status($top_level_layout);
			
			if ( $status['customized'] === false && $status['template'] === false )
				continue;
			
			//If the layout has a template assigned to it, use the template.  Templates will take precedence over customized status.
			if ( $status['template'] )
				return 'template-' . $status['template'];	
				
			//If it's a customized layout and the layout has blocks, then use the layout itself
			if ( $status['customized'] && count(HeadwayBlocksData::get_blocks_by_layout($top_level_layout)) > 0 )
				return $top_level_layout;
			
		}

		//If there STILL isn't a customized layout, just return the top level of the current layout.
		return end($hierarchy);
		
	}
	
	
	/**
	 * Returns name of the current layout being viewed.
	 * 
	 * @return string
	 **/
	public static function get_current_name() {
														
		return self::get_name(self::get_current());
		
	}
	
	
	/**
	 * Returns the current hierarchy. 
	 * 
	 * @return array
	 **/
	public static function get_current_hierarchy() {
				
		$current_layout = array();
		$queried_object = get_queried_object();
				
		//Now the fun begins
		if ( is_home() || ( get_option('show_on_front') == 'posts' && is_front_page() ) ) {
			
			$current_layout[] = 'index';
			
		} elseif ( is_front_page() && !is_home() ) {
									
			$current_layout[] = 'front_page';
						
		} elseif ( is_singular() ) {
			
			$post_type = get_post_type_object($queried_object->post_type);
			
			$current_layout[] = 'single';
			$current_layout[] = 'single-' . $post_type->name;
			
			//If it has no parents, just stop.
			if ( $queried_object->post_parent === 0 ) {
				
				$current_layout[] = 'single-' . $post_type->name . '-' . $queried_object->ID;
			
			//Otherwise, figure out parents and grandparents	
			} else {
				
				//Set up variables 
				$posts = array(
					$queried_object->ID
				);
				
				$post = $queried_object;
				$parents_str = '';
				
				//Get to business
				while ($post->post_parent != 0) {
					
					$posts[] = $post->post_parent;
					
					$post = get_post($post->post_parent);
					
				}
				
				foreach (array_reverse($posts) as $post) {
					
					$current_layout[] = 'single-' . $post_type->name . '-' . $parents_str . $post;
					
					$parents_str .= $post . '-'; 
					
				}
								
			}			
							
		} elseif ( is_archive() || is_search() ) {
			
			$current_layout[] = 'archive';
			
			if ( is_date() ) {
				
				$current_layout[] = 'archive-date';
				
			} elseif ( is_author() ) {
								
				$current_layout[] = 'archive-author';
				$current_layout[] = 'archive-author-' . $queried_object->ID;
				
			} elseif ( is_category() ) {
				
				$current_layout[] = 'archive-category';
				$current_layout[] = 'archive-category-' . $queried_object->term_id;
				
			} elseif ( is_search() ) {
				
				$current_layout[] = 'archive-search';
				
			} elseif ( is_tag() ) {
				
				$current_layout[] = 'archive-post_tag';
				$current_layout[] = 'archive-post_tag-' . $queried_object->term_id;
				
			} elseif ( is_tax() ) {
				
				$current_layout[] = 'archive-taxonomy';
				$current_layout[] = 'archive-taxonomy-' . $queried_object->taxonomy;
				$current_layout[] = 'archive-taxonomy-' . $queried_object->taxonomy . '-' . $queried_object->term_id;
				
			} elseif ( is_post_type_archive() ) {
				
				$current_layout[] = 'archive-post_type';
				$current_layout[] = 'archive-post_type-' . $queried_object->name;
				
			}
			
		} elseif ( is_404() ) {

			$current_layout[] = 'four04';

		}
		
		
		//I think we're finally done.
		return $current_layout;

	}
		
		
	/**
	 * Returns friendly name of the layout specified.
	 * 
	 * @return string
	 **/
	public static function get_name($layout) {
		
		if ( !$layout )
			return null;
		
		$layout_parts = explode('-', $layout);
		$id = end($layout_parts);
		
		switch($layout_parts[0]) {

			case 'front_page':
				return 'Front Page';
			break;
			
			case 'index':
				return 'Blog Index';
			break;
			
			case 'single':
				if ( $id == 'single' )
					return 'Single';
										
				if ( is_numeric($id) )
					return get_the_title($id) ? get_the_title($id) : '(No Title)';
				
				//If everything else hasn't triggered, then it's a post type	
				$post_type = get_post_type_object($id);
				
				return $post_type->labels->singular_name;
			break;
			
			case 'archive':
				if ( $id == 'archive' )
					return 'Archive';
						
				switch($layout_parts[1]) {
					
					case 'category':
						if ( $id == 'category' )
							return 'Category';
														
						$term = get_term($id, 'category');
							
						return $term->name ? $term->name : '(No Title)';
					break;
					
					case 'search':
						return 'Search';
					break;
					
					case 'date':
						return 'Date';
					break;
					
					case 'author':
						if ( $id == 'author' )
							return 'Author';
						
						$user_data = get_userdata($id);
					
						return $user_data->display_name;
					break;
					
					case 'post_tag':
						if ( $id == 'post_tag' ) 
							return 'Post Tag';
														
						$term = get_term($id, 'post_tag');
						
						return $term->name ? $term->name : '(No Title)';
					break;
					
					case 'taxonomy':
						if ( $id == 'taxonomy' ) 
							return 'Taxonomy';
							
						if ( is_numeric($id) ) {
														
							$term = get_term($id, $layout_parts[2]);
							
							return $term->name ? $term->name : '(No Title)';
							
						} elseif ( is_string($id) ) {
							
							$taxonomy = get_taxonomy($id);
							
							return $taxonomy->labels->singular_name ? $taxonomy->labels->singular_name : '(No Title)';
							
						}
					break;
					
					case 'post_type':
						if ( $id == 'post_type' )
							return 'Post Type';
														
						$post_type = get_post_type_object($id);
							
						return $post_type->labels->singular_name;
					break;
					
					case 'post_format':
						if ( $id == 'post_format' )
							return 'Post Format';
														
						$term = get_term($id, 'post_format');
							
						return $term->name;
					break;
					
				}
			
			break;
			
			case 'four04':
				return '404 Layout';
			break;
			
			case 'template':
				$templates = self::get_templates();
				
				if ( isset($templates[$layout_parts[1]]) )
					return $templates[$layout_parts[1]];
				else
					return null;
			break;
			
		}
		
		return false;
				
	}
		
	
	/**
	 * Determines whether or not the layout is capable of using WordPress' meta functions.  This is merely
	 * a simple check to see if the layout is a post or page.
	 * 
	 * @return bool
	 **/
	public static function is_meta_capable($layout = false) {
		
		if ( !$layout )
			$layout = self::get_current();
			
		//If it's a singular item such as a post, page, or any custom post type, then it can store meta.  To check, we'll just see if there's a numerical ID assigned.
		if ( is_numeric($layout) )
			return true;
		else
			return false;

	}
	
	
	/**
	 * Gets the status of the layout.  This will tell if it's customized, using a template, or none of the previous mentioned.
	 * 
	 * @return string
	 **/
	public static function get_status($layout) {
												
		$customized = ( HeadwayLayoutOption::get($layout, 'customized') === true ) ? true : false;	
		$template = ( HeadwayLayoutOption::get($layout, 'template') !== null ) ? HeadwayLayoutOption::get($layout, 'template') : false;
					
		$status = array(
			'customized' => $customized,
			'template' => $template
		);		

		return $status;
		
	}
	

	/**
	 * Returns all pages and their hierarchy for listing.
	 * 
	 * @return array
	 **/
	public static function get_pages() {
		
		$layouts = array();
		
		if ( get_option('show_on_front') == 'page' )
			$layouts['front_page'] = array();
			
		$layouts['index'] = array();
		$layouts['single'] = array();
		$layouts['archive'] = array();
		$layouts['four04'] = array();
		
		//Queries
		$post_types = get_post_types(array('public' => true), 'objects');
		
		//Single			
			foreach($post_types as $post_type) {
				
				$layouts['single']['single-' . $post_type->name] = self::get_pages_posts($post_type->name);
				
			}
              
		//Archives
			$layouts['archive'] = array(
				'archive-category' => self::get_pages_terms('category'),
				'archive-search' => array(),
				'archive-date' => array(),
				'archive-author' => array(),
				'archive-post_tag' => self::get_pages_terms('post_tag'),
				'archive-taxonomy' => array(),
				'archive-post_type' => array(),
				'archive-post_format' => self::get_pages_terms('post_format')
			);
			
			
			//Authors
				$author_query = get_users(array('who' => 'author'));
				$authors = array();
				
				foreach($author_query as $author) {
			
					$layouts['archive']['archive-author']['archive-author-' . $author->ID] = array();
			
				}
			
			
			//Taxonomies and Terms
				$taxonomies_query = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
				$exclude = array('link_category');	
				$taxonomies = array();

				foreach($taxonomies_query as $slug => $taxonomy) {

					$layouts['archive']['archive-taxonomy']['archive-taxonomy-' . $slug] = self::get_pages_terms($slug, true);

				}
		
						
			//Post Types
				$excluded_post_types = array('post', 'page', 'attachment');
		
				foreach($post_types as $post_type) {
			
					//If excluded, skip it
					if ( in_array($post_type->name, $excluded_post_types) )
						continue;
		
					$layouts['archive']['archive-post_type']['archive-post_type' . '-' . $post_type->name] = array();
			
				}
			
					
		return $layouts;
                
	}	

		
		/** 
		 * Recursive function to find terms and their children.
		 * 
		 * @see HeadwayLayout::get_pages()
		 * 
		 * @return array
		 **/
		public static function get_pages_terms($taxonomy, $add_taxonomy_to_id = false, $term_parent = 0, $parents = array()) {
							
			if ( HeadwayOption::get('layout-selector-safe-mode') && $taxonomy != 'category' )
				return null;
				
			$query = get_terms($taxonomy, array('parent' => $term_parent));
			$terms = array();
		
			foreach($query as $term) {
			
				//Handle hierarchy stuff
				if ( $term_parent !== 0 && end($parents) !== $term_parent )
					$parents[] = $term_parent;
													
				$parents_str = (count($parents) !== 0) ? implode('-', $parents) . '-' : null;
				//End hierarchial stuff
			
				//Add taxonomy prefix if set
				$taxonomy_id = $add_taxonomy_to_id ? 'taxonomy-' . $taxonomy : $taxonomy;
			
				$terms['archive-' . $taxonomy_id . '-' . $parents_str . $term->term_id] = self::get_pages_terms($taxonomy, $add_taxonomy_to_id, $term->term_id, $parents);
			
			}
		
			return $terms;
		
		}
	
	
		/** 
		 * Recursive function to find posts/pages and their children.
		 * 
		 * @see HeadwayLayout::get_pages()
		 * 
		 * @return array
		 **/
		public static function get_pages_posts($post_type = 'post', $post_parent = 0, $parents = array()) {
			
			if ( HeadwayOption::get('layout-selector-safe-mode') && $post_type != 'page' )
				return null;
		
			$query = get_posts(array(
				'post_type' => $post_type, 
				'post_parent' => $post_parent,
				'numberposts' => 99999
			));
			
			$posts = array();
				
			foreach($query as $post) {
				
				//Hierarchial stuff
				if ( $post_parent !== 0 && end($parents) !== $post_parent )
					$parents[] = $post_parent;
													
				$parents_str = (count($parents) !== 0) ? implode('-', $parents) . '-' : null;	
				//End hierarchial stuff					
									
				$posts['single-' . $post_type . '-' . $parents_str . $post->ID] = self::get_pages_posts($post_type, $post->ID, $parents);
			
			}		
		
			return $posts;
		
		}
	
	
	/** 
	 * Simple function to query for all Headway layout templates from the database.
	 * 
	 * @return array
	 **/
	public static function get_templates() {
		
		$templates = HeadwayOption::get('list', 'templates', array());
		
		return $templates;
		
	}


}