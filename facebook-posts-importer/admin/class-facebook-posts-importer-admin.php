<?php
/*
Description: This class is for importing the posts from a facebook json file.
Version: 1.0
Author: Raman Kumar
*/

class Facebook_Posts_Importer_Admin {

    private $mimes   = array("application/json", "application/octet-stream");
    private $success = false;
    private $error;
    private $preview_num = 200;
    private $version;

    function __construct($version = null, $file = null) {

    	$this->version = $version;
        if(!empty($file['uploaded'])) {
            $this->processFile($file['uploaded']);
        }

    }

    public function renderForms() { 
        echo '<div id="response">'.$this->getErrorMsg().'</div>'; 
        echo $this->getForm();
    }

    public function getSuccess() {
        return $this->success;
    }

    public function getErrorMsg() {
        return $this->error;
    }

    private function validateFileType($type) {

        $valid = false;

        foreach($this->mimes as $mime) {
            if($type == $mime) { 
                $valid = true;
                break;
            }
        }

        if(!$valid) {
            $this->error = '<span class="error">File is not a JSON format</span>';
            $valid = false;
        } 
        
        return $valid;
    }

    public function getForm() {

            return '<form enctype="multipart/form-data" method="POST">
            Please upload facebook json: <input name="uploaded" type="file" />
            <input type="submit" value="Upload" />
            </form>';
    }


    //PROCESS THE JSON FILE FOR IMPORTING POSTS
    public function processFile($file = null) {
        
        global $argv, $argc;// will be used for parsing json from command line

        if($this->validateFileType($file['type'])) {

            $this->error = '<span class="error">Something went wrong.</span>';
            $target = UPLOADS . basename( $file['name']); 
 
            if(move_uploaded_file($file['tmp_name'], $target)) {
                $this->success = true;
                $this->error = '<span>' . $file['name'] . " File uploaded Successfully</span>";
                
            }
        }
        
        //$target var can be used for command line file
        $contents = file_get_contents( $target ); 
        
        //clean the json code
        $slices = $this->json_clean_decode( $contents );
        
        $i = 0;
        foreach ($slices->data as $value) {
            
            $picture = property_exists($value, 'picture') ? $value->picture : '';

            //fetch the url parameter from the picture property
            $parts = parse_url($picture);
            parse_str($parts['query'], $query);
            if( isset( $query['url'] ) ){
                $imageurl = $query['url']; 
            } else {
                $imageurl = '';
            }

             
            $title = property_exists($value, 'name') ? $value->name : '';
            $content = property_exists($value, 'message') ? $value->message : ''; //Because in maximum posts no description was there
            
            //call save function of the class
            $this->save_post_to_database( $title, $content, $imageurl );
            $i++;

        }
        echo $i." Posts saved Successfully!!!!";

    }

    //CLEAN THE JSON CODE
    public function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0) { 

        // search and remove comments like /* */ and // 
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t](//).*)#", '', $json); 
        
        if(version_compare(phpversion(), '5.4.0', '>=')) { 
            $json = json_decode($json, $assoc, $depth, $options); 
            
        } 
        elseif(version_compare(phpversion(), '5.3.0', '>=')) { 
            $json = json_decode($json, $assoc, $depth); 
        } 
        else { 
            $json = json_decode($json, $assoc); 
        } 

        return $json; 
    } 


    //SAVING THE POSTS IN THE WORDPRESS DATABASE
    public function save_post_to_database( $title='', $content='', $image_url =''){

        // Create post object
        $my_post = array(
             'post_title'    =>   htmlentities($title),
             'post_content'  =>  $content ,
             'post_status'   => 'publish',
             'post_author'   => 1,
             'post_category' => array(8,39)
        );

        // Insert the post into the database and return the new post ID
        $post_id = wp_insert_post( $my_post );


        //download and set the featured image of the post
        if($image_url != '' && ($filecontent = file_get_contents($image_url) !== false)){
            
            $image_data = file_get_contents($image_url); //for bigger images I would prefer fgets 
            $filename = basename( $image_url );
            if( wp_mkdir_p( IMAGE_UPLOADS_DIR ) )
                $file = IMAGE_UPLOADS_DIR . '/' . $filename;
            else 
                $file = IMAGE_UPLOADS_BASEDIR . '/' . $filename;
            
            file_put_contents($file, $image_data);

            $file_url = IMAGE_UPLOADS_URL . '/' . $filename; echo "\n";
            $wp_filetype = wp_check_filetype($filename, null );

            $attachment = array(
                'guid' => $file_url,
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            set_post_thumbnail( $post_id, $attach_id );
        }
    }
    
}

//FOR UNIT TESTING
if( ! array_key_exists( 'facebook-posts-importer', $GLOBALS ) ) { 
    // Store a reference to the plugin in GLOBALS so that our unit tests can access it
    $GLOBALS['facebook-posts-importer'] = new Facebook_Posts_Importer_Admin();

}//end





