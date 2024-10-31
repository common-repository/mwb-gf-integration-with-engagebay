<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$crm_slug = 'engagebay';

// Get settings data.
$settings = get_option( 'mwb-' . $crm_slug . '-gf-setting', false );

if ( ! empty( $settings ) && is_array( $settings ) ) {
	if ( isset( $settings['data_delete'] ) && 'yes' == $settings['data_delete'] ) { // @codingStandardsIgnoreLine

		// Delete all feeds.
		$args = array(
			'post_type'      => 'mwb_' . $crm_slug . '_gf',
			'posts_per_page' => -1,
		);

		$all_feeds = get_posts( $args );

		if ( ! empty( $all_feeds ) && is_array( $all_feeds ) ) {
			foreach ( $all_feeds as $feed ) {
				$post_meta = get_post_meta( $feed->ID );
				if ( ! empty( $post_meta ) && is_array( $post_meta ) ) {
					foreach ( $post_meta as $key => $value ) {
						delete_post_meta( $feed->ID, $key );
					}
				}
				wp_delete_post( $feed->ID, true );
			}
		}
		unregister_post_type( 'mwb_' . $crm_slug . '_gf' );

		// Drop logs table.
		global $wpdb;
		$table_name = $wpdb->prefix . 'mwb_' . $crm_slug . '_gf_log';

		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Delete options at last.
		$options = array(
			'mwb-' . $crm_slug . '-gf-setting',
			'mwb-' . $crm_slug . '-gf-api-key',
			'mwb-' . $crm_slug . '-gf-base-url',
			'mwb-' . $crm_slug . '-gf-active',
			'mwb-' . $crm_slug . '-gf-crm-connected',
			'mwb-' . $crm_slug . '-gf-authorised',
			'mwb-' . $crm_slug . '-gf-owner-account',
			'mwb-' . $crm_slug . '-gf-log-last-delete',
			'mwb-' . $crm_slug . '-gf-log-table-created',
			'mwb-' . $crm_slug . '-gf-onboarding-data-sent',
			'mwb-' . $crm_slug . '-gf-onboarding-data-skipped',
			'mwb-' . $crm_slug . '-gf-synced-forms-count',
			'mwb-' . $crm_slug . '-gf-settings-response',
		);

		foreach ( $options as $option ) {
			if ( get_option( $option ) ) {
				delete_option( $option );
			}
		}

		// unscedule cron.
		wp_unschedule_event( time(), 'mwb_' . $crm_slug . '_gf_clear_log' );
	}
}
