<?php
/**
 * @package GNUTalerPayment
 */
/**
 * Plugin Name: GNU Taler Payment for Woocommerce
 * Plugin URI: https://github.com/Sakaeo/GNU-Taler-Plugin
 *      //Or Wordpress pluin URI
 * Description: This plugin enables the payment via the GNU Taler payment system
 * Version: 1.0.1
 * Author: Hofmann Dominique & Strübin Jan
 * Author URI: https://i.pinimg.com/originals/75/eb/90/75eb90f3e667aa24a514b801e3b96a54.jpg
 *      //TBD
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 2.2
 **/

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */


require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//Exit if accessed directly.
if (!defined('ABSPATH')) exit();

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'gnutaler_add_gateway_class' );
function gnutaler_add_gateway_class( $gateways ) {
    $gateways[] = 'WC_GNUTaler_Gateway'; // your class name is here
    return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'gnutaler_init_gateway_class' );
function gnutaler_init_gateway_class() {

    //Check if WooCommerce is active, if not then deactivate and show error message
    if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
        deactivate_plugins(plugin_basename(__FILE__) );
        wp_die( "<strong>GNU Taler</strong> requires <strong>WooCommerce</strong> plugin to work normally. Please activate it or install it from <a href=\"http://wordpress.org/plugins/woocommerce/\" target=\"_blank\">here</a>.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
    }

    class WC_GNUTaler_Gateway extends WC_Payment_Gateway {

        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct() {
            $this->id = 'gnutaler'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = false; // in case you need a custom credit card form
            $this->method_title = 'GNU Taler Gateway';
            $this->method_description = 'This plugin enables the payment via the GNU Taler payment system'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->testmode = 'yes' === $this->get_option( 'testmode' );

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields(){
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable GNU Taler Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'GNU Taler',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default'     => 'Pay with the new Payment system GNU Taler.',
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable Test Mode',
                    'type'        => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
            );
        }


        /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
        public function payment_scripts() {

        }

        public function validate_fields(){

            if( empty( $_POST[ 'billing_first_name' ]) ) {
                wc_add_notice(  'First name is required!', 'error' );
                return false;
            }
            return true;

        }

        /*
         * We're processing the payments here, everything about it is in Step 5
         */
        public function process_payment( $order_id ) {

        }

    }
}