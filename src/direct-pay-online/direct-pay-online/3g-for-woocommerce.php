<?php
/**
 *
 * Plugin Name:       DPO for Woocommerce
 * Plugin URI:        https://wordpress.org/plugins/direct-pay-online/
 * Description:       Online payments plug in powered by DPO, credit cards, PayPal and Mobile Money
 * Version:           1.0.9
 * Author:            DPO
 * Author URI:        http://www.directpay.online/
 * Copyright:         © 2016 DPO.
 *
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * WC requires at least: 2.2
*/

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//Exit if accessed directly.
if (!defined('ABSPATH')) exit();

//Check if WooCommerce is active, if not then deactivate and show error message
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
   deactivate_plugins(plugin_basename(__FILE__) );
   wp_die( "<strong>DPO</strong> requires <strong>WooCommerce</strong> plugin to work normally. Please activate it or install it from <a href=\"http://wordpress.org/plugins/woocommerce/\" target=\"_blank\">here</a>.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
}

add_action('plugins_loaded', 'woocommerce_3g_init');

//Main DPO plugin function
function woocommerce_3g_init(){
	//check if woocommerce  installed 
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	//Gateway class
	class WC_Gateway_3G extends WC_Payment_Gateway {

		const VERSION_3G = '1.0.9';

		protected $default_image = 'https://www.directpay.online/wp-content/uploads/2018/08/All-Cards-and-Mobile-Money-257%C3%9795.jpg';
		
		public function __construct(){

			// Within your constructor, you should define the following variables:
			$this->id                 = 'woocommerce_3g';
			$this->icon 	    	  =  $this->default_image;
			$this->has_fields  		  =  true;
			$this->method_title 	  = 'DPO';
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables in settings
			$this->enabled             = $this->get_option( 'enabled' );
			$this->title               = $this->get_option( 'title' );
			$this->company_token       = $this->get_option( 'company_token' );
			$this->reduce_stock        = $this->get_option( 'reduce_stock' );
			$this->url        		   = $this->get_option( '3g_url');
			$this->ptl_type        	   = $this->get_option( 'ptl_type');
			$this->ptl        	   	   = $this->get_option( 'ptl');
			$this->is_default_img      = $this->get_option( 'is_default_img');
			$this->image_url           = $this->get_option( 'image_url');

			if ($this->is_default_img != 'yes') {
				if (empty($this->image_url)) 
					$this->icon = '';
				else
				    $this->icon = $this->image_url;
			}
			

			//save options
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }

			add_action( 'wp_enqueue_scripts', array($this, 'frontend_scripts_and_styles'), 100 );
			add_action( 'woocommerce_thankyou_'. $this->id, array($this, 'check_3g_response'));
			
		}
		
	 
		//include css and js external file
		public function frontend_scripts_and_styles(){

        	wp_register_style('main-3g', plugins_url('/assets/css/main-3g.css',__FILE__ ));
        	wp_enqueue_style( 'main-3g');
        }

        //plugin input settings
        public function init_form_fields(){

			$this->form_fields = array(

				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'type'        => 'checkbox',
					'label'   	  => __( 'Enable DPO', 'woocommerce' ),
					'default'     => 'yes'
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'DPO', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'company_token' => array(
					'title'       => __( 'Company Token', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to receive token number from 3G gateway', 'woocommerce' ),
					'placeholder' => __( 'For Example: 57466282-EBD7-4ED5-B699-8659330A6996', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'3g_url' =>array(
					'title'       => __( '3G URL', 'woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
				),
				'ptl_type' =>array(
					'title'       => __( 'PTL Type ( Optional )', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Define payment time limit  tag is hours or minutes.', 'woocommerce' ),
					'options'     => array(
				        'hours'   => __('Hours', 'woocommerce' ),
				        'minutes' => __('Minutes', 'woocommerce' )
				    ),
				    'default'     => 'hours'
				),
				'ptl' =>array(
					'title'       => __( 'PTL ( Optional )', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Define number of hours to payment time limit', 'woocommerce' ),
					'desc_tip'    => true
				),
				'reduce_stock' => array(
					'title'       => __( 'Manage Stock', 'woocommerce' ),
					'type'        => 'checkbox',
					'label'   	  => __( 'Reduce Stock Automatically', 'woocommerce' ),
					'description' => __( 'The stock will be reduced automatically, after successful payment.', 'woocommerce' ),
					'default'     => 'yes'
				),
				'is_default_img' => array(
					'title'       => __( 'Checkout Image', 'woocommerce' ),
					'type'        => 'checkbox',
					'label'   	  => __( 'Use default DPO checkout image', 'woocommerce' ),
					'description' => __( 'To change checkout image, uncheck checkbox and add new image url to "Image URL" field below', 'woocommerce' ),
					'default'     => 'yes'
				),
				'image_url' => array(
					'title'       => __( 'Image URL', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Type URL of new checkout image, make sure that "Checkout Image" checkbox is unchecked', 'woocommerce' ),
					'desc_tip'    => true,
				)
				
			);
		}

		//WooCommerce DPO settings html
		public function admin_options() {
			?>
				<h2><?php _e('DPO','woocommerce'); ?></h2>
				<table class="form-table">
				<?php $this->generate_settings_html(); ?>
				</table> 
			<?php
		}

		public function process_payment( $order_id ) {

			
			$response = $this->before_payment($order_id);
			
			if ($response === FALSE){

				//show error message
				wc_add_notice( __('Payment error: Unable to connect to the payment gateway, please try again', 'woothemes'), 'error');
				return array(
		        	'result' 	=> 'fail',
					'redirect'	=> ''
		        );

			}else{

				//convert the XML result into array
        		$xml = new SimpleXMLElement($response);

        		if ($xml->Result[0] != '000') {

        			//show error message
					wc_add_notice( __('Payment error code: '.$xml->Result[0]. ', '.$xml->ResultExplanation[0] , 'woothemes'), 'error');
					return array(
			        	'result' 	=> 'fail',
						'redirect'	=> ''
			        );
	        	}
        		// create 3G gateway paymnet URL
    			$paymnetURL = $this->url."/pay.php?ID=".$xml->TransToken[0];
    			return array(
        			'redirect' => $paymnetURL,
        			'result' => 'success'
        		);
			}
		}

		//get all form details from user
		public function before_payment ( $order_id ){

			global $woocommerce;
		
			$order = new WC_Order( $order_id );

			$param = array(
				'order_id'			  => $order_id,
				'amount' 			  => (isset($order->order_total))? '<PaymentAmount>'.$order->order_total.'</PaymentAmount>' : "",
				'first_name' 		  => (isset($order->billing_first_name))? '<customerFirstName>'.$order->billing_first_name.'</customerFirstName>' : "",
				'last_name' 		  => (isset($order->billing_last_name))? '<customerLastName>'.$order->billing_last_name.'</customerLastName>' : "",
				'phone' 		      => (isset($order->billing_phone))? '<customerPhone>'.$order->billing_phone.'</customerPhone>' : "",
				'email' 		      => (isset($order->billing_email))? '<customerEmail>'.$order->billing_email.'</customerEmail>' : "",
				'address' 		      => (isset($order->billing_address_1))? '<customerAddress>'.$order->billing_address_1.'</customerAddress>' : "",
				'city' 		      	  => (isset($order->billing_city))? '<customerCity>'.$order->billing_city.'</customerCity>' : "",
				'zipcode' 		      => (isset($order->billing_postcode))? '<customerZip>'.$order->billing_postcode.'</customerZip>' : "",
				'country' 		      => (isset($order->billing_country))? '<customerCountry>'.$order->billing_country.'</customerCountry>' : "",
				'ptl_type' 		      => ($this->ptl_type == 'minutes')? '<PTLtype>minutes</PTLtype>' : "",
				'ptl' 		      	  => (!empty($this->ptl))? '<PTL>'.$this->ptl.'</PTL>' : "",
				'currency'			  => get_woocommerce_currency()
			);

			//save payment parametres to session 
			$woocommerce->session->paymentToken = $param;

			//create xml and send request return response
			$response =  $this->create_send_xml_request($param, $order);

			return $response;
		}

		//create xml and send by curl return response
		public function create_send_xml_request($param, $order){

			//URL for 3G to send the buyer to after review and continue from 3G.
			$returnURL = $this->get_return_url( $order );
			//URL for 3G to send the buyer to if they cancel the payment.
			$cancelURL = WC()->cart->get_cart_url();

			//get all pruducts in the cart retrieve service type and description of the product
			$service = '';

			foreach (WC()->cart->cart_contents as $cart_item) {

				//get product settings
				$product_data = get_post_meta($cart_item['product_id']);
				//get product details
				$single_product = new WC_Product($cart_item['product_id']);

				$serviceType = isset($product_data["service_type"][0]) ? $product_data["service_type"][0] : 0;
				$serviceDesc = preg_replace('/&/', 'and', $single_product->post->post_title);
			
				//create each product service xml
				$service .= '<Service>
								<ServiceType>'.$serviceType.'</ServiceType>
								<ServiceDescription>'.$serviceDesc.'</ServiceDescription>
								<ServiceDate>'.current_time('Y/m/d H:i') .'</ServiceDate>
							</Service>';
			}
			
			$input_xml = '<?xml version="1.0" encoding="utf-8"?>
					<API3G>
						<CompanyToken>'.$this->company_token.'</CompanyToken>
						<Request>createToken</Request>
						<Transaction>'.$param["first_name"].
									   $param["last_name"].
									   $param["phone"].
									   $param["email"].
									   $param["address"].
									   $param["city"].
									   $param["zipcode"].
									   $param["country"].
									   $param["amount"].'
							<PaymentCurrency>'.$param["currency"].'</PaymentCurrency>
							<CompanyRef>'.$param["order_id"].'</CompanyRef>
							<RedirectURL>'.htmlspecialchars ($returnURL).'</RedirectURL>
							<BackURL>'.htmlspecialchars ($cancelURL).'</BackURL>
							<CompanyRefUnique>0</CompanyRefUnique>
							'.$param["ptl_type"].
							  $param["ptl"].'
						</Transaction>
						<Services>'.$service.'</Services>
					</API3G>';

			$response = $this->createCURL($input_xml);

			return $response;
		}

		//generate Curl and return response
		public function createCURL($input_xml){

			$url =$this->url."/API/v6/";
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSLVERSION,6);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);

			$response = curl_exec($ch);
		
			curl_close($ch);

			return $response;
		}

		
		//verify 3g response
		public function check_3g_response($order_id){

			global $woocommerce;

			$transactionToken = $_GET['TransactionToken'];

			if (!isset($transactionToken)) {
				wc_add_notice( __('Transaction Token error, please contact support center', 'woothemes'), 'error');
				wp_redirect( WC()->cart->get_cart_url()); exit; 
			}
			
			//get verify token response from 3g
			$response = $this->verifytoken($transactionToken);

			if ($response) {

				$order = wc_get_order( $order_id );
				
				if ($response->Result[0] == '000') {

					// Reduce stock levels
					if ($this->reduce_stock == 'yes'){
						$order->update_status('processing', __( 'The transaction paid successfully and waiting for approval.', 'woocommerce' ));
						$order->payment_complete();
					}
					else
						$order->update_status('on-hold', __( 'The transaction paid successfully and waiting for approval. Notice that the stock will NOT reduced automaticlly. ', 'woocommerce' ));
				}
				else{

					$error_code = $response->Result[0];
					$error_desc = $response->ResultExplanation[0];
					
					$order->update_status('failed ', __( 'Payment Failed: '.$error_code. ', '.$error_desc.'. Notice that the stock is NOT reduced. ', 'woocommerce' ));
					wc_add_notice( __('Payment Failed: '.$error_code. ', '.$error_desc,'woothemes' ),'error');
					wp_redirect( WC()->cart->get_checkout_url()); exit; 
				}
			}
			else{

				wc_add_notice( __(' Varification error: Unable to connect to the payment gateway, please try again', 'woothemes'), 'error');
				wp_redirect( WC()->cart->get_cart_url()); exit; 
			}
			
		}
		//verifyToken response from 3G
		public function verifytoken($transactionToken){

			$input_xml = '<?xml version="1.0" encoding="utf-8"?>
						<API3G>
						  <CompanyToken>'.$this->company_token.'</CompanyToken>
						  <Request>verifyToken</Request>
						  <TransactionToken>'.$transactionToken.'</TransactionToken>
						</API3G>';

			$response = $this->createCURL($input_xml);

			if ($response !==  FALSE) {
				//convert the XML result into array
        		$xml = new SimpleXMLElement($response);

        		return $xml;
        	}
			
			return false;
		}

	}//End of Class

	
	//Add the Gateway to WooCommerce
	function woocommerce_add_gateway_3g ($methods) {
		$methods[] = 'WC_Gateway_3G';
		return $methods;
	}
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_3g');
}

//Adding custom tabs (Service_type) to woocommerce product data settingds
function custom_tab_options_tab() {
	?>
		<li class="custom_tab"><a href="#3g_service_tab_data"><?php _e('DPO Service Type', 'woothemes'); ?></a></li>
	<?php
}
add_action('woocommerce_product_write_panel_tabs', 'custom_tab_options_tab');

function custom_tab_options() {
	global $post;
	
	$custom_tab_options = array(
		'service_type' => get_post_meta($post->ID, 'service_type', true)
	);
	
	?>
		<div id="3g_service_tab_data" class="panel woocommerce_options_panel">
		
			<div class="options_group custom_tab_options">                								
				<p class="form-field">
					<label><?php _e('Service Type:', 'woothemes'); ?></label>
					<input type="text"  name="service_type" value="<?php echo @$custom_tab_options['service_type']; ?>" placeholder="<?php _e('For example: 45', 'woothemes'); ?>" />
				</p>
			
	        </div>	
		</div>
	<?php
}
add_action('woocommerce_product_write_panels', 'custom_tab_options');

/**
 * Process meta
 * 
 * Processes the custom tab options when a post is saved
 */
function process_product_meta_custom_tab( $post_id ) {

	update_post_meta( $post_id, 'service_type', $_POST['service_type']);
}
add_action('woocommerce_process_product_meta', 'process_product_meta_custom_tab');
