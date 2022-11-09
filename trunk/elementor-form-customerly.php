<?php

/**
 * Elementor Form Email Marketing and CRM by Customerly
 *
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Elementor Form Email Marketing and CRM by Customerly
 * Plugin URI:        https://www.customerly.io/features/email-marketing/?utm_source=wordpress&utm_medium=plugin&utm_campaign=elementor_plugin_uri
 * Description:       Use Elementor Forms with Customerly powerful Email Marketing, Marketing Automation and CRM.
 * Version:           1.0
 * Author:            Customerly
 * Author URI:        https://www.customerly.io/features/email-marketing/?utm_source=wordpress&utm_medium=plugin&utm_campaign=plugin_author_uri
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('ELEMENTOR_CUSTOMERLY_PLUGIN_PATH', plugin_dir_path(__FILE__));
const CUSTOMERLY_API_BASE_URL = 'https://api.customerly.io/v1/';


function register_elementor_customerly_actions( $form_actions_registrar ) {

require_once(__DIR__ . '/customerly.php');
    $form_actions_registrar->register( new \Elementor_Customerly_Action() );

}
add_action( 'elementor_pro/forms/actions/register', 'register_elementor_customerly_actions' );
