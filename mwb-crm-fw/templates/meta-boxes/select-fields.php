<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the select fields section of feeds.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/mwb-crm-fw/templates/meta-boxes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	exit;
}
?>
<div id="mwb-fields-form-section-wrapper" class="mwb-feeds__content  mwb-content-wrap row-hide">
	<a class="mwb-feeds__header-link">
		<?php esc_html_e( 'Map Fields', 'mwb-gf-integration-with-engagebay' ); ?>
	</a>
	<div id="mwb-fields-form-section" class="mwb-feeds__meta-box-main-wrapper">
	<?php
	$mapping_exists = ! empty( $params['mapping_data'] );

	foreach ( $params['crm_fields'] as $key => $fields_data ) {
		$option_data  = $params['field_options'];
		$default_data = array(
			'field_type'  => 'standard_field',
			'field_value' => '',
		);

		if ( $mapping_exists ) {
			if ( ! array_key_exists( $fields_data['field_name'], $params['mapping_data'] ) ) {
				continue;
			}
			$default_data = $params['mapping_data'][ $fields_data['field_name'] ];

		} else {
			if ( isset( $fields_data['is_required'] ) && ! $fields_data['is_required'] ) {
				continue;
			}
		}

		Mwb_Gf_Integration_Engagebay_Template_Manager::get_field_section_html(
			$option_data,
			$fields_data,
			$default_data
		);
	}
	?>
	</div>
</div>
