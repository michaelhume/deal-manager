<?php
/**
 * meta-boxes.php
 *
 *  Create the additional meta boxes for our custom post types.
 *  This file basically outlines our data model for the entire site
 *  in conjunction with the post-types
 *
 * @package MH_Deal_Manager
 * @author Michael Hume
 * @since 1.0.0
 * @link http://www.deluxeblogtips.com/meta-box/
 * @link https://github.com/rilwis/meta-box
 * @license GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 *
 * @todo add meta data for all custom post types
 * @todo add validation
 *
 *  Available hooks for the RW Meta Box Plugin
 *
 *  Inside meta box:
 *  Before:
 *      do_action( 'rwmb_before' );                                                  <-- all boxes
 *      add_action( "rwmb_before_[$meta-box-id]", array($this, 'my_function') )      <-- target a single box
 *  After:
 *      do_action( 'rwmb_after' );
 *      do_action( "rwmb_after_[$meta-box-id]", array($this, 'my_function') );
 *  Before Save Actions:        
 *      do_action( 'rwmb_before_save_post', $post_id );                                 <-- all
 *      do_action( "rwmb_[$meta-box-id]_before_save_post", $post_id );         <-- specific meta-box
 *  After save action
 *      do_action( 'rwmb_after_save_post', $post_id );
 *      do_action( "rwmb_[$meta-box-id]_after_save_post", $post_id );
 */


/**
 * NanofabMetaBoxes class.
 */
class MH_Deal_Manager_MetaBoxes{

    /**
     * prefix
     * 
     * (default value: Nanofab::PREFIX)
     * 
     * @var mixed
     * @access public
     */
    private $plugin_slug;
    
    /**
     * meta_boxes
     * 
     * (default value: array())
     * 
     * @var array
     * @access private
     */
    private $meta_boxes = array();
    
    /**
     * post_id
     * 
     * @var mixed
     * @access private
     */
    private $post_id;
    
    
    /**
     * defaults
     * 
     * (default value: array())
     * 
     * @var array
     * @access private
     */
    private $default = array();
    
