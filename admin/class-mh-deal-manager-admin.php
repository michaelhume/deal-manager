<?php
/**
 * Plugin Name.
 *
 * @package   MH_Deal_Manager_Admin
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 *
 * @package MH_Deal_Manager_Admin
 * @author  Michael Hume <m.p.hume@gmail.com>
 */
if ( ! class_exists( 'MH_Deal_Manager_Admin' ) ) {
	class MH_Deal_Manager_Admin 
	{

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;
	
		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;
	
		/**
		 * Initialize the plugin by loading admin scripts & styles and adding a
		 * settings page and menu.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {
	
			/*
			 * @TODO :
			 *
			 * - Uncomment following lines if the admin class should only be available for super admins
			 */
			/* if( ! is_super_admin() ) {
				return;
			} */
	
			/*
			 * Call $plugin_slug from public plugin class.
			 *
			 */
			$plugin = MH_Deal_Manager::get_instance();
			$this->plugin_slug = $plugin->get_plugin_slug();
	
			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	
			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	
			// Add an action link pointing to the options page.
			$plugin_basename = plugin_basename( MHDM_PLUGIN_DIR . '/' . $this->plugin_slug . '.php' );
			add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
			
			// admin setup
			add_action( 'after_setup_theme', array( $this, 'setup_meta_boxes' ) );	
			add_action( 'after_setup_theme', array( $this, 'check_plugin_dependancies' ) );
			add_action( 'after_setup_theme', array( $this, 'extended_user_profile' ) );
			
	
			/*
			 * Define custom functionality.
			 *
			 * Read more about actions and filters:
			 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
			 */
			add_action( '@TODO', array( $this, 'action_method_name' ) );
			add_filter( '@TODO', array( $this, 'filter_method_name' ) );
	
		}
	
		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {
	
			/*
			 * @TODO :
			 *
			 * - Uncomment following lines if the admin class should only be available for super admins
			 */
			/* if( ! is_super_admin() ) {
				return;
			} */
	
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
	
			return self::$instance;
		}
		
		
		/**
		 * setup_meta_boxes function.
		 *
		 *	Setup the custom meta boxes for our custom post types
		 * 
		 * @access public
		 * @return void
		 */
		public function setup_meta_boxes(){
			require_once( MHDM_PLUGIN_DIR . '/admin/includes/plugin/meta-box/meta-box.php' );
			require_once( MHDM_PLUGIN_DIR . '/admin/includes/extend/meta-boxes.php' );
		}
	
		/**
		 * extended_user_profile function.
		 * 
		 * @access public
		 * @return void
		 * @since    1.0.0
		 */
		public function extended_user_profile(){
			require_once( MHDM_PLUGIN_DIR . '/admin/includes/extend/users.php' );	
		}
		
		
		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles() {
	
			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}
	
			$screen = get_current_screen();
			if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
				wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), MH_Deal_Manager::VERSION );
			}
	
		}
	
		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts() {
	
			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}
	
			$screen = get_current_screen();
			if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {
				wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), MH_Deal_Manager::VERSION );
			}
	
		}
	
		/**
		 * check_plugin_dependancies function.
		 *
		 *	Check for our required plugins
		 *
		 *	@see http://tgmpluginactivation.com/
		 * 
		 * @access public
		 * @return void
		 * @since    1.0.0
		 */
		public function check_plugin_dependancies(){
		
			// check plugin dependancies
			$required = array(
							'groups' => 'Group_Group',
							);
			
			foreach ( $required as $plugin => $class ){
				if ( !class_exists ( $class ) ){
					require_once MHDM_PLUGIN_DIR . '/admin/includes/plugin/class-tgm-plugin-activation.php';
					add_action( 'tgmpa_register', array( $this, 'install_dependancies' ) );
					break;
				}	
			}
		}
		
		/**
		 * install_plugins function.
		 *
		 *	@see http://tgmpluginactivation.com/
		 * 
		 * @access public
		 * @return void
		 * @since    1.0.0
		 */
		public function install_dependancies(){
			
			$plugins = array(
						array(
							'name'     				=> 'groups', 
							'slug'     				=> 'groups', 
							'source'   				=> 'groups.1.4.6.zip', 
							'required' 				=> true, 
							'version' 				=> '1.4.6', 
							'force_activation' 		=> false, 
							'force_deactivation' 	=> true, 
							'external_url' 			=> '', 
						),
				
						// This is an example of how to include a plugin from the WordPress Plugin Repository
						array(
							'name' 		=> 'Capability Manager Enhanced',
							'slug' 		=> 'capability-manager-enhanced',
							'required' 	=> false,
						),
					);
			
			$config = array(
					'domain'       		=> $this->plugin_slug,         								// Text domain - likely want to be the same as your theme.
					'default_path' 		=> MHDM_PLUGIN_DIR . '/admin/includes/plugin/dependancies/',       // Default absolute path to pre-packaged plugins
					'parent_menu_slug' 	=> $this->plugin_slug . '-main', 							// Default parent menu slug
					'parent_url_slug' 	=> 'admin.php', 											// Default parent URL slug
					'menu'         		=> 'install-required-plugins', 	// Menu slug
					'has_notices'      	=> true,                       	// Show admin notices or not
					'is_automatic'    	=> true,					   	// Automatically activate plugins after installation or not
					'message' 			=> '',							// Message to output right before the plugins table
					'strings'      		=> array(
						'page_title'                       			=> __( 'Install Required Plugins', $this->plugin_slug ),
						'menu_title'                       			=> __( 'Install Plugins', $this->plugin_slug ),
						'installing'                       			=> __( 'Installing Plugin: %s', $this->plugin_slug ), // %1$s = plugin name
						'oops'                             			=> __( 'Something went wrong with the plugin API.', $this->plugin_slug ),
						'notice_can_install_required'     			=> _n_noop( 'Deal Manager requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
						'notice_can_install_recommended'			=> _n_noop( 'Deal Manager recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
						'notice_cannot_install'  					=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
						'notice_can_activate_required'    			=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
						'notice_can_activate_recommended'			=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
						'notice_cannot_activate' 					=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
						'notice_ask_to_update' 						=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s)
						'notice_cannot_update' 						=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
						'install_link' 					  			=> _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
						'activate_link' 				  			=> _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
						'return'                           			=> __( 'Return to Required Plugins Installer', $this->plugin_slug ),
						'plugin_activated'                 			=> __( 'Plugin activated successfully.', $this->plugin_slug ),
						'complete' 									=> __( 'All plugins installed and activated successfully. %s', $this->plugin_slug ), // %1$s = dashboard link
						'nag_type'									=> 'error' // Determines admin notice type - can only be 'updated' or 'error'
					)
				);
				
			tgmpa( $plugins, $config );
	
		}

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {
	
			/*
			 * Add a settings page for this plugin to the Settings menu.
			 *
			 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
			 *
			 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
			 *
					 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
			 */
		
			// create a new top level menu for all options
	        $this->plugin_screen_hook_suffix[] = 
	                add_menu_page( 
	                            __('Deal Manager Configuration', $this->plugin_slug ),      // Page Title
	                            __('Deal Manager', $this->plugin_slug),                     // Menu Title
	                            'administrator',                                            // capability
	                            $this->plugin_slug . '-main',                               // page slug
	                            array( $this, 'display_main_admin_page'),            		// callback function for display
	                            'dashicons-share-alt' ,               						// icon url
	                            '99.1'                               	                    // position
	                            );
	                            
	        // add submenu to dashboard
	        $this->plugin_screen_hook_suffix[] = 
	                add_submenu_page( 
	                            $this->plugin_slug . '-main',                       // top level slug
	                            'Shortcodes',                                       // Page title  
	                            'Shortcodes',                                       // menu title
	                            'manage_options',                                  // capability
	                            $this->plugin_slug . '-shortcodes',                 // page slug
	                            array( $this, 'display_shortcodes_admin_page' )             // callback to handle display
	                            );
	
	
		}
	
		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_main_admin_page() {
			include_once( 'views/main.php' );
		}
		
		/**
		 * Render the shortcodes settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_shortcodes_admin_page() {
			include_once( 'views/shortcodes.php' );
		}
	
		/**
		 * Add settings action link to the plugins page.
		 *
		 * @since    1.0.0
		 */
		public function add_action_links( $links ) {
	
			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug .'-main' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
				),
				$links
			);
	
		}
	
		/**
		 * NOTE:     Actions are points in the execution of a page or process
		 *           lifecycle that WordPress fires.
		 *
		 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
		 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
		 *
		 * @since    1.0.0
		 */
		public function action_method_name() {
			// @TODO: Define your action hook callback here
		}
	
		/**
		 * NOTE:     Filters are points of execution in which WordPress modifies data
		 *           before saving it or sending it to the browser.
		 *
		 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
		 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
		 *
		 * @since    1.0.0
		 */
		public function filter_method_name() {
			// @TODO: Define your filter hook callback here
		}
	
	}
}
