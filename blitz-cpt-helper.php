<?php
/*
Plugin Name: Blitz CPT Helper
Plugin URI: 
Description: This plugin is necessary for any and all Blitz CPT Plugins. Creates: Page Titles | Pagination for Archive & Single Pages | Enqueues Slick & AMP-Carousel | [img] shortcode
Version: 1.0.0
Author: John D.
GitHub Plugin URI: 
*/

/*******************************************
Shortcode List
1) [blitz-img id="" size="" layout=""]

Filter List

1) blitz_page_title
	add_filter('blitz_page_title', 'my_custom_page_titles', 10, 1);
	function my_custom_page_titles($title){ return $title; }
	<h1>$title</h1>
2)
*******************************************/

require_once( plugin_dir_path( __FILE__ ) . 'includes/public-functions.php' );

$blitz_helper = new Blitz_CPT_Helper;

class Blitz_CPT_Helper{
  
  public function __construct() {

	$theme_check = wp_get_theme();

    if($theme_check->get("Template") == 'blitz'){
		
		// global $stylesheets;
		// $stylesheets['shared'][] = plugin_dir_path( __FILE__ ) . "/css/blitz-helper.css";
		
		// Array for all CPT Plugins - 'post_type_slug' => 'Title'
		add_action( 'after_theme_setup', array($this, 'helper_global_variables'), 0 );
		
		// Single & Archive Page Titles
		add_filter('blitz_before_content_amp', array( $this, 'cpt_before_content' ), 19 );
		add_filter('blitz_before_content', array( $this, 'cpt_before_content' ), 19 );
		
		
		// Turn off Default WP Pagination - NOT used, Submit button involved.
		add_filter( 'blitz_pagination_args', array( $this, 'archive_pagination_args' ), 10 );
		
		// Limit Pagination
		add_action('pre_get_posts', array( $this, 'number_of_posts_on_archive' ), 99 );
		add_filter('blitz_amp_archive_args', array( $this, 'number_of_posts_on_archive' ), 99 );
		// Format Archive Pagination
		add_action( 'blitz_pagination', array( $this, 'archive_pagination' ), 10 );
		// Single Page Paginaion
		add_filter( 'blitz_before_footer', array( $this, 'single_pagination' ) );
		// Enqueue Stylesheets
		add_action( 'get_header', array($this, 'helper_styles'), 20 );
		
		// AMP Components
		add_filter( 'blitz_amp_head', array($this, 'helper_amp_scripts') );
		
    } else {
		add_action( 'wp_enqueue_scripts', array($this, 'helper_non_blitz_scripts') );
    }
		
	// Slick Scripts & Styles
	add_action( 'wp_enqueue_scripts', array($this, 'helper_core_scripts') );
	// Admin Scripts & Styles
	add_action( 'admin_enqueue_scripts', array($this, 'helper_admin_scripts') );
  }
  
  public function helper_global_variables() {
	global $blitz_cpts;
	$blitz_cpts = array();
  }
  
  public function helper_non_blitz_scripts(){
    wp_enqueue_style( 'blitz-helper-css', plugin_dir_url( __FILE__ ) . 'css/blitz-helper.css' ); 
    wp_enqueue_style( 'blitz-helper-structure-css', plugin_dir_url( __FILE__ ) . 'css/blitz-helper-structure.css' );
  }
  
  public function helper_admin_scripts($suffix){

	if ( 
		( strpos($suffix, 'blitz_' ) !== false )
		&& ( strpos($suffix, '-option' ) !== false )
	) {
		wp_enqueue_style( 'blitz-helper-admin-css', plugin_dir_url( __FILE__ ) . 'css/blitz-helper-admin.css' ); 
		wp_enqueue_script( 'blitz-helper-media-option', plugin_dir_url( __FILE__ ) . 'js/media-options.js');
	}
  }
  
  public function helper_core_scripts(){  
    wp_enqueue_script( 'blitz-helper-slick-js', plugin_dir_url( __FILE__ ) . 'slick/slick.min.js');
    wp_enqueue_style( 'blitz-helper-slick-css', plugin_dir_url( __FILE__ ) . 'slick/slick.css');
	
	// Equal Height for Slick Slides
    wp_enqueue_script( 'slick-equal-height-js', plugin_dir_url( __FILE__ ) . 'js/slick-equal-height.js');
  }
  
  public function helper_styles() {
	global $stylesheets;
	// List Page Normalizing SCSS
	
	if ( is_archive() ) $stylesheets['shared'][] = plugin_dir_path( __FILE__ ) . "css/blitz-helper.scss";
  }
  
  public function helper_amp_scripts ( $output ) {
	// AMP-Carousel
	$output .= '<script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.1.js"></script>';
	return $output;
  }
  
