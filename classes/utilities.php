<?php
namespace pl_ar_utilities;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
}

class pl_ar_Utilities  {

    public function __construct() {
      add_action( 'init', array($this, 'pl_ar_plugin_update'));
      add_action( 'wp_enqueue_scripts',array($this, 'pl_ar_my_enqueue_frontend'));
      add_action( 'wp_enqueue_scripts',array($this, 'pl_ar_my_enqueue_frontend_button'), 100000);
      add_action( 'admin_menu',array($this, 'pl_ar_settings_menu')); 
      add_action('wp_ajax_pl_ar_ajax_database_delete',array($this, 'pl_ar_ajax_database_delete'));
      add_action("wp_ajax_nopriv_pl_ar_ajax_database_delete",array($this, "pl_ar_ajax_database_delete"));
      //js and css for file manager
      add_action( 'admin_enqueue_scripts',array($this, 'pl_ar_my_enqueue_fm_admin'));
      add_action('wp_ajax_pl_ar_current_option',array($this,'pl_ar_current_option'));
      add_action('wp_ajax_nopriv_pl_ar_current_option',array($this,'pl_ar_current_option'));
      add_filter( 'page_template', array($this,'pl_ar_redirect_page_template' ));
      add_action( 'pre_get_posts' ,array($this,'pl_ar_exclude_ar_page' ));
    }

    //Plugin Update (since version 1.2.0 a table update takes place at plugin update)
    public function pl_ar_plugin_update(){
      global $wpdb;
      $new_ar_version='1.2.0';/////////////////////////////////////////////////////////new version number(also update it in pl_ar_plugin_activation)
      if (get_option("plugin_ar_version")!=$new_ar_version){
        update_option("plugin_ar_version", $new_ar_version);

       // check if color columns exists
        $BackGroundColors= $wpdb->get_results(  "SELECT buttonBackGroundColor FROM {$wpdb->prefix}pl_ar_table"  );
        if(empty($BackGroundColors)){
           $wpdb->query("ALTER TABLE {$wpdb->prefix}pl_ar_table ADD buttonBackGroundColor text NOT NULL");
        }

        $TextColors= $wpdb->get_results(  "SELECT buttonTextColor FROM {$wpdb->prefix}pl_ar_table"  );
        if(empty($TextColors)){
           $wpdb->query("ALTER TABLE {$wpdb->prefix}pl_ar_table ADD buttonTextColor text NOT NULL");
        }
      } 
      
    }

    //Plugin Activation
    public function pl_ar_plugin_activation(){
      //add the options
      delete_option('pl_ar_current_id');
      add_option('pl_ar_current_id', '');
      delete_option('pl_ar_current_scale');
      add_option('pl_ar_current_scale', '');
      delete_option('pl_ar_current_rotation');
      add_option('pl_ar_current_rotation', '');
      delete_option("plugin_ar_version");
      add_option("plugin_ar_version", '1.2.0');//////////////////////////plugin version

      global $wpdb;
      $the_page_title = 'AR_page';
      $the_page_name = 'ar_page';

      $the_page = get_page_by_title( $the_page_title );

      if ( ! $the_page ) {

          // Create post-page object
          $_p = array();
          $_p['post_title'] = $the_page_title;
          $_p['post_content'] = "[ar_page]";
          $_p['post_status'] = 'publish';
          $_p['post_type'] = 'page';
          $_p['comment_status'] = 'closed';
          $_p['ping_status'] = 'closed';
          $_p['post_category'] = array(1); // the default 'Uncatrgorised'
          $_p['page_template']  = 'ar_template.php';

          // Insert the post into the database
          $the_page_id = wp_insert_post( $_p );

      }
      else {
          // the plugin may have been previously active and the page may just be trashed...

          $the_page_id = $the_page->ID;

          //make sure the page is not trashed...
          $the_page->post_status = 'publish';
          $the_page_id = wp_update_post( $the_page );

      }

      delete_option( 'my_plugin_page_id' );
      add_option( 'my_plugin_page_id', $the_page_id );
      //create the ar page
      
    }
    
