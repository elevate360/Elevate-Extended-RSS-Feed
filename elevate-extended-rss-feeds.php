<?php
/*
Plugin Name: Elevate Extended RSS Feeds
Plugin URI: https://elevate360.com.au/
Description: Extends the default RSS feed to include additional meta-data such as the featured image URL
Version: 1.0.0
Author: Simon Codrington
Author URI: http://simoncodrington.com.au
Text Domain: elevate-extended-rss-feeds
Domain Path: /languages
*/


class elevate_feeds{
	
	private static $instance = null;
	
	
	//constructor
	public function __construct(){
		add_filter('rss2_ns', array($this, 'add_media_namespace_for_rss_feed'), 10, 1);
		add_filter('rss2_item', array($this, 'add_post_featured_image_to_feed'), 10, 1);
	}
	
	//get or set instance of this class
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}	
		return self::$instance;
	}
	
	//adds media namespace for RSS (so it validates)
	public function add_media_namespace_for_rss_feed(){
		echo 'xmlns:media="http://search.yahoo.com/mrss/" xmlns:georss="http://www.georss.org/georss"';
	}
	
	//adds the featured image as an RSS media element
	//Output based on: http://www.rssboard.org/media-rss#media-content
	public function add_post_featured_image_to_feed(){
		
		$html = '';	
		global $post;
		
		if($post){
			
			$has_post_thumbnail = has_post_thumbnail($post);
			if($has_post_thumbnail){
				$post_thumbnail_id = get_post_thumbnail_id($post->ID);
				$post_thumbnail = get_post($post_thumbnail_id);
				
				//get info about the featured image
				$post_thumbnail_title = $post_thumbnail->post_title;
				$post_thumbnail_description = $post_thumbnail->post_content;
				$post_thumbnail_caption = $post_thumbnail->post_excerpt;
				$post_thumbnail_alt = get_post_meta($post_thumbnail->ID, '_wp_attachment_image_alt', true);
				$post_thumbnail_mime = $post_thumbnail->post_mime_type;
				
				//get different sizes for featured image
				$post_thumbnails = array(
					wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail'),
					wp_get_attachment_image_src($post_thumbnail_id, 'medium'),
					wp_get_attachment_image_src($post_thumbnail_id, 'large'));
				
				//loop through them for output
				if(!empty($post_thumbnails)){
					echo '<media:group>';
					foreach($post_thumbnails as $post_thumbnail){
						
						$post_thumbnail_url = $post_thumbnail[0];
						$post_thumbnail_width = $post_thumbnail[1];
						$post_thumbnail_height = $post_thumbnail[2];
						
						//add media element
						echo  '<media:content url="' . $post_thumbnail_url . '" type="' . $post_thumbnail_mime . '" medium="image" height="' . $post_thumbnail_height . '" width="' . $post_thumbnail_width . '">';
	
						//output title
						if(!empty($post_thumbnail_title)){
							echo '<media:title type="plain">' . $post_thumbnail_title . '</media:title>';
						}
						//output description
						if(!empty($post_thumbnail_description)){
							echo '<media:description type="plain">' . $post_thumbnail_description . '</media:description>';
						}
						
						echo '</media:content>';
					}
					echo '</media:group>';
				}
			}
		}
	}
	
}
$elevate_feeds = elevate_feeds::getInstance();


?>