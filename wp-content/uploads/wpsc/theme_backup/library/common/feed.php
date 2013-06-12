<?php
class HeadwayFeed {
	
	
	public static function init() {
		
		add_filter('pre_get_posts', array(__CLASS__, 'filter_feed'));
		add_filter('feed_link', array(__CLASS__, 'feed_url'));
		
	}
	
	
	public static function feed_url($feed) {
		
		//Do not change the URL of comment feed URLs.
		if ( strpos($feed, 'comment') !== false )
			return $feed;
		
		$feed_url = HeadwayOption::get('feed-url');
			
		//If the feed URL option doesn't have http[://] at the beginning, then we're a no go on changing the feed URL.
		if ( !$feed_url || strpos($feed_url, 'http') !== 0 )
			return $feed;
			
		return $feed_url;
		
	}
	
	
	public static function filter_feed($query) {
		
		if($query->is_feed){
			
			$excluded_cats = HeadwayOption::get('feed-exclude-cats');
			
			if ( is_array($excluded_cats) && count($excluded_cats) > 0 )
				$query->set('category__not_in', $excluded_cats);
				
		}

		return $query;
		
	}
	
	
}