  // Single & Archive Page Titles
  public function cpt_before_content($output) {
	if( is_page('home') || is_page('amp') || is_404() ) return $output;

	global $wp;
	$link = strtolower($wp->request);
	$queried_object = get_queried_object();
	$category = $queried_object->name;
	
	// Image Logic
	$image_id = get_post_thumbnail_id( $post->ID );
	// Archive Image
	if ( is_archive() ) {
		global $blitz_cpts;
		$slug = $this->get_archive_post_type();
		$values = $this->get_post_type_options($slug);
		foreach( $blitz_cpts as $slug => $name ) {
			if ( is_post_type_archive($slug) ) {
				if ( $values['featured_image_list'] !== '' ) { $image_id = $values['featured_image_list']; } 
			}
		}
	// Single Image
	} elseif ( is_single() ) {
		global $blitz_cpts;
		$type = get_post_type();
		$values = $this->get_post_type_options($type);
		if ( $values['featured_image_single'] !== '' ) { $image_id = $values['featured_image_single']; }
	}
	$image = wp_get_attachment_image_src( $image_id, 'full' );
	// Default Image
	if ( $image === false ) $image[0] = 'http://sitemines.com/basement-systems-template/wp-content/uploads/2018/10/stock-image-7.jpg';
	$style = 'style="background-image:url('.$image[0].')"';
	
	// Title Logic
	// Archive Title
	$title = get_the_title();
	if ( is_archive() ) {
		global $blitz_cpts;
		$slug = $this->get_archive_post_type();
		$values = $this->get_post_type_options($slug);
		foreach( $blitz_cpts as $slug => $name ) {
			if ( is_post_type_archive($slug) ) {
				if ( $values['cpt-title-plural'] == '' ) { $title = $name; } 
				else { $title = $values['cpt-title-plural']; }
			}
		}
	// Single Title
	} elseif ( is_single() ) {
		global $blitz_cpts;
		$type = get_post_type();
		$values = $this->get_post_type_options($type);
		if ( $values['cpt-title-plural'] == '' ) { $title = $blitz_cpts[$type]; } 
		else { $title = $values['cpt-title-plural']; }
	}
	
	$title = apply_filters( 'blitz_page_title', $title );
    
	$output .= '<div id="page_title" '.$style.'>'; 
	$output .= '<div class="constraint">';
	$output .= '<h1>'.$title.'</h1>';
	$output .= '</div>';
	$output .= '</div>'; 
    
    return $output;
  }
  
  public function number_of_posts_on_archive($var){
	
	$args = current_filter() == 'pre_get_posts' ? array() : $var;
	
	$slug = $args['post_type'];
	$values = $this->get_post_type_options($slug);
	 
	if ( (is_object($var) && $var->is_archive && !get_query_var('amp')) || current_filter() == 'blitz_amp_archive_args' ) {
		
		// If there is no Pagination, show all posts
		if ( $values['cpt-pagination'] !== 'on'  ) {
			$args['posts_per_page'] = -1;
		} else {
			// Limit number of posts displayed
			if ( $values['cpt-limit'] !== '' ) {
				$args['posts_per_page'] = $values['cpt-limit'];
			} else {
				$args['posts_per_page'] = 9;							
			}
		}
	}
	
	if (current_filter() == 'pre_get_posts') {
		if (!empty($args)) {
			foreach($args as $key => $val) {
				$var->set($key, $val);
			}
		}
		
		return $var;
	}
	
	return $args;
  }
  
  public function archive_pagination_args($args) {
	$slug = $this->get_archive_post_type();
	$values = $this->get_post_type_options($slug);
	
	//if we are gonna do custom shiznick
	if ( $values['cpt-pagination'] == 'on' && $values['cpt-pagination-style'] == 'on' ) { $args = array(); } 
	return $args;
  }
  
