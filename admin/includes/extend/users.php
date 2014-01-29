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
	     * @var mixed
	     * @access private
	     */
	    private $plugin_slug;
	    
	    /**
	     * __construct function.
	     * 
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
	        
	    }
		
		public function show_profile_fields( $user ){
			include MHDM_PLUGIN_DIR . '/admin/views/user-profile.php';
		}
		
		public function save_profile_fields( $user_id ){
			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;
		
			/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
			update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
		}
		
		public function edit_contact_methods( $contact_methods ) {
			$contact_methods['facebook'] = __('Facebook', $this->plugin_slug ); 
			$contact_methods['twitter']  = __('Twitter', $this->plugin_slug );
			$contact_methods['instagram']  = __('Instagram', $this->plugin_slug );
			$contact_methods['linkedin']  = __('Linked In', $this->plugin_slug );

			unset( $contact_methods['yim'] );
			unset( $contact_methods['aim'] );
			unset( $contact_methods['jabber'] );

			return $contact_methods;
		 }
		
	}
}

$obj = new MH_Deal_Manager_Users;
$obj = null;
?>