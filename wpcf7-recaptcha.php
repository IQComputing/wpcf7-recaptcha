<?php
/**
 * Plugin Name: ReCaptcha v2 for Contact Form 7
 * Description: ReCaptcha v2 Fix for Contact Form 7 5.1 and later.
 * Version: 1.4.5
 * Author: IQComputing
 * Author URI: http://www.iqcomputing.com/
 * License: GPL2
 * Text Domain: wpcf7-recaptcha
 */


defined( 'ABSPATH' ) or die( 'You cannot be here.' );


/**
 * IQComputing Contact Form 7 reCaptcha Fix, Deity Class
 */
Class IQFix_WPCF7_Deity {


	/**
	 * WPCF7 ReCaptcha Plugin Version
	 *
	 * @var String
	 */
	public static $version = '1.4.5';


	/**
	 * Class Registration, set up necessities
	 *
	 * @return void
	 */
	public static function register() {

		$class = new self();
		$class->action_hooks();
		$class->filter_hooks();
		$class->include_files();

	}


	/**
	 * Really don't like dynamically assigning URLs by user saved options.
	 * This method will verify at every stage that the given value is either
	 * `google.com` or `recaptcha.net`
	 * Nowhere in between can this value be changed to anything but those two.
	 *
	 * @param $expectation
	 *
	 * @return $reality
	 */
	public static function verify_recaptcha_source( $expectation = 'google.com' ) {

		$reality = ( in_array( $expectation, array( 'google.com', 'recaptcha.net' ) ) ) ? $expectation : 'google.com';

		return $reality;

	}


	/**
	 * Include any necessary files
	 *
	 * @return void
	 */
	private function include_files() {

		$selection 		= WPCF7::get_option( 'iqfix_recaptcha' );
		$cf7_version 	= ( defined( 'WPCF7_VERSION' ) ) ? WPCF7_VERSION : WPCF7::get_option( 'version', '0' );

		// Prevent update from v2 to v3 notice.
		WPCF7::update_option( 'recaptcha_v2_v3_warning', false );

		if( empty( $selection ) || version_compare( $cf7_version, '5.1', '<' ) ) {
			return;
		}

		include_once( plugin_dir_path( __FILE__ ) . 'recaptcha-v2.php' );

		if( class_exists( 'Flamingo_Contact' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'flamingo.php' );	// Flamingo updates
		}

	}


	/**
	 * Save the reCaptcha settings from our options page
	 * @see IQFix_WPCF7_Deity::display_recaptcha_version_subpage()
	 *
	 * @return Boolean
	 */
	private function save_recaptcha_settings() {

		// Form hasn't POSTed, return early
		if( ! isset( $_POST, $_POST['iqfix_recaptcha_version'], $_POST['iqfix_wpcf7_submit'] ) ) {
			return false;
		}

		// Ensure we have and can verify our nonce. IF not, return early
		if( ! ( ! empty( $_POST['iqfix_wpcf7_nonce'] ) && wp_verify_nonce( $_POST['iqfix_wpcf7_nonce'], 'iqfix_wpcf7_vers_select' ) ) ) {
			return false;
		}

		$selection 	= intval( $_POST['iqfix_recaptcha_version'] );
		$source		= ( isset( $_POST, $_POST['iqfix_recaptcha_source'] ) ) ? sanitize_text_field( $_POST['iqfix_recaptcha_source'] ) : 'google.com';
		$source		= self::verify_recaptcha_source( $source );

		// Save Network Settings
		if( is_network_admin() && isset( $_POST['wpcf7_recaptcha_network'] ) ) {

			$sitekey 	= trim( $_POST['wpcf7_recaptcha_network']['sitekey'] );
			$secretkey 	= trim( $_POST['wpcf7_recaptcha_network']['secretkey'] );

			update_site_option( 'network_iqfix_recaptcha', array(
				'sitekey' 			=> $sitekey,
				'secret'			=> $secretkey,
				'iqfix_recaptcha'	=> $selection,
				'recaptcha_source'	=> $source,
			) );

		// Save Regular WPCF7 Settings
		} else {

			WPCF7::update_option( 'iqfix_recaptcha', 		$selection 	);
			WPCF7::update_option( 'iqfix_recaptcha_source', $source 	);

		}

		return true;

	}


	/**
	 * Add any necessary action hooks
	 *
	 * @return void
	 */
	private function action_hooks() {

		add_action( 'admin_menu',			array( $this, 'register_submenus' ) );
		add_action( 'network_admin_menu', 	array( $this, 'register_network_submenus' ) );

	}


	/**
	 * Add any necessary filter hooks
	 *
	 * @return void
	 */
	private function filter_hooks() {

		add_filter( 'option_wpcf7', array( $this, 'network_wpcf7_options' ), 9 );

	}


	/**
	 * Register submenus for picking ReCaptcha versions
	 *
	 * @return void
	 */
	public function register_submenus() {

		$cf7_admin_cap = ( defined( 'WPCF7_ADMIN_READ_WRITE_CAPABILITY' ) ) ? WPCF7_ADMIN_READ_WRITE_CAPABILITY : 'publish_pages';

		add_submenu_page(
			'wpcf7',
			esc_html__( 'reCaptcha Version', 'wpcf7-recaptcha' ),
			esc_html__( 'reCaptcha Version', 'wpcf7-recaptcha' ),
			$cf7_admin_cap,
			'recaptcha-version',
			array( $this, 'display_recaptcha_version_subpage' )
		);

	}


	/**
	 * Register submenus for the Network Admin Panel
	 *
	 * @return void
	 */
	public function register_network_submenus() {

		add_submenu_page(
			'plugins.php',
			esc_html__( 'WPCF7 reCaptcha Settings', 'wpcf7-recaptcha' ),
			esc_html__( 'WPCF7 reCaptcha Settings', 'wpcf7-recaptcha' ),
			'manage_network_plugins',
			'recaptcha-version',
			array( $this, 'display_recaptcha_version_subpage' )
		);

	}


	/**
	 * Display reCaptcha version subpage
	 *
	 * @return void
	 */
	public function display_recaptcha_version_subpage() {

		$updated 		= $this->save_recaptcha_settings();
		$cf7_version 	= ( defined( 'WPCF7_VERSION' ) ) ? WPCF7_VERSION : WPCF7::get_option( 'version', '0' );

		// Grab Network Settings
		if( is_network_admin() ) {

			$network_options = get_site_option( 'network_iqfix_recaptcha' );
			$selection		 = ( ! empty( $network_options['iqfix_recaptcha'] ) ) 	? $network_options['iqfix_recaptcha'] 	: '';
			$source			 = ( ! empty( $network_options['recaptcha_source'] ) )	? $network_options['recaptcha_source']	: '';
			$sitekey		 = ( ! empty( $network_options['sitekey'] ) ) 			? $network_options['sitekey'] 			: '';
			$secretkey		 = ( ! empty( $network_options['secret'] ) ) 			? $network_options['secret'] 			: '';

		// Grab Site Settings
		} else {

			$selection 	= WPCF7::get_option( 'iqfix_recaptcha' );
			$source 	= WPCF7::get_option( 'iqfix_recaptcha_source' );

		}

		// Show simple message
		if( version_compare( $cf7_version, '5.1', '<' ) ) {
			printf(
				'<div class="wrap"><h1>%1$s</h1><p>%2$s</p></div>',
				esc_html__( 'ReCaptcha v2 for Contact Form 7', 'wpcf7-recaptcha' ),
				esc_html__( 'This version of Contact Form 7 already uses reCaptcha version 2, you do not need \'ReCaptcha v2 for Contact Form 7\' installed at this time.', 'wpcf7-recaptcha' )
			);
			return;
		}

		?>

			<div class="wrap">
				<style>
					#iqFacebook		{margin-top: 40px;}
					#iqFacebook a	{display: inline-block; margin-bottom: 12px;}
					#iqFacebook img	{display: block; max-width: 100%; height: auto;}
				</style>

				<?php

					printf( '<h1>%1$s</h1>', esc_html__( 'ReCaptcha v2 for Contact Form 7', 'wpcf7-recaptcha' ) );

					if( $updated ) {
						printf(
							'<div class="notice updated"><p>%1$s</p></div>',
							esc_html__( 'Your reCaptcha settings have been updated.', 'wpcf7-recaptcha' )
						);
					}

					/* translators: %s is a shortcode example wrapped in <code> tags. */
					printf( '<p>%1$s</p>',
						sprintf( esc_html__( 'Select the version of reCaptcha you would like to use. You will still need to use the %s shortcode tag in your Contact Form 7 forms.', 'wpcf7-recaptcha' ),
							'<code>[recaptcha]</code>'
						)
					);
				?>

				<form method="post">
					<?php wp_nonce_field( 'iqfix_wpcf7_vers_select', 'iqfix_wpcf7_nonce' ); ?>

					<label for="iqfix_recaptcha_version"><strong><?php esc_html_e( 'Select reCaptcha Usage', 'wpcf7-recaptcha' ); ?>:</strong></label><br />
					<select id="iqfix_recaptcha_version" name="iqfix_recaptcha_version">
						<option value="0"><?php esc_html_e( 'Default Usage', 'wpcf7-recaptcha' ); ?></option>
						<option value="2" <?php selected( $selection, 2, true ); ?>><?php esc_html_e( 'reCaptcha Version 2', 'wpcf7-recaptcha' ); ?></option>
					</select>

					<?php printf( '<p>%s</p>', esc_html__( 'If you\'re not sure if your country blocks Google then you may leave this as default. Otherwise, if your country blocks google.com requests then please select the suggested recaptcha.net alternative below.', 'wpcf7-recaptcha' ) ); ?>

					<label for="iqfix_recaptcha_source"><strong><?php esc_html_e( 'Select reCaptcha Source', 'wpcf7-recaptcha' ); ?>:</strong></label><br />
					<select id="iqfix_recaptcha_source" name="iqfix_recaptcha_source">
						<option value="google.com">google.com</option>
						<option value="recaptcha.net" <?php selected( $source, 'recaptcha.net', true ); ?>>recaptcha.net</option>
					</select>

					<?php if( is_network_admin() ) : ?>

						<hr />
						<h2><?php esc_html__( 'Network Wide Settings', 'wpcf7-recaptcha' ); ?></h2>

						<p><strong><?php _e( 'Please read all of the below before committing to these changes.', 'wpcf7-recaptcha' ); ?></strong></p>
						<p><?php _e( 'You may set Network wide API keys below. Please ensure that every network site is on the domain list in the Google API Consolefor this API key. ReCaptcha keys can still be set ( or unset ) on a per site basis if necessary.', 'wpcf7-recaptcha' ); ?></p>
						<p><?php
							/* translators: Care for HTML in string used for emphasis. */
							_e( 'Do note that these keys will automatically apply to all network websites upon save <strong>if keys are not detected</strong>. If some network websites use reCaptcha v3 please <u>do not use this option</u> and set it on a per site level.', 'wpcf7-recaptcha' );
						?></p>

						<table class="form-table">
							<tbody>
								<tr>
									<th><?php _e( 'Site Key', 'wpcf7-recaptcha' ); ?></th>
									<td><input type="text" name="wpcf7_recaptcha_network[sitekey]" class="regular-text" value="<?php echo esc_attr( $sitekey ); ?>" /></td>
								</tr>
								<tr>
									<th><?php _e( 'Secret Key', 'wpcf7-recaptcha' ); ?></th>
									<td><input type="password" name="wpcf7_recaptcha_network[secretkey]" class="regular-text" value="<?php echo esc_attr( $secretkey ); ?>" /></td>
								</tr>
							</tbody>
						</table>

					<?php endif; ?>

					<?php submit_button( esc_html__( 'Submit', 'wpcf7-recaptcha' ), 'submit', 'iqfix_wpcf7_submit' ); ?>
				</form>

				<div id="iqFacebook">
					<?php
						printf( '<a href="%1$s" target="_blank"><img src="%2$s" width="540" height="410" alt="%3$s" /></a>',
							esc_url( 'https://www.facebook.com/iqcomputing' ),
							plugins_url( 'assets/images/facebook-like.png', __FILE__ ),
							/* translators: Image alternative tag. */
							esc_attr__( 'Like IQComputing on Facebook mascot', 'wpcf7-recaptcha' )
						);
						printf( '<p>%1$s</p>',
							esc_html__( 'Click the image above and like us on Facebook! Facebook is where you will receive the latest news on both this plugin and all future IQComputing plugins.', 'wpcf7-recaptcha' )
						);
					?>
				</div> <!-- id="iqFacebook" -->

			</div>

		<?php

	}


	/**
	 * Filter Hook into WPCF7 get option
	 * Maybe replace it work a network option
	 *
	 * @param Mixed $value
	 * @param Stirng $option_name
	 *
	 * @return Mixed $value
	 */
	public function network_wpcf7_options( $value ) {

		// If we're not on a multisite setup we can skip this filter hook
		if( ! is_multisite() || empty( $value ) ) {
			return $value;
		}

		// Allow sites to be setup regardless of network specifciations
		if( is_admin()
			&& isset( $_GET, $_GET['page'], $_GET['service'], $_GET['action'] )
			&& 'wpcf7-integration' == $_GET['page']
			&& 'recaptcha' == $_GET['service']
			&& 'setup' == $_GET['action']
		) {
			return $value;
		}

		// Grab Network Option
		$network_wpcf7 = get_site_option( 'network_iqfix_recaptcha' );
		$network_wpcf7 = array_filter( (array)$network_wpcf7 );

		// Set site keys IF there are no API keys set.
		if( empty( $value['recaptcha'] ) && ! empty( $network_wpcf7['sitekey'] ) && ! empty( $network_wpcf7['secret'] ) ) {
			$value['recaptcha'] = array(
				$network_wpcf7['sitekey'] => $network_wpcf7['secret'],
			);
		}

		// Set IQFix reCaptcha
		if( ! isset( $value['iqfix_recaptcha'] ) && ! empty( $network_wpcf7['iqfix_recaptcha'] ) ) {
			$value['iqfix_recaptcha'] = $network_wpcf7['iqfix_recaptcha'];
		}

		// Set IQFix reCaptcha Source
		if( ! isset( $value['iqfix_recaptcha_source'] ) && ! empty( $network_wpcf7['recaptcha_source'] ) ) {
			$value['iqfix_recaptcha_source'] = $network_wpcf7['recaptcha_source'];
		}

		return $value;

	}


} // END Class IQFix_WPCF7_Deity


/**
 * Initialize Class
 *
 * @return void
 */
function iqfix_wpcf7_deity_init() {

	if( class_exists( 'WPCF7' ) ) {
		IQFix_WPCF7_Deity::register();
	}

}
add_action( 'plugins_loaded', 'iqfix_wpcf7_deity_init' );


/**
 * Remove upgrade notice from v2 to v3
 * Prevent api keys from being reset.
 *
 * @return void
 */
function iqfix_wpcf7_upgrade_recaptcha_v2_v3_removal() {

	remove_action( 'wpcf7_upgrade', 'wpcf7_upgrade_recaptcha_v2_v3', 10 );

}
add_action( 'admin_init', 'iqfix_wpcf7_upgrade_recaptcha_v2_v3_removal', 9 );