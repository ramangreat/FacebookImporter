<?php
require_once( '../admin/class-facebook-posts-importer-admin.php' );
 
class Tests_Facebook_posts_importer extends WP_UnitTestCase {

	private $fbpImporter;
 
    function setUp() {
         
        parent::setUp();
        $this->fbpImporter = $GLOBALS['facebook-posts-importer'];
     
    } // end setup
     
    function testPluginInitialization() {
        $this->assertFalse( null == $this->fbpImporter );
    } // end 
 
} // end class

