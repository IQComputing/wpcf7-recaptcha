<?php
/**
 * Replace reCaptcha v3 with ReCaptcha v2
 * Use [recaptcha] shortcode when possible
 */


defined( 'ABSPATH' ) or die( 'You cannot be here.' );


/**
 * Remove old hooks and add new hook callbacks
 * 
 * @return void
 */
function iqfix_wpcf7_manage_hooks() {

	// reCaptcha Verification
	remove_filter( 'wpcf7_spam', 'wpcf7_recaptcha_verify_response', 9 );
	add_filter( 'wpcf7_spam', 'iqfix_wpcf7_recaptcha_check_with_google', 9 );

	// reCaptcha Enqueues
	remove_action( 'wp_enqueue_scripts', 'wpcf7_recaptcha_enqueue_scripts', 10 );
	add_action( 'wp_enqueue_scripts', 'iqfix_wpcf7_recaptcha_enqueue_scripts', 10 );

	// reCaptcha Footer Javascript
	remove_action( 'wp_footer', 'wpcf7_recaptcha_onload_script', 40 );
	add_action( 'wp_footer', 'iqfix_wpcf7_recaptcha_callback_script', 40 );

}
add_action( 'setup_theme', 'iqfix_wpcf7_manage_hooks' );


/**
 * Remove current [recaptcha] tag and replace it with old reCaptcha tag
 * 
 * @return void
 */
function iqfix_wpcf7_add_recaptcha_tag() {

	wpcf7_remove_form_tag( 'recaptcha' );
	wpcf7_add_form_tag(
		'recaptcha',
		'iqfix_wpcf7_recaptcha_form_tag_handler',
		array( 'display-block' => true )
	);

}
add_action( 'wpcf7_init', 'iqfix_wpcf7_add_recaptcha_tag', 20 );


/**
 * Register the Google reCaptcha API script
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN241
 * 
 * @return void
 */
function iqfix_wpcf7_recaptcha_enqueue_scripts() {
	
	$source = WPCF7::get_option( 'iqfix_recaptcha_source' );
	$source = IQFix_WPCF7_Deity::verify_recaptcha_source( $source );
	
	$url = sprintf( 'https://www.%s/recaptcha/api.js', $source );
	$url = add_query_arg( array(
		'hl'		=> esc_attr( get_locale() ),	// Lowercase L
		'onload'	=> 'recaptchaCallback',
		'render' 	=> 'explicit',
	), $url );

	wp_register_script( 'google-recaptcha', $url, array(), '2.0', true );
	wp_localize_script( 'google-recaptcha', 'wpcf7iqfix', array(
        'recaptcha_empty' => esc_html__( 'Please verify that you are not a robot.', 'wpcf7-recaptcha' ),
    ) );

}
// See `iqfix_wpcf7_manage_hooks` callback above


/**
 * reCaptcha Javascript
 * 
 * @return void
 */
function iqfix_wpcf7_recaptcha_callback_script() {

	if ( ! wp_script_is( 'google-recaptcha', 'enqueued' ) ) {
		return;
	}

?>
<script type="text/javascript">
var recaptchaWidgets = [];
var recaptchaCallback = function() {
	var forms = document.getElementsByTagName( 'form' );
	var pattern = /(^|\s)g-recaptcha(\s|$)/;

	for ( var i = 0; i < forms.length; i++ ) {
		var divs = forms[ i ].getElementsByTagName( 'div' );

		for ( var j = 0; j < divs.length; j++ ) {
			var sitekey = divs[ j ].getAttribute( 'data-sitekey' );

			if ( divs[ j ].className && divs[ j ].className.match( pattern ) && sitekey ) {
				var params = {
					'sitekey': sitekey,
					'type': divs[ j ].getAttribute( 'data-type' ),
					'size': divs[ j ].getAttribute( 'data-size' ),
					'theme': divs[ j ].getAttribute( 'data-theme' ),
					'badge': divs[ j ].getAttribute( 'data-badge' ),
					'tabindex': divs[ j ].getAttribute( 'data-tabindex' )
				};

				var callback = divs[ j ].getAttribute( 'data-callback' );

				if ( callback && 'function' == typeof window[ callback ] ) {
					params[ 'callback' ] = window[ callback ];
				}

				var expired_callback = divs[ j ].getAttribute( 'data-expired-callback' );

				if ( expired_callback && 'function' == typeof window[ expired_callback ] ) {
					params[ 'expired-callback' ] = window[ expired_callback ];
				}

				var widget_id = grecaptcha.render( divs[ j ], params );
				recaptchaWidgets.push( widget_id );
				break;
			}
		}
	}
};

document.addEventListener( 'wpcf7submit', function( event ) {
	switch ( event.detail.status ) {
		case 'spam':
		case 'mail_sent':
		case 'mail_failed':
			for ( var i = 0; i < recaptchaWidgets.length; i++ ) {
				grecaptcha.reset( recaptchaWidgets[ i ] );
			}
	}
}, false );

document.addEventListener( 'wpcf7spam', function( event ) {
	var wpcf7forms = document.getElementsByClassName( 'wpcf7' );
	Array.prototype.forEach.call( wpcf7forms, function( form ) {
		var response  = form.querySelector( 'input[name="g-recaptcha-response"]' );
		var recaptcha = form.querySelector( 'div.wpcf7-recaptcha' );
		if( '' === response.value ) {
			var recaptchaWrapper = recaptcha.parentElement;
			wpcf7.notValidTip( recaptchaWrapper, wpcf7iqfix.recaptcha_empty );
		}
	} );
} );
</script>
<?php

}
// See `iqfix_wpcf7_manage_hooks` callback above


