<?php
namespace pl_ar_shortcode;

if (!defined('ABSPATH'))
{
  exit; // Exit if accessed directly
}

class pl_ar_Shortcode  {
  public function __construct() {
    add_shortcode( 'ar-plugin',array($this, 'custom_button_shortcode'));
    add_action( 'admin_enqueue_scripts',array($this, 'pl_ar_my_enqueue_js_admin'));
    add_action( 'admin_enqueue_scripts',array($this, 'pl_ar_enqueue_css_admin' ));
    add_action('wp_ajax_pl_ar_query',array($this, 'pl_ar_query'));
	  add_action("wp_ajax_nopriv_pl_ar_query",array($this, "pl_ar_query")); 
  }

  //general enqueue function (admin)
  public function pl_ar_my_enqueue_js_admin() {
    if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }
    wp_enqueue_script( 'shortcode_script', PL_AR_LINK.'/js/shortcode.js', array('jquery') );
    wp_localize_script( 'shortcode_script', 'pl_ar_ajax_admin_params', array('pl_ar_nonce' => wp_create_nonce('pl_ar_admin_ajax_nonce'),'Foldertree_url' =>PL_AR_LINK.'Foldertree.php','ajaxurl' => admin_url( 'admin-ajax.php' )));
  }

	public function pl_ar_enqueue_css_admin() {
    wp_enqueue_style( 'foldertree-style', PL_AR_LINK.'css/filetree.css' );
    wp_enqueue_style( 'input_boxes-style', PL_AR_LINK.'css/input_boxes.css' );
  }
  
  //Update database with Ajax post data (from shortcode.js)
	public function pl_ar_query() {
		check_ajax_referer( 'pl_ar_admin_ajax_nonce', 'security' );
		global $wpdb;
	  //create random shortcode that is not in array
	  $current_shortcodes = $wpdb->get_results("SELECT shordcode_id FROM {$wpdb->prefix}pl_ar_table");
		do {
		    $rand_shortcode = rand(1, 10000);
		} while(in_array($rand_shortcode, $current_shortcodes));
    //get the posts
	  $objects = sanitize_text_field($_POST['objects']);
	  $markers = sanitize_text_field($_POST['markers']);
	  $button_name = sanitize_text_field($_POST['button_name']);
	  $button_back_color = sanitize_text_field($_POST['button_back_color']);
	  $button_text_color = sanitize_text_field($_POST['button_text_color']);
	  $type = sanitize_text_field($_POST['type']);

    $Jobjects = json_encode($objects);//need that because of the backslash\escape char
    $Jmarkers = json_encode($markers);
    //check if entry exists
	  $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pl_ar_table WHERE objects = {$Jobjects} AND markers = {$Jmarkers}");
	  //if the selector not exists greate new row. else replace
	  if ($count == 0 ){
	  		  //insert values to a new row
		  $wpdb->insert($wpdb->prefix.'pl_ar_table',
		  array(
		    'objects' => $objects , 
		    'markers' => $markers ,
		    'shortcode_id' => $rand_shortcode,
		    'shortcode_text' => '[ar-plugin id="'.$rand_shortcode.'" type="'.$type.'" name="'.$button_name.'" color="'.$button_back_color.'" text-color="'.$button_text_color.'"]',
		    'buttonName' => $button_name,
		    'buttonBackGroundColor' => $button_back_color,
		    'buttonTextColor' => $button_text_color
		  ), 
		  array( 
		    '%s',
		    '%s',
		    '%s',
		    '%s',
		    '%s',
		    '%s'
		  )  );
	  
		  $pl_ar_id=$wpdb->get_var("SELECT id FROM {$wpdb->prefix}pl_ar_table WHERE objects = {$objects} AND markers = {$markers}");

			echo $rand_shortcode;
			die();
		}
		else{
			$already_shortcode = $wpdb->get_var("SELECT shortcode_id FROM {$wpdb->prefix}pl_ar_table WHERE objects = {$Jobjects} AND markers = {$Jmarkers}");
			echo 'Entry already exist with id:'.$already_shortcode;
		  die();
		}
	}	
	

  //function for creating shortcode page
  public function pl_ar_shortcode_page_html(){
    ?>
    <div id="pl_ar_panel">
    	<h2 class="pl_ar_head_title">Ar plugin editor</h2>
	    <div id="pl_ar_main_box">
		    <div class="pl_ar_object_box" style="height: auto;">
		    	  <input type="text" class="pl_ar_object_path_class" id="pl_ar_object_path" placeholder="Object path"  disabled/>
		        <label class="pl_ar_button" id="pl_ar_insert_object" >Insert Object (gltf or image)</label>
					  <input type="text" class="pl_ar_marker_path_class" id="pl_ar_marker_path" placeholder="Marker path"  disabled/>
					  <label class="pl_ar_button" id="pl_ar_insert_marker" >Insert Marker (patt)</label>
				
				<div class="pl_ar_object_path_class" style="margin: 30px;">
					<select id="pl_ar_type">
						<option value="face">Face</option>
						<option value="image">Image</option>
						<option value="marker">Marker</option>
						<option value="location">Location</option>
					</select>
					<label class="pl_ar_button">AR Type</label>
				</div>
		    </div>
		  </div>  
		  <div>
		  <div class='pl_ar_shortcode_button_box'>
		    <div class="pl_ar_button_box">	
		      <label id="pl_ar_add_button" class="pl_ar_button">Add item</label>	
		    </div>
		    <div class="pl_ar_button_box">	
		      <label id="pl_ar_add_shortcode_button" class="pl_ar_button">Create shortcode</label>	
		    </div>
		  </div>   
	    <div class="pl_ar_shortcode_box">	
	      <label id="pl_ar_add_shortcode_txt" class="pl_ar_txt"  disabled>.</label>	
	      <label id="pl_ar_copy_link" class="pl_ar_button">Copy shortcode</label>	
	    </div>
		   
	  </div>  
    <?php
  }

  public function custom_button_shortcode($atts)
  {
	  shortcode_atts(
		  [
			  'id'         => '',
			  'name'       => '',
			  'color'      => '',
			  'text-color' => '',
			  'scale'      => '',
			  'rotation'   => '',
			  'option'     => '',
			  'type'       => 'marker',
		  ],
		  $atts
	  );

	  $ButtTextColor = '' == $atts['text-color'] ? '#FFFFFF' : $atts['text-color'];
	  $ButtColor = '' == $atts['color'] ? '#034f85' : $atts['color'];
	  $style = "color:{$ButtTextColor}; background-color: {$ButtColor}";

	  $data = $atts; 
	  unset($data['id'], $data['name'],$data['text-color'],$data['color']);

	  foreach ([
		  'scale'    => '0.05 0.05 0.05',
		  'rotation' => '0 0 0',
		  'position' => '0 0 0',
	  ] as $key => $value) {
		  if (!isset($data[$key]) || '' == $data[$key]) {
			  $data[$key] = $value;
		  }
	  }

	  if ('location' == $data['type']) {
		  $data['look-at'] = $data['look-at'] ?? '[gps-camera]';
	  }

	 
	  $dataStr = '';
	  foreach ($data as $key => $value) {
		  $dataStr .= ' data-'.$key.'="'.$value.'"';
	  }

	  return '<input type="button" class="ar_button" style="'.$style.'" id="'.$atts['id'].'" value="'.$atts['name'].'"'.$dataStr.'/>';
  }
} 