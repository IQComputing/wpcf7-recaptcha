<?php
/**
 * This file works with reCaptcha v2 to prevent Spam submissions from being stored.
 * 
 * Originally, submitting a form without interacting with the reCaptcha box would submit to Contact Form 7
 * And end up being labelled as "Spam" by Flamingo.
 * This update will only check a submission _after_ reCaptcha has been compelted.
 */

 
defined( 'ABSPATH' ) or die( 'Hey, you\'re not a flamingo! Goodbye.' );


Class IQFix_Flamingo {
	
	
	/**
	 * Class Registration, set up necessities
	 * 
	 * @return void
	 */
	public static function register() {
		
		$selection = WPCF7::get_option( 'iqfix_recaptcha' );
		
		if( empty( $selection ) ) {
			return;
		}
		
		$class = new self();
		$class->action_hooks();

	}
	
	
	/**
	 * Add any necessary action hooks
	 * 
	 * @return void
	 */
	private function action_hooks() {
		
		// See: contact-form-7/modules/flamingo.php
		// Run before priority 10 to shortcircut Flamingo hook
		add_action( 'wpcf7_submit', array( $this, 'flamingo_acceptable_mail' ), 9, 2 );
		
	}
	
	
	/**
	 * Remove the flamingo submission hook if our form has a ReCaptcha and an empty ReCaptcha response
	 * @see contact-form-7/modules/flamingo.php LN7
	 * 
	 * @param Array $types
	 * 
	 * @return Array $types
	 */
	public function flamingo_acceptable_mail( $wpcform, $result ) {
		
		$recaptcha = $wpcform->scan_form_tags( array( 'type' => 'recaptcha' ) );
		
		if( ! empty( $recaptcha ) ) {
			
			if( empty( $_POST['g-recaptcha-response'] ) ) {
				
				// Remove the Flamingo hook, don't run it until we have a successful recaptcha
				remove_action( 'wpcf7_submit', 'wpcf7_flamingo_submit', 10 );
				
			}
			
		}
		
	}
	
	
} // END IQFix_Flamingo Class


IQFix_Flamingo::register();