    /**
     * __construct function.
     * 
     * @access public
     * @return void
     */
    public function __construct(){
    	$plugin = MH_Deal_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

        add_action( 'admin_init', array( $this, 'register_meta_boxes') );
    }
    
    
    /**
     * register_meta_boxes function.
     * 
     * @access public
     * @return void
     */
    public function register_meta_boxes(){
    
        // Make sure there's no errors when the plugin is deactivated or during upgrade
    	if ( !class_exists( 'RW_Meta_Box' ) ){
        	return;
    	}
    
        // get info about the current post
        $this->post_id = ( isset( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : false;
        
        // get registered post types         	
    	$post_types = get_post_types();
    	
    	if ( array_key_exists('deal', $post_types ) ){ $this->deal_meta(); }
    	//if ( array_key_exists('requirement', $post_types ) ){ $this->requirement_meta(); }
    	//if ( array_key_exists('associate', $post_types ) ){ $this->associate_meta(); }
    	//if ( array_key_exists('property', $post_types ) ){ $this->property_meta(); }

        foreach ( (array) $this->meta_boxes as $meta_box ){
    		new RW_Meta_Box( $meta_box );
    	}
    	
    }
    
   
    /**
     * deal_meta function.
     *
     * @TODO restrict user select to client role
     * 
     * @access private
     * @return void
     */
    private function deal_meta(){
        
         $this->meta_boxes['deal-required'] = array(
                'id'        => 'deal-required',
                'title'     => __('Required Deal Information', $this->plugin_slug),
                'pages'     => array('deal'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(
                        array(
                        	'name'			=> __( 'REF', $this->plugin_slug ),
                			'desc' 			=> __( 'Broker Reference Number', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_ref_number',
                			'clone' 		=> false,
                			'type' 			=> 'text',
                            'placeholder'   => 'MGAB-###',

                        ),
                        
                        array(
                			'name'          => __( 'Client', $this->plugin_slug ),
                			'desc'			=> __( 'The client associated with this deal' ),
                			'id'            => $this->plugin_slug . '_client',
                			'type'          => 'user',
                			'clone'			=> true,
			                'placeholder'   => 'Select Client',

                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                            ),
                        
                         array(
                			'name'          => __( 'Deal Type', $this->plugin_slug ),
                			'desc'          => __( 'Type of deal of the application', $this->plugin_slug),
                			'id'            => $this->plugin_slug . '_deal_type',
                			'type'          => 'taxonomy',
			                'placeholder'   => 'Select Type',
                			'options' => array(
                				// Taxonomy name
                				'taxonomy' => 'deal_type',
                				'type' => 'select',
                				// Additional arguments for get_terms() function. Optional
                				'args' => array( 'hide_empty' => false )
                                ),
                        ),
                        
                        
                        
                ),// end fields
                        
            	'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_client' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_ref_number' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_deal_type' => array(
                				'required'  => true,
                                )
                                
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_client' => array(
                				'required'  => __( 'Please Select a Client', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_ref_number' => array(
                				'required'  => __( 'Please Input a Ref Number', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_deal_type' => array(
	            				'required'  => __( 'Please Select a Deal Type', $this->plugin_slug ),
	                            ),
                		)
                    )// end validation
        		);// end meta 

/*
// !-- Optional Info        		
        $this->meta_boxes['project-optional'] = array(
                'id'        => 'project-optional',
                'title'     => __('Optional Information', $this->plugin_slug),
                'pages'     => array('project'),
                'context'   => 'side',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'Additional Group(s)', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_secondary_group',
                			'desc'          => __('Used for project sharing', $this->plugin_slug),
                			'type'          => 'post',
			                'placeholder'   => 'Select Group',
			                'clone'         => true,
                			// Post type
                			'post_type'     => 'group',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                        
                        array(
                			'name'          => __( 'Project Manager(s)', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_project_manager',
                			'desc'          => __( 'Users must be a member of this project first (see project users)', $this->plugin_slug ),
                			'type'          => 'post',
			                'placeholder'   => 'Select User',
			                'clone'         => true,
                			// Post type
                			'post_type'     => 'project-user',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                				'meta_query' => array(
                                            		array(
                                            			'key' => 'nanofab_project',
                                            			'value' => $this->post_id,
                                            		)
                                            	)	
                                ),
                        ),
                                    		
                        array(
                			'name'    => __( 'Expiration Date', $this->plugin_slug ),
                			'id'      => $this->plugin_slug . '_end_date',
                			'desc'    => __('The project will expire after this date (yyyy-mm-dd)', $this->plugin_slug),
                			'type'    => 'date',
                			'placeholder'   => 'Project expires after',
                			// jQuery date picker options. See here http://api.jqueryui.com/datepicker
                			'js_options' => array(
                				//'appendText'      => __( '(yyyy-mm-dd)', $this->plugin_slug ),
                				'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
                				'changeMonth'     => true,
                				'changeYear'      => true,
                				'showButtonPanel' => true,
                                ),
                        ),

                		array(
                			'name' => __( 'Project Cap', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_project_cap',
                			'type' => 'number',
                            'placeholder'   => 'Spending Limit',
                			'min'  => 0,
                			'step' => 100,
                			
                		),

                        array(
                			'name' => __( 'Warning Limit', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_project_warning',
                			'type' => 'number',
                            'placeholder'   => 'Send Warning',
                			'min'  => 0,
                			'step' => 100,
                			
                		),
                		 array(
                			'name' => __( 'Billable Account', $this->plugin_slug ),
                			'desc' => __('The account that should be billed for charges to this project. Multiple accounts possible', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_account',
                			'clone' => true,
                			'type' => 'text',
                            'placeholder'   => 'PO Number / Speed Code',
                		),
                            
                ),// end fields
    		);// end meta 
*/         
    } // end project meta

    
    /**
     * usage_meta function.
     * 
     * @access private
     * @return void
     */
    private function usage_meta(){

// !-- Required Info                 
        $this->meta_boxes['usage-required'] = array(
                'id'        => 'usage-required',
                'title'     => __('Usage Details (Required)', $this->plugin_slug),
                'pages'     => array('usage'),
                'context'   => 'normal',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'User Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_user',
                			'type'          => 'user',
			                'placeholder'   => 'Select User',

                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                        ),
                        array(
                			'name'          => __( 'Project Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_project',
                			'type'          => 'post',
			                'placeholder'   => 'Select Project',
                			// Post type
                			'post_type'     => 'project',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                         array(
                        			'name'          => __( 'Service Request', $this->plugin_slug ),
                        			'id'            => $this->plugin_slug . '_service_request',
                        			'type'          => 'post',
        			                'placeholder'   => 'Select Service Request',
                        			// Post type
                        			'post_type'     => 'service-request',
                        			// Field type, either 'select' or 'select_advanced' (default)
                        			'field_type'    => 'select_advanced',
                        			// Query arguments (optional). No settings means get all published posts
                        			'query_args'    => array(
                        				'post_status' => 'publish',
                        				'posts_per_page' => '-1',
                                        ),
                                ),                       

                ), // end fields
                'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_project' => array(
                				'required'  => true,
                                ),
                           
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => __( 'Please Select a User', $this->plugin_slug ),
                				
                                ),
                            $this->plugin_slug . '_project_name' => array(
                				'required'  => __( 'Please Select a Project', $this->plugin_slug ),
                				
                                ),
                            
                		)
                    )// end validation
                );// end meta
                
// !-- Equipment Information
        $this->meta_boxes['usage-equipment'] = array(
                'id'        => 'usage-equipment',
                'title'     => __('Equipment Details', $this->plugin_slug),
                'pages'     => array('usage'),
                'context'   => 'normal',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                                array(
                        			'name'          => __( 'Equipment', $this->plugin_slug ),
                        			'id'            => $this->plugin_slug . '_equipment',
                        			'type'          => 'post',
                                    'post_type'     => 'equipment',
                                    'placeholder'   => 'Select Tool',
                        			// Field type, either 'select' or 'select_advanced' (default)
                        			'field_type' => 'select_advanced',
                        			// Query arguments (optional). No settings means get all published posts
                        			'query_args' => array(
                        				'post_status' => 'publish',
                        				'posts_per_page' => '-1',
                        			)
                                ),
                                array(
                        			'name'          => __( 'Actual Time Used', $this->plugin_slug ),
                        			'desc'          => __('The actual time used on the equipment', $this->plugin_slug ),
                        			'id'            => $this->plugin_slug . '_equipment_time_actual',
                        			'type' => 'time',
                            			// jQuery datetime picker options.
                            			// For date options, see here http://api.jqueryui.com/datepicker
                            			// For time options, see here http://trentrichardson.com/examples/timepicker/
                            			'js_options' => array(
                            				'stepMinute' => 1,
                            				'showSecond' => true,
                            				'stepSecond' => 10,
                                            'hourMax'    => 99,
                            			),
                                ),
                                array(
                        			'name'          => __( 'Billable Time', $this->plugin_slug ),
                        			'desc'          => __('The amount of time to be billed', $this->plugin_slug ),
                        			'id'            => $this->plugin_slug . '_equipment_time_billed',
                        			'type' => 'time',
                            			// jQuery datetime picker options.
                            			// For date options, see here http://api.jqueryui.com/datepicker
                            			// For time options, see here http://trentrichardson.com/examples/timepicker/
                            			'js_options' => array(
                            				'stepMinute' => 1,
                            				'showSecond' => true,
                            				'stepSecond' => 10,
                                            'hourMax'    => 99,
                            			),
                                ),
                )// end fields
            );// end meta-box
    } // end usage meta
    
    
    /**
     * equipment_user_meta function.
     * 
     * @access private
     * @return void
     */
    private function equipment_user_meta(){

// !-- Required Info
        $this->meta_boxes['equipment-user-required'] = array(
                'id'        => 'equipment-user-required',
                'title'     => __('Configuration', $this->plugin_slug),
                'pages'     => array('equipment-user'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'User Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_user',
                			'type'          => 'user',
			                'placeholder'   => 'Select User',
                			
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                        ),
                        
                        array(
                			'name'          => __( 'Equipment', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_equipment',
                			'type'          => 'post',
                            'post_type'     => 'equipment',
                            'placeholder'   => 'Select Tool',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type' => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args' => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                			)
                        ),
                ), // end fields
                'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_equipment' => array(
                				'required'  => true,
                                )
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => __( 'Please Select a User', $this->plugin_slug ),
                				
                                ),
                		)// end messages
                    )// end validation
                );// end meta
                
