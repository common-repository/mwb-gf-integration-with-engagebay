<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the accounts creation page.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Gf_Integration_With_Engagebay
 * @subpackage Mwb_Gf_Integration_With_Engagebay/mwb-crm-fw/templates/tab-contents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$connected = get_option( 'mwb-' . $this->crm_slug . '-gf-crm-connected', false );

?>
<?php if ( '1' !== get_option( 'mwb-' . $this->crm_slug . '-gf-active', false ) || '1' !== $connected ) : ?>
	<?php if ( '1' !== $connected ) : ?>
		<section class="mwb-intro">
			<div class="mwb-content-wrap">
				<div class="mwb-intro__header">
					<h2 class="mwb-section__heading">
						<?php esc_html_e( 'Getting started with Gravity Forms and Engagebay', 'mwb-gf-integration-with-engagebay' ); ?>
					</h2>
				</div>
				<div class="mwb-intro__body mwb-intro__content">
					<p>
					<?php
					echo sprintf(
						/* translators: %1$s: crm name %2$s: crm name %3$s: crm objects %4$s: crm name */
						esc_html__( 'With this GF %1$s Integration you can easily sync all your Gravity Form Submissions data over %2$s. It will create %3$s over %4$s CRM, based on your Gravity Form Feed data.', 'mwb-gf-integration-with-engagebay' ),
						esc_html( $this->crm_name ),
						esc_html( $this->crm_name ),
						esc_html( 'Contacts, Companies, Deals etc.' ),
						esc_html( $this->crm_name )
					);
					?>
					</p>
					<ul class="mwb-intro__list">
						<li class="mwb-intro__list-item">
							<?php
							echo sprintf(
								/* translators: %s: crm name */
								esc_html__( 'Connect your %s CRM account with GF.', 'mwb-gf-integration-with-engagebay' ),
								esc_html( $this->crm_name )
							);
							?>
						</li>
						<li class="mwb-intro__list-item">
							<?php
							echo sprintf(
								/* translators: %s: crm name */
								esc_html__( 'Sync your data over %s.', 'mwb-gf-integration-with-engagebay' ),
								esc_html( $this->crm_name )
							);
							?>
						</li>
					</ul>
					<div class="mwb-intro__button">
						<a href="javascript:void(0)" class="mwb-btn mwb-btn--filled" id="mwb-showauth-form">
							<?php esc_html_e( 'Connect your Account', 'mwb-gf-integration-with-engagebay' ); ?>
						</a>
					</div>
				</div> 
			</div>
		</section>
	<?php endif; ?>

	<!--============================================================================================
										Authorization form start.
	================================================================================================-->

	<div class="mwb_eb_gf__account-wrap <?php echo esc_html( false === $connected ? 'row-hide' : '' ); ?>" id="mwb-gf-auth_wrap">

		<!-- Logo section start -->
		<div class="mwb-sf_gf__logo-wrap">
			<div class="mwb-sf_gf__logo-engagebay">
				<img src="<?php echo esc_url( MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/engagebay.png' ); ?>" alt="Engagebay">
			</div>
			<div class="mwb-sf_gf__logo-contact">
				<img src="<?php echo esc_url( MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/gravity-form.png' ); ?>" alt="GF">
			</div>
		</div>
		<!-- Logo section end -->

		<!-- Login form start -->
		<form method="post" id="mwb_eb_gf_account_form">

			<div class="mwb_eb_gf_table_wrapper">
				<div class="mwb_eb_gf_account_setup">
					<h2>
						<?php esc_html_e( 'Enter your credentials here', 'mwb-gf-integration-with-engagebay' ); ?>
					</h2>
				</div>

				<table class="mwb_eb_gf_table">
					<tbody>
						<div class="mwb-auth-notice-wrap row-hide">
							<p class="mwb-auth-notice-text">
								<?php esc_html_e( 'Authorization has been successful ! Validating Connection .....', 'mwb-gf-integration-with-engagebay' ); ?>
							</p>
						</div>

						<!-- Consumer key start  -->
						<tr class="mwb-api-fields row-hide">
							<th>							
								<label><?php esc_html_e( 'Rest API Key', 'mwb-gf-integration-with-engagebay' ); ?></label>
							</th>

							<td>
								<?php
								$api_key = ! empty( $params['api_key'] ) ? sanitize_text_field( wp_unslash( $params['api_key'] ) ) : '';
								?>
								<div class="mwb-sf-gf__secure-field">
									<input type="password"  name="mwb_account[api_key]" id="mwb-<?php echo esc_attr( $this->crm_slug ); ?>-gf-api-key" value="<?php echo esc_html( $api_key ); ?>" required>
									<div class="mwb-sf-gf__trailing-icon">
										<span class="dashicons dashicons-visibility mwb-toggle-view"></span>
									</div>
								</div>
							</td>
						</tr>
						<!-- Consumer key end -->

						<!-- Engagebay domain start -->
						<tr class="mwb-web-fields">
							<th>
								<label><?php esc_html_e( 'Engagebay Base Url', 'mwb-gf-integration-with-engagebay' ); ?></label>
							</th>

							<td>
								<?php
									$base_url = ! empty( $params['base_url'] ) ? sanitize_text_field( wp_unslash( $params['base_url'] ) ) : '';
								?>
								<input type="url" name="mwb_account[base_url]" id="mwb-<?php echo esc_attr( $this->crm_slug ); ?>-gf-base-url" value="<?php echo esc_attr( $base_url ); ?>">
							</td>
						</tr>
						<!-- Engagebay domain end -->


						<!-- Save & connect account start -->
						<tr>
							<th>
							</th>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( '?mwb-gf-perform-auth=1' ) ) ); ?>" class="mwb-btn mwb-btn--filled mwb_eb_gf_submit_account" id="mwb-<?php echo esc_attr( $this->crm_slug ); ?>-gf-authorize-button" ><?php esc_html_e( 'Authorize', 'mwb-gf-integration-with-engagebay' ); ?></a>
							</td>
						</tr>
						<!-- Save & connect account end -->
					</tbody>
				</table>
			</div>
		</form>
		<!-- Login form end -->

		<!-- Info section start -->
		<div class="mwb-intro__bottom-text-wrap ">
			<p>
				<?php esc_html_e( 'Don’t have an account yet. ', 'mwb-gf-integration-with-engagebay' ); ?>
				<a href="https://app.engagebay.com/signup?plan=free" target="_blank" class="mwb-btn__bottom-text"><?php esc_html_e( 'Create A Free Account', 'mwb-gf-integration-with-engagebay' ); ?></a>
			</p>
			<p>
				<?php esc_html_e( 'Check app setup guide. ', 'mwb-gf-integration-with-engagebay' ); ?>
				<a href="javascript:void(0)" class="mwb-btn__bottom-text trigger-setup-guide"><?php esc_html_e( 'Show Me How', 'mwb-gf-integration-with-engagebay' ); ?></a>
			</p>
		</div>
		<!-- Info section end -->
	</div>

