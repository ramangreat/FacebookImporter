<?php
/*
Description: This class is for running the internal dependencies of the plugin classes
Version: 1.0
Author: Raman Kumar
*/

class Facebook_Posts_Importer {

	protected $loader;

	protected $admin;

	protected $plugin_slug;

	protected $version; //for version control

	public function __construct() {

		$this->plugin_slug = 'facebook-posts-importer-slug';
		$this->version = '1.0';

		$this->load_dependencies();
		$this->define_admin_hooks();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-facebook-posts-importer-admin.php';

		require_once plugin_dir_path( __FILE__ ) . 'class-facebook-posts-importer-loader.php';
		$this->loader = new Facebook_Posts_Importer_Loader();

	}

	private function define_admin_hooks() {

		$this->admin = new Facebook_Posts_Importer_Admin( $this->get_version(), $_FILES );
		$this->loader->add_action( 'process_file_the_other_way', $this->admin, 'processFile' );
		
	}

	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}

	public function renderForms() {
		$this->admin->renderForms();
	}

}
