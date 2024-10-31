<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the header of feeds section.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/mwb-crm-fw/templates/meta-boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="mwb_<?php echo esc_attr( $this->crm_slug ); ?>_gf__feeds-wrap">
	<div class="mwb-sf_gf__logo-wrap">
		<div class="mwb-sf_gf__logo-zoho">
			<img src="<?php echo esc_url( MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/engagebay.png' ); ?>" alt="Engagebay">
		</div>
		<div class="mwb-sf_gf__logo-contact">
			<img src="<?php echo esc_url( MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/gravity-form.png' ); ?>" alt="GF">
		</div>
	</div>