/**
 * reCaptcha Callback
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5
 * contact-form-7\modules\recaptcha.php LN326
 * 
 * @param WPCF7_FormTag $tag
 * 
 * @return String $html
 */
function iqfix_wpcf7_recaptcha_form_tag_handler( $tag ) {

	if ( ! wp_script_is( 'google-recaptcha', 'registered' ) ) {
		wpcf7_recaptcha_enqueue_scripts();
	}

	wp_enqueue_script( 'google-recaptcha' );

	$atts = array();

	$recaptcha = WPCF7_RECAPTCHA::get_instance();
	$atts['data-sitekey'] = $recaptcha->get_sitekey();
	$atts['data-type'] = $tag->get_option( 'type', '(audio|image)', true );
	$atts['data-size'] = $tag->get_option(
		'size', '(compact|normal|invisible)', true );
	$atts['data-theme'] = $tag->get_option( 'theme', '(dark|light)', true );
	$atts['data-badge'] = $tag->get_option(
		'badge', '(bottomright|bottomleft|inline)', true );
	$atts['data-tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['data-callback'] = $tag->get_option( 'callback', '', true );
	$atts['data-expired-callback'] =
		$tag->get_option( 'expired_callback', '', true );

	$atts['class'] = $tag->get_class_option(
		wpcf7_form_controls_class( $tag->type, 'g-recaptcha' ) );
	$atts['id'] = $tag->get_id_option();

	$html = sprintf( '<div %1$s></div>', wpcf7_format_atts( $atts ) );
	$html .= iqfix_wpcf7_recaptcha_noscript(
		array( 'sitekey' => $atts['data-sitekey'] ) );
	$html = sprintf( '<div class="wpcf7-form-control-wrap">%s</div>', $html );

	return $html;

}
// See `iqfix_wpcf7_add_recaptcha_tag` callback above.


/**
 * Display reCaptcha noscript tag should javacript be disabled.
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN360
 * 
 * @param Array $args
 * 
 * @return String
 */
function iqfix_wpcf7_recaptcha_noscript( $args = '' ) {

	$args = wp_parse_args( $args, array(
		'sitekey' => '',
	) );

	if ( empty( $args['sitekey'] ) ) {
		return;
	}
	
	$source = WPCF7::get_option( 'iqfix_recaptcha_source' );
	$source = IQFix_WPCF7_Deity::verify_recaptcha_source( $source );
	$url 	= add_query_arg( 'k', $args['sitekey'],
		sprintf( 'https://www.%s/recaptcha/api/fallback', $source )
	);

	ob_start();
?>

<noscript>
	<div class="grecaptcha-noscript">
		<iframe src="<?php echo esc_url( $url ); ?>" frameborder="0" scrolling="no" width="310" height="430">
		</iframe>
		<textarea name="g-recaptcha-response" rows="3" cols="40" placeholder="<?php esc_attr_e( 'reCaptcha Response Here', 'wpcf7-recaptcha' ); ?>">
		</textarea>
	</div>
</noscript>
<?php
	return ob_get_clean();
}


/**
 * Verify submission is legitimate, verify reCaptcha response
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN395
 * 
 * @param Boolean $spam
 * 
 * @return Boolean $spam
 */
function iqfix_wpcf7_recaptcha_check_with_google( $spam ) {

	if ( $spam ) {
		return $spam;
	}

	$contact_form = wpcf7_get_current_contact_form();

	if ( ! $contact_form ) {
		return $spam;
	}

	$tags = $contact_form->scan_form_tags( array( 'type' => 'recaptcha' ) );

	if ( empty( $tags ) ) {
		return $spam;
	}

	$recaptcha = IQFix_ReCaptcha::get_instance();

	if( ! $recaptcha->is_active() ) {
		return $spam;
	}

	$response_token = wpcf7_recaptcha_response();

	$spam = ! $recaptcha->verify( $response_token );

	return $spam;

}
// See `iqfix_wpcf7_manage_hooks` callback above


/**
 * Grab and return the posted reCaptcha response
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN509
 * 
 * @return String|FALSE
 */
function wpcf7_recaptcha_response() {

	if ( isset( $_POST['g-recaptcha-response'] ) ) {
		return $_POST['g-recaptcha-response'];
	}

	return false;

}


/**
 * Add [recaptcha] to Contact Form 7 field list
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN426
 * 
 * @return void
 */
function iqfix_wpcf7_add_tag_generator_recaptcha() {

	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add(
		'recaptcha',
		esc_html__( 'reCaptcha', 'wpcf7-recaptcha' ),
		'iqfix_wpcf7_tag_generator_recaptcha',
		array( 'nameless' => 1 )
	);

}
add_action( 'wpcf7_admin_init', 'iqfix_wpcf7_add_tag_generator_recaptcha', 45 );


/**
 * Show [recaptcha] field settings
 * 
 * The following function was not written by IQComputing and is included in
 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
 * contact-form-7\modules\recaptcha.php LN432
 * 
 * @param WPCF7_ContactForm $contact_form
 * @param Array $args
 * 
 * @return void
 */
function iqfix_wpcf7_tag_generator_recaptcha( $contact_form, $args = '' ) {

	$args 		= wp_parse_args( $args, array() );
	$recaptcha 	= IQFix_ReCaptcha::get_instance();

	if ( ! $recaptcha->is_active() ) {

?>
<div class="control-box">
<fieldset>
<legend><?php
	/* translators: %s is a link to the Contact Form 7 blog post regarding reCaptcha v3 */
	echo sprintf( esc_html__( "To use reCaptcha, first you need to install an API key pair. For more details, see %s.", 'wpcf7-recaptcha' ), wpcf7_link( 'https://contactform7.com/recaptcha/', esc_html__( 'reCaptcha', 'wpcf7-recaptcha' ) ) );
?></legend>
</fieldset>
</div>
<?php

		return;
	}
	
	/* translators: %s is a link to the Contact Form 7 blog post regarding reCaptcha v3 */
	$description 	= esc_html__( "Generate a form-tag for a reCaptcha widget. For more details, see %s.", 'wpcf7-recaptcha' );
	$desc_link 		= wpcf7_link( 'https://contactform7.com/recaptcha/', esc_html__( 'reCaptcha', 'wpcf7-recaptcha' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( $description, $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php
		/* translators: ReCaptcha size (normal or compact) */
		esc_html_e( 'Size', 'wpcf7-recaptcha' );
	?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php
			/* translators: Screen reader text, reCaptcha size (normal or compact) */
			esc_html_e( 'Size', 'wpcf7-recaptcha' );
		?></legend>
		<label for="<?php echo esc_attr( $args['content'] . '-size-normal' ); ?>"><input type="radio" name="size" class="option default" id="<?php echo esc_attr( $args['content'] . '-size-normal' ); ?>" value="normal" checked="checked" /> <?php /* translators: ReCaptcha size: normal */ esc_html_e( 'Normal', 'wpcf7-recaptcha' ); ?></label>
		<br />
		<label for="<?php echo esc_attr( $args['content'] . '-size-compact' ); ?>"><input type="radio" name="size" class="option" id="<?php echo esc_attr( $args['content'] . '-size-compact' ); ?>" value="compact" /> <?php /* translators: ReCaptcha size: compact */ esc_html_e( 'Compact', 'wpcf7-recaptcha' ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><?php
		/* translators: ReCaptcha theme (light or dark) */
		esc_html_e( 'Theme', 'wpcf7-recaptcha' );
	?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php
			/* translators: Screen reader text, reCaptcha theme (light or dark) */
			esc_html_e( 'Theme', 'wpcf7-recaptcha' );
		?></legend>
		<label for="<?php echo esc_attr( $args['content'] . '-theme-light' ); ?>"><input type="radio" name="theme" class="option default" id="<?php echo esc_attr( $args['content'] . '-theme-light' ); ?>" value="light" checked="checked" /> <?php /* translators: ReCaptcha theme: light */ esc_html_e( 'Light', 'wpcf7-recaptcha' ); ?></label>
		<br />
		<label for="<?php echo esc_attr( $args['content'] . '-theme-dark' ); ?>"><input type="radio" name="theme" class="option" id="<?php echo esc_attr( $args['content'] . '-theme-dark' ); ?>" value="dark" /> <?php /* translators: ReCaptcha theme: dark */ esc_html_e( 'Dark', 'wpcf7-recaptcha' ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php
		/* translators: HTML Attribute ID for reCaptcha box */
		esc_html_e( 'Id attribute', 'wpcf7-recaptcha' );
	?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php
		/* translators: HTML Attribute class for reCaptcha box */
		esc_html_e( 'Class attribute', 'wpcf7-recaptcha' );
	?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="recaptcha" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php
		/* translators: Insert shortcode tag into the page content */
		esc_attr_e( 'Insert Tag', 'wpcf7-recaptcha' );
	?>" />
	</div>
</div>
<?php

}
// See `iqfix_wpcf7_add_tag_generator_recaptcha` callback above


/**
 * The following class is supposed to use and replicate some functionality
 * From Contact Form 7 v5.0.5
 * 
 * @return void
 */
function iqfix_recaptcha_class_init() {

	if( ! class_exists( 'WPCF7_RECAPTCHA' ) ) {
		return false;
	}
		
	Class IQFix_ReCaptcha extends WPCF7_RECAPTCHA {

		private static $instance;
		private $sitekeys;


		/**
		 * Class initialization
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN202
		 * 
		 * return void
		 */
		private function __construct() {
			$this->sitekeys = WPCF7::get_option( 'recaptcha' );
		}


		/**
		 * Singleton
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN10 
		 * 
		 * @return IQFix_ReCaptcha
		 */
		public static function get_instance() {
			if ( empty( self::$instance ) ) {
				self::$instance = new self;
			}
	
			return self::$instance;
		}


		/**
		 * Check if reCaptcha is active
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN26
		 * 
		 * @return Boolean
		 */
		public function is_active() {
			$sitekey = $this->get_sitekey();
			$secret = $this->get_secret( $sitekey );
			return $sitekey && $secret;
		}


		/**
		 * Get set reCaptcha sitekey
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN45
		 * 
		 * @return String|FALSE
		 */
		public function get_sitekey() {
			if ( empty( $this->sitekeys )
			or ! is_array( $this->sitekeys ) ) {
				return false;
			}
	
			$sitekeys = array_keys( $this->sitekeys );
	
			return $sitekeys[0];
		}
	

		/**
		 * Get set reCaptcha secret key
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN55
		 * 
		 * @return String|FALSE
		 */
		public function get_secret( $sitekey ) {
			$sitekeys = (array) $this->sitekeys;
	
			if ( isset( $sitekeys[$sitekey] ) ) {
				return $sitekeys[$sitekey];
			} else {
				return false;
			}
		}


		/**
		 * Verify reCaptcha Response
		 * 
		 * The following method was not written by IQComputing and is included in
		 * Contact Form 7 v5.0.5 by Takayuki Miyoshi
		 * contact-form-7\modules\recaptcha.php LN65
		 * 
		 * @param String $response_token
		 * 
		 * @return Boolean $is_human
		 */
		public function verify( $response_token ) {

			$is_human = false;

			if ( empty( $response_token ) ) {
				return $is_human;
			}
			
			$source  = WPCF7::get_option( 'iqfix_recaptcha_source' );
			$source  = IQFix_WPCF7_Deity::verify_recaptcha_source( $source );
			$url 	 = sprintf( 'https://www.%s/recaptcha/api/siteverify', $source );
			$sitekey = $this->get_sitekey();
			$secret  = $this->get_secret( $sitekey );
			
			$response = wp_safe_remote_post( $url, array(
				'body' => array(
					'secret' => $secret,
					'response' => $response_token,
					'remoteip' => $_SERVER['REMOTE_ADDR'],
				),
			) );
	
			if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
				
				if ( WP_DEBUG ) {
					$this->log( $endpoint, $request, $response );
				}
	
				return $is_human;
				
			}
	
			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response, true );
			$is_human = isset( $response['success'] ) && true == $response['success'];
			
			return apply_filters( 'wpcf7_recaptcha_verify_response', $is_human, $response );

		}

	}

}
add_action( 'init', 'iqfix_recaptcha_class_init', 11 );


/**
 * Add some inline CSS for the reCaptcha iframe
 * 
 * @return void
 */
function iqfix_recaptcha_inline_css() {
	
	$iqfix_css ='.wpcf7 .wpcf7-recaptcha iframe {margin-bottom: 0;}';
	wp_add_inline_style( 'contact-form-7', $iqfix_css );
	
}
add_action( 'wp_enqueue_scripts', 'iqfix_recaptcha_inline_css' );