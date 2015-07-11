<?php
/*
Plugin Name: Facebook Posts Importer
Plugin URI: 
Description: This plugin is for importing facebook posts from facebook json
Version: 1.0
Author: Raman Kumar
Author URI: 
Author Email: raman.mca2007@gmail.com
*/

$upload_dir = wp_upload_dir(); //getting the path to main images uploads folder 
$image_uploads_dir = $upload_dir['path'];
$image_uploads_base_dir = $upload_dir['basedir'];
$image_uploads_url = $upload_dir['url'];

define("IMAGE_UPLOADS_DIR", $image_uploads_dir);
define("IMAGE_UPLOADS_BASEDIR", $image_uploads_base_dir);
define("IMAGE_UPLOADS_URL", $image_uploads_url);

$uploads = plugin_dir_path( __FILE__ ).'uploads/';
define("UPLOADS", $uploads);
if ( ! defined( 'WPINC' ) ) {
  die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-facebook-posts-importer.php';

function run_facebook_posts_importer() {

  $fpi = new Facebook_Posts_Importer();
  $fpi->run();
  $fpi->renderForms();

}

add_action( 'admin_menu', 'my_plugin_menu' );

function my_plugin_menu() {
  add_options_page( 'My Plugin Options', 'Facebook Posts Importer', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
}
function my_plugin_options() {
?>
<h3>Upload JSON file</h3>
<?php
    run_facebook_posts_importer();
}
?>