    //Plugin deactivation
    public function pl_ar_plugin_deactivation(){
       global $wpdb;

      //  the id of our page...
      if( get_page_by_title('AR_page') ) {
        // Delete Ar page.
        $page = get_page_by_title('AR_page');//get the id by the title
        wp_delete_post( $page->ID, false); // Set to False if you want to send them to Trash.
      }
      delete_option('pl_ar_current_id');
      delete_option('pl_ar_current_scale');
      delete_option('pl_ar_current_rotation');
      delete_option("plugin_ar_version"); 
      delete_option("pl_ar_current_options");
    }

    //update current options for plugin
    public function pl_ar_current_option(){
      check_ajax_referer( 'pl_ar_ajax_nonce', 'security' );
      update_option( 'pl_ar_current_id', sanitize_text_field($_POST['selector'] ));
      // update_option( 'pl_ar_current_scale', sanitize_text_field($_POST['scale'] ));
      // update_option( 'pl_ar_current_rotation', sanitize_text_field($_POST['rotation'] ));

      if(isset($_POST['options']) && is_array($_POST['options'])){
        $options = [];

        foreach ($_POST['options'] as $key => $value) {
           $key = sanitize_text_field($key);
           $options[$key] = sanitize_text_field($value);
        }

        update_option( 'pl_ar_current_options', $options);
      }

      //respond with ar page link
      $ar_page_link=get_permalink( get_page_by_title( 'AR_page' ) );
      echo $ar_page_link;
      die();
    }

    //redirect path for custom ar template
    public function pl_ar_redirect_page_template ($page_template) {
      if ( is_page( 'AR_page' ) ) {
            $page_template = PL_AR_PATH. 'ar_template.php';
        }
      return $page_template;
    }
    
