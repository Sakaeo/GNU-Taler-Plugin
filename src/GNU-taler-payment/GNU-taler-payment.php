<?php
/**
 * @package GNUTalerPayment
 */
/**
 * Plugin Name: GNU Taler Payment for Woocommerce
 * Plugin URI: https://github.com/Sakaeo/GNU-Taler-Plugin
 *      //Or Wordpress pluin URI
 * Description: This plugin enables the payment via the GNU Taler payment system
 * Version: 1.0.3
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

function gnutaler_add_gateway_class( $gateways ) {
    $gateways[] = 'WC_GNUTaler_Gateway';
    return $gateways;
}

add_filter( 'woocommerce_payment_gateways', 'gnutaler_add_gateway_class' );

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'gnutaler_init_gateway_class' );
function gnutaler_init_gateway_class()
{



    //Check if WooCommerce is active, if not then deactivate and show error message
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die("<strong>GNU Taler</strong> requires <strong>WooCommerce</strong> plugin to work normally. Please activate it or install it from <a href=\"http://wordpress.org/plugins/woocommerce/\" target=\"_blank\">here</a>.<br /><br />Back to the WordPress <a href='" . get_admin_url(null, 'plugins.php') . "'>Plugins page</a>.");
    }


    class WC_GNUTaler_Gateway extends WC_Payment_Gateway
    {

        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {
            $this->id = 'gnutaler'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = false; // in case you need a custom credit card form
            $this->method_title = 'GNU Taler Gateway';
            $this->method_description = 'This plugin enables the payment via the GNU Taler payment system'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products', 'refunds'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->GNU_Taler_Backend_URL = $this->get_option('GNU_Taler_Backend_URL');
            $this->Fulfillment_url = $this->get_option('Fulfillment_url');
            $this->Order_text = $this->get_option('Order_text');

            //Verify if the GNU Taler Backend URL in the settings is valid or not
            $result = $this->verifyBackendURL($this->get_option("GNU_Taler_Backend_URL", 1));

            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));


            // We need custom JavaScript to obtain a token
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable GNU Taler Gateway',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'GNU Taler',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Pay with the new Payment system GNU Taler.',
                ),
                'testmode' => array(
                    'title' => 'Test mode',
                    'label' => 'Enable Test Mode',
                    'type' => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default' => 'yes',
                    'desc_tip' => true,
                ),

                'GNU_Taler_Backend_URL' => array(
                    'title' => 'GNU Taler Backend URL',
                    'type' => 'text',
                    'description' => 'Set the URL for the GNU Taler Backend.',
                    'default' => 'https://backend.demo.taler.net',
                ),

                'Fulfillment_url' => array(
                    'title' => 'GNU Taler Fulfillment URL',
                    'type' => 'text',
                    'description' => 'Set the URL where the user should return after finishing the payment process.',
                    'default' => '',
                ),

                'Order_text' => array(
                    'title' => 'Summarytext of the order',
                    'type' => 'text',
                    'description' => 'Set the text the customer should see as a summary when he confirms the payment.',
                    'default' => 'Order',
                ),
            );
        }


        /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
        public function payment_scripts()
        {

        }

        /*
         * Method for calling REST-API
         */

        function callAPI($method, $url, $body, $purpose)
        {

            if ($purpose == "create_order") {
                $url = $url . "/order";
            } else if ($purpose == "confirm_payment") {
                $url = $url . "/check-payment?order_id=" . $body;
            } else if ($purpose == "create_refund"){
                $url = $url . "/refund";
            } else if ($purpose == "confirm_refund"){

            }
            $curl = curl_init();

            switch ($method) {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);
                    if ($body) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                    }
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    if ($body) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                    }
                    break;
                case "GET":
                    curl_setopt($curl, CURLOPT_VERBOSE, 1);
                    // curl_setopt($curl, CURLOPT_HEADER, 1);
                    break;
            }

            // OPTIONS:
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: ApiKey sandbox',
                'Content-Type: application/json',
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            // EXECUTE:
            $result = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (curl_error($curl)) {
                $error_msg = curl_error($curl);
            }
            curl_close($curl);
            if (isset($error_msg)) {
                return $error_msg;
            }
            if ($http_code != "200"){
                return $http_code;
            }
            return $result;
        }

        /*
         *  Here we verify if the GNU Taler Backend URL, given in the settings, is valid.
         */

        public function verifyBackendURL($String)
        {
            return false;
        }

        /*
         * Convert the order to the GNU Taler JSON-format for sending to the backend
         */


        public function convertToJSON($order_id){
            $wcorder = wc_get_order( $order_id );
            $wc_order_total_amount = $wcorder->get_total();
            $wc_order_curreny = $wcorder->get_currency();
            $wc_cart = WC()->cart->get_cart();
            $wc_order_id = $wcorder->get_order_key() . "_" . $wcorder->get_order_number();

            $wc_order_products_array = array();
            foreach ($wc_cart as $product){
                $wc_order_products_array[] = array(
                    'description' => "Order of product: " . $product['data']->get_title(),
                    'quantity' => $product['quantity'],
                    'price' => $wc_order_curreny . ":" . $product['data']->get_price(),
                    'product_id' => $product['data']->get_id(),
                );

            }

            $order_json = array(
                "order" => array(
                    //"amount" => $wc_order_curreny . ":" . $wc_order_total_amount,
                    "amount" => "KUDOS:0.1",
                    "summary" => "Order of the following items:",
                    "fulfillment_url" => "http://gnutaler.hofmd.ch",
                    "order_id" => $wc_order_id,
                    //"merchant" => array(),
                    "products" => $wc_order_products_array,
                    //"products" => $wc_order_products_array
                )
            );

            return $order_json;
        }


        /*
         * We're processing the payments here
         */
        public function process_payment($order_id)
        {


            // we need it to get any order detailes
            $wcorder = wc_get_order( $order_id );
            $cart = WC()->cart->get_cart();

            // Gets the url of the backend from the WooCommerce Settings
            $backendURL = $this->get_option("GNU_Taler_Backend_URL", 1);

            $order_json = $this->convertToJSON($order_id);


            // Send the POST-Request via CURL to the GNU Taler Backend
            $order_confirmation = $this->callAPI('POST', $backendURL, json_encode($order_json, JSON_UNESCAPED_SLASHES), "create_order");
            $order_confirmation_id = explode("\"", $order_confirmation)[3];


            // Send the final confirmation to execute the payment transaction to the GNU Taler Backend
            $payment_confirmation = $this->callAPI("GET", $backendURL, $order_confirmation_id, "confirm_payment");
            $payment_confirmation_url = explode("\"", $payment_confirmation)[3];

            //Completes the order
            $wcorder->payment_complete();

            //Empties the shopping cart
            WC()->cart->empty_cart();

            wc_add_notice($order_confirmation_id, "success");


            return array(
                'result'    => 'success',
                'redirect'  => $payment_confirmation_url
            );



        }

        public function process_refund($order_id, $amount = null, $reason = '') {

            $wcorder = wc_get_order($order_id);

            $refund_json = array(
                "order_id" => $wcorder->get_order_key() . "_" . $wcorder->get_order_number(),
                //"refund" => $wcorder->get_currency() . ":" . $amount,
                "refund" => "KUDOS:0.05",
                "instance" => "default",
                "reason" => $reason
            );

            // Gets the url of the backend from the WooCommerce Settings
            $backendURL = $this->get_option("GNU_Taler_Backend_URL", 1);

            $wc_order_status = $wcorder->get_status();

            if ($wc_order_status == "processing"  || $wc_order_status == "on hold" || $wc_order_status == "completed" || $wc_order_status == "refunded") {
                $refund_confirmation = $this->callAPI("POST", $backendURL, json_encode($refund_json, JSON_UNESCAPED_SLASHES), "create_refund");
                $refund_url = json_decode($refund_confirmation);
                $wcorder->update_status("refunded");
                $wcorder->add_order_note("To finish the refund process please confirm the refund via the following url: " . $refund_confirmation);
                return true;
            }
            else{
                $wcorder->add_order_note("The refund process failed");
                return false;
            }
        }
    }


}