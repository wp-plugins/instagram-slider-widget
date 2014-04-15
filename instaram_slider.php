<?php
/*
Plugin Name: Instagram Slider Widget
Plugin URI: http://jrwebstudio.com/instagram-slider/
Version: 1.0.3
Description: Instagram Slider Widget is a responsive slider widget that shows 20 latest images from a public instagram user.
Author: jetonr
Author URI: http://jrwebstudio.com/
License: GPLv2 or later
*/

/**
 * After the plugins have loaded initalise a single instance of JR_InstagramSlider
 */
add_action( 'plugins_loaded', array( 'JR_InstagramSlider', 'get_instance' ) );

/**
 * JR_InstagramSlider Class
 */
class JR_InstagramSlider extends WP_Widget {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.3';

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * Initialize the plugin by registering widget and loading public scripts
	 *
	 */	
	public function __construct() {

		// Register Widget On Widgets Init
		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		// Enqueue Plugin Styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue' ) );
		

		$widget_options = array(
			'classname'   => 'jr-insta-slider',
			'description' => __( 'A widget that displays a slider with instagram images ', 'jrinstaslider' )
		);		
		
		parent::__construct( 'jr_insta_slider', __('Instagram Slider', 'jrinstaslider'), $widget_options );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register widget on windgets init
	 *
	 * @return void
	 */
	public function register_widget() {
		register_widget( __CLASS__ );
	}

	/**
	 * Enqueue public-facing Scripts and style sheet.
	 *
	 * @return void
	 */
	public function public_enqueue() {
		
		// Enqueue Styles
		wp_enqueue_style( 
			'instag-slider', 
			plugins_url( 'assets/css/instag-slider.css', __FILE__ ), 
			array(), 
			self::VERSION 
		);
		
		// Enqueue Scripts
		wp_enqueue_script(
			'jquery-flexi-slider',
			plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ),
			array( 'jquery' ),
			'2.2',
			true
		);
	}
	
	/**
	 * The Public view of the Widget  
	 *
	 * @return mixed
	 */	
	public function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title        = apply_filters('widget_title', $instance['title'] );
		$username     = $instance['username'];
		$images_link  = $instance['images_link'];
		$randomise 	  = isset( $instance['randomise'] ) ? 'on' : 'off';
		$images_nr    = $instance['images_number'];
		$refresh_hour = $instance['refresh_hour'];
		$template	  = $instance['template'];

		echo $before_widget;

		// Display the widget title 
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		// Get instagram data 
		$insta_data = $this->instagram_data( $username, $refresh_hour, $images_nr );

		// Randomise Images
		if ( "on" == $randomise )  {
			shuffle( $insta_data );
		}
		
		//include the template based on user choice
		$this->template( $template, $insta_data, $images_link );
		
