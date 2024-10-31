<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://makewebbetter.com
 * @since             1.0.0
 * @package           Mwb_Gf_Integration_With_Engagebay
 *
 * @wordpress-plugin
 * Plugin Name:       MWB GF Integration with Engagebay
 * Plugin URI:        https://wordpress.org/plugins/mwb-gf-integration-with-engagebay/?utm_source=MWB-gfengagebay-org&utm_medium=gfengagebay-pluginbackend&utm_campaign=MWB-gfengagebay-integration
 * Description:       MWB GF Integration with Engagebay plugin allows the admin to associate Gravity Forms fields with Engagebay modules. and sends all data over to the connected Engagebay account and creates contacts, tasks, companies etc.
 * Version:           1.0.0
 * Author:            MakeWebBetter
 * Author URI:        https://makewebbetter.com/?utm_source=MWB-gfengagebay-org&utm_medium=gfengagebay-pluginbackend&utm_campaign=MWB-gfengagebay-integration
 *
 * Requires at least: 4.0
 * Tested up to:      5.8.1
 *
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       mwb-gf-integration-with-engagebay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$crm_name = 'Engagebay';
$crm_slug = 'engagebay';

/**
 * Check Plugin Dependency on Gravity Forms plugin
 *
 * @return array
 */
function mwb_engagebay_gf_plugin_activation() {

	$active['status'] = false;
	$active['msg']    = 'gf_inactive';

	if ( true === mwb_engagebay_gf_is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
		$active['status'] = true;
		$active['msg']    = '';
	}

	return $active;
}

/**
 * Check if a particular plugin is active or not.
 *
 * @param string $slug Slug of the plugin to check if active or not.
 * @return boolean
 */
function mwb_engagebay_gf_is_plugin_active( $slug = '' ) {

	if ( empty( $slug ) ) {
		return;
	}

	$active_plugins = get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_option( 'active_sitewide_plugins', array() ) );
	}

	if ( in_array( $slug, $active_plugins, true ) || array_key_exists( $slug, $active_plugins ) ) {
		return true;
	} else {
		return false;
	}
}

$engagebay_gf_is_plugin_active = mwb_engagebay_gf_plugin_activation();