// !-- Optional Info         
         $this->meta_boxes['equipment-user-optional'] = array(
            'id'        => 'equipment-user-optional',
            'title'     => __('Optional Information', $this->plugin_slug),
            'pages'     => array('equipment-user'),
            'context'   => 'normal',
            'priority'  => 'core',
            'autosave'  => true,

            'fields'    => array(            		
                        array(
                			'name'    => __( 'Inactivity Threshold', $this->plugin_slug ),
                			'desc'    => __('The user\'s access will expire after this period of inactivity (days)', $this->plugin_slug),
                			'id'      => $this->plugin_slug . '_inactivity_threshold',
                			'type'    => 'number',
                			'placeholder'   => 'Expire after (N) days',
                			'min'  => 0,
                			'step' => 1,
                        ),
                        array(
                			'name'    => __( 'Expiration Date', $this->plugin_slug ),
                			'id'      => $this->plugin_slug . '_expiration_date',
                			'desc'    => __('Set a fixed date after which this user will expire (yyyy-mm-dd)', $this->plugin_slug),
                			'type'    => 'date',
                			'placeholder'   => 'User expires after',
                			// jQuery date picker options. See here http://api.jqueryui.com/datepicker
                			'js_options' => array(
                				//'appendText'      => __( '(yyyy-mm-dd)', $this->plugin_slug ),
                				'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
                				'changeMonth'     => true,
                				'changeYear'      => true,
                				'showButtonPanel' => true,
                                ),
                        ),
                        array(
                			'name'          => __( 'Trained By', $this->plugin_slug ),
                			'desc'          => __( 'Only nanoFAB staff', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_trained_by',
                			'type'          => 'user',
			                'placeholder'   => 'Select Trainer',
			                'clone'         => true,
                			
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				//'role' => 'nanofab_staff'
                				'role'  => $this->default['roles'][ $this->plugin_slug . '-trainer-role' ]
                                ),
                        ),
                        
                        array(
                			'name'          => __( 'Sign Off Date', $this->plugin_slug ),
                			'desc'          => __('yyyy-mm-dd', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_sign_off_date',
                			'type'          => 'date',
                			// jQuery date picker options. See here http://api.jqueryui.com/datepicker
                			'js_options' => array(
                    				'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
                    				'changeMonth'     => true,
                    				'changeYear'      => true,
                    				'showButtonPanel' => true,
                    			),
                        ),
                
                ),// end fields
    		);// end meta 

    }// end equipment user meta
    
    
    /**
     * project_user_meta function.
     * 
     * @access private
     * @return void
     */
    private function project_user_meta(){
// !-- Required Info                 
        $this->meta_boxes['project-user-required'] = array(
                'id'        => 'project-user-required',
                'title'     => __('Configuration', $this->plugin_slug),
                'pages'     => array('project-user'),
                'context'   => 'normal',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'User Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_user',
                			'type'          => 'user',
			                'placeholder'   => 'Select User',

                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                        ),
                        array(
                			'name'          => __( 'User Status', $this->plugin_slug ),
                			'desc'          => __( 'Applies to this project only', $this->plugin_slug),
                			'id'            => $this->plugin_slug . '_user_status',
                			'type'          => 'taxonomy',
			                'placeholder'   => 'Select Status',
                			'options' => array(
                				// Taxonomy name
                				//'taxonomy' => 'project_user_status',
                				'taxonomy'  => $this->default['taxonomy'][ $this->plugin_slug . '-project-user-status' ],
                				// options: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree', select_advanced or 'select'. Optional
                				'type' => 'select_advanced',
                				// Additional arguments for get_terms() function. Optional
                				'args' => array( 'hide_empty' => false )
                                ),
                        ),
                        array(
                			'name'          => __( 'Project Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_project',
                			'type'          => 'post',
			                'placeholder'   => 'Select Project',
                			// Post type
                			'post_type'     => 'project',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                ), // end fields
                'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_project' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_user_status' => array(
                				'required'  => true,
                                )
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => __( 'Please Select a User', $this->plugin_slug ),
                				
                                ),
                            $this->plugin_slug . '_project_name' => array(
                				'required'  => __( 'Please Select a Project', $this->plugin_slug ),
                				
                                ),
                            $this->plugin_slug . '_user_status' => array(
                				'required'  => __( 'Please Select a Status', $this->plugin_slug ),
                				
                                ),
                		)
                    )// end validation
                );// end meta
                