		echo $after_widget;
	}

	/**
	 * Update the widget settings 
	 *
 	 * @param    array    $new_instance    New instance values
 	 * @param    array    $old_instance    Old instance values	 
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['username']      = $new_instance['username'];
		$instance['template']      = $new_instance['template'];
		$instance['images_link']   = $new_instance['images_link'];		
		$instance['randomise']     = $new_instance['randomise'];
		$instance['images_number'] = $new_instance['images_number'];
		$instance['refresh_hour']  = $new_instance['refresh_hour'];

		return $instance;
	}

	/**
	 * Widget Settings Form
	 *
	 * @return mixed
	 */	
	public function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Instagram Slider', 'jrinstaslider'), 'username' => __('', 'jrinstaslider'), 'template' => 'slider', 'images_link' => 'image_url', 'images_number' => 5, 'refresh_hour' => 5 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'jrinstaslider'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e('Instagram Username:', 'jrinstaslider'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>" />
		</p>
        <p>
          <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Images Layout', 'jrinstaslider' ); ?>
          <select class="widefat" name="<?php echo $this->get_field_name( 'template' ); ?>">
          <option value="slider" <?php echo ($instance['template'] == 'slider') ? ' selected="selected"' : ''; ?>><?php _e('Slider', 'jrinstaslider'); ?></option>
          <option value="thumbs" <?php echo ($instance['template'] == 'thumbs') ? ' selected="selected"' : ''; ?>><?php _e('Thumbnails', 'jrinstaslider'); ?></option>
          </select>  
          </label>
        </p>
       <p>
            <?php _e('Link Images To:', 'jrinstaslider'); ?><br>
            <label><input type="radio" id="<?php echo $this->get_field_id( 'images_link' ); ?>" name="<?php echo $this->get_field_name( 'images_link' ); ?>" value="image_url" <?php checked( 'image_url', $instance['images_link'] ); ?> /> <?php _e('Instagram Image URL', 'jrinstaslider'); ?></label><br />         
            <label><input type="radio" id="<?php echo $this->get_field_id( 'images_link' ); ?>" name="<?php echo $this->get_field_name( 'images_link' ); ?>" value="user_url" <?php checked( 'user_url', $instance['images_link'] ); ?> /> <?php _e('Instagram Profile URL', 'jrinstaslider'); ?></label><br />
        </p>
         <p>
            <label for="<?php echo $this->get_field_id( 'randomise' ); ?>"><?php _e( 'Randomise Images:', 'jrinstaslider' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'randomise' ); ?>" name="<?php echo $this->get_field_name( 'randomise' ); ?>" type="checkbox" value="1" <?php checked( '1', $instance['randomise'] ); ?> />
        </p>       
		<p>
			<label  for="<?php echo $this->get_field_id( 'images_number' ); ?>"><?php _e('Number of Images to Show:', 'jrinstaslider'); ?>
			<input  class="small-text" id="<?php echo $this->get_field_id( 'images_number' ); ?>" name="<?php echo $this->get_field_name( 'images_number' ); ?>" value="<?php echo $instance['images_number']; ?>" />
			<small><?php _e('( max 20 )', 'jrinstaslider'); ?></small>
            </label>
		</p>
		<p>
			<label  for="<?php echo $this->get_field_id( 'refresh_hour' ); ?>"><?php _e('Check for new images every:', 'jrinstaslider'); ?>
			<input  class="small-text" id="<?php echo $this->get_field_id( 'refresh_hour' ); ?>" name="<?php echo $this->get_field_name( 'refresh_hour' ); ?>" value="<?php echo $instance['refresh_hour']; ?>" />
			<small><?php _e('hours', 'jrinstaslider'); ?></small>
            </label>
		</p>
        
		<?php
	}

	/**
	 * Stores the fetched data from instagram in WordPress DB using transients
	 *	 
 	 * @param    string    $username    	Instagram Username to fetch images from
 	 * @param    string    $cache_hours     Cache hours for transient
 	 * @param    string    $nr_images    	Nr of images to fetch from instagram		  	 
	 *
	 * @return array of localy saved instagram data
	 */
	private function instagram_data( $username, $cache_hours, $nr_images ) {
		
		$opt_name    = 'jr_insta_'.md5( $username );
		$instaData 	 = get_transient( $opt_name );
		$user_opt    = get_option( $opt_name );
	
		if ( 
			false === $instaData 
			|| $user_opt['username']    != $username 
			|| $user_opt['cache_hours'] != $cache_hours 
			|| $user_opt['nr_images']   != $nr_images 
		   ) 
		{
			$instaData    = array();
			$insta_url    = 'http://instagram.com/';
			$user_profile = $insta_url.$username;
			$json     	  = wp_remote_get( $user_profile, array( 'sslverify' => false, 'timeout'=> 60 ) );
			$user_options = compact('username', 'cache_hours', 'nr_images');
			update_option($opt_name, $user_options);
			if ( $json['response']['code'] == 200 ) {
	
				$json 	  = $json['body'];
				$json     = strstr( $json, '{"entry_data"' );
				
				// Compatibility for version of php where strstr() doesnt accept third parameter
				if ( version_compare( phpversion(), '5.3.10', '<' ) ) {
					$json = substr( $json, 0, strpos($json, '</script>' ) );
				} else {
					$json = strstr( $json, '</script>', true );
				}
				
				$json     = rtrim( $json, ';' );
				( $results = json_decode( $json, true ) ) && json_last_error() == JSON_ERROR_NONE;
					
				if ( ( $results ) && is_array( $results ) ) {
					foreach( $results['entry_data']['UserProfile'][0]['userMedia'] as $current => $result ) {
			
						if( $current >= $nr_images ) break;
						$caption      = $result['caption'];
						$image        = $result['images']['standard_resolution'];
						$id           = $result['id'];
						$image        = $image['url'];
						$link         = $result['link'];
						$created_time = $caption['created_time'];
						$text         = $this->utf8_4byte_to_3byte($caption['text']);
										
						$filename_data= explode('.',$image);
		
						if ( is_array( $filename_data ) ) {
	
							$fileformat   = end( $filename_data );
	
							if ( $fileformat !== false ){
								
								$image = $this->download_insta_image( $image, md5( $id ) . '.' . $fileformat );
								array_push( $instaData, array(
									'id'          => $id,
									'user_name'	  => $username,
									'user_url'	  => $user_profile,
									'created_time'=> $created_time,
									'text'        => $text,
									'image'       => $image,
									'link'        => $link
								));
								
							} // end -> if $fileformat !== false
						
						} // end -> is_array( $filename_data )
						
					} // end -> foreach
				
				} // end -> ( $results ) && is_array( $results ) )
			
			} // end -> $json['response']['code'] === 200 )
	
			if ( $instaData ) {
				set_transient( $opt_name, $instaData, $cache_hours * 60 * 60 );
			} // end -> true $instaData
		
		} // end -> false === $instaData
		
		return $instaData;
	}


	/**
	 * Save Instagram images to upload folder and ads to media.
	 * If the upload fails it returns the remote image url. 
 	 *
	 * @param    string    $url    		Url of image to download
 	 * @param    string    $file    	File path for image	
	 *
	 * @return   string    Url to image
	 */
	private function download_insta_image( $url , $file ){

		$upload_dir = wp_upload_dir();
		$local_file =  $upload_dir['path'] . '/' . $file; 
		
		if ( file_exists( $local_file ) ) {
			return $upload_dir['baseurl'] . $upload_dir['subdir'] . '/' . $file;
		}		
		
		$get 	   = wp_remote_get( $url, array( 'sslverify' => false ) );
		$body      = wp_remote_retrieve_body( $get );
		$upload	   = wp_upload_bits( $file, '', $body );
			
		if ( $upload ) {
			return $upload['url'];
		}
		
		return $url;
	}
	
	/**
	 * Sanitize 4-byte UTF8 chars; no full utf8mb4 support in drupal7+mysql stack.
	 * This solution runs in O(n) time BUT assumes that all incoming input is
	 * strictly UTF8.
	 *
	 * @param    string    $input 		The input to be sanitised
	 *
	 * @return the sanitized input
	 */
	private function utf8_4byte_to_3byte( $input ) {
	  
	  if (!empty($input)) {
		$utf8_2byte = 0xC0 /*1100 0000*/; $utf8_2byte_bmask = 0xE0 /*1110 0000*/;
		$utf8_3byte = 0xE0 /*1110 0000*/; $utf8_3byte_bmask = 0XF0 /*1111 0000*/;
		$utf8_4byte = 0xF0 /*1111 0000*/; $utf8_4byte_bmask = 0xF8 /*1111 1000*/;
	 
		$sanitized = "";
		$len = strlen($input);
		for ($i = 0; $i < $len; ++$i) {
		  $mb_char = $input[$i]; // Potentially a multibyte sequence
		  $byte = ord($mb_char);
		  if (($byte & $utf8_2byte_bmask) == $utf8_2byte) {
			$mb_char .= $input[++$i];
		  }
		  else if (($byte & $utf8_3byte_bmask) == $utf8_3byte) {
			$mb_char .= $input[++$i];
			$mb_char .= $input[++$i];
		  }
		  else if (($byte & $utf8_4byte_bmask) == $utf8_4byte) {
			// Replace with ? to avoid MySQL exception
			$mb_char = '?';
			$i += 3;
		  }
	 
		  $sanitized .=  $mb_char;
		}
	 
		$input= $sanitized;
	  }
	 
	  return $input;
	}

	/**
	 * Function to display Templates for widget
	 *
	 * @param    string    $template	The input to be sanitised
	 * @param    array	   $data_arr	The input to be sanitised
	 * @param    string    $link_to		The input to be sanitised	 	 
	 *
	 * @include file templates
	 *
	 * return void
	 */
	private function template( $template, $data_arr, $link_to ){
		
		$filename = plugin_dir_path( __FILE__ ) . "views/" . $template . '.php';
	
		if( file_exists( $filename ) ){

			include $filename;
	
		} else {
			
			echo __( sprintf( 'Template not found<br>%s' , $filename), 'jrinstaslider' );
		}
	}
	
} // end of class JR_InstagramSlider