<?php else : ?>

	<!-- Successfull connection start -->
	<section class="mwb-sync">
		<div class="mwb-content-wrap">
			<div class="mwb-sync__header">
				<h2 class="mwb-section__heading">
					<?php
					echo sprintf(
						/* translators: %s: crm name */
						esc_html__( 'Congrats! You’ve successfully set up the MWB GF Integration with %s Plugin.', 'mwb-gf-integration-with-engagebay' ),
						esc_html( $this->crm_name )
					);
					?>
				</h2>
			</div>
			<div class="mwb-sync__body mwb-sync__content-wrap">            
				<div class="mwb-sync__image">    
					<img src="<?php echo esc_url( MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/congo.jpg' ); ?>" >
				</div>       
				<div class="mwb-sync__content">            
					<p> 
						<?php
						echo sprintf(
							/* translators: %s: crm name */
							esc_html__( 'Now you can go to the dashboard and check for the synced data. You can create your feeds, edit them in the feeds tab. If you do not see your data over %s CRM, you can check the logs for any possible error.', 'mwb-gf-integration-with-engagebay' ),
							esc_html( $this->crm_name )
						);
						?>
					</p>
					<div class="mwb-sync__button">
						<a href="javascript:void(0)" class="mwb-btn mwb-btn--filled mwb-onboarding-complete">
							<?php esc_html_e( 'View Dashboard', 'mwb-gf-integration-with-engagebay' ); ?>
						</a>
					</div>
				</div>             
			</div>       
		</div>
	</section>
	<!-- Successfull connection end -->

<?php endif; ?>
