<?php
/**
 * 	class-shortcodes.php
 * 
 *  A class to handle all of our shortcodes.
 *  Each function represents one shortcode. Main entry point
 *  is the static public function - shortcode.
 *
 * @see http://codex.wordpress.org/Shortcode_API
 * @package   MH_Deal_Manager_Admin
 * @subpackage Shortcodes
 * @author    Michael Hume <michael.hume@ualberta.ca>
 * @license   GPL-2.0+
 * @copyright 2014 Michael Hume
 * @version    1.00.00

 *	
 */
 
 if ( ! class_exists('MHDM_Shortcodes') ) 
{ 
    class MHDM_Shortcodes 
    {
        
       /**
        *   Main Class constructor
        *   Setup hooks and filters for this plugin
        *
        *   @visibility public
        *   @since 1.00.00
        */
        public function __construct(){
        
        }
        
        /**
        *   Shortcode handler function.
        *
        *   passes the shortcode off to the proper handler based
        *   on the $tag argument. This is the main entry point to 
        *   this class.
        *
        *   @visibility public
        *   @since 1.00.00
        */
        public static function shortcode( $atts = null, $content = null, $tag = null ){
            
            return self::$tag( $atts, $content );
        }
        
                
    }
    
}
 
?>