<?php
/*
Plugin Name: Instagram Slider Widget
Plugin URI: http://jrwebstudio.com/instagram-slider/
Version: 1.2.0
Description: Instagram Slider Widget is a responsive slider widget that shows 20 latest images from a public instagram user.
Author: jetonr
Author URI: http://jrwebstudio.com/
License: GPLv2 or later
*/

/**
 * On widgets Init register Widget
 */
add_action( 'widgets_init', array( 'JR_InstagramSlider', 'register_widget' ) );

/**
 * JR_InstagramSlider Class
 */
class JR_InstagramSlider extends WP_Widget {
	
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var     string
	 */
	const VERSION = '1.2.0';	
	
	/**
	 * Initialize the plugin by registering widget and loading public scripts
	 *
	 */
	public function __construct() {
		
		// Widget ID and Class Setup
		parent::__construct( 'jr_insta_slider', __( 'Instagram Slider', 'jrinstaslider' ), array(
				'classname' => 'jr-insta-slider',
				'description' => __( 'A widget that displays a slider with instagram images ', 'jrinstaslider' ) 
			) 
		);
				
		// Enqueue Plugin Styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this,	'public_enqueue' ) );
		
		// Enqueue Plugin Styles and scripts for admin pages
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		
		// Instgram Action to display images
		add_action( 'jr_instagram', array( $this, 'instagram_images' ) );		
	}
	
	/**
	 * Register widget on windgets init
	 */
	public static function register_widget() {
		register_widget( __CLASS__ );
	}
	
