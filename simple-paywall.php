<?php
/**
 * Plugin Name: Simple Paywall
 * Description: The most powerful way to quickly and easily monetize your content. A fully featured paywall solution, including metered paywall, to help you convert readers into subscribers. Highly customizable to your needs and super easy to use.
 * Version: 0.6.0
 * Author: Simple Paywall
 * Author URI: https://simplepaywall.com/
 * License: GPLv2 or later
 * License URI: https://opensource.org/licenses/gpl-license.php
 * Text Domain: simple-paywall
 * Domain Path: /languages
 * Tags: paywall, metered paywall, subscriptions, memberships, paid content, recurring payments, content monetization, metered access, pay wall, metered pay wall
 */

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Any use of this software constitutes acceptance and agreement to Simple Paywall's Terms of Service (https://simplepaywall.com/terms-of-service/) and Simple Paywall's Privacy Policy (https://simplepaywall.com/privacy-policy/).
 */

/**
 * Restrict direct access to plugin
 * @see https://wordpress.stackexchange.com/questions/108418/what-are-the-differences-between-wpinc-and-abspath
 * @see https://www.pluginvulnerabilities.com/2017/04/20/security-tip-for-developers-you-dont-need-to-restrict-direct-access-to-php-files-twice/
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set environment
define( 'SIMPLE_PAYWALL_ENV', 'production' );

// Set constants
define( 'SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION', '0.6.0' );
define( 'SIMPLE_PAYWALL_URL', plugins_url( '', __FILE__ ) );
define( 'SIMPLE_PAYWALL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_PAYWALL_SLUG', 'simple-paywall' );
define( 'SIMPLE_PAYWALL_BASENAME', plugin_basename( __FILE__ ) );
define( 'SIMPLE_PAYWALL_RELATIVE_DIR', dirname( SIMPLE_PAYWALL_BASENAME ) );
define( 'SIMPLE_PAYWALL_API_VERSION', '1' );
define( 'SIMPLE_PAYWALL_API', ( SIMPLE_PAYWALL_ENV === 'development' ? 'http://api.simplepaywall.com.test/v' : 'https://api.simplepaywall.com/v' ) . SIMPLE_PAYWALL_API_VERSION );
define( 'SIMPLE_PAYWALL_APP', ( SIMPLE_PAYWALL_ENV === 'development' ? 'http://app.simplepaywall.com.test' : 'https://app.simplepaywall.com' ) );

require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'plugin-activator' . '.php';

/**
 * Activate plugin
 * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
 */
function activate_simple_paywall_plugin() {
	$activate = new Simple_Paywall_Plugin_Activator();
	$activate->activate();
}
register_activation_hook( __FILE__ , 'activate_simple_paywall_plugin' );

/**
 * Deactivate plugin
 * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
 */
function deactivate_simple_paywall_plugin() {
	Simple_Paywall_Plugin_Activator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_simple_paywall_plugin' );

require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '.php';

/**
 * Instantiates the Simple_Paywall class and then
 * calls its run method officially starting up the plugin.
 */
function run_simple_paywall() {
	$simple_paywall = new Simple_Paywall();
	$simple_paywall->init();
}
run_simple_paywall();