    //hide ar page from edit list
    function pl_ar_exclude_ar_page( $query ) {
      if( !is_admin() )
          return $query;
      global $pagenow;
      $page = get_page_by_title('AR_page');//get the id by the title
      if( 'edit.php' == $pagenow && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ) )
          $query->set( 'post__not_in', array($page->ID) ); // array page ids
      return $query;
    }

    public function pl_ar_my_enqueue_fm_admin(){
      wp_enqueue_style( 'sfm-admin-normalize',  PL_AR_LINK.'css/normalize.css' );
      wp_enqueue_style( 'sfm-admin-cosmostrap',  PL_AR_LINK.'css/cosmostrap.css' );
      wp_enqueue_style( 'sfm-admin-jquery-ui', PL_AR_LINK.'css/jquery-ui.css', false, '1.0.0' );
      wp_enqueue_style( 'sfm-admin-elfinder', PL_AR_LINK.'vendor/elfinder/css/elfinder.full.css', false, '1.0.0' );
      wp_enqueue_style( 'sfm-admin-theme', PL_AR_LINK.'vendor/elfinder/themes/windows-10/css/theme.css', false, '1.0.0' );
      wp_enqueue_style( 'sfm-admin-style', PL_AR_LINK.'css/plugin-admin-style.css' );
      wp_enqueue_script( 'jquery-ui-dialog' ); 
      wp_enqueue_script( 'jquery-ui-draggable' );
      wp_enqueue_script( 'jquery-ui-droppable' );
      wp_enqueue_script( 'jquery-ui-resizable' );
      wp_enqueue_script( 'jquery-ui-selectable' );
      wp_enqueue_script( 'jquery-ui-button' );
      wp_enqueue_script( 'jquery-ui-slider' );
      wp_enqueue_script( 'sfm-admin-popper',  PL_AR_LINK.'js/popper.min.js' );
      wp_enqueue_script( 'sfm-admin-bootstrap',  PL_AR_LINK.'js/bootstrap.min.js' );
      wp_enqueue_script( 'sfm-admin-elfinder-min', PL_AR_LINK.'vendor/elfinder/js/elfinder.min.js' );
      wp_enqueue_script( 'sfm-admin-editor-default', PL_AR_LINK.'vendor/elfinder/js/extras/editors.default.min.js' );
      wp_enqueue_script( 'sfm-admin-vendor-script', PL_AR_LINK.'vendor/elfinder/js/script.js' );
      wp_localize_script('sfm-admin-vendor-script', 'elfScript', array('pluginsDirUrl' => plugin_dir_url( dirname( __FILE__ ) ),));
      wp_enqueue_script( 'sfm-admin-script',  PL_AR_LINK.'js/plugin-admin-script.js' );
      wp_enqueue_script( 'shortcode_script', PL_AR_LINK.'/js/shortcode.js', array('jquery') );
    }

    //add table for plugin
    //add table at activation
    public function pl_ar_table_activation() {
      global $wpdb;
      $table_name = $wpdb->prefix . "pl_ar_table";
      $charset_collate = $wpdb->get_charset_collate();
    
      $sql = "CREATE TABLE  $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        objects text NOT NULL,
        markers text NOT NULL,
        shortcode_id text NOT NULL,
        shortcode_text text NOT NULL,
        buttonName text NOT NULL,
        buttonBackGroundColor text NOT NULL,
        buttonTextColor text NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }
    

    //remove table at deactivation
    public function pl_ar_table_deactivation() {                    
      global $wpdb;
      $table_name= $wpdb->prefix . 'pl_ar_table';
      $sql = "DROP TABLE IF EXISTS $table_name";
      $wpdb->query($sql);
      
    }
    
    //general enqueue function (frontend)
    public function pl_ar_my_enqueue_frontend() {
        if ( ! did_action( 'wp_enqueue_media' ) ) {
            // wp_enqueue_media();
        }
        //scripts for ar page only
        // $page = get_page_by_title('AR_page');
        // if ( $page->ID == get_the_ID() ){
        //   wp_enqueue_script( 'aframe_min', PL_AR_LINK.'js/aframe-master.js' );
        //   wp_enqueue_script( 'aframe-ar', PL_AR_LINK.'js/aframe-ar.js' );
        //   wp_enqueue_script( 'aframe-extras', PL_AR_LINK.'js/aframe-extras.loaders.min.js' );
        //   wp_enqueue_script( 'aframe-resize', PL_AR_LINK.'js/resize.js' );
        // }

        //js scripts
        wp_enqueue_script( 'main_script', PL_AR_LINK.'js/script.js', array('jquery') );
        wp_localize_script( 'main_script', 'pl_ar_ajax_params', array('pl_ar_nonce' => wp_create_nonce('pl_ar_ajax_nonce'),'ajaxurl' => admin_url( 'admin-ajax.php' )));
    }
     
     //style for ar button
     public function pl_ar_my_enqueue_frontend_button() {
        //css for frontend button
        wp_enqueue_style( 'front-button-style', PL_AR_LINK.'css/ar_button.css' );
        //progress bar css
        wp_enqueue_style( 'ar_front-style', PL_AR_LINK.'css/ar_front.css' );
    }

    //add ar plugin menu items
    public function pl_ar_settings_menu() {
      add_menu_page( 'Ar_plugin_menu', 'AR plugin', 'administrator', 'ar_plugin',array($this, 'pl_ar_help_page'),PL_AR_LINK.'icon_menu.png');
      //submenus
      add_submenu_page( 'ar_plugin','File manager','File manager', 'administrator', 'pl_ar_fm',array($this, 'pl_ar_file_manager_page_html'));
      add_submenu_page( 'ar_plugin','Shortcode_creator','Shortcode creator', 'administrator', 'shortcode_creator',array('pl_ar_shortcode\pl_ar_Shortcode','pl_ar_shortcode_page_html'));
      add_submenu_page( 'ar_plugin','pl_ar_database','Database', 'administrator', 'pl_ar_database',array('pl_ar_utilities\pl_ar_Utilities','pl_ar_display_database'));
     
    }
    
    //function for displaying file manager
    public function pl_ar_file_manager_page_html(){
      ?>
      <div class="sfm-wrapper container" style="padding: 20px 0;">
          <div id="elfinder"></div>
      </div>
      <?php
    }

    public function pl_ar_display_database(){
      global $wpdb;
      $result = $wpdb->get_results("SELECT objects, markers,shortcode_text,shortcode_id FROM {$wpdb->prefix}pl_ar_table"); 
      $html = "<h1 style='text-align:center'>List of all ar plugin shortcodes</h1>";   
      $html .= "<table id='pl_ar_database_table' style='margin: 50px; width:90%'>";
      $html .="<tr>";
      $html .="<th style='background-color:#669999; border: 1px solid black;padding: 5px;'>Objects</th>";
      $html .="<th style='background-color:#669999; border: 1px solid black;padding: 5px;'>Marker</th>";
      $html .="<th style='background-color:#669999; border: 1px solid black;padding: 5px;'>Shortcode</th>";
      $html .="<th style='background-color:#669999; border: 1px solid black;padding: 5px;'></th>";
      $html .="</tr>";   
      foreach($result as $r){
        $html .="<tr>";
        $count=1;
        foreach ($r as $d){   
          if($count == 4){
            $html .="<td style='border-bottom: 1px solid black;padding: 5px; width:15%'>
            <label class='pl_ar_button_dat' id=".esc_html($d)." >Delete</label>
            <label class='pl_ar_button_dat' id=".esc_html($d)." >Copy shortcode</label>
            </td>";
          }
          else{
             $html .="<td style='border-bottom: 1px solid black;padding: 5px;'>".esc_html($d);
          }
          $count++;
          $html .="</td>";
        }
        $html .="</tr>";
      }
      $html .="</table>";
      echo $html;
    } 
    
    public function pl_ar_ajax_database_delete(){
      check_ajax_referer( 'pl_ar_admin_ajax_nonce', 'security' );
      global $wpdb;
      $wpdb->delete( $wpdb->prefix.'pl_ar_table',array( 'shortcode_id' => sanitize_text_field($_POST['button_id']) )  );
      echo sanitize_text_field($_POST['button_id']);
      die();
    }

    public function pl_ar_help_page(){
      ?>
      <div style=" width:70%; padding-top:20px; margin-left: 20px;">

        <h1>Wordpress augmented reality plugin</h1>
        <p>  Wordpress augmented reality plugin is based on  <a href="https://github.com/jeromeetienne/AR.js/blob/master/README.md">AR.js</a> a lightweight library for Augmented Reality on the Web and can be used to integrate marker based augmented reality on your wordpress (https only) site with images and gltf files</p>

        <h2><br>Marker based augmented reality</h2>
        <p>The most simple explanation for the term "Marked based augmented reality" is that you have to print the marker images, place them in your physical environment and then point the camera on them for displaying the augmented reality objects, this can be very useful for the above applications:</p>
        <ul>
          <li>Training and educational</li>
          <li>Product marketing</li>
          <li>Engineering intructions</li>
          <li>And more</li>
        </ul>  
        
        <h2><br>Using the plugin</h2>
        <p> The main function of this plugin is to create a shortcode-button where you can paste anywhere in your site, this shortcode-button will create a link to a page where the augmented reality will run. For creating the shortcode you can run the editor at the shortcode creator page, just choose a marker and an object, then click on the create shortcode button, a shortcode will be created with the form:<h4>[ar-plugin id="someId" name="someName" ]</h4> Finally, paste the shortcode at your page and a button will appear on the frontend page with the link for the augmented reality page </p>

        <h2><br>File manager</h2>
        <p>The file manager page uses a simple file manager for uploading and editing your augmented reality objects and your marker files.There are 3 main folders:</p>
        <ul>
          <li>Objects, upload any image or gltf file to the objects folder so you can choose it from the shortcode creator</li>
          <li>Markers, If you want a your own marker you can upload the patt file on the marker folder, for creating your markers you can visit <a href="https://jeromeetienne.github.io/AR.js/three.js/examples/marker-training/examples/generator.html">this</a> page. </li>
          <li>Images, the image folder is for storing your markers as an image</li>
        </ul> 


        <h2><br>More settings</h2>
        <ul>
          <li>You can use multiple pairs of marker-object for one physical plane by clicking add item on the shortcode creator</li>
          <li>You can view and edit all created shortcodes on the database page</li>
          <li>You can manually scale the augmented reality object by adding the scale attribute in your shortcode, for example [ar-plugin id="someId" name="someName" scale="2"] this will double the size, by default the object will autoscale to 2</li>
          <li>You can manually rotate the augmented reality object by adding the rotation attribute in your shortcode, for example [ar-plugin id="someId" name="someName" rotation=["45 90 120"] this will rotate the object 45 degrees on x axis, 90 degrees on y axis and 120 degrees on z axis</li>
        </ul> 
        <p></p>
        
      </div>
      <?php 
    }

   
}