<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/admin/partials
 */

$headings = $this->add_plugin_headings();
?>

<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<main class="mwb-sf-gf-main">
		<header class="mwb-sf-gf-header">
			<h1 class="mwb-sf-gf-header__title"><?php echo esc_html( ! empty( $headings['name'] ) ? $headings['name'] : '' ); ?></h1>
			<span class="mwb-sf-gf-version"><?php echo sprintf( 'v%s', esc_html( ! empty( $headings['version'] ) ? $headings['version'] : '1.0.0' ) ); ?></span>
		</header>
		<?php if ( true == get_option( 'mwb-' . $this->crm_slug . '-gf-authorised' ) ) : // phpcs:ignore ?>
			<!-- Dashboard Screen -->
			<?php do_action( 'mwb_' . $this->crm_slug . '_gf_nav_tab' ); ?>
		<?php else : ?>
			<!-- Authorisation Screen -->
			<?php do_action( 'mwb_' . $this->crm_slug . '_gf_auth_screen' ); ?>
		<?php endif; ?>
	</main>