	/**
	 * Enqueue public-facing Scripts and style sheet.
	 */
	public function public_enqueue() {
		
		wp_enqueue_style( 'instag-slider', plugins_url( 'assets/css/instag-slider.css', __FILE__ ), array(), self::VERSION );
		
		wp_enqueue_script( 'jquery-pllexi-slider', plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), '2.2', false );
	}
	
	/**
	 * Enqueue admin side scripts and styles
	 * 
	 * @param  string $hook
	 */
	public function admin_enqueue( $hook ) {
		
		if ( 'widgets.php' != $hook ) {
			return;
		}
		
		wp_enqueue_style( 'jr-insta-admin-styles', plugins_url( 'assets/css/jr-insta-admin.css', __FILE__ ), array(), self::VERSION );

		wp_enqueue_script( 'jr-insta-admin-script', plugins_url( 'assets/js/jr-insta-admin.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
				
	}
	
	/**
	 * The Public view of the Widget  
	 *
	 * @return mixed
	 */
	public function widget( $args, $instance ) {
		
		extract( $args );
		
		//Our variables from the widget settings.
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		
		// Display the widget title 
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		do_action( 'jr_instagram', $instance );
		
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
	public function update( $new_instance, $instance ) {
				
		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['username']         = $new_instance['username'];
		$instance['source']           = $new_instance['source'];
		$instance['template']         = $new_instance['template'];
		$instance['images_link']      = $new_instance['images_link'];
		$instance['custom_url']       = $new_instance['custom_url'];
		$instance['orderby']          = $new_instance['orderby'];
		$instance['images_number']    = $new_instance['images_number'];
		$instance['columns']          = $new_instance['columns'];
		$instance['refresh_hour']     = $new_instance['refresh_hour'];
		$instance['image_size']       = $new_instance['image_size'];
		$instance['image_link_rel']   = $new_instance['image_link_rel'];
		$instance['image_link_class'] = $new_instance['image_link_class'];
		$instance['controls']         = $new_instance['controls'];
		$instance['animation']        = $new_instance['animation'];
		$instance['description']      = $new_instance['description'];
			
		if ( $instance['source'] == 'instagram' ) {
			$this->instagram_data( $instance['username'], $instance['refresh_hour'], $instance['images_number'] );
		}

		return $instance;
	}
	
	
	/**
	 * Widget Settings Form
	 *
	 * @return mixed
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'            => __('Instagram Slider', 'jrinstaslider'),
			'username'         => '',
			'source'           => 'instagram',
			'template'         => 'slider',
			'images_link'      => 'image_url',
			'custom_url'       => '',
			'orderby'          => 'rand',
			'images_number'    => 5,
			'columns'          => 4,
			'refresh_hour'     => 5,
			'image_size'       => 'full',
			'image_link_rel'   => '',
			'image_link_class' => '',
			'controls'		   => 'prev_next',
			'animation'        => 'slide',
			'description'      => array( 'username', 'time','caption' )
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		<div class="jr-container">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'jrinstaslider'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e('Instagram Username:', 'jrinstaslider'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>" />
			</p>
			<p>
				<?php _e( 'Source:', 'jrinstaslider' ); ?><br>
				<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'source' ); ?>" name="<?php echo $this->get_field_name( 'source' ); ?>" value="instagram" <?php checked( 'instagram', $instance['source'] ); ?> /> <?php _e( 'Instagram', 'jrinstaslider' ); ?></label>  
				<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'source' ); ?>" name="<?php echo $this->get_field_name( 'source' ); ?>" value="media_library" <?php checked( 'media_library', $instance['source'] ); ?> /> <?php _e( 'WP Media Library', 'jrinstaslider' ); ?></label>
				<br><small class="description"><?php _e( 'WP Media Library option will display previously saved instagram images for the user in the field above!', 'jrinstaslider') ?></small>
			</p>			
			<p>
				<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template', 'jrinstaslider' ); ?>
					<select class="widefat" name="<?php echo $this->get_field_name( 'template' ); ?>" id="<?php echo $this->get_field_id( 'template' ); ?>">
						<option value="slider" <?php echo ($instance['template'] == 'slider') ? ' selected="selected"' : ''; ?>><?php _e( 'Slider - Normal', 'jrinstaslider' ); ?></option>
						<option value="slider-overlay" <?php echo ($instance['template'] == 'slider-overlay') ? ' selected="selected"' : ''; ?>><?php _e( 'Slider - Overlay Text', 'jrinstaslider' ); ?></option>
						<option value="thumbs" <?php echo ($instance['template'] == 'thumbs') ? ' selected="selected"' : ''; ?>><?php _e( 'Thumbnails', 'jrinstaslider' ); ?></option>
					</select>  
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order by', 'jrinstaslider' ); ?>
					<select class="widefat" name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id( 'orderby' ); ?>">
						<option value="date-ASC" <?php selected( $instance['orderby'], 'date-ASC', true); ?>><?php _e( 'Date - Ascending', 'jrinstaslider' ); ?></option>
						<option value="date-DESC" <?php selected( $instance['orderby'], 'date-DESC', true); ?>><?php _e( 'Date - Descending', 'jrinstaslider' ); ?></option>
						<option value="popular-ASC" <?php selected( $instance['orderby'], 'popular-ASC', true); ?>><?php _e( 'Popularity - Ascending', 'jrinstaslider' ); ?></option>
						<option value="popular-DESC" <?php selected( $instance['orderby'], 'popular-DESC', true); ?>><?php _e( 'Popularity - Descending', 'jrinstaslider' ); ?></option>
						<option value="rand" <?php selected( $instance['orderby'], 'rand', true); ?>><?php _e( 'Random', 'jrinstaslider' ); ?></option>
					</select>  
				</label>
			</p>	
			<p>
				<label for="<?php echo $this->get_field_id( 'images_link' ); ?>"><?php _e( 'Link to', 'jrinstaslider' ); ?>
					<select class="widefat" name="<?php echo $this->get_field_name( 'images_link' ); ?>" id="<?php echo $this->get_field_id( 'images_link' ); ?>">
						<option value="image_url" <?php selected( $instance['images_link'], 'image_url', true); ?>><?php _e( 'Instagram Image', 'jrinstaslider' ); ?></option>
						<option value="user_url" <?php selected( $instance['images_link'], 'user_url', true); ?>><?php _e( 'Instagram Profile', 'jrinstaslider' ); ?></option>
						<option value="local_image_url" <?php selected( $instance['images_link'], 'local_image_url', true); ?>><?php _e( 'Locally Saved Image', 'jrinstaslider' ); ?></option>
						<option value="attachment" <?php selected( $instance['images_link'], 'attachment', true); ?>><?php _e( 'Attachment Page', 'jrinstaslider' ); ?></option>
						<option value="custom_url" <?php selected( $instance['images_link'], 'custom_url', true ); ?>><?php _e( 'Custom Link', 'jrinstaslider' ); ?></option>
						<option value="none" <?php selected( $instance['images_link'], 'none', true); ?>><?php _e( 'None', 'jrinstaslider' ); ?></option>
					</select>  
				</label>
			</p>			
			<p class="<?php if ( 'custom_url' != $instance['images_link'] ) echo 'hidden'; ?>">
				<label for="<?php echo $this->get_field_id( 'custom_url' ); ?>"><?php _e( 'Custom link:', 'jrinstaslider'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'custom_url' ); ?>" name="<?php echo $this->get_field_name( 'custom_url' ); ?>" value="<?php echo $instance['custom_url']; ?>" />
				<small><?php _e('* use this field only if the above option is set to <strong>Custom Link</strong>', 'jrinstaslider'); ?></small>
			</p>
			<p>
				<label  for="<?php echo $this->get_field_id( 'images_number' ); ?>"><?php _e( 'Number of images to show:', 'jrinstaslider' ); ?>
					<input  class="small-text" id="<?php echo $this->get_field_id( 'images_number' ); ?>" name="<?php echo $this->get_field_name( 'images_number' ); ?>" value="<?php echo $instance['images_number']; ?>" />
					<small><?php _e( 'limit is 20 if <strong>Source</strong> is Instagram', 'jrinstaslider' ); ?></small>
				</label>
			</p>
			<p class="<?php if ( 'thumbs' != $instance['template'] ) echo 'hidden'; ?>">
				<label  for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Number of Columns:', 'jrinstaslider' ); ?>
					<input class="small-text" id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" value="<?php echo $instance['columns']; ?>" />
					<small><?php _e('max is 10 ( only for thumbnails template )', 'jrinstaslider'); ?></small>
				</label>
			</p>			
			<p class="<?php if ( 'instagram' != $instance['source'] ) echo 'hidden'; ?>">
				<label  for="<?php echo $this->get_field_id( 'refresh_hour' ); ?>"><?php _e( 'Check for new images every:', 'jrinstaslider' ); ?>
					<input  class="small-text" id="<?php echo $this->get_field_id( 'refresh_hour' ); ?>" name="<?php echo $this->get_field_name( 'refresh_hour' ); ?>" value="<?php echo $instance['refresh_hour']; ?>" />
					<small><?php _e('hours', 'jrinstaslider'); ?></small>
				</label>
			</p>
			<p>
				<strong>Advanced Options</strong> 
				<?php 
				$advanced_class = '';
				$advanced_text = '[ - Close ]';		
				if ( '' == trim( $instance['image_link_rel'] ) && '' == trim( $instance['image_link_class'] ) && '' == trim( $instance['image_size'] ) )  { 
					$advanced_class = 'hidden';
					$advanced_text = '[ + Open ]';
				}
				?>
				<a href="#" class="jr-advanced"><?php echo $advanced_text;  ?></a>
			</p>
			<div class="jr-advanced-input <?php echo $advanced_class; ?>">
				<div class="jr-image-options">
					<h4 class="jr-advanced-title"><?php _e( 'Advanced Image Options', 'jrinstaslider'); ?></h4>
					<?php 
					$image_size_options = get_intermediate_image_sizes(); 
					if ( is_array( $image_size_options && !empty($image_size_options ) ) ) :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image size', 'jrinstaslider' ); ?></label>
						<select class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
							<option value=""><?php _e('Select Image Size', 'jrinstaslider') ?></option>
							<?php
							foreach ( $image_size_options as $image_size_option ) {
								echo '
									<option value="' . esc_attr( $image_size_option ) . '"'
									. selected( $image_size_option, $instance['image_size'], false )
									. '>' . $image_size_option . '</option>';
							}
							?>
						</select>
						<small class="description"><?php _e( 'Image Sizes are defined using add_image_size() function', 'jrinstaslider' ); ?></small>
					</p>
					<?php endif; ?>
					<p>
						<label for="<?php echo $this->get_field_id( 'image_link_rel' ); ?>"><?php _e( 'Image Link rel attribute', 'jrinstaslider' ); ?>:</label>
						<input class="widefat" id="<?php echo $this->get_field_id( 'image_link_rel' ); ?>" name="<?php echo $this->get_field_name( 'image_link_rel' ); ?>" value="<?php echo $instance['image_link_rel']; ?>" />
						<small class="description"><?php _e( 'Specifies the relationship between the current page and the linked website', 'jrinstaslider' ); ?></small>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id( 'image_link_class' ); ?>"><?php _e( 'Image Link class', 'jrinstaslider' ); ?>:</label>
						<input class="widefat" id="<?php echo $this->get_field_id( 'image_link_class' ); ?>" name="<?php echo $this->get_field_name( 'image_link_class' ); ?>" value="<?php echo $instance['image_link_class']; ?>" />
						<small class="description"><?php _e( 'Usefull if you are using jQuery lightbox plugins to open links', 'jrinstaslider' ); ?></small>

					</p>
				</div>
				<div class="jr-slider-options <?php if ( 'thumbs' == $instance['template'] ) echo 'hidden'; ?>">
					<h4 class="jr-advanced-title"><?php _e( 'Advanced Slider Options', 'jrinstaslider'); ?></h4>
					<p>
						<?php _e( 'Slider Navigation Controls:', 'jrinstaslider' ); ?><br>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="prev_next" <?php checked( 'prev_next', $instance['controls'] ); ?> /> <?php _e( 'Prev & Next', 'jrinstaslider' ); ?></label>  
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="numberless" <?php checked( 'numberless', $instance['controls'] ); ?> /> <?php _e( 'Numberless', 'jrinstaslider' ); ?></label>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="none" <?php checked( 'none', $instance['controls'] ); ?> /> <?php _e( 'No Navigation', 'jrinstaslider' ); ?></label>
					</p>
					<p>
						<?php _e( 'Slider Animation:', 'jrinstaslider' ); ?><br>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'animation' ); ?>" name="<?php echo $this->get_field_name( 'animation' ); ?>" value="slide" <?php checked( 'slide', $instance['animation'] ); ?> /> <?php _e( 'Slide', 'jrinstaslider' ); ?></label>  
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'animation' ); ?>" name="<?php echo $this->get_field_name( 'animation' ); ?>" value="fade" <?php checked( 'fade', $instance['animation'] ); ?> /> <?php _e( 'Fade', 'jrinstaslider' ); ?></label>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e( 'Slider Text Description:', 'jrinstaslider' ); ?></label>
						<select size=3 class='widefat' id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>[]" multiple="multiple">
							<option value='username' <?php $this->selected( $instance['description'], 'username' ); ?>><?php _e( 'Username', 'jrinstaslider'); ?></option>
							<option value='time'<?php $this->selected( $instance['description'], 'time' ); ?>><?php _e( 'Time', 'jrinstaslider'); ?></option> 
							<option value='caption'<?php $this->selected( $instance['description'], 'caption' ); ?>><?php _e( 'Caption', 'jrinstaslider'); ?></option> 
						</select>
						<small class="description"><?php _e( 'Hold ctrl and click the fields you want to show/hide on your slider. Leave all unselected to hide them all. Default all selected.', 'jrinstaslider') ?></small>
					</p>					
				</div>
			</div>
			<p class="pressthis"><a target="_blank" title="Donate To Keep This Plugin Alive!" href="http://goo.gl/RZiu34"><span>Donate To Keep This Plugin Alive!</span></a></p>        
		</div>
		<?php
	}

	/**
	 * Selected array function echoes selected if in array
	 * 
	 * @param  array $haystack The array to search in
	 * @param  string $current  The string value to search in array;
	 * 
	 * @return string
	 */
	private function selected( $haystack, $current ) {
		
		if( is_array( $haystack ) && in_array( $current, $haystack ) ) {
			$current = $haystack = 1;
		}
		selected( $haystack, $current, true );
	}	

	/**
	 * Echoes the Display Instagram Images method
	 * 
	 * @param  array $args
	 * 
	 * @return void
	 */
	public function instagram_images( $args ) {
		echo $this->display_images( $args );
	}

	/**
	 * Runs the query for images and returns the html
	 * 
	 * @param  array  $args 
	 * 
	 * @return string       
	 */
	private function display_images( $args ) {

		$username         = isset( $args['username'] ) && !empty( $args['username'] ) ? $args['username'] : false;
		$source           = isset( $args['source'] ) && !empty( $args['source'] ) ? $args['source'] : 'instagram';
		$template         = isset( $args['template'] ) ? $args['template'] : 'slider';
		$orderby          = isset( $args['orderby'] ) ? $args['orderby'] : 'rand';
		$images_link      = isset( $args['images_link'] ) ? $args['images_link'] : 'local_image_url';
		$custom_url       = isset( $args['custom_url'] ) ? $args['custom_url'] : '';
		$images_number    = isset( $args['images_number'] ) ? absint( $args['images_number'] ) : 5;
		$columns          = isset( $args['columns'] ) ? absint( $args['columns'] ) : 4;
		$refresh_hour     = isset( $args['refresh_hour'] ) ? absint( $args['refresh_hour'] ) : 5;
		$image_size       = isset( $args['image_size'] ) ? $args['image_size'] : 'full';
		$image_link_rel   = isset( $args['image_link_rel'] ) ? $args['image_link_rel'] : '';
		$image_link_class = isset( $args['image_link_class'] ) ? $args['image_link_class'] : '';
		$controls         = isset( $args['controls'] ) ? $args['controls'] : 'prev_next';
		$animation        = isset( $args['animation'] ) ? $args['animation'] : 'slide';
		$description      = isset( $args['description'] ) ? $args['description'] : array();

		if ( false == $username ) {
			return false;
		}

		if ( !empty( $description ) && !is_array( $description ) ) {
			$description = explode( ',', $description );
		}

		if ( $source == 'instagram' && $refresh_hour == 0 ) {
			$refresh_hour = 5;
		}
		
		$template_args = array(
			'image_size'  => $image_size,
			'link_rel'    => $image_link_rel,
			'link_class'  => $image_link_class
		);

		$query_args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'orderby'		 => 'rand',
			'no_found_rows'  => true
		);
		
		if ( $orderby != 'rand' ) {
			
			$orderby = explode( '-', $orderby );
			$meta_key = $orderby[0] == 'date' ? 'jr_insta_timestamp' : 'jr_insta_popularity';
			
			$query_args['meta_key'] = $meta_key;
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = $orderby[1];
		}
		
		if ( $source != 'instagram' ) {
			$query_args['posts_per_page'] = $images_number;
			$query_args['meta_query'] = array(
				array(
					'key'     => 'jr_insta_username',
					'value'   => $username,
					'compare' => '='
				)
			);
		} else {
			$query_args['post__in'] = $this->instagram_data( $username, $refresh_hour, $images_number );
		}
		
		$instagram_images = new WP_Query( $query_args );

		$output = __( 'No Images Yet', 'jrinstaslider' );
		
		if ( $instagram_images->have_posts() ) {

			$images_div_class = 'jr-insta-thumb';
			$ul_class         = 'thumbnails jr_col_' . $columns;
			$slider_script    = ''; 
			
			if ( $template != 'thumbs' ) {
				
				$template_args['description'] = $description;
				$direction_nav = ( $controls == 'prev_next' ) ? 'true' : 'false';
				$control_nav   = ( $controls == 'numberless' ) ? 'true': 'false';
				$ul_class      = 'slides';
				 
				if ( $template == 'slider' ) {
					$images_div_class = 'pllexislider pllexislider-normal';
		            $slider_script =
					"<script type='text/javascript'>" . "\n" .
					"	jQuery(document).ready(function($) {" . "\n" .
					"		$('.pllexislider-normal').pllexislider({" . "\n" .
					"			animation: '{$animation}'," . "\n" .
					"			directionNav: {$direction_nav}," . "\n" .
					"			controlNav: {$control_nav}," . "\n" .
					"			prevText: ''," . "\n" .
					"			nextText: ''," . "\n" .
					"		});" . "\n" .
					"	});" . "\n" .
					"</script>" . "\n";
				} else {
					$images_div_class = 'pllexislider pllexislider-overlay';
		            $slider_script =
					"<script type='text/javascript'>" . "\n" .
					"	jQuery(document).ready(function($) {" . "\n" .
					"		$('.pllexislider-overlay').pllexislider({" . "\n" .
					"			animation: '{$animation}'," . "\n" .
					"			directionNav: {$direction_nav}," . "\n" .
					"			controlNav: {$control_nav}," . "\n" .					
					"			prevText: ''," . "\n" .
					"			nextText: ''," . "\n" .									
					"			start: function(slider){" . "\n" .
					"				slider.hover(" . "\n" .
					"					function () {" . "\n" .
					"						slider.find('.jr-insta-datacontainer, .pllex-control-nav, .pllex-direction-nav').stop(true,true).fadeIn();" . "\n" .
					"					}," . "\n" .
					"					function () {" . "\n" .
					"						slider.find('.jr-insta-datacontainer, .pllex-control-nav, .pllex-direction-nav').stop(true,true).fadeOut();" . "\n" .
					"					}" . "\n" .
					"				);" . "\n" .
					"			}" . "\n" .
					"		});" . "\n" .
					"	});" . "\n" .
					"</script>" . "\n";				
				}
	        }

			$images_div = "<div class='{$images_div_class}'>\n";
			$images_ul  = "<ul class='no-bullet {$ul_class}'>\n";
			
			$output = $slider_script . $images_div . $images_ul;

				while ( $instagram_images->have_posts() ) : $instagram_images->the_post();
					
					$id = get_the_id();

					if ( 'image_url' == $images_link ) {
						$template_args['link_to'] = get_post_meta( $id, 'jr_insta_link', true );
					} elseif ( 'user_url' == $images_link ) {
						$template_args['link_to'] = 'http://instagram.com/' . $username;
					} elseif ( 'local_image_url' == $images_link ) {
						$template_args['link_to'] = wp_get_attachment_url( $id );
					} elseif ( 'attachment' == $images_link ) {
						$template_args['link_to'] = get_permalink( $id );
					} elseif ( 'custom_url' == $images_link ) {
						$template_args['link_to'] = $custom_url;
					}

					$output .= $this->get_template( $template, $template_args );

				endwhile;
				
			$output .= "</ul>";
 			$output .= "</div>";

		}
		
		wp_reset_postdata();
		
		return $output;
	}

	/**
	 * Function to display Templates styles
	 *
	 * @param    string    $template
	 * @param    array	   $args	    
	 *
	 * return mixed
	 */
	private function get_template( $template, $args ) {

		$image_url = wp_get_attachment_image_src( get_the_id(), $args['image_size'] );
		$all_metas = get_post_custom( get_the_id() );
		$link_to   = isset( $args['link_to'] ) ? $args['link_to'] : false;
		$caption   = get_the_excerpt();
		$short_caption = wp_trim_words( $caption, 10 );

		$image_src = '<img src="' . $image_url[0] . '" alt="' . $short_caption . '" title="' . $short_caption . '" />';
		$image_output  = $image_src;

		if ( $link_to ) {
			$image_output  = '<a href="' . $link_to . '" target="_blank"';

			if ( ! empty( $args['link_rel'] ) ) {
				$image_output .= ' rel="' . $args['link_rel'] . '"';
			}

			if ( ! empty( $args['link_class'] ) ) {
				$image_output .= ' class="' . $args['link_class'] . '"';
			}
			$image_output .= ' title="' . $short_caption . '">' . $image_src . '</a>';
		}		

		$output = '';
		
		// Template : Normal Slider
		if ( $template == 'slider' ) {
			
			$output .= "<li>";

				$output .= $image_output;

				if ( count( $args['description'] ) >= 1 ) { 
					
					$output .= "<div class='jr-insta-datacontainer'>\n";
				
						if ( $all_metas['jr_insta_timestamp'][0] && in_array( 'time', $args['description'] ) ) {
							$time = human_time_diff( $all_metas['jr_insta_timestamp'][0] );
							$output .= "<span class='jr-insta-time'>{$time} ago</span>\n";
						}
						if ( in_array( 'username', $args['description'] ) ) {
							$output .= "<span class='jr-insta-username'>by <a rel='nofollow' href='http://instagram.com/{$all_metas['jr_insta_username'][0]}' target='_blank'>{$all_metas['jr_insta_username'][0]}</a></span>\n";
						}

						if ( $caption != '' && in_array( 'caption', $args['description'] ) ) {
							$caption   = preg_replace( '/@([a-z0-9_]+)/i', '&nbsp;<a href="http://instagram.com/$1" rel="nofollow" target="_blank">@$1</a>&nbsp;', $caption );
							$output .= "<span class='jr-insta-caption'>{$caption}</span>\n";
						}

					$output .= "</div>\n";
				}

			$output .= "</li>";
		
		// Template : Slider with text Overlay on mouse over
		} elseif ( $template == 'slider-overlay' ) {
			
			$output .= "<li>";
			
				$output .= $image_output;
			
				if ( count( $args['description'] ) >= 1 ) {
					
					$output .= "<div class='jr-insta-wrap'>\n";

						$output .= "<div class='jr-insta-datacontainer'>\n";

							if ( $all_metas['jr_insta_timestamp'][0] && in_array( 'time', $args['description'] ) ) {
								$time = human_time_diff( $all_metas['jr_insta_timestamp'][0] );
								$output .= "<span class='jr-insta-time'>{$time} ago</span>\n";
							}
							
							if ( in_array( 'username', $args['description'] ) ) {
								$output .= "<span class='jr-insta-username'>by <a rel='nofollow' target='_blank' href='http://instagram.com/{$all_metas['jr_insta_username'][0]}'>{$all_metas['jr_insta_username'][0]}</a></span>\n";
							}

							if ( $caption != '' && in_array( 'caption', $args['description'] ) ) {
								$caption   = preg_replace( '/@([a-z0-9_]+)/i', '&nbsp;<a href="http://instagram.com/$1" rel="nofollow" target="_blank">@$1</a>&nbsp;', $caption );
								$output .= "<span class='jr-insta-caption'>{$caption}</span>\n";
							}

						$output .= "</div>\n";

					$output .= "</div>\n";
				}
			
			$output .= "</li>";
		
		// Template : Thumbnails no text	
		} elseif ( $template == 'thumbs' ) {

			$output .= "<li>";
			$output .= $image_output;
			$output .= "</li>";

		} else {

			$output .= 'This template does not exist!';
		}

		return $output;
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
		
		$opt_name  = 'jr_insta_' . md5( $username );
		$instaData = get_transient( $opt_name );
		$user_opt  = (array) get_option( $opt_name );
		
		if ( false === $instaData || $user_opt['username'] != $username || $user_opt['cache_hours'] != $cache_hours || $user_opt['nr_images'] != $nr_images ) {
			
			$instaData    = array();
			$user_options = compact( 'username', 'cache_hours', 'nr_images' );
			
			$json = wp_remote_get( 'http://instagram.com/' . $username, array( 'sslverify' => false, 'timeout' => 60 ) );
			
			if ( $json['response']['code'] == 200 ) {
				
				$json = $json['body'];
				$json = strstr( $json, 'window._sharedData = ' );
				$json = str_replace( 'window._sharedData = ', '', $json );
				
				// Compatibility for version of php where strstr() doesnt accept third parameter
				if ( version_compare( PHP_VERSION, '5.3.10', '>=' ) ) {
					$json = substr( $json, 0, strpos( $json, '</script>' ) );
				} else {
					$json = strstr( $json, '</script>', true );
				}
				
				$json = rtrim( $json, ';' );
				
				// Function json_last_error() is not available before PHP * 5.3.0 version
				if ( function_exists( 'json_last_error' ) ) {
					
					( $results = json_decode( $json, true ) ) && json_last_error() == JSON_ERROR_NONE;
					
				} else {
					
					$results = json_decode( $json, true );
				}
				
				if ( ( $results ) && is_array( $results ) ) {
					
					foreach ( $results['entry_data']['UserProfile'][0]['userMedia'] as $current => $result ) {
						
						if ( $result['type'] != 'image' ) {
							$nr_images++;
							continue;
						}
						
						if ( $current >= $nr_images ) {
							break;
						}
						
						$image_data['username']   = $result['user']['username'];
						$image_data['url']        = $result['images']['standard_resolution']['url'];
						$image_data['caption']    = $this->sanitize( $result['caption']['text'] );
						$image_data['id']         = $result['id'];
						$image_data['link']       = $result['link'];
						$image_data['popularity'] = (int) ( $result['comments']['count'] ) + ( $result['likes']['count'] );
						$image_data['timestamp']  = $result['created_time'];
						
						if ( isset( $user_opt['saved_images'][$image_data['id']] ) ) {
							
							if ( is_string( get_post_status( $user_opt['saved_images'][$image_data['id']] ) ) ) {
								
								$this->update_wp_attachment( $user_opt['saved_images'][$image_data['id']], $image_data );
								
								$instaData[$image_data['id']] = $user_opt['saved_images'][$image_data['id']];
								
							} else {
								
								$user_opt['deleted_images'][$image_data['id']] = $image_data['url'];
							}
							
						} else {
							
							$id = $this->save_wp_attachment( $image_data );
							
							if ( $id ) {
								
								$user_opt['saved_images'][$image_data['id']] = $id;
								
								$instaData[$image_data['id']] = $id;
							}
							
						} // end isset $saved_images 
						
					} // end -> foreach
					
				} // end -> ( $results ) && is_array( $results ) )
				
			} // end -> $json['response']['code'] === 200 )
			
			update_option( $opt_name, array_merge( $user_options, $user_opt ) );
			
			if ( $instaData ) {
				set_transient( $opt_name, $instaData, $cache_hours * 60 * 60 );
			}
			
		} // end -> false === $instaData
		
		return $instaData;
	}

	/**
	 * Updates attachment using the id
	 * @param     int      $attachment_ID
	 * @param     array    image_data
	 * @return    void
	 */
	private function update_wp_attachment( $attachment_ID, $image_data ) {
		
		update_post_meta( $attachment_ID, 'jr_insta_popularity', $image_data['popularity'] );
	}
	
	/**
	 * Save Instagram images to upload folder and ads to media.
	 * If the upload fails it returns the remote image url. 
	 *
	 * @param    string    $url    		Url of image to download
	 * @param    string    $file    	File path for image	
	 *
	 * @return   string    $url 		Url to image
	 */
	private function save_wp_attachment( $image_data ) {
		
		$image_info = pathinfo( $image_data['url'] );
		
		if ( !in_array( $image_info['extension'], array( 'jpg', 'jpe', 'jpeg', 'gif', 'png' ) ) ) {
			return false;
		}
		
		// These files need to be included as dependencies when on the front end.
		if ( !is_admin() ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		$tmp = download_url( $image_data['url'] );
		
		$file_array             = array();
		$file_array['name']     = $image_info['basename'];
		$file_array['tmp_name'] = $tmp;
		
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}
		
		$id = media_handle_sideload( $file_array, 0, NULL, array(
			 'post_excerpt' => $image_data['caption'] 
		) );
		
		// If error storing permanently, unlink
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}
		
		unset( $image_data['caption'] );
		
		foreach ( $image_data as $meta_key => $meta_value ) {
			update_post_meta( $id, 'jr_insta_' . $meta_key, $meta_value );
		}
		
		return $id;
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
	private function sanitize( $input ) {
		
		$input = trim( str_replace( '#', '', $input ) );
		
		if ( !empty( $input ) ) {
			$utf8_2byte       = 0xC0 /*1100 0000*/ ;
			$utf8_2byte_bmask = 0xE0 /*1110 0000*/ ;
			$utf8_3byte       = 0xE0 /*1110 0000*/ ;
			$utf8_3byte_bmask = 0XF0 /*1111 0000*/ ;
			$utf8_4byte       = 0xF0 /*1111 0000*/ ;
			$utf8_4byte_bmask = 0xF8 /*1111 1000*/ ;
			
			$sanitized = "";
			$len       = strlen( $input );
			for ( $i = 0; $i < $len; ++$i ) {
				
				$mb_char = $input[$i]; // Potentially a multibyte sequence
				$byte    = ord( $mb_char );
				
				if ( ( $byte & $utf8_2byte_bmask ) == $utf8_2byte ) {
					$mb_char .= $input[++$i];
				} else if ( ( $byte & $utf8_3byte_bmask ) == $utf8_3byte ) {
					$mb_char .= $input[++$i];
					$mb_char .= $input[++$i];
				} else if ( ( $byte & $utf8_4byte_bmask ) == $utf8_4byte ) {
					// Replace with ? to avoid MySQL exception
					$mb_char = '';
					$i += 3;
				}
				
				$sanitized .= $mb_char;
			}
			
			$input = $sanitized;
		}
		
		return $input;
	}
	
} // end of class JR_InstagramSlider