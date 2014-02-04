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
if ( ! class_exists( 'MH_Deal_Manager_MetaBoxes' ) ) {
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
    	if ( array_key_exists('requirement', $post_types ) ){ $this->requirement_meta(); }
    	if ( array_key_exists('associate', $post_types ) ){ $this->associate_meta(); }
    	if ( array_key_exists('property', $post_types ) ){ $this->property_meta(); }

        foreach ( (array) $this->meta_boxes as $meta_box ){
    		new RW_Meta_Box( $meta_box );
    	}
    	
    }
    
   
    /**
     * deal_meta function.
     *
     * @TODO restrict user select to client role
     * @TODO create client role on activation
     * @TODO add ability to copy deal and add copied from meta
     * 
     * @access private
     * @return void
     */
    private function deal_meta(){
    
		 $this->create_emergency_contact();
        
         $this->meta_boxes['deal-required'] = array(
                'id'        => 'deal-required',
                'title'     => __('Required Information', $this->plugin_slug),
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
                			'name'          => __( 'Deal Type', $this->plugin_slug ),
                			'desc'          => __( 'Type of deal of the application', $this->plugin_slug),
                			'id'            => $this->plugin_slug . '_deal_type',
                			'type'          => 'taxonomy',
			                'placeholder'   => 'Select Type',
                			'options' => array(
				                				'taxonomy' => 'deal_type',
				                				'type' => 'select_advanced',
				                				'args' => array( 'hide_empty' => false )
				                                ),
                        ),
                        
                        array(
                        	'name'			=> __( 'Broker(s)', $this->plugin_slug ),
                			'desc' 			=> __( 'The Mortgage Broker(s) on this deal', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_broker',
                			'type'          => 'user',
                			'clone'			=> true,
			                'placeholder'   => 'Select Broker',
							'field_type'    => 'select_advanced',
                			'query_args'    => array(
					                				'role' => ''
					                                ),

                        ),
                        
                         array(
                			'name'          => __( 'Client(s)', $this->plugin_slug ),
                			'desc'			=> __( 'The client(s) associated with this deal' ),
                			'id'            => $this->plugin_slug . '_client',
                			'type'          => 'user',
                			'clone'			=> true,
			                'placeholder'   => 'Select Client',
							'field_type'    => 'select_advanced',
                			'multiple'		=> false,
                			'query_args'    => array(
					                				'role' => ''
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
        		);// end deal-required meta 

			$this->meta_boxes['deal-financial'] = array(
                'id'        => 'deal-financial',
                'title'     => __('Financial Information', $this->plugin_slug),
                'pages'     => array('deal'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,

                'fields'    => array(
                        array(
                        	'name'			=> __( 'Purchase Amount', $this->plugin_slug ),
                			'desc' 			=> __( 'Purchase Amount', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_purchase_amount',
                			'clone' 		=> false,
                			'type' 			=> 'number',
                            'placeholder'   => '$500,000',

                        ),
                        
                         array(
                        	'name'			=> __( 'Down Payment', $this->plugin_slug ),
                			'desc' 			=> __( 'Client Down Payment', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_down_payment',
                			'clone' 		=> false,
                			'type' 			=> 'number',
                            'placeholder'   => '$50,000',

                        ),
                        
                         array(
                        	'name'			=> __( 'Mortgage Amount', $this->plugin_slug ),
                			'desc' 			=> __( 'Mortgage Amount', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_mortgage_amount',
                			'clone' 		=> false,
                			'type' 			=> 'number',
                            'placeholder'   => '$450,000',

                        ),
                        
                        array(
                        	'name'			=> __( 'Interest Rate', $this->plugin_slug ),
                			'desc' 			=> __( 'Interest Rate', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_interest_rate',
                			'clone' 		=> false,
                			'type' 			=> 'number',
                            'placeholder'   => '35',
                            'suffix' 		=> __( ' %', $this->plugin_slug ),

                        ),
                        
                         array(
                        	'name'			=> __( 'Amortization Period', $this->plugin_slug ),
                			'desc' 			=> __( 'Amortization Period', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_amortization_period',
                			'type' 			=> 'slider',
							'suffix' 		=> __( ' Years', $this->plugin_slug ),
							'js_options' 	=> array(
												'min'   => 5,
												'max'   => 40,
												'step'  => 1,
												)
                        ),
                        
                ),// end fields
                        
            	'validation' => array(
                    'rules' => array(
                			$this->plugin_slug . '_purchase_amount' => array(
                				'required'  => false,
                				'number' => true,
                                ),
                            $this->plugin_slug . '_down_payment' => array(
                				'required'  => false,
                				'number' => true,
                                ),
                            $this->plugin_slug . '_mortgage_amount' => array(
                				'required'  => false,
                				'number' => true,
                                ),
                            $this->plugin_slug . '_interest_rate' => array(
                				'required'  => false,
                				'number' => true,
                                ),
                            $this->plugin_slug . '_amortization_period' => array(
                				'required'  => false,
                				'number' => true,
                                ),
                                
                		),
                		// optional override of default jquery.validate messages
                	'messages' => array(
                			$this->plugin_slug . '_purchase_amount' => array(
                				'number'  => __( 'Please Enter a Valid Number', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_down_payment' => array(
                				'number'  => __( 'Please Enter a Valid Number', $this->plugin_slug ),
                                ),
                            $this->plugin_slug . '_mortgage_amount' => array(
	            				'number'  => __( 'Please Enter a Valid Number', $this->plugin_slug ),
	                            ),
	                        $this->plugin_slug . '_interest_rate' => array(
	            				'number'  => __( 'Please Enter a Valid Number', $this->plugin_slug ),
	                            ),
	                        $this->plugin_slug . '_amortization_period' => array(
	            				'number'  => __( 'Please Enter a Valid Number', $this->plugin_slug ),
	                            ),
                		)
                    )// end validation
        		);// end deal-financial meta 
	
		// do some special stuff here to pull list of associate tax terms to build lists
			$terms = get_terms( 'associate_type' );
			foreach ( $terms as $term ){
	            
	            $fields[] = array(
                        	'name'			=> __( $term->name . '(s)', $this->plugin_slug ),
                			'desc' 			=> __( 'Add ' . ucwords( $term->name ), $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_' . $term->name,
                			'clone' 		=> true,
                			'type' 			=> 'post',
                			'post_type' 	=> 'associate',
                            'placeholder'   => 'Select ' . ucwords( $term->name ),
                            
                            'field_type'    => 'select_advanced',
                			'query_args'    => array(
					                				'associate_type' => $term->name,
					                                ),
                        );
				$validation['rules'][ $this->plugin_slug . '_' . $term->name ] = array( 'required' => false );
				$validation['messages'][ $this->plugin_slug . '_' . $term->name ] = array( 'required' => '' );
	        } // end foreach term
			
			$this->meta_boxes['deal-associates'] = array(
                'id'        => 'deal-associates',
                'title'     => __('Associates', $this->plugin_slug),
                'pages'     => array('deal'),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => $fields,
            	'validation' => $validation, 
            	
        		);// end deal-required meta 
        	
        	$this->meta_boxes['deal-property'] = array(
                'id'        => 'deal-property',
                'title'     => __('Properties', $this->plugin_slug),
                'pages'     => array('deal'),
                'context'   => 'normal',
                'priority'  => 'low',
                'autosave'  => true,
                'fields'    => array(
                        array(
                        	'name'			=> __( 'Property', $this->plugin_slug ),
                			'desc' 			=> __( 'Add Property', $this->plugin_slug ),
                			'id'   			=> $this->plugin_slug . '_property',
                			'clone' 		=> true,
                			'type' 			=> 'post',
                			'post_type'		=> 'property',
                            'field_type'    => 'select_advanced',
                            'placeholder'	=> 'Select Property',
                            'query_args'    => array(),

                        ),
                 ), // end fields
        		);// end deal-required meta 	        		
    
    } // end deal meta

    
    /**
     * property_meta function.
     * 
     * @access private
     * @return void
     */
    private function property_meta(){
		
		$this->meta_boxes['property-listing'] = array(
            'id'        => 'property-listing',
            'title'     => __('Listing Information', $this->plugin_slug),
            'pages'     => array('property'),
            'context'   => 'normal',
            'priority'  => 'high',
            'autosave'  => true,

            'fields'    => array(
            
            				array(
                    			'name'  => __( 'MLS', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_mls',
                    			'desc'  => __( 'MLS Number', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    		 array(
		            			'name'          => __( 'Listing Agent(s)', $this->plugin_slug ),
		            			'desc'			=> __( 'Agent for the seller', $this->plugin_slug ),
		            			'id'            => $this->plugin_slug . '_listing_agent',
		            			'type'          => 'post',
		            			'post_type'		=> 'associate',
		            			'clone'			=> true,
				                'placeholder'   => 'Select Realtor',
								'field_type'    => 'select_advanced',
		            			'query_args'    => array(
		            								'associate_type' => 'realtor',
					            				),
	                        ),
	                        
	                        array(
		            			'name'          => __( 'Buyer Agent(s)', $this->plugin_slug ),
		            			'desc'			=> __( 'Agent for the purchaser', $this->plugin_slug ),
		            			'id'            => $this->plugin_slug . '_buyer_agent',
		            			'type'          => 'post',
		            			'post_type'		=> 'associate',
		            			'clone'			=> true,
				                'placeholder'   => 'Select Realtor',
								'field_type'    => 'select_advanced',
		            			'query_args'    => array(
		            								'associate_type' => 'realtor',
					            				),
	                        ),
                        
	                        array(
	                    			'name'  => __( 'List Price', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_price',
	                    			'placeholder' => '$125,000',
	                    			'type'  => 'number',
	                    		),
	                        
	                        array(
	                    			'name'  => __( 'Time on Market', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_time_on_market',
	                    			'type'  => 'slider',
	                    			'suffix' 		=> __( ' Months', $this->plugin_slug ),
									'js_options' 	=> array(
														'min'   => 0,
														'max'   => 36,
														'step'  => 1,
														)                    			
	                    		),
            	
			), // end fields
		);
		
		$this->meta_boxes['property-location'] = array(
            'id'        => 'property-location',
            'title'     => __('Location', $this->plugin_slug),
            'pages'     => array('property'),
            'context'   => 'normal',
            'priority'  => 'high',
            'autosave'  => true,

            'fields'    => array(
            
            				array(
                    			'name'  => __( 'Legal Land Description', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_lot',
                    			'desc'  => __( 'Lot #, Site', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    		array(
                    			'name'  => __( 'Address', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_address',
                    			'desc'  => __( 'Street (mailing) Address', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                        
	                        // City
	                        array(
	                    			'name'  => __( 'City', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_city',
	                    			'placeholder' => 'Edmonton',
	                    			'type'  => 'text',
	                    		),
	                        
	                        // Province
	                        array(
	                    			'name'  => __( 'Province (State)', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_province',
	                    			'placeholder' => 'AB',
	                    			'type'  => 'text',                    			
	                    		),
	                    		
	                        // postal code
	                        array(
	                    			'name'  => __( 'Postal (zip) Code', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_postal_code',
	                    			'placeholder' => 'T6G 2V4',
	                    			'type'  => 'text',                    			
	                    		),
	                        
	
	                        // Country
	                        array(
	                    			'name'  => __( 'County', $this->plugin_slug ),
	                    			'id'    => $this->plugin_slug . '_county',
	                    			'placeholder' => 'Canada',
	                    			'type'  => 'text',
	                    		),

            	
			), // end fields
		);

			
	}


    /**
     * requirement_meta function.
     * 
     * @access private
     * @return void
     */
    private function requirement_meta(){
		
		$this->meta_boxes['requirement'] = array(
            'id'        => 'requirement',
            'title'     => __('Required Information', $this->plugin_slug),
            'pages'     => array('requirement'),
            'context'   => 'normal',
            'priority'  => 'high',
            'autosave'  => true,

            'fields'    => array(
                                        
                     array(
	            			'name'          => __( 'Deal', $this->plugin_slug, $this->plugin_slug ),
	            			'desc'			=> __( 'The deal to link this requirement to' ),
	            			'id'            => $this->plugin_slug . '_deal',
	            			'type'          => 'post',
	            			'post_type'		=> 'deal',
	            			'clone'			=> false,
			                'placeholder'   => 'Select Deal',
							'field_type'    => 'select_advanced',
	            			'query_args'    => array(),
                        ),
                     
                     array(
	            			'name'          => __( 'Due Date', $this->plugin_slug ),
	            			'desc'			=> __( 'Enter the date the requirement must be met by', $this->plugin_slug ),
	            			'id'            => $this->plugin_slug . '_date',
	            			'type'          => 'date',
	            			'js_options' 	=> array(
										'appendText'      => __( ' (yyyy-mm-dd)', $this->plugin_slug ),
										'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
										'changeMonth'     => true,
										'changeYear'      => true,
										'showButtonPanel' => true,
									),
                        ),
                     
                     array(
	            			'name'          => __( 'Requested By', $this->plugin_slug ),
	            			'desc'			=> __( 'Who wants this requirement', $this->plugin_slug ),
	            			'id'            => $this->plugin_slug . '_requested_by',
	            			'type'          => 'post',
	            			'post_type'		=> 'associate',
	            			'clone'			=> true,
			                'placeholder'   => 'Select Associate',
							'field_type'    => 'select_advanced',
	            			'query_args'    => array(),
                        ),
                        
                        
					array(
							'name' 				=> __( 'Sample', $this->plugin_slug ),
							'desc'				=> __( 'Upload a Sample?', $this->plugin_slug ),
							'id'   				=> $this->plugin_slug . '_sample',
							'type' 				=> 'file',
							'max_file_uploads' 	=> 4,
							'mime_type' 		=> '', // Leave blank for all file types
						),
						
						
            ),// end fields
            
    	);// end deal-required meta 


    } // end requirement meta
    
     /**
     * associate_meta function.
     * 
     * @access private
     * @return void
     */
    private function associate_meta(){
        
        $this->create_emergency_contact();

        $this->meta_boxes['associate-personal'] = array(
                'id'        => 'associate-personal',
                'title'     => __('Personal Information', $this->plugin_slug ),
                'pages'     => array('associate' ),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                		// contact title
                        array(
                    			'name'  		=> __( 'Title', $this->plugin_slug ),
                    			'id'    		=> $this->plugin_slug . '_title',
                    			'desc'  		=> __( 'Formal Salutation', $this->plugin_slug ),
                    			'type'  		=> 'select_advanced',
	                    		'options'  		=> array(
													'mr' 		=> __( 'Mr.',  $this->plugin_slug ),
													'mrs' 		=> __( 'Mrs.',  $this->plugin_slug ),
													'ms' 		=> __( 'Ms.',  $this->plugin_slug ),
													'dr' 		=> __( 'Dr.',  $this->plugin_slug ),
													'sir' 		=> __( 'Sir',  $this->plugin_slug ),
													'madam' 	=> __( 'Madam',  $this->plugin_slug ),
													
												),
								'multiple'    	=> false,
								'placeholder' 	=> __( 'Select a Title',  $this->plugin_slug ),

                    		),
                    		
                        // first name
                        array(
                    			'name'  => __( 'First Name', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_first_name',
                    			'desc'  => __( 'Associate First Name', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    	
                        // last name
                        array(
                    			'name'  => __( 'Last Name', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_last_name',
                    			'desc'  => __( 'Associate Last Name', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    	
                    	// birthdate
                    	array(
	            			'name'          => __( 'Birth Date', $this->plugin_slug ),
	            			'desc'			=> __( 'Why not remember?', $this->plugin_slug ),
	            			'id'            => $this->plugin_slug . '_brithdate',
	            			'type'          => 'date',
	            			'js_options' 	=> array(
										'appendText'      => __( ' (yyyy-mm-dd)', $this->plugin_slug ),
										'dateFormat'      => __( 'yy-mm-dd', $this->plugin_slug ),
										'changeMonth'     => true,
										'changeYear'      => true,
										'showButtonPanel' => true,
									),
                        ),
                                        		
                	), // end fields
                ); // end associate-personal
                
        $this->meta_boxes['associate-company'] = array(
                'id'        => 'associate-company',
                'title'     => __('Professional Information', $this->plugin_slug ),
                'pages'     => array('associate' ),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                		
                		array(
                    			'name'  		=> __( 'Company', $this->plugin_slug ),
                    			'id'    		=> $this->plugin_slug . '_company',
                    			'desc'  		=> __( 'Add a company', $this->plugin_slug ),
                    			'type'  		=> 'text',
	                    		'clone'			=> true,

                    		),

                        array(
                    			'name'  => __( 'Position', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_position',
                    			'desc'  => __( 'Official position', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    	
						array(
                    			'name'  => __( 'Manager', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_manager',
                    			'desc'  => __( 'In the event of an escalation', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                    	
                    	array(
	            			'name'          => __( 'Length at position', $this->plugin_slug ),
	            			'desc'			=> __( 'How long has they been there (years)', $this->plugin_slug ),
	            			'id'            => $this->plugin_slug . '_position_years',
	            			'type'          => 'slider',
	            			'suffix' 		=> __( ' years', $this->plugin_slug ),
	            			'js_options' => array(
												'min'   => 0,
												'max'   => 50,
												'step'  => 1,
											),
                        ),
                        
                        array(
	            			'name'          => __( '&nbsp;', $this->plugin_slug ),
	            			'desc'			=> __( 'How long has they been there (months)', $this->plugin_slug ),
	            			'id'            => $this->plugin_slug . '_position_months',
	            			'type'          => 'slider',
	            			'suffix' 		=> __( ' months', $this->plugin_slug ),
	            			'js_options' => array(
												'min'   => 0,
												'max'   => 12,
												'step'  => 1,
											),
                        ),
                        
                	), // end fields
                ); // end associate-company
                
        $this->meta_boxes['associate-social'] = array(
                'id'        => 'associate-social',
                'title'     => __('Social Information', $this->plugin_slug ),
                'pages'     => array('associate' ),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                
                				array(
		                    			'name'  => __( 'Facebook', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_facebook',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'http://www.facebook.com/',
		                    			'clone'			=> true,
		                    		),
		                    	
		                    	array(
		                    			'name'  => __( 'Google+', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_google',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'https://plus.google.com/',
		                    			'clone'			=> true,
		                    		),
		                    	
		                    	array(
		                    			'name'  => __( 'Twitter', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_twitter',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'http://twitter.com/',
		                    			'clone'			=> true,
		                    		),
		                    	
		                    	array(
		                    			'name'  => __( 'Linked In', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_linkedin',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'https://linkedin.com/',
		                    			'clone'			=> true,
		                    		),
		                    		
		                    	array(
		                    			'name'  => __( 'Instagram', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_instagram',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'https://instagram.com/',
		                    			'clone'			=> true,
		                    		),				

                			    array(
		                    			'name'  => __( 'Email', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_email',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'name@email.com',
		                    			'clone'			=> true,
		                    		),
		                        
		                        array(
		                    			'name'  => __( 'Web Site', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_url',
		                    			'type'  => 'url',
		                    			'placeholder'   => 'www.yourdomain.com',
		                    			'clone' 		=> true,
		                    		),
		                    	
		                    	array(
		                    			'name'  => __( 'Other', $this->plugin_slug ),
		                    			'id'    => $this->plugin_slug . '_other',
		                    			'type'  => 'text',
		                    			'placeholder'   => 'WhatsApp? AIM? Yahoo? IM? etc...',
		                    			'clone' 		=> true,
		                    		),
                
                )
        );
        
        
        $this->meta_boxes['associate-mail'] = array(
                'id'        => 'associate-mail',
                'title'     => __('Old School Contact', $this->plugin_slug ),
                'pages'     => array('associate' ),
                'context'   => 'normal',
                'priority'  => 'high',
                'autosave'  => true,
                'fields'    => array(
                        // Address
                        array(
                    			'name'  => __( 'Address', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_address',
                    			'desc'  => __( 'Street (mailing) Address', $this->plugin_slug ),
                    			'type'  => 'text',
                    		),
                        
                        // City
                        array(
                    			'name'  => __( 'City', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_city',
                    			'placeholder' => 'Edmonton',
                    			'type'  => 'text',
                    		),
                        
                        // Province
                        array(
                    			'name'  => __( 'Province (State)', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_province',
                    			'placeholder' => 'AB',
                    			'type'  => 'text',                    			
                    		),
                    		
                        // postal code
                        array(
                    			'name'  => __( 'Postal (zip) Code', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_postal_code',
                    			'placeholder' => 'T6G 2V4',
                    			'type'  => 'text',                    			
                    		),
                        

                        // Country
                        array(
                    			'name'  => __( 'County', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_county',
                    			'placeholder' => 'Canada',
                    			'type'  => 'text',
                    		),
                        
                        // Phone
                        array(
                    			'name'  => __( 'Phone', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_phone',
                    			'desc'  => __( 'Primary phone (cell/home/business)', $this->plugin_slug ),
                    			'type'  => 'text',
                    			'class' => 'phone',
                    			'clone'	=> true,
                    		),
                    		
                        // fax
                        array(
                    			'name'  => __( 'Fax', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_fax',
                    			'type'  => 'text',
                    			'class' => 'phone',
                    		),
                      )
        );// end contact meta box
    }// end associate info meta


          
    
// !-- Multi Use meta box
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
                'title'     => __('Emergency Contact Information', $this->plugin_slug ),
                'pages'     => array('property', 'deal', 'associate'),
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
                    			'size' 	=> '25',
                    			'placeholder' => 'John Doe',
                    		),
                 
                        // Phone
                        array(
                    			'name'  => __( 'Phone', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_emergency_phone',
                    			'desc'  => __( 'Primary phone (cell/home/business)', $this->plugin_slug ),
                    			'type'  => 'text',
                    			'class' => 'phone',
                    			'size' 	=> '25',
                    			'placeholder' => '(780) 555-1234',
                    		),
                    
                        // email
                        array(
                    			'name'  => __( 'Email', $this->plugin_slug ),
                    			'id'    => $this->plugin_slug . '_emergency_email',
                    			'type'  => 'text',
                    			'size' 	=> '25',
                    			'placeholder'   => 'name@email.com',
                    		),
                        
                      
                    )
        );// end emergency contact meta box
    }// end emergency contact meta
    
       

}    // end class	
}
$obj = new MH_Deal_Manager_MetaBoxes();
$obj = null;
?>