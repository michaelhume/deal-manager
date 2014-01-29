<?php
/**
 * Deal Manager.
 *
 * @package   MH_Deal_Manager
 * @author    Michael Hume <m.p.hume@gmail.com>
 * @license   GPL-2.0+
 * @link      vivahume.com
 * @copyright 2014 Michael Hume
 */

/**
 * 
 *	Actions:    http://codex.wordpress.org/Plugin_API#Actions
 *	Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
 *	Filters: http://codex.wordpress.org/Plugin_API#Filters
 *  Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
 *
 *
 * @package MH_Deal_Manager
 * @author  Michael Hume <m.p.hume@gmail.com>
 */
if ( ! class_exists( 'MH_Deal_Manager' ) ) {
	class MH_Deal_Manager {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'mh-deal-manager';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		

		// setup the plugin
		add_action( 'after_setup_theme', array( $this, 'check_plugin_dependancies' ) );
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		add_action( 'after_setup_theme', array( $this, 'load_custom_post_types' ) );
		
		// add shortcodes
		//add_shortcode( 'short_code_here', array( $this, 'shortcode_handler' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
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
	 * check_plugin_dependancies function.
	 *
	 *	Check for our required plugins
	 *
	 *	@see http://tgmpluginactivation.com/
	 * 
	 * @access public
	 * @return void
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
	 */
	public function install_dependancies(){
		
		$plugins = array(
					array(
						'name'     				=> 'groups', 
						'slug'     				=> 'groups', 
						'source'   				=> MHDM_PLUGIN_DIR . '/admin/includes/dependancies/groups.1.4.6.zip', 
						'required' 				=> true, 
						'version' 				=> '1.4.6', 
						'force_activation' 		=> true, 
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
				'default_path' 		=> MHDM_PLUGIN_DIR . '/admin/includes/dependancies/',       // Default absolute path to pre-packaged plugins
				'parent_menu_slug' 	=> 'themes.php', 				// Default parent menu slug
				'parent_url_slug' 	=> 'themes.php', 				// Default parent URL slug
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
	 * load_custom_post_types function.
	 * 
	 * @access public
	 * @return void
	 */
	public function load_custom_post_types(){
		require_if_theme_supports( $this->plugin_slug . '-post-types', MHDM_PLUGIN_DIR . '/includes/core/post-types.php' );	
	}
	
	
	/**
	 * add_theme_support function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_theme_support(){
		
		add_theme_support(
     		$this->plugin_slug .'-post-types',
    		array(  'deal', 
    		        'requirement', 
    		        'associate', 
    		        'property',
    		       )
    	);
    	
    	add_theme_support(
    	    $this->plugin_slug .'-custom-meta'
    	);
	}

	/**
	 * Function to handle [all-shortcodes]
	 *
	 *  Passes shortcode off to our shortcode class
	 *
	 *  @since    1.00.00
	 *  @return     null
	 */
	
	public function shortcode_handler( $atts, $content, $tag ){
    	require_once( MHDM_PLUGIN_DIR . '/includes/classes/class-shortcodes.php' );
    	return MHDM_Shortcodes::shortcode( $atts, $content, $tag );
    	
	}
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated1
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 *
	 *	@TODO remove costom caps during deactivation (setup in post-types)
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}


	
	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}
}
}
