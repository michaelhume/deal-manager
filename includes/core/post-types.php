<?php
/**
 * post-types.php
 * Create the custom post types & taxonomy for the system. Each post type can be individually
 *  activated through the theme_support API
 *
 * @package MH_Deal_Manager
 * @author Michael Hume
 * @since 1.0.0
 * @link http://codex.wordpress.org/Function_Reference/register_post_type#Example
 * @license GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 *
 *  @todo add help screens
 */
if ( ! class_exists( 'MH_Deal_Manager_Post_Types' ) ) {
	class MH_Deal_Manager_Post_Types
	{
	    /**
	     * plugin_slug
	     * 
	     * @since 1.0.0
	     * @var mixed
	     * @access private
	     */
	    private $plugin_slug;
	    
	    
		/**
		 * instance
		 *
		 *	Instance of class singleton
		 * 
		 * (default value: null)
		 * 
		 * @var mixed
		 * @access protected
		 * @static
		 */
		protected static $instance = null;
	    
	    /**
	     * registered_types
	     * 
	     * (default value: array())
	     * 
	     * @since 1.0.0
	     * @var array
	     * @access protected
	     */
	    protected $registered_types = array();
	    
	    
	    /**
	     * __construct function.
	     *
	     * @since 1.0.0 
	     * @access public
	     * @return void
	     */
	    private function __construct(){
	        
	        $plugin = MH_Deal_Manager::get_instance();
	        $this->plugin_slug = $plugin->get_plugin_slug();
	                
	        add_action('after_setup_theme', array( $this, 'register_post_types'), 16);         
	        
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
		 * get_registered_types function.
		 * 
		 * since 1.0.0
		 * @access public
		 * @return void
		 */
		public function get_registered_types(){
			return $this->registered_types;
			
		}
	    
	    /**
	     * register_post_types function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public function register_post_types(){

			// register deal cpt	        
			add_action('init', array( $this, 'register_deal_cpt'));
			add_action('init', array( $this, 'register_deal_taxonomy'), 0);
			add_action('admin_head', array( $this, 'add_deal_help'));
			
            // remove default taxonomy box 
			add_action('admin_menu', array( $this, 'remove_deal_type_taxonomy_box')); 

			// register requirement cpt
            add_action('init', array( $this, 'register_requirement_cpt'));
            add_action('init', array( $this, 'register_requirement_taxonomy'), 0);
            add_action('admin_head', array( $this, 'add_requirement_help'));            
	        
			// register associate cpt
            add_action('init', array( $this, 'register_associate_cpt'));
            add_action('init', array( $this, 'register_associate_taxonomy'), 0);
            add_action('admin_head', array( $this, 'add_associate_help'));  

			// register property cpt	        
            add_action('init', array( $this, 'register_property_cpt'));
            add_action('init', array( $this, 'register_property_taxonomy'), 0);
            add_action('admin_head', array( $this, 'add_property_help'));
	
			       
	    }// end __construct function
	    
	    
	    /**
	     * register_post_types_now function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public static function register_post_types_now(){
	    	
	    	$cpt = new self;
	    	
	    	$cpt->register_deal_cpt();
		    $cpt->register_requirement_cpt();
		    $cpt->register_associate_cpt();
		    $cpt->register_property_cpt();
		    
		    $cpt->register_deal_taxonomy();
		    $cpt->register_requirement_taxonomy();
		    $cpt->register_associate_taxonomy();
		    $cpt->register_property_taxonomy();
	    }
	    
	// !--------------- Deal Component
	    
	    /**
	     * Register Deal post type
	     * Do not use before init
	     *
	     * @link http://codex.wordpress.org/Function_Reference/register_post_type
	     * @see register_post_type
	     * @since 1.0.0
	     */	
	    public function register_deal_cpt(){
	        
	        $args = array(
	            'labels'             => $this->create_cpt_labels( 'Deal', 'Deals' ),
	            'public'             => true,
	            'publicly_queryable' => true,
	            'show_ui'            => true,
	            'show_in_menu'       => true,
	            'query_var'          => true,
	            'rewrite'            => array( 'slug' => 'deals', 'with_front' => false ),
	            'has_archive'        => true,
	            'hierarchical'       => true,
	            'menu_position'      => 11,
	            'menu_icon'			 => 'dashicons-portfolio',
	            'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'comments' ),
	            'taxonomies'         => array( 'deal-status', 'deal-source', 'deal-type' ),
	            'map_meta_cap'       => true,
	            'capability_type'    => array( 'deal', 'deals' ),
	           );
	        
	        $r = register_post_type( 'deal', $args );
	        if ( !is_wp_error( $r ) ){
		        $this->set_capabilities('deal');		        
		        $this->registered_types[] = 'deal';
	        }
		}
		
		/**
		 * Create Deal taxonomies
		 * Do not use before init
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
		 * @see register_taxonomy
		 * @since 1.0.0
		 */
		public function register_deal_taxonomy() {
		  
	    	$args = array(
	    		'hierarchical'      => true,
	    		'labels'            => $this->create_taxonomy_labels( 'Deal Status', 'Deal States' ),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'deal-states' ),
	    	);    	
	    	register_taxonomy( 'deal_state', array( 'deal' ), $args );
	
	        $args = array(
	    		'hierarchical'      => false,
	    		'labels'            => $this->create_taxonomy_labels( 'Deal Source' ),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'deal-source' ),
	    	);
	    	register_taxonomy( 'deal_source', array( 'deal' ), $args );
	
			$args = array(
	    		'hierarchical'      => true,
	    		'labels'            => $this->create_taxonomy_labels( 'Deal Type' ),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'deal-type' ),
	    	);
	    	register_taxonomy( 'deal_type', array( 'deal' ), $args );
	
	    }
	    
	    /**
	     * add_deal_help function.
	     *
	     *	add context sensitive help to the deal screen
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public function add_deal_help(){
	        
	        $screen = get_current_screen();
	        // Return early if we're not on our post type.
	        if ( 'deal' != $screen->post_type ){
	            return;
	        }
	        
	        // Setup help tabs
	        $tabs['definitions'] = array(
	            'id'      => $screen->post_type . '-1',						//unique id for the tab
	            'title'   => 'Definitions',                         		//unique visible title for the tab
	            'content' => '<h3>Definitions</h3>'.          				//actual help text
	                            '<p>This page defines a deal - the top level item in our package</p>'.
	                            '<ol>'.
	                                '<li>Step 1</li>'.
	                                '<li>Step 2</li>'.
	                                '<li>Step 3</li>'.
	                                '<li>Step 4</li>'.
	                            '</ol>',  
	            );
	        
	        $tabs['fields'] = array(
	            'id'      => $screen->post_type . '-2', 
	            'title'   => 'Fields',             
	            'content' => '<h3>Field Definitions</h3>'.
	                            '<p>Help content</p>',  
	            );
	        
	        // Add the help tab.
	        foreach ( $tabs as $tab ){
	            $screen->add_help_tab( $tab );            
	        }
	
	    }
	    
	    
	    /**
	     * remove_deal_type_taxonomy_box function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public function remove_deal_type_taxonomy_box(){
		    remove_meta_box('deal_typediv', 'deal', 'normal');
	    }
	    
	// !--------------- Requirement Component
	
	    /**
	     * Register requirement post type
	     * Do not use before init
	     *
	     * @see register_post_type
	     * @since 1.0.0
	     */
	    public function register_requirement_cpt(){
	        
	        $args = array(
	            'labels'             => $this->create_cpt_labels('Requirement'),
	            'public'             => true,
	            'publicly_queryable' => true,
	            'show_ui'            => true,
	            'show_in_menu'       => true,
	            'query_var'          => true,
	            'rewrite'            => array( 'slug' => 'requirement', 'with_front' => false ),
	            'has_archive'        => true,
	            'hierarchical'       => false,
	            'menu_position'      => 11,
	            'menu_icon'			 => 'dashicons-list-view',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions', 'comments', 'page-attributes' ),
	            'taxonomies'         => array( 'req_type', 'req_state'  ),
	            'map_meta_cap'       => true,
	            'capability_type'    => array( 'requirement', 'requirements' ),
	           );
	        
	        $r = register_post_type( 'requirement', $args );
	        if ( !is_wp_error( $r ) ){
		        $this->set_capabilities('requirement');		        
		        $this->registered_types[] = 'requirement';
	        }
		}
		
	    /**
		 * Create requirement taxonomies
		 * Do not use before init
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
		 * @see register_taxonomy
		 * @since 1.0.0
		 */
		public function register_requirement_taxonomy() {
	
	    	$args = array(
	    		'hierarchical'      => false,
	    		'labels'            => $this->create_taxonomy_labels( 'Requirement State' ),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'req-states' ),
	    	);    	
	    	register_taxonomy( 'req_state', array( 'requirement' ), $args );
	
	        $args = array(
	    		'hierarchical'      => false,
	    		'labels'            => $this->create_taxonomy_labels( 'Requirement Type' ),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'req-type' ),
	    	);
	    	register_taxonomy( 'req_type', array( 'requirement' ), $args );
	
	    }
	    
	    
		/**
		 * add_requirement_help function.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function add_requirement_help(){
	        
	        $screen = get_current_screen();
	        // Return early if we're not on our post type.
	        if ( 'requirement' != $screen->post_type ){
	            return;
	        }
	        
	        // Setup help tabs
	        $tabs['definitions'] = array(
	            'id'      => $screen->post_type . '-1',                              //unique id for the tab
	            'title'   => 'Definitions',                         //unique visible title for the tab
	            'content' => '<h3>Definitions</h3>'.          //actual help text
	                            '<p>This page defines a requirement> Requirements are attached tied to deals and clients.</p>'.
	                            '<ol>'.
	                                '<li>Step 1</li>'.
	                                '<li>Step 2</li>'.
	                                '<li>Step 3</li>'.
	                                '<li>Step 4</li>'.
	                            '</ol>',  
	            );
	        
	        $tabs['fields'] = array(
	            'id'      => $screen->post_type . '-2', 
	            'title'   => 'Fields',             
	            'content' => '<h3>Field Definitions</h3>'.
	                            '<p>Help content</p>',  
	            );
	        
	        // Add the help tab.
	        foreach ( $tabs as $tab ){
	            $screen->add_help_tab( $tab );            
	        }
	
	    }
	
	// !--------------- Associate Component
	
	    /**
	     * Register associate post type
	     * Do not use before init
	     *
	     * @see register_post_type
	     * @since 1.0.0
	     */
	    public function register_associate_cpt(){
	         
	        $args = array(
	            'labels'             => $this->create_cpt_labels('Associate'),
	            'public'             => true,
	            'publicly_queryable' => true,
	            'show_ui'            => true,
	            'show_in_menu'       => true,
	            'query_var'          => true,
	            'rewrite'            => array( 'slug' => 'associates', 'with_front' => false ),
	            'has_archive'        => true,
	            'hierarchical'       => false,
	            'menu_position'      => 11,
	            'menu_icon'			 => 'dashicons-groups',
	            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'comments' ),
	            'taxonomies'         => array( 'associate_type' ),
	            'map_meta_cap'       => true,
	            'capability_type'    => array( 'associate', 'associates' ),
	           );
	        
	        $r = register_post_type( 'associate', $args );
	        if ( !is_wp_error( $r ) ){
		        $this->set_capabilities('associate');		        
		        $this->registered_types[] = 'associate';
	        }
		}
		
		/**
		 * Create Associate taxonomies
		 * Do not use before init
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
		 * @see register_taxonomy
		 * @since 1.0.0
		 */
		public function register_associate_taxonomy() {
	  
	    	$args = array(
	    		'hierarchical'      => true,
	    		'labels'            => $this->create_taxonomy_labels('Associate Type'),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'associate-types' ),
	    	);
	    	register_taxonomy( 'associate_type', array( 'associate' ), $args );
	    
	    }
	
		
		/**
		 * add_associate_help function.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function add_associate_help(){
	        
	        $screen = get_current_screen();
	        // Return early if we're not on our post type.
	        if ( 'associate' != $screen->post_type ){
	            return;
	        }
	        
	        // Setup help tabs
	        $tabs['definitions'] = array(
	            'id'      => $screen->post_type . '-1',                              //unique id for the tab
	            'title'   => 'Definitions',                         //unique visible title for the tab
	            'content' => '<h3>Definitions</h3>'.          //actual help text
	                            '<p>Add some content</p>'.
	                            '<ol>'.
	                                '<li>Step 1</li>'.
	                                '<li>Step 2</li>'.
	                                '<li>Step 3</li>'.
	                                '<li>Step 4</li>'.
	                            '</ol>',  
	            );
	        
	        $tabs['fields'] = array(
	            'id'      => $screen->post_type . '-2', 
	            'title'   => 'Fields',             
	            'content' => '<h3>Field Definitions</h3>'.
	                            '<p>Help content</p>',  
	            );
	        
	        // Add the help tab.
	        foreach ( $tabs as $tab ){
	            $screen->add_help_tab( $tab );            
	        }
	
	    }
	    
	// !--------------- Property Component    
	
	    /**
	     * Register property type
	     * Do not use before init
	     *
	     * @see register_post_type
	     * @since 1.0.0
	     */	
	    public function register_property_cpt(){
	                
	        $args = array(
	            'labels'             => $this->create_cpt_labels('Property', 'Properties'),
	            'public'             => true,
	            'publicly_queryable' => true,
	            'show_ui'            => true,
	            'show_in_menu'       => true,
	            'query_var'          => true,
	            'rewrite'            => array( 'slug' => 'properties', 'with_front' => false ),
	            'has_archive'        => true,
	            'hierarchical'       => false,
	            'menu_position'      => 11,
	            'menu_icon'			 => 'dashicons-admin-home',
	            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'comments' ),
	            'taxonomies'         => array( 'property_type', 'property_price_range' ),
	            'map_meta_cap'       => true,
	            'capability_type'    => array( 'property', 'properties' ),
	           );
	        
	        $r = register_post_type( 'property', $args );
	        if ( !is_wp_error( $r ) ){
		        $this->set_capabilities('property');		        
		        $this->registered_types[] = 'property';
	        }
		}
		
		/**
		 * Create property taxonomies
		 * Do not use before init
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
		 * @see register_taxonomy
		 * @since 1.0.0
		 */
		public function register_property_taxonomy() {
	
		 	$args = array(
	    		'hierarchical'      => false,
	    		'labels'            => $this->create_taxonomy_labels('Property Type'),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'property-type' ),
	    	);
	    	register_taxonomy( 'property_type', array( 'property' ), $args );
	 	
	    	$args = array(
	    		'hierarchical'      => false,
	    		'labels'            => $this->create_taxonomy_labels('Property Price Range'),
	    		'show_ui'           => true,
	    		'show_admin_column' => true,
	    		'query_var'         => true,
	    		'rewrite'           => array( 'slug' => 'property-price-type' ),
	    	);
	    	register_taxonomy( 'property_price_type', array( 'property' ), $args );
	    }
	    
	    
	    /**
	     * remove_mask_taxonomy_box function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	   // public function remove_mask_taxonomy_box(){
	   //      remove_meta_box('tagsdiv-mask_state', 'mask', 'normal');
	   // }
	    
	    
	    /**
	     * add_property_help function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public function add_property_help(){
	        
	        $screen = get_current_screen();
	        // Return early if we're not on our post type.
	        if ( 'property' != $screen->post_type ){
	            return;
	        }
	        
	        // Setup help tabs
	        $tabs['definitions'] = array(
	            'id'      => $screen->post_type . '-1',                              //unique id for the tab
	            'title'   => 'Definitions',                         //unique visible title for the tab
	            'content' => '<h3>Definitions</h3>'.          //actual help text
	                            '<p>Add some content</p>'.
	                            '<ol>'.
	                                '<li>Step 1</li>'.
	                                '<li>Step 2</li>'.
	                                '<li>Step 3</li>'.
	                                '<li>Step 4</li>'.
	                            '</ol>',  
	            );
	        
	        $tabs['fields'] = array(
	            'id'      => $screen->post_type . '-2', 
	            'title'   => 'Fields',             
	            'content' => '<h3>Field Definitions</h3>'.
	                            '<p>Help content</p>',  
	            );
	        
	        // Add the help tab.
	        foreach ( $tabs as $tab ){
	            $screen->add_help_tab( $tab );            
	        }
	
	    }
		    
	// --- Private helper functions -->    
	    
	    /**
		 * Private function to add capabilities to the user roles
		 *  only do this once
		 *
		 * @link http://codex.wordpress.org/Function_Reference/get_role
		 * @link http://codex.wordpress.org/Function_Reference/add_cap
		 * @since 1.0.0
		 */
	    private function set_capabilities($type, $role = 'administrator'){
	        
	        if ( get_option( $this->plugin_slug . '-caps-' . $type . '-' . $role ) ){
	            return;    
	        }
	        
	        $role_obj = get_role( $role );
	        $post_obj = get_post_type_object( $type );
	        
	        if ( property_exists(  $post_obj, 'cap' ) ){
	            foreach ( get_object_vars( $post_obj->cap ) as $cap ){
	                $role_obj->add_cap( $cap );
	            }
	            add_option( $this->plugin_slug . '-caps-' . $type . '-' . $role, true, '', 'no' );
	        }
	        
	    }
	    
	    
	    /**
	     * create_taxonomy_labels function.
	     * 
	     * @since 1.0.0
	     * @access private
	     * @param string $singular
	     * @param string $plural (default: false)
	     * @return array
	     */
	    private function create_taxonomy_labels( $singular, $plural = false ){
	        
	        if ( !$plural ) { $plural = $singular . 's'; }
	        return array(
	    		'name'              => _x( $plural, 'taxonomy general name' ),
	    		'singular_name'     => _x( $singular, 'taxonomy singular name' ),
	    		'search_items'      => __( 'Search ' . $plural, $this->plugin_slug ),
	    		'all_items'         => __( 'All ' . $plural, $this->plugin_slug ),
	    		'parent_item'       => __( 'Parent ' . $singular, $this->plugin_slug ),
	    		'parent_item_colon' => __( 'Parent ' . $singular .':', $this->plugin_slug ),
	    		'edit_item'         => __( 'Edit ' . $singular, $this->plugin_slug ),
	    		'update_item'       => __( 'Update ' . $singular, $this->plugin_slug ),
	    		'add_new_item'      => __( 'Add New ' . $singular, $this->plugin_slug ),
	    		'new_item_name'     => __( 'New ' . $singular . ' Name', $this->plugin_slug ),
	    		'menu_name'         => __( $plural , $this->plugin_slug ),
	    	);
	    }
	    
	    
	    /**
	     * create_cpt_labels function.
	     * 
	     * @since 1.0.0
	     * @access private
	     * @param string $singular
	     * @param string $plural (default: false)
	     * @return array
	     */
	    private function create_cpt_labels( $singular, $plural = false ){
	        if ( !$plural ) { $plural = $singular . 's'; }
	        return array(
	            'name'               => __( $plural, $this->plugin_slug),
	            'singular_name'      => __( $singular, $this->plugin_slug),
	            'add_new'            => __( 'Add New', $this->plugin_slug),
	            'add_new_item'       => __( 'Add New ' . $singular, $this->plugin_slug),
	            'edit_item'          => __( 'Edit ' . $singular, $this->plugin_slug),
	            'new_item'           => __( 'New ' . $singular, $this->plugin_slug),
	            'all_items'          => __( 'All ' . $plural, $this->plugin_slug),
	            'view_item'          => __( 'View ' . $singular, $this->plugin_slug),
	            'search_items'       => __( 'Search ' . $plural, $this->plugin_slug),
	            'not_found'          => __( 'No '. $plural .' found', $this->plugin_slug),
	            'not_found_in_trash' => __( 'No '. $plural .' found in Trash', $this->plugin_slug),
	            'parent_item_colon'  => '',
	            'menu_name'          => __( $plural, $this->plugin_slug),
	          );  
	    } 
	}    	
}
$obj = MH_Deal_Manager_Post_Types::get_instance();

?>