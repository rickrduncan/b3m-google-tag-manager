<?php
/**
* Plugin Name:		B3M Google Tag Manager
* Description:		Ability to add the Google Tag Manager container to your website.
* Author:			Rick R. Duncan - B3Marketing, LLC
* Author URI:		http://rickrduncan.com
*
* License:			GPLv3
* License URI:		https://www.gnu.org/licenses/gpl-3.0.html
*
* Version:			2.0.0
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;


// Start Class
if ( ! class_exists( 'B3M_Google_Tag_Manager' ) ) {

	class B3M_Google_Tag_Manager {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// We only need to register the admin panel on the back-end
			if ( is_admin() ) {
				add_action( 'admin_menu', array( 'B3M_Google_Tag_Manager', 'b3m_gtm_add_admin_menu' ) );
				add_action( 'admin_init', array( 'B3M_Google_Tag_Manager', 'b3m_gtm_register_settings' ) );
			}
			
			// Load GTM container code (Part 1) in <head>
			add_action( 'wp_head', array( $this, 'b3m_gtm_add_head_code' ) );
			
			
			// Load GTM container code (Part 2) in <body>
			add_action( 'genesis_before', array( $this, 'b3m_gtm_add_body_code' ) );
		}


		/**
		 * Returns all theme options
		 *
		 * @since 1.0.0
		 */
		public static function get_theme_options() {
			return get_option( 'b3m_gtm_options' ); //unique name of our options is b3m_gtm_options
		}

		/**
		 * Returns single theme option
		 *
		 * @since 1.0.0
		 */
		public static function get_theme_option( $id ) {
			$options = self::get_theme_options();
			if ( isset( $options[$id] ) ) {
				return $options[$id];
			}
		}

		/**
		 * Add sub menu page
		 *
		 * @since 1.0.0
		 */
		public static function b3m_gtm_add_admin_menu() {
			add_menu_page(
				'GTM Settings',
				'GTM Settings',
				'manage_options',
				'gtm-settings',
				array( 'B3M_Google_Tag_Manager', 'create_gtm_page' )
			);
		}
		

		/**
		 * Step 1 - Insert GTM code into <head> section of page.
		 *
		 * @since 1.0.1 Added the datalayer script tag.
		 * @since 1.0.0
		 */
		public static function b3m_gtm_add_head_code() { 
			$options    	= get_option( 'b3m_gtm_options' );	// Return our plugin options
			$gtm_id       	= empty( $options['b3m_gtm_id'] ) ? 'WARNING - NO ID SPECIFIED' : $options['b3m_gtm_id'];			// Google Tag Manager id number
			$gtm_enabled	= empty( $options['b3m_gtm_enabled'] ) ? '' : $options['b3m_gtm_enabled'];	// Check box for enabled/disabled

			if ( ! current_user_can( 'manage_options' ) && ( $gtm_enabled ) )  { //only include GA code for non-admin users and when "on" is checked ?>
			<!-- Google Tag Manager --><script>dataLayer = [];</script><script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo $gtm_id; ?>');</script><!-- End Google Tag Manager --><?php
			}	
		}

		
		/**
		 * Step 2 - Insert GTM code after the opening <body> tag
		 *
		 * @since 1.0.0
		 */
		public static function b3m_gtm_add_body_code() {
			$options		= get_option( 'b3m_gtm_options' );	// Return our plugin options
			$gtm_id       	= empty( $options['b3m_gtm_id'] ) ? 'WARNING - NO ID SPECIFIED' : $options['b3m_gtm_id'];			// Google Tag Manager id number
			$gtm_enabled	= empty ( $options['b3m_gtm_enabled'] ) ? '' : $options['b3m_gtm_enabled'];	// Check box for enabled/disabled

			if ( ! current_user_can( 'manage_options' ) && ( $gtm_enabled ) && ( current_filter() == 'genesis_before' ) )  {
				echo '<!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $gtm_id . ' height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->';
			}
		}
		
		
		/**
		 * Register a setting and its sanitization callback.
		 *
		 * We are only registering 1 setting so we can store all options in a single option as
		 * an array. You could, however, register a new setting for each option
		 *
		 * @since 1.0.0
		 */
		public static function b3m_gtm_register_settings() {
			register_setting( 'b3m_gtm_options', 'b3m_gtm_options', array( 'B3M_Google_Tag_Manager', 'sanitize' ) );
		}

		/**
		 * Sanitization callback
		 *
		 * @since 1.0.0
		 */
		public static function sanitize( $options ) {

			// If we have options lets sanitize them
			if ( $options ) {

				if ( ! empty( $options['b3m_gtm_id'] ) ) {
					$options['b3m_gtm_id'] = sanitize_text_field( $options['b3m_gtm_id'] );
				} else {
					unset( $options['b3m_gtm_id'] ); // Remove from options if empty
				}
				if ( ! empty( $options['b3m_gtm_enabled'] ) ) {
					$options['b3m_gtm_enabled'] = sanitize_text_field( $options['b3m_gtm_enabled'] );
				} else {
					unset( $options['b3m_gtm_enabled'] ); // Remove from options if empty
				}
			}

			// Return sanitized options
			return $options;
		}	


		/**
		 * Settings page output
		 *
		 * @since 1.0.0
		 */
		public static function create_gtm_page() { ?>

			<div class="wrap">

				<h1><?php esc_html_e( 'Google Tag Manager Settings Page' ); ?></h1>

				<form method="post" action="options.php">

					<?php settings_fields( 'b3m_gtm_options' ); ?>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row">Enable GTM?</th>
							<td>
								<?php $value = self::get_theme_option( 'b3m_gtm_enabled' ); ?>
								<input name="b3m_gtm_options[b3m_gtm_enabled]" type="checkbox" value="yes" <?php echo ($value ? 'checked=checked' : ''); ?> /> 
								<?php esc_html_e( 'Include the Google Tag Manager Container on all web pages?' ) ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'GTM ID' ); ?></th>
							<td>
								<?php $value = self::get_theme_option( 'b3m_gtm_id' ); ?>
								<input class="regular-text" type="text" name="b3m_gtm_options[b3m_gtm_id]" value="<?php echo esc_attr( $value ); ?>">
								<p id="tagline-description" class="description">What is your container ID number?</p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>

				</form>

			</div><!-- .wrap -->
		<?php }
	}
}
new B3M_Google_Tag_Manager();