if ( true === $engagebay_gf_is_plugin_active['status'] ) {

	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define( 'MWB_GF_INTEGRATION_WITH_ENGAGEBAY_VERSION', '1.0.0' );

	define( 'MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL', plugin_dir_url( __FILE__ ) );  // Plugin Url path.

	define( 'MWB_GF_INTEGRATION_WITH_ENGAGEBAY_DIRPATH', plugin_dir_path( __FILE__ ) );   // Plugin Filesystem Directory path.

	define( 'MWB_GF_EG_ONBOARD_PLUGIN_NAME', 'MWB GF Integration with EngageBay' );   // Onboard Plugin Name.


	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-mwb-gf-integration-with-engagebay-activator.php
	 */
	function activate_mwb_gf_integration_with_engagebay() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-gf-integration-with-engagebay-activator.php';
		Mwb_Gf_Integration_With_Engagebay_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-mwb-gf-integration-with-engagebay-deactivator.php
	 */
	function deactivate_mwb_gf_integration_with_engagebay() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-gf-integration-with-engagebay-deactivator.php';
		Mwb_Gf_Integration_With_Engagebay_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_mwb_gf_integration_with_engagebay' );
	register_deactivation_hook( __FILE__, 'deactivate_mwb_gf_integration_with_engagebay' );

	// Add settings link in plugin action links.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'engagebay_gf_settings_link' );

	/**
	 * Add settings link callback.
	 *
	 * @since 1.0.0
	 * @param string $links link to the admin area of the plugin.
	 * @return array
	 */
	function engagebay_gf_settings_link( $links ) {

		global $crm_slug;

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=mwb_' . $crm_slug . '_gf_page&tab=accounts' ) . '">' . esc_html__( 'Settings', 'mwb-gf-integration-with-engagebay' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	add_filter( 'plugin_row_meta', 'engagebay_gf_important_links', 10, 3 );

	/**
	 * Add custom links.
	 *
	 * @param   string $links link to index file of plugin.
	 * @param   string $file index file of plugin.
	 * @param   array  $plugin_data  plugin data.
	 *
	 * @since    1.0.0
	 * @return array
	 */
	function engagebay_gf_important_links( $links, $file, $plugin_data ) {

		if ( strpos( $file, basename( __FILE__ ) ) !== false ) {

			$row_meta_links = array(
				'demo'    => '<a href="https://demo.makewebbetter.com/get-personal-demo/mwb-gf-integration-with-engagebay/?utm_source=MWB-gfengagebay-org&utm_medium=gfengagebay-pluginbackend&utm_campaign=MWB-gfengagebay-integration" target="_blank"><img src="' . MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/Demo.svg" style="width: 20px;padding-right: 5px;"></i>' . esc_html__( 'Demo', 'mwb-gf-integration-with-engagebay' ) . '</a>',
				'doc'     => '<a href="https://docs.makewebbetter.com/mwb-gf-integration-with-engagebay/?utm_source=MWB-gfengagebay-org&utm_medium=gfengagebay-pluginbackend&utm_campaign=MWB-gfengagebay-integration" target="_blank"><img src="' . MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/Documentation.svg" style="width: 20px;padding-right: 5px;"></i>' . esc_html__( 'Documentation', 'mwb-gf-integration-with-engagebay' ) . '</a>',
				'support' => '<a href="https://support.makewebbetter.com/wordpress-plugins-knowledge-base/category/mwb-gf-integration-with-engagebay/?utm_source=MWB-gfengagebay-org&utm_medium=gfengagebay-pluginbackend&utm_campaign=MWB-gfengagebay-integration" target="_blank"><img src="' . MWB_GF_INTEGRATION_WITH_ENGAGEBAY_URL . 'admin/images/Support.svg" style="width: 20px;padding-right: 5px;"></i>' . esc_html__( 'Support', 'mwb-gf-integration-with-engagebay' ) . '</a>',
			);

			return array_merge( $links, $row_meta_links );
		}

		return (array) $links;
	}


	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-mwb-gf-integration-with-engagebay.php';


	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_mwb_gf_integration_with_engagebay() {

		$plugin = new Mwb_Gf_Integration_With_Engagebay();
		$plugin->run();

	}
	run_mwb_gf_integration_with_engagebay();
} else {

	// Deactivate the plugin if Gravity forms is not active.
	add_action( 'admin_init', 'mwb_engagebay_gf_activation_failure' );

	/**
	 * Deactivate the plugin.
	 */
	function mwb_engagebay_gf_activation_failure() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'mwb_engagebay_gf_activation_notice' );

	/**
	 * This function displays plugin activation error notices.
	 */
	function mwb_engagebay_gf_activation_notice() {

		global $engagebay_gf_is_plugin_active;

		$dependent   = esc_html( 'Gravity Forms' );
		$plugin_name = esc_html( 'MWB GF Integration with Engagebay' );

		// To hide Plugin activated notice.
		unset( $_GET['activate'] ); // @codingStandardsIgnoreLine

		?>

		<?php if ( 'gf_inactive' === $engagebay_gf_is_plugin_active['msg'] ) { ?>

			<div class="notice notice-error is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %1$s: Dependent plugin, %2$s: The plugin. */
						esc_html__( ' %1$s is not activated, Please activate %1$s first to activate %2$s', 'mwb-gf-integration-with-engagebay' ),
						'<strong>' . esc_html( $dependent ) . '</strong>',
						'<strong>' . esc_html( $plugin_name ) . '</strong>'
					);
					?>
				</p>
			</div>

			<?php
		}
	}
}