  public function archive_pagination() {
	  
	$slug = $this->get_archive_post_type();
	$values = $this->get_post_type_options($slug);
	
	// List Pagination Labels Logic
	// Left
	if ( $values['cpt-older'] != '' ) { $right_label = $values['cpt-older']; }
	else { $right_label = 'Next'; }
	if ( $values['cpt-icon-style'] == 'default' ) { $right_label .= ' »'; }
	// Right
	if ( $values['cpt-newer'] != '' ) { $left_label = $values['cpt-newer']; } 
	else { $left_label = 'Previous'; }
	if ( $values['cpt-icon-style'] == 'default' ) { $left_label = '« '.$left_label; }
	
	// List Pagination Icons Logic
	switch( $values['cpt-icon-style'] ) {
		case 'default':
			$left_icon  = '';
			$right_icon = '';
			break;
		case 'fontawesome':
			$left_icon  = '<i class="fas '.$values['cpt_fa_left_list'].'"></i>';
			$right_icon = '<i class="fas '.$values['cpt_fa_right_list'].'"></i>';
			break;
		case 'upload':
			$left_icon  = do_shortcode('[blitz-img id="'.$values['image_attachment_id_prev'].'"]');
			$right_icon = do_shortcode('[blitz-img id="'.$values['image_attachment_id_next'].'"]');
			break;
	}
	
	$submit = '<a href="'.get_home_url().'/submit-a-'.strtolower($values['cpt-title-singular']).'/" class="cpt-submit">Submit '.$values['cpt-title-singular'].'</a>';
	if ( $values['cpt-list-submit'] == 'on' ) {
		if ( $values['cpt-pagination'] !== 'on' ) $submit = '<div class="pagination-wrapper">'.$submit.'</div>';
		echo $submit;
	}
	
	if ( $values['cpt-pagination'] == 'on' && $values['cpt-pagination-style'] == 'on' ) {
		if ( get_previous_posts_link( '' ) != '' ) {
			echo '<div class="older">';
			echo $left_icon;
			echo '<span>'.$left_label.'</span>';
			echo get_previous_posts_link( '' );
			echo '</div>';
		}
		if ( get_next_posts_link( '' ) != '' ) {
			echo '<div class="newer">';
			echo '<span>'.$right_label.'</span>';
			echo $right_icon;
			echo get_next_posts_link( '' );
			echo '</div>';
		}
	}
  }
  
  public function single_pagination() {
	  
	$output = '';
	$slug = $this->get_archive_post_type();
	$values = $this->get_post_type_options($slug);
	
	if ( $values['cpt-single-pagination'] !== 'on' && !is_single() ) return;
	
	$next_post = get_next_post();
	$previous_post = get_previous_post();
	
	// Single Pagination Label Logic
	// Left
	if ( $values['cpt-older-single'] != '' ) { $left_label = $values['cpt-older-single']; }
	else { $left_label = $previous_post->post_title; }
	if ( $values['cpt-icon-style-single'] == 'default' ) { $left_label = '« '.$left_label; }
	// Right
	if ( $values['cpt-newer-single'] != '' ) { $right_label = $values['cpt-newer-single']; }
	else { $right_label = $next_post->post_title; }
	if ( $values['cpt-icon-style-single'] == 'default' ) { $right_label .= ' »'; }
	
	// Single Pagination Icons Logic
	switch( $values['cpt-icon-style-single'] ) {
		case 'default':
			$left_icon  = '';
			$right_icon = '';
			break;
		case 'fontawesome':
			$left_icon  = '<i class="fas '.$values['cpt_fa_left_single'].'"></i>';
			$right_icon = '<i class="fas '.$values['cpt_fa_right_single'].'"></i>';
			break;
		case 'upload':
			$left_icon  = do_shortcode('[blitz-img id="'.$values['image_attachment_id_next_single'].'"]');
			$right_icon = do_shortcode('[blitz-img id="'.$values['image_attachment_id_prev_single'].'"]');
			break;
	}
	
	if ( $previous_post->post_title !== NULL ) {
		$output.= '<div class="older">';
			$output .= $left_icon;
			$output .= '<span>'.$left_label.'</span>';
			$output .= '<a href="'.$previous_post->guid.'"></a>';
		$output .= '</div>';
	}
	if ( $next_post->post_title !== NULL ) {
		$output .= '<div class="newer">';
			$output .= '<span>'.$right_label.'</span>';
			$output .= $right_icon;
			$output .= '<a href="'.$next_post->guid.'"></a>';
		$output .= '</div>';
	}
	  
	$output = 
		'<div id="" class="section single-pagination">
			<div class="constraint">
				<div id="" class="column width-12 ">
					<div class="wrapper code pagination">
						'.$output.'
					</div>
				</div>
			</div>
		</div>';
		
	if ($values['cpt-single-pagination'] == 'on' && is_single() ) return $output;
  }
  
  public function get_archive_post_type() {
	$slug = get_queried_object()->name;
	if ( !isset($slug) || is_null($slug) || empty($slug) ) $slug = get_post_type();
	
	return $slug;
  }
  
  public function get_post_type_options($slug) {
	if ( !isset($slug) || is_null($slug) || empty($slug) ) { 
		$slug = $this->get_archive_post_type();	
	}	
	
	$inputs = get_option($slug.'_option');
	$inputs = json_decode($inputs);
	if ( !is_null($inputs) ) { foreach( $inputs as $key => $value ) { $values[$key] = $value; }	}
	
	return $values;
  }
  
}

