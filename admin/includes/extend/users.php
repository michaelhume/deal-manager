<?php
/**
 * users.php
 * Extends the deafult wordpress user
 *
 * @package MH_Deal_Manager
 * @author Michael Hume
 * @since 1.0.0
 * @link http://codex.wordpress.org/Function_Reference/register_post_type#Example
 * @license GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 *
 *  @todo add help screens
 */

if ( ! class_exists( 'MH_Deal_Manager_Users' ) ) {
	class MH_Deal_Manager_Users
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
	     * user_fields
	     * 
	     * (default value: array())
	     * 
	     * @since 1.0.0
	     * @var array
	     * @access protected
	     */
	    protected $user_fields = array();
	    
	    /**
	     * __construct function.
	     * 
	     * @since 1.0.0
	     * @access public
	     * @return void
	     */
	    public function __construct(){
	        
	        $plugin = MH_Deal_Manager::get_instance();
	        $this->plugin_slug = $plugin->get_plugin_slug();
	                
			add_action( 'show_user_profile', array( $this, 'show_profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'show_profile_fields' ) );
			
			add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
			
			// modernize contact methods
			add_filter( 'user_contactmethods',array( $this, 'edit_contact_methods' ) ,10,1);
			
			$this->init_user_fields();
	        
	    }
		
		/**
		 * show_profile_fields function.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param mixed $user
		 * @return void
		 */
		public function show_profile_fields( $user ){
		
			$op  = '<h3>' . __('Deal Manager Client Information', $this->plugin_slug ) . '</h3>';
			
			foreach ( $this->user_fields as $field_group ){
				
				$op .= '<hr/>';
				$op .= '<table class="form-table">';
				
				foreach ( $field_group as $name => $fields ){
					
					if ( !is_array( $fields ) ) continue;
					
					$value = get_user_meta( $user->ID,  $name, true );
					if ( empty( $value ) && $fields['default'] )
						$value = $fields['default'];
					
					$defaults = array( 	'type' => 'text', 
										'id' => $name, 
										'class' => 'regular-text', 
										'placeholder' => '', 
										'desc' => '', 
										'label' => '', 
										'default' => false,
									 );
									 
					$fields = wp_parse_args( $fields, $defaults );
					$op .= '<tr>';
					
					if ( !empty( $fields['label'] ) )
						$op .= '<th><label for="'. $name .'">'. __( $fields['label'], $this->plugin_slug ) .'</label></th>';
					
					$op .= '<td>';
					
					switch ( $fields['type'] ){
						case 'select':
							
							if ( !is_array( $fields['options'] ) ) continue;
							ksort( $fields['options'] );
							
							$op .= 	'<select name="'. $name .'" id="'. $fields['id'] .'" class="'. $fields['class'] .'">';
							foreach ( $fields['options'] as $option ){
								$s = ( $option['value'] == $value ) ? 'selected' : '';
								$op .= '<option value="'. $option['value'] .'" '. $s .'>' . $option['label'];
							}
							$op .= '</select>';
							break;
						
						case 'text':
						case 'date':
						default:
							$op .= '<input type="'. $fields['type'] .'" name="'. $name .'" id="'. $fields['id'] .'" placeholder="'. $fields['placeholder'] .'" value="'. esc_attr ( $value ) .'" class="'. $fields['class'] .'" />';
							break;
						
					}
					
					$op .= '<span class="description">'. $fields['description'] .'</span>';
					$op .= '</td></tr>';	
				}
				
				$op .= '</table>';
			}
			
			echo $op;
		}
		
		
		/**
		 * save_profile_fields function.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param mixed $user_id
		 * @return void
		 */
		public function save_profile_fields( $user_id ){
			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;
		
			foreach ( $this->user_fields as $field_group ){
				foreach ( $field_group as $name => $fields ){
					update_usermeta( $user_id, $name, $_POST[ $name ] );						
				}
			}
		}
		
		
		/**
		 * edit_contact_methods function.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param mixed $contact_methods
		 * @return void
		 */
		public function edit_contact_methods( $contact_methods ) {
		
			unset( $contact_methods['yim'] );
			unset( $contact_methods['aim'] );
			unset( $contact_methods['jabber'] );
			
			$contact_methods['address'] = __('Address', $this->plugin_slug ); 
			$contact_methods['city'] = __('City', $this->plugin_slug ); 
			$contact_methods['province'] = __('Province (State)', $this->plugin_slug ); 
			$contact_methods['postalcode'] = __('Postal (Zip) Code', $this->plugin_slug ); 
			$contact_methods['country'] = __('Country', $this->plugin_slug ); 
			
			$contact_methods['facebook'] = __('Facebook', $this->plugin_slug ); 
			$contact_methods['twitter']  = __('Twitter', $this->plugin_slug );
			$contact_methods['instagram']  = __('Instagram', $this->plugin_slug );
			$contact_methods['linkedin']  = __('Linked In', $this->plugin_slug );

			return $contact_methods;
		 }
		 
		 
		 /**
		  * init_user_fields function.
		  *
		  *	Build an array of new user fields to add to the user profile screen
		  * 
		  * @since 1.0.0
		  * @access private
		  * @return void
		  */
		 private function init_user_fields(){
			
			// add all of our new user fields, key is fields name, followed by array of type, & default
			$this->user_fields['personal'] = array(

					'phone' => array( 'type' => 'phone',
									 'id' => 'phone', 
									 'class' => 'regular-text', 
									 'placeholder' => __('(780) 555-1234', $this->plugin_slug ), 
									 'desc' => __('Enter Phone Number', $this->plugin_slug), 
									 'label' => __('Phone', $this->plugin_slug ),
									 'default' => false,
									 ),
					
					'cell' => array( 'type' => 'phone',
									 'id' => 'cell', 
									 'class' => 'regular-text', 
									 'placeholder' => __('(780) 555-1234', $this->plugin_slug ), 
									 'desc' => __('Enter Cell Number', $this->plugin_slug), 
									 'label' => __('Cell', $this->plugin_slug ),
									 'default' => false,
									 ),

					
					'birthdate' => array( 	'type' => 'date', 
											'id' => 'birthdate', 
											'class' => 'regular-text', 
											'placeholder' => '', 
											'desc' => __('Enter Brithdate', $this->plugin_slug), 
											'label' => __('Date of Birthdate', $this->plugin_slug ),
											'default' => false, 
										 ),
					
					'marital_status' => array(	'type' => 'select', 
												'id' => 'marital_status', 
												'class' => '', 'placeholder' => '', 
												'desc' => __('Select Marital Status', $this->plugin_slug), 
												'label' => __('Marital Status', $this->plugin_slug ), 
												'default' => false,
												'options' => array(
															0 => array( 'value' => 0, 'label' => __(' --- ', $this->plugin_slug )),
															1 => array( 'value' => 'single', 'label' => __('Single', $this->plugin_slug )),
															2 => array( 'value' => 'married', 'label' => __('Married', $this->plugin_slug )),
															3 => array( 'value' => 'divorced', 'label' => __('Divorced', $this->plugin_slug )),
															4 => array( 'value' => 'widow', 'label' => __('Widowed', $this->plugin_slug ))
													), 
											),
					
					'citizenship' => array(		'type' => 'select', 
												'id' => 'citizenship', 
												'class' => '', 'placeholder' => '', 
												'desc' => __('Select Citizenship', $this->plugin_slug), 
												'label' => __('Citizenship', $this->plugin_slug ), 
												'default' => false,
												'options' => array(
															0 => array( 'value' => 0, 'label' => __(' --- ', $this->plugin_slug )),
															1 => array( 'value' => 'canadian', 'label' => __('Canadian', $this->plugin_slug )),
															2 => array( 'value' => 'permanent_resident', 'label' => __('Permanent Resident', $this->plugin_slug )),
															3 => array( 'value' => 'landed_immigrant', 'label' => __('Landed Immigrant', $this->plugin_slug )),
															4 => array( 'value' => 'other', 'label' => __('Other', $this->plugin_slug ))
													), 
											),
											
					'dependants' => array( 	'type' => 'number', 
											'id' => 'dependants', 
											'class' => 'regular-text', 
											'placeholder' => '', 
											'desc' => __('Enter number of dependants under 18', $this->plugin_slug), 
											'label' => __('Dependants', $this->plugin_slug ), 
											'default' => false,
										),
					
					);
			 
		 }
	}
}

$obj = new MH_Deal_Manager_Users;
$obj = null;
?>