// !-- Optional Info                  
         $this->meta_boxes['project-user-optional'] = array(
            'id'        => 'project-user-optional',
            'title'     => __('Optional Information', $this->plugin_slug),
            'pages'     => array('project-user'),
            'context'   => 'normal',
            'priority'  => 'core',
            'autosave'  => true,

            'fields'    => array(            		
                        array(
                			'name'    => __( 'Expiration Date', $this->plugin_slug ),
                			'desc'    => __('The user will expire after this date', $this->plugin_slug),
                			'id'      => $this->plugin_slug . '_end_date',
                			'type'    => 'date',
                			// jQuery date picker options. See here http://api.jqueryui.com/datepicker
                			'js_options' => array(
                				//'appendText'      => __( '(yyyy-mm-dd)', $this->plugin_slug ),
                				'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
                				'changeMonth'     => true,
                				'changeYear'      => true,
                				'showButtonPanel' => true,
                                ),
                        ),

                		array(
                			'name' => __( 'Project Cap', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_project_cap',
                			'type' => 'number',
                            'placeholder'   => 'Spending Limit',
                			'min'  => 0,
                			'step' => 100,
                			
                		),

                        array(
                			'name' => __( 'Warning Limit', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_project_warning',
                			'type' => 'number',
                            'placeholder'   => 'Send Warning',
                			'min'  => 0,
                			'step' => 100,
                			
                		),
                		
                		array(
                			'name'     => __( 'Can Submit Masks', $this->plugin_slug ),
                			'id'       => $this->plugin_slug . '_can_submit_masks',
                			'type'     => 'select_advanced',
                			'options'  => array(
                				'yes' => __( 'Yes', $this->plugin_slug ),
                				'no' => __( 'No', $this->plugin_slug ),
                			),
                			'multiple'    => false,
                			// 'std'         => 'value2', // Default value, optional
                			'placeholder' => __( 'Select an Item', $this->plugin_slug ),
                		), 
                		array(
                			'name'          => __( 'Mask Limit', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_mask_limit',
                			'type'          => 'number',
                            'placeholder'   => 'Maximum Masks',
                			'min'  => 0,
                			'step' => 1,
                		),                            
                ),// end fields
    		);// end meta 

    }// end project user meta
    
    private function service_meta(){

// !-- Required Info
         $this->meta_boxes['service'] = array(
                'id'        => 'service-required',
                'title'     => __('Service Details', $this->plugin_slug),
                'pages'     => array('service'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'Equipment', $this->plugin_slug ),
                			'desc'          => __('Equipment required to complete this service', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_equipment',
                			'type'          => 'post',
                            'post_type'     => 'equipment',
                            'placeholder'   => 'Select Tool',
                            'clone'         => 'true',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type' => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args' => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                			)
                        ),
                        array(
                			'name'          => __( 'Typical Staff Hours', $this->plugin_slug ),
                			'desc'          => __('Average Staff time to complete (information only)', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_typical_staff_hours',
                			'type' => 'time',
                    			// jQuery datetime picker options.
                    			// For date options, see here http://api.jqueryui.com/datepicker
                    			// For time options, see here http://trentrichardson.com/examples/timepicker/
                    			'js_options' => array(
                    				'stepMinute' => 1,
                    				'showSecond' => false,
                                    'hourMax'    => 99,
                    			),
                        ),
                        array(
                			'name'          => __( 'Complexity', $this->plugin_slug ),
                			'desc'          => __('An indication of the complexity of this service - higher number = more complex', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_complexity',
                			'type'  => 'slider',
                    			// jQuery UI slider options. See here http://api.jqueryui.com/slider/
                    			'js_options' => array(
                    				'min'   => 0,
                    				'max'   => 10,
                    				'step'  => 1,
                    			),
                        ),
                        
                ), // end fields
            );// end meta box

// !-- Billing Info
        $fields[] = array(
                			'name' => __( 'For all Funding Types:', $this->plugin_slug ),
                			'id'   => "funding-type-header",
                			'type' => 'heading',
                		);
        $fields[] = array(
            			'name' => __( 'Base Rate', $this->plugin_slug ),
            			'desc' => __('A Base Rate for this service', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_base_rate',
            			'type' => 'number',
            			'min'  => 0,
            			'step' => 5,
            		);
        $fields[] = array(
            			'name' => __( 'Maximum Rate', $this->plugin_slug ),
            			'desc' => __('Maximum charge for this service', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_max_rate',
            			'type' => 'number',
            			'min'  => 0,
            		);
        $fields[] = array(
            			'name' => __( 'Staff Rate', $this->plugin_slug ),
            			'desc' => __('Hourly Charge out rate for staff time', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_staff_rate',
            			'type' => 'number',
            			'min'  => 0,
            		);
       
        // get all of the project types taxonomies and build an input for each one
        $types = get_terms('funding_type', 'hide_empty=0');
        foreach ( $types as $term ){
            
            $fields[] = array(
                			'name' => __( $term->name, $this->plugin_slug ),
                			'id'   => $term->term_id,
                			'type' => 'heading',
                            
                		);
            $fields[] = array(
                			'name' => __( 'Rate', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$term->name . '_rate',
                			'type' => 'number',
                            'placeholder'   => '0',
                			'min'  => 0,
                			'step' => 1,
                			
                		);
            $fields[] = array(
                			'name' => __( 'Unit', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$term->name . '_unit',
                			'type' => 'select',
                            'options'  => array(
                				'minute' => __( 'Minute', $this->plugin_slug ),
                				'hour' => __( 'Hour', $this->plugin_slug ),
                				'day' => __( 'Day', $this->plugin_slug ),
                				'week' => __( 'Week', $this->plugin_slug ),
                				'sample' => __( 'Sample', $this->plugin_slug ),
                				'flat_rate' => __( 'Flat Rate', $this->plugin_slug ),
                			),
                		);    
        }
// !-- Rates
        // create the meta box
        $this->meta_boxes['service-rates'] = array(
                'id' => 'service-rates',
                'title' => __( 'Billing Structure', $this->plugin_slug ),
                'pages' => array( 'service' ),
                'context' => 'normal',
                'priority' => 'high',
                'autosave' => true,
                'fields' =>  $fields,
                
            ); 
            // end meta-box definition


    }// end service meta
    
    /**
     * service_request_meta function.
     * 
     * @access private
     * @return void
     */
    private function service_request_meta(){
// !-- Emergency Info                 
        $this->create_emergency_contact();
        
// !-- Request Info
        $this->meta_boxes['service-request'] = array(
                'id'        => 'service-request',
                'title'     => __('Request Information', $this->plugin_slug),
                'pages'     => array('service-request'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'User Name', $this->plugin_slug ),
                			'desc'          => __( 'Service requested by', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_user',
                			'type'          => 'user',
			                'placeholder'   => 'Select User',
                			
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                        ),
                        array(
                			'name'          => __( 'Project Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_project',
                			'desc'          => __('Project to be billed', $this->plugin_slug ),
                			'type'          => 'post',
			                'placeholder'   => 'Select Project',
                			// Post type
                			'post_type'     => 'project',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),

                        array(
                			'name'          => __( 'Service', $this->plugin_slug ),
                			'desc'          => __('The service to be completed', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_service',
                			'type'          => 'post',
                            'post_type'     => 'service',
                            'placeholder'   => 'Select Service',
                            'clone'         => 'true',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type' => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args' => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                			)
                        ),
                         array(
                			'name'          => __( 'Additional File(s)', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_file',
                			'type'          => 'file_advanced'
                		),
                		

                ), // end fields
                'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => true,
                                ),
                            $this->plugin_slug . '_equipment' => array(
                				'required'  => true,
                                )
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_user' => array(
                				'required'  => __( 'Please Select a User', $this->plugin_slug ),
                				
                                ),
                		)// end messages
                )// end validation
            );// end meta
                

// !-- Service Details
        $this->meta_boxes['service-details'] = array(
                'id'        => 'service-details',
                'title'     => __('Service Details', $this->plugin_slug),
                'pages'     => array('service-request'),
                'context'   => 'normal',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                        array(
                			'name'          => __( 'Mask', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_mask',
                			'desc'          => __('Mask Details', $this->plugin_slug ),
                			'type'          => 'post',
			                'placeholder'   => 'Select Mask',
			                'clone'         => true,
                			// Post type
                			'post_type'     => 'mask',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                         array(
                			'name'          => __( 'Sample', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_sample',
                			'desc'          => __('Sample Details', $this->plugin_slug ),
                			'type'          => 'post',
			                'placeholder'   => 'Select Sample',
			                'clone'         => true,
                			// Post type
                			'post_type'     => 'sample',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                         array(
                    			'name' => __( 'Laser Time', $this->plugin_slug ),
                    			'desc' => __('Laser time (for mask processing)', $this->plugin_slug ),
                    			'id'   => $this->plugin_slug . '_laser_time',
                    			'type' => 'time',
                    			// jQuery datetime picker options.
                    			// For date options, see here http://api.jqueryui.com/datepicker
                    			// For time options, see here http://trentrichardson.com/examples/timepicker/
                    			'js_options' => array(
                    				'stepMinute' => 1,
                    				'showSecond' => true,
                    				'stepSecond' => 10,
                    			),
                        ),
                        array(
                    			'name' => __( 'Staff Time', $this->plugin_slug ),
                    			'desc' => __('Actual staff time used', $this->plugin_slug ),
                    			'id'   => $this->plugin_slug . '_staff_time',
                    			'type' => 'time',
                    			// jQuery datetime picker options.
                    			// For date options, see here http://api.jqueryui.com/datepicker
                    			// For time options, see here http://trentrichardson.com/examples/timepicker/
                    			'js_options' => array(
                    				'stepMinute' => 1,
                    				'showSecond' => false,
                                    'hourMax'    => 99,
                    			),
                        ),

                ) // end fields
            );// end meta

// !-- Results Info
        $this->meta_boxes['service-results'] = array(
                'id'        => 'service-results',
                'title'     => __('Service Results', $this->plugin_slug),
                'pages'     => array('service-request'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(                      
                        array(
                			'name'          => __( 'Result File(s)', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_sample_result',
                			'type'          => 'file_advanced'
                		),
                        
                        array(
                			'name'             => __( 'Result Image(s)', $this->plugin_slug ),
                			'id'               => $this->plugin_slug . '_sample_image',
                			'type'             => 'plupload_image'
                		),
                		 // notes
                      	array(
                			'name' => __( 'Additional Notes', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_sample_notes',
                			'type' => 'wysiwyg',
                			// Set the 'raw' parameter to TRUE to prevent data being passed through wpautop() on save
                			'raw'  => false,
                			// Editor settings, see wp_editor() function: look4wp.com/wp_editor
                			'options' => array(
                				'textarea_rows' => 4,
                				'teeny'         => true,
                				'media_buttons' => false,
                			),
                		),
                		
                ) // end fields
            );// end meta

            
// !-- Request State
     $this->meta_boxes['service-state'] = array(
                'id'        => 'service-state',
                'title'     => __('Service State', $this->plugin_slug),
                'pages'     => array('service-request'),
                'context'   => 'side',
                'priority'  => 'core',
                'autosave'  => true,

                'fields'    => array(
                    array(
                    			'name'    => __( 'Service State', $this->plugin_slug ),
                    			'id'      => $this->plugin_slug . '_state',
                    			'type'    => 'taxonomy',
                    			'placeholder'   => 'Select State',
                    			'options' => array(
                    				// Taxonomy name
                    				//'taxonomy' => 'mask_state',
                    				'taxonomy'  => $this->default['taxonomy'][ $this->plugin_slug . '-service-request-status' ],
                    				// options: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree', select_advanced or 'select'. Optional
                    				'type' => 'select_advanced',
                    				// Additional arguments for get_terms() function. Optional
                    				'args' => array( 'hide_empty' => false )
                                    ),
                        ),
                
                ),// end fields
            );// end meta box
    
    }// end service meta
    
    
    /**
     * sample_meta function.
     * 
     * @access private
     * @return void
     */
    private function sample_meta(){
        
    }// end sample meta
    
    /**
     * equipment_meta function.
     * 
     * @access private
     * @return void
     */
    private function equipment_meta(){

// !-- Contact Info                 
        $this->create_contact_info();
// !-- Emergency Info                 
        $this->create_emergency_contact();
        
// !-- Billing Info
        $fields = array();
        $fields[] = array(
                			'name' => __( 'For all Funding Types:', $this->plugin_slug ),
                			'id'   => "funding-type-header",
                			'type' => 'heading',
                		);
        $fields[] = array(
            			'name' => __( 'Grace Period', $this->plugin_slug ),
            			'desc' => __('Grace setup period before we start billing', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_grace_period',
            			'type' => 'time',
                        'std' => '00:05:00',
            			// jQuery datetime picker options.
            			// For date options, see here http://api.jqueryui.com/datepicker
            			// For time options, see here http://trentrichardson.com/examples/timepicker/
            			'js_options' => array(
            				'stepMinute' => 1,
            				'showSecond' => true,
            				'stepSecond' => 10,
            			),
            		);
        $fields[] = array(
            			'name' => __( 'Minimum Period', $this->plugin_slug ),
            			'desc' => __('Minimum period once we start billing', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_minimum_period',
            			'type' => 'time',
            			'std' => '00:60:00',
                        'js_options' => array(
            				'stepMinute' => 1,
            				'showSecond' => false,
            				'stepSecond' => 10,
            			),
            		);    		
        $fields[] = array(
            			'name' => __( 'Maximum Period', $this->plugin_slug ),
            			'desc' => __('Maximum period once we start billing', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_maximum_period',
            			'type' => 'time',
            			'std' => '72:00:00',
                        'js_options' => array(
            				'stepMinute' => 1,
            				'showSecond' => false,
            				'hourMax'    => 99,
            			),
            		);
        $fields[] = array(
            			'name' => __( '24hr Maximum Period', $this->plugin_slug ),
            			'desc' => __('Maximum period billed in a 24hr period', $this->plugin_slug ),
            			'id'   => $this->plugin_slug . '_maximum_24_period',
            			'type' => 'time',
            			'std' => '00:12:00',
                        'js_options' => array(
            				'stepMinute' => 1,
            				'showSecond' => false,
            			),
            		);
        
        // get all of the project types taxonomies and build an input for each one
        $types = get_terms('funding_type', 'hide_empty=0');
        foreach ( $types as $term ){
            
            $fields[] = array(
                			'name' => __( $term->name, $this->plugin_slug ),
                			'id'   => $term->term_id,
                			'type' => 'heading',
                            
                		);
            $fields[] = array(
                			'name' => __( 'Rate', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$term->name . '_rate',
                			'type' => 'number',
                            'placeholder'   => '0',
                			'min'  => 0,
                			'step' => 1,
                			
                		);
            $fields[] = array(
                			'name' => __( 'Unit', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$term->name . '_unit',
                			'type' => 'select',
                            'options'  => array(
                				'minute' => __( 'Minute', $this->plugin_slug ),
                				'hour' => __( 'Hour', $this->plugin_slug ),
                				'day' => __( 'Day', $this->plugin_slug ),
                				'week' => __( 'Week', $this->plugin_slug ),
                			),
                		);    
        }
// !-- Rates
        // create the meta box
        $this->meta_boxes['equipment-rates'] = array(
                'id' => 'equipment-rates',
                'title' => __( 'Billing Structure', $this->plugin_slug ),
                'pages' => array( 'equipment' ),
                'context' => 'normal',
                'priority' => 'high',
                'autosave' => true,
                'fields' =>  $fields,
                
            ); 
            // end meta-box definition

// !-- Facility Info        
        $this->meta_boxes['equipment-facility'] = array(
                'id' => 'equipment-facility',
                'title' => __( 'Facility Information', $this->plugin_slug ),
                'pages' => array( 'equipment' ),
                'context' => 'normal',
                'priority' => 'high',
                'autosave' => true,
                'fields'    => array(
                        
                        array(
                    			'name'          => __( 'Physical Location', $this->plugin_slug ),
                    			'desc'          => __( 'Where is this tool actually located', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_location',
                    			'type'          => 'post',
    			                'placeholder'   => 'Select Location',
                    			// Post type
                    			'post_type'     => 'location',
                    			// Field type, either 'select' or 'select_advanced' (default)
                    			'field_type'    => 'select_advanced',
                    			// Query arguments (optional). No settings means get all published posts
                    			'query_args'    => array(
                    				'post_status' => 'publish',
                    				'posts_per_page' => '-1',
                                    ),
                        ),
                        array(
                    			'name'          => __( 'Interlock Device', $this->plugin_slug ),
                    			'desc'          => __( 'Physical Interlocks prevent unauthorized user', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_interlock',
                    			'type'          => 'post',
    			                'placeholder'   => 'Select Interlock',
                    			// Post type
                    			'post_type'     => 'interlock',
                    			// Field type, either 'select' or 'select_advanced' (default)
                    			'field_type'    => 'select_advanced',
                    			// Query arguments (optional). No settings means get all published posts
                    			'query_args'    => array(
                    				'post_status' => 'publish',
                    				'posts_per_page' => '-1',
                                    ),
                        ),
                        array(
                    			'name'          => __( 'Training Staff', $this->plugin_slug ),
                    			'desc'          => __( 'Available System Trainers', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_trainer',
                    			'type'          => 'user',
                    			'clone'         => true,
                    			'placeholder'   => 'Select Trainer',
                            			// Field type, either 'select' or 'select_advanced' (default)
                            			'field_type'    => 'select_advanced',
                            			// Query arguments (optional). No settings means get all published posts
                            			'query_args'    => array(
                            				//'role' => 'nanofab_staff'
                            				'role'  => $this->default['roles'][ $this->plugin_slug . '-trainer-role' ]
                                    ),
                        ),
                        array(
                    			'name'          => __( 'Service Staff', $this->plugin_slug ),
                    			'desc'          => __( 'Available Service Staff', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_service',
                    			'type'          => 'user',
                    			'clone'         => true,
                    			'placeholder'   => 'Select Service Tech',
                            			// Field type, either 'select' or 'select_advanced' (default)
                            			'field_type'    => 'select_advanced',
                            			// Query arguments (optional). No settings means get all published posts
                            			'query_args'    => array(
                            				//'role' => 'nanofab_staff'
                            				'role'  => $this->default['roles'][ $this->plugin_slug . '-service-role' ]
                                    ),
                            ),
                        )// end fields
                );// end meta-box definition 

// !-- Booking Info
        $fields = array();
        
        $fields[] = array(
            			'name' => __( 'Base iCal Resource', $this->plugin_slug ),
            			'desc'          => __( 'Enter the iCal calendar resource link', $this->plugin_slug ),
            			'id'   => $this->plugin_slug .'_base_ical_url',
            			'type' => 'url',
                        'placeholder'   => 'http://www.google.com/calendar/ical/yourlink',
            		);
        $fields[] = array(
            			'name' => __( 'Base GoogleCal Resource', $this->plugin_slug ),
            			'desc'          => __( 'Enter the google calendar resource link', $this->plugin_slug ),
            			'id'   => $this->plugin_slug .'_base_googlecal_url',
            			'type' => 'url',
                        'placeholder'   => 'http://www.google.com/calendar/ical/yourlink',
            		);
            		
       // get all of the project types taxonomies and build an input for each one
        $levels = get_terms('user_level', 'hide_empty=0');
        foreach ( $levels as $level ){
            
            $fields[] = array(
                			'name' => __( $level->name, $this->plugin_slug ),
                			'id'   => $level->term_id,
                			'type' => 'heading',
                            
                		);
            $fields[] = array(
                			'name' => __( 'iCal Resource', $this->plugin_slug ),
                			'desc'          => __( 'Enter the iCal calendar resource link', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$level->name . '_ical_url',
                			'type' => 'url',
                            'placeholder'   => 'http://www.google.com/calendar/ical/yourlink',
                		);
            $fields[] = array(
                			'name' => __( 'GoogleCal Resource', $this->plugin_slug ),
                			'desc'          => __( 'Enter the google calendar resource link', $this->plugin_slug ),
                			'id'   => $this->plugin_slug .'_'.$level->name . '_googlecal_url',
                			'type' => 'url',
                            'placeholder'   => 'http://www.google.com/calendar/ical/yourlink',
                		);
            
        }

        // create the meta box
        $this->meta_boxes['equipment-booking'] = array(
                'id' => 'equipment-booking',
                'title' => __( 'Booking Structure', $this->plugin_slug ),
                'pages' => array( 'equipment' ),
                'context' => 'normal',
                'priority' => 'core',
                'autosave' => true,
                'fields' =>  $fields,
                
            ); 
            // end meta-box definition
            
// !-- Training Information        
        $this->meta_boxes['equipment-options'] = array(
                'id' => 'equipment-options',
                'title' => __( 'Optional Configuration', $this->plugin_slug ),
                'pages' => array( 'equipment' ),
                'context' => 'normal',
                'priority' => 'high',
                'autosave' => true,
                'fields'    => array(
                    	array(
                    			'name'  => __( 'Training Expires after', $this->plugin_slug ),
                    			'desc'  => __( 'Users without activity for (N) days will expire - leave blank for no expiration', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_training_valid_for',
                    			'type'  => 'number',
                    			'min'   => 0,
                    			'step'  => 5,
                    			'max'   => 730,
                        ),
                        array(
                    			'name'  => __( 'Maximum daily Logins', $this->plugin_slug ),
                    			'desc'  => __( 'Notify staff if a user exceeds this number - 0 for no limit', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_daily_login_limit',
                    			'type'  => 'slider',
                    			'suffix' => __( ' times', $this->plugin_slug ),
                    
                    			// jQuery UI slider options. See here http://api.jqueryui.com/slider/
                    			'js_options' => array(
                    				'min'   => 0,
                    				'max'   => 10,
                    				'step'  => 1,
                    			),
                        ),
                    	
                    )// end fields
                );// end meta-box definition 
                         
// !-- Tool Specifics        
        $this->meta_boxes['equipment-specs'] = array(
                'id' => 'equipment-specs',
                'title' => __( 'Tool Specific Data', $this->plugin_slug ),
                'pages' => array( 'equipment' ),
                'context' => 'normal',
                'priority' => 'core',
                'autosave' => true,
                'fields'    => array(
                        
                        array(
                    			'name'          => __( 'Vendor', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_vendor',
                    			'type'          => 'text',
    			                'placeholder'   => 'Vendor',
                            ),
                        array(
                    			'name'          => __( 'Product Line', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_product',
                    			'type'          => 'text',
    			                'placeholder'   => 'Product Name',
                            ),
                         array(
                    			'name'          => __( 'Model', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_model',
                    			'type'          => 'text',
    			                'placeholder'   => 'Model Number',
                            ),
                         array(
                    			'name'          => __( 'Serial Number', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_serial_number',
                    			'type'          => 'text',
    			                'placeholder'   => 'SN123456789',
                            ),
                         array(
                    			'name'          => __( 'Service Contract', $this->plugin_slug ),
                    			'id'            => $this->plugin_slug . '_service_contract',
                    			'type'          => 'text',
    			                'placeholder'   => 'Contract Number',
                            ),
                            
                        
                        )// end fields
                );// end meta-box definition        
    } // end equipment meta
    
    /**
     * mask_meta function.
     * 
     * @access private
     * @return void
     */
    private function mask_meta(){
        
// !-- submission information
        $this->meta_boxes['mask-submission'] = array(
                'id'        => 'mask-submission',
                'title'     => 'Submission Information',
                'pages'     => array('mask'),
                'context'   => 'normal',
                'priority'  => 'core',
                'autosave'  => true,
                'fields'    => array(
                        
                        
                        array(
                			'name'          => __( 'User Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_user',
                			'type'          => 'user',
			                'placeholder'   => 'Select User',

                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'role' => ''
                                ),
                        ),
                        
                        /*
                        // this info moved into service request 
                       
                        array(
                			'name'          => __( 'Project Name', $this->plugin_slug ),
                			'id'            => $this->plugin_slug . '_project',
                			'type'          => 'post',
			                'placeholder'   => 'Select Project',
                			// Post type
                			'post_type'     => 'project',
                			// Field type, either 'select' or 'select_advanced' (default)
                			'field_type'    => 'select_advanced',
                			// Query arguments (optional). No settings means get all published posts
                			'query_args'    => array(
                				'post_status' => 'publish',
                				'posts_per_page' => '-1',
                                ),
                        ),
                        */
                        // gdsii file
                       array(
                    			'name' => __( 'GDSii File', $this->plugin_slug ),
                    			'id'   => $this->plugin_slug . '_gdsii_file',
                    			'type' => 'file_advanced',
                    			'max_file_uploads' => 1,
                    			'mime_type' => 'gdsii', // Leave blank for all file types
                    		),
                    	
                        // Cell Name
                        array(
                    			'name'  => __( 'Cell Name', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_cell',
                    			'desc'  => __( 'Top Level Cell Name', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    		
                        // Layer Number
                        array(
                    			'name'  => __( 'Layer Number', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_layer',
                    			'desc'  => __( 'The layer number to fabricate', $this->plugin_slug ),
                    			'type'  => 'number',
                    		),
                            
                        // Exposure
                        array(
                    			'name'  => __( 'Exposure Mode', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_exposure',
                    			'desc'  => __( 'Inverted or Non-Inverted', $this->plugin_slug ),
                    			'type'  => 'select_advanced',
                    			'options' => array(
                    			            'inverted' => __('Inverted', $this->plugin_slug),
                    			            'noninverted' => __('Non-Inverted', $this->plugin_slug),
                    			        ),
                    			'multiple' => false,
                    			'placeholder' => __("Select Mode", $this->plugin_slug),
                    		),
   
                         // orientation
                        array(
                    			'name'  => __( 'Orientation', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_orientation',
                    			'desc'  => __( 'RRCD Flips mask on Y-axis', $this->plugin_slug ),
                    			'type'  => 'select_advanced',
                    			'options' => array(
                    			            'rrcd' => __('RRCD', $this->plugin_slug),
                    			            'rrcu' => __('RRCU', $this->plugin_slug),
                    			        ),
                    			'multiple' => false,
                    			'placeholder' => __("Select Mode", $this->plugin_slug),
                    		),
                    		
                        // Pitch
                        array(
                    			'name'  => __( 'Pitch', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_pitch',
                    			'desc'  => __( 'Smallest feature on your mask', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                        // notes
                        array(
                			'name' => __( 'Additional Notes', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_notes',
                			'type' => 'wysiwyg',
                			// Set the 'raw' parameter to TRUE to prevent data being passed through wpautop() on save
                			'raw'  => false,
                			// Editor settings, see wp_editor() function: look4wp.com/wp_editor
                			'options' => array(
                				'textarea_rows' => 4,
                				'teeny'         => true,
                				'media_buttons' => false,
                			),
                		),
                        
                      )// end fields
        );// end contact meta box
       
// !-- Mask State                        
        $this->meta_boxes['mask-state'] = array(
                'id'        => 'mask-state',
                'title'     => 'Mask State',
                'pages'     => array('mask'),
                'context'   => 'side',
                'priority'  => 'core',
                'autosave'  => true,
                'fields'    => array(
                
                       array(
                    			'name'    => __( 'Mask State', $this->plugin_slug ),
                    			'id'      => $this->plugin_slug . '_state',
                    			'type'    => 'taxonomy',
                    			'placeholder'   => 'Select State',
                    			'options' => array(
                    				// Taxonomy name
                    				//'taxonomy' => 'mask_state',
                    				'taxonomy'  => $this->default['taxonomy'][ $this->plugin_slug . '-mask-status' ],
                    				// options: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree', select_advanced or 'select'. Optional
                    				'type' => 'select_advanced',
                    				// Additional arguments for get_terms() function. Optional
                    				'args' => array( 'hide_empty' => false )
                                    ),
                        ),                        
                        
                    )// end fields
        );// end contact meta box

    } // end mask meta
    
    /**
     * location_meta function.
     * 
     * @access private
     * @return void
     */
    private function location_meta(){
        $this->create_contact_info();
        $this->create_emergency_contact();
    } // end location meta
        
        		
    /**
     * interlock_meta function.
     * 
     * @access private
     * @return void
     */
    private function interlock_meta(){
// !-- Required Info         
         $this->meta_boxes['interlock'] = array(
                'id' => 'interlock',
                'title' => __( 'Configuration ', $this->plugin_slug ),
                'pages' => array( 'interlock' ),
                'context' => 'normal',
                'priority' => 'high',
                'autosave' => true,
                'fields' => array(  
                    // Hostname
                		array(
                			'name'  => __( 'hostname', $this->plugin_slug ),
                			'id'    => $this->plugin_slug . '_hostname',
                			'desc'  => __( 'Hostname', $this->plugin_slug ),
                			'type'  => 'url',
                		), 
                    // MAC
                		array(
                			'name'  => __( 'mac', $this->plugin_slug ),
                			'id'    => $this->plugin_slug . '_mac',
                			'desc'  => __( 'MAC Address', $this->plugin_slug ),
                			'type'  => 'text',
                		),
                	 // IP
                		array(
                			'name'  => __( 'ip', $this->plugin_slug ),
                			'id'    => $this->plugin_slug . '_ip',
                			'desc'  => __( 'IP Address', $this->plugin_slug ),
                			'type'  => 'text',
                		),	 
                	// Status
                		array(
                			'name'     => __( 'status', $this->plugin_slug ),
                			'id'       => $this->plugin_slug . '_status',
                			'type'     => 'select',
                			// Array of 'value' => 'Label' pairs for select box
                			'options'  => array(
                				'active' => __( 'Active', $this->plugin_slug ),
                				'inactive' => __( 'Inactive', $this->plugin_slug ),
                			),
                			// Select multiple values, optional. Default is false.
                			'multiple'    => false,
                			'std'         => 'active'
                		),
                	// Relay Number
                		array(
                			'name' => __( 'Relay Number', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_relay',
                			'type' => 'slider',
                
                			// Text labels displayed before and after value
                			'prefix' => __( '#', $this->plugin_slug ),
                			
                			// jQuery UI slider options. See here http://api.jqueryui.com/slider/
                			'js_options' => array(
                				'min'   => 1,
                				'max'   => 10,
                				'step'  => 1,
                            ),
                		)                    	
                    ),// end fields
                    'validation' => array(
                		'rules' => array(
                			$this->plugin_slug . '_ip' => array(
                				    'required'  => true,
                                    ),
                            $this->plugin_slug . '_relay' => array(
                				    'required'  => true,
                                    ),
                            $this->plugin_slug . '_hostname' => array(
                				    'required'  => true,
                                    ),                
                		),
                		// optional override of default jquery.validate messages
                		'messages' => array(
                			$this->plugin_slug . '_ip' => array(
                				'required'  => __( 'Need an IP address', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_relay' => array(
                				'required'  => __( 'Need a relay', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_hostname' => array(
                				'required'  => __( 'Need a hostname', $this->plugin_slug ),
                                ),        
                		)
                	)// end validation
                ); // end meta-box definition
    }// end interlock meta
    
    
// !-------------- Multi Use meta boxes
    /**
     * create_emergency_contact function.
     * 
     * @access private
     * @return void
     */
    private function create_emergency_contact(){
        if ( array_key_exists( 'emergency_contact', $this->meta_boxes ) ){
            return;
        }
        
        $this->meta_boxes['emergency_contact'] = array(
                'id'        => 'emergency-contact',
                'title'     => 'Emergency Contact Information',
                'pages'     => array('location', 'group', 'equipment', 'service-request'),
                'context'   => 'side',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                       
                    	// contact name
                        array(
                    			'name'  => __( 'Emergency Contact', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_emergency_contact',
                    			'desc'  => __( 'In case of emergency', $this->plugin_slug ),
                    			'type'  => 'text',
                    			'placeholder' => 'John Doe',
                    			'std'   => $this->default['emergency_contact'][$this->plugin_slug . '-emergency-contact']
                    		),
                 
                        // Phone
                        array(
                    			'name'  => __( 'Phone', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_emergency_phone',
                    			'desc'  => __( 'Primary phone (cell/home/business)', $this->plugin_slug ),
                    			'type'  => 'text',
                    			'class' => 'phone',
                    			'placeholder' => '(780) 555-1234',
                    			'std'   => $this->default['emergency_contact'][$this->plugin_slug . '-emergency-phone']
                    		),
                    
                        // email
                        array(
                    			'name'  => __( 'Email', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_emergency_email',
                    			'type'  => 'text',
                    			'placeholder'   => 'name@email.com',
                    			'std'   => $this->default['emergency_contact'][$this->plugin_slug . '-emergency-email']
                    		),
                        
                      
                    )
        );// end emergency contact meta box
    }// end emergency contact meta
    
    /**
     * create_contact_info function.
     * 
     * @access private
     * @return void
     */
    private function create_contact_info(){
        
        if ( array_key_exists( 'contact', $this->meta_boxes ) ){
            return;
        }
    
        $this->meta_boxes['contact'] = array(
                'id'        => 'contact',
                'title'     => 'Contact Information',
                'pages'     => array('location', 'equipment', 'group' ),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                        // contact name
                        array(
                    			'name'  => __( 'Primary Contact', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_name',
                    			'desc'  => __( 'Main Contact', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    	
                        // Room #
                        array(
                    			'name'  => __( 'Room Number', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_room',
                    			'desc'  => __( 'Office, Suite, Apt, etc.. Number', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    		
                        // Building
                        array(
                    			'name'  => __( 'Building', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_building',
                    			'desc'  => __( 'Campus Building ex. ECERF', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                            
                        // Address
                        array(
                    			'name'  => __( 'Address', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_address',
                    			'desc'  => __( 'Street (mailing) Address', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                        
                        // City
                        array(
                    			'name'  => __( 'City', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_city',
                    			'placeholder' => 'Edmonton',
                    			'type'  => 'text',
                    		),
                        
                        // Province
                        array(
                    			'name'  => __( 'Province (State)', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_province',
                    			'placeholder' => 'AB',
                    			'type'  => 'text',                    			
                    		),
                    		
                        // postal code
                        array(
                    			'name'  => __( 'Postal (zip) Code', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_postal_code',
                    			'placeholder' => 'T6G 2V4',
                    			'type'  => 'text',                    			
                    		),
                        

                        // Country
                        array(
                    			'name'  => __( 'County', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_county',
                    			'placeholder' => 'Canada',
                    			'type'  => 'text',
                    		),
                        
                        // Phone
                        array(
                    			'name'  => __( 'Phone', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_phone',
                    			'desc'  => __( 'Primary phone (cell/home/business)', $this->plugin_slug ),
                    			'type'  => 'text',
                    			'class' => 'phone'
                    		),
                    		
                        // fax
                        array(
                    			'name'  => __( 'Fax', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_fax',
                    			'type'  => 'text',
                    			'class' => 'phone',
                    		),
                        
                       
                    	// email
                        array(
                    			'name'  => __( 'Email', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_email',
                    			'type'  => 'text',
                    			'placeholder'   => 'name@email.com',
                    		),
                        
                        // url
                        array(
                    			'name'  => __( 'Web Site', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_contact_url',
                    			'type'  => 'url',
                    			'placeholder'   => 'www.yourdomain.com',
                    		),
                        
                        // notes
                      	array(
                			'name' => __( 'Additional Notes', $this->plugin_slug ),
                			'id'   => $this->plugin_slug . '_contact_notes',
                			'type' => 'wysiwyg',
                			// Set the 'raw' parameter to TRUE to prevent data being passed through wpautop() on save
                			'raw'  => false,
                			'placeholder'  => __( 'Additional Information', $this->plugin_slug ),
                
                			// Editor settings, see wp_editor() function: look4wp.com/wp_editor
                			'options' => array(
                				'textarea_rows' => 4,
                				'teeny'         => true,
                				'media_buttons' => false,
                			),
                		),
                    )
        );// end contact meta box
    }// end contact info meta
    

}    // end class	

$obj = new MH_Deal_Manager_MetaBoxes();
$obj = null;
?>