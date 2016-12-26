<?php
if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

/**
 * Delete all the options we created with this plugin.
 *
 * @since 2.0.0
 */
delete_option( 'b3m_gtm_options' );

?>