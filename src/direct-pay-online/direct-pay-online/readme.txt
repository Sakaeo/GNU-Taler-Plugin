=== DPO for Woocommerce ===
Contributors: DPO
Description:  Online payments plug in powered by DPO, credit cards, PayPal and Mobile Money
Author: DPO
Author URI: http://www.directpay.online/
Tags: woocommerce, e-commerce, direct pay, direct pay online, dpo, payment
Version: 1.0.9
Requires at least: 2.2
Tested up to: 4.9
Stable tag: trunk
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Online payments plug in powered by DPO, credit cards, PayPal and Mobile Money

== Description ==

DPO provides a safe and secure payments plugin for WooCommerce, the plugin connected the WooCommerce to an online payments service which supports all mode of payments, Credit cards, PayPal, and Mobile Money, bank transfers and more, it's a multi currency service which can accept any payment from anywhere in the world. 
DPO payments service is a PCI DSS Level 1 certified and included with the latest fraud prevention and risk management features. 
For support contact us at: support@directpay.online


== Installation ==

1. Ensure you have latest version of WooCommerce plugin installed
2. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the DPO for WooCommerce plugin through the 'Plugins' screen in WordPress.
4. Use WooCommerce settings-> checkout tab -> Direct Pay Online Settings to configure the plugin.


== Frequently Asked Questions ==

= Do I have to be a DPO customer to use the plugin? =

Yes, you need an online account with DPO to use the plugin. 
What to join the home of online payments? Contact us at **sales@directpay.online** and start accepting payments online, anywhere.. anytime.

= Can the DPO plugin work without an ecommerce plugin? =

Wordpress has lots of free ecommerce plugins like woocommerce, WP eCommerce, Ecwid.. Etc For it to work you will  need to an ecommerce plugin.

= Can the DPO plugin work with all ecommerce plugins in wordpress? =

Yes, wordpress has many ecommerce plugins to choose from that can suit your business and they are all compatible with the DPO plugin.

= Can the plugin work with websites built on other (CMS) Content Management Systems? =

The plugin is specifically designed for wordpress, however DPO has an amazing API that can integrate with any website in the world! Contact us **sales@directpay.online** to get started today.

= What is the 3G URL? =

The secure payment gateway URL that you to hosts your customers when making a payment.

= What is a PTL and how can I use it? =

The payment time limit is the time span that a customer has to make a payment for a specific transaction.

= How can I use the PTL? =

Default is set to 96hrs but you can customize it based on hours and minutes. 
This parameter depends on your business model. 
Contact our support team **sales@directpay.online** to show you how you can use this amazing tool.

= There is no DPO payment option on checkout page =

Check DPO setting. Simply, go to your admin panel, select WooCommerce > Settings >Checkout tab > Direct Pay Online, Check if Enable 3G Direct Pay checkbox is checked.

= I receive Payment error code: 802, Wrong CompanyToken after place order =

Means that the company token number in 3G Direct Pay Plugin setting is incorrect. It very important to verify that the company token number that you received from 3G team is defined properly, otherwise the plugin will not work.
Example for company token: 57466282-EBD7-4ED5-B699-8659330A6996.

=  I receive String could not be parsed as XML after place order =

Means that the 3G URL  address in DPO Plugin setting is incorrect. You need to verify that 3G URL address that you received from 3G team is defined property. 
The correct 3G URL address is https://secure.3gdirectpay.com , type the address in 3G URL field  and save changes.

=  I receive Payment error code: 913, Service type does not exist after place order  =

Means that the service type of the product or service that you order is not defined correctly. Each product must be set correct service type number, that you received from 3G team.
Service type must be a numeric only, otherwise the plugin will not work. To define service type simply go to your admin panel > Products > select product > in Product Data select 3G service Type tab, type the correct service type number only that you received from 3G Team and save changes.
You may, also, receive service type error like: Xml error, Payment error code: 912, Data mismatch in one of service fields - ServiceType not numeric.

=  Quick step by step solution for general plugin errors  =

1. Check that woocommerce and DPO plugins are activated properly.
2. Check DPO settings:
   * Checkbox Enable DPO must be checked
   * Company token must be defined correctly and be the same as you received from DPO
   * 3G URL must be  https://secure.3gdirectpay.com
3. Check product settings
   * Verify that the Direct Pay service type is defined and is numeric only. This number must be the same as you received from DPO. Each product or service must have service type number.
4. After you checked the settings and verify that all the fields are defined correctly but the problem not solved, please contact the DPO support for additional help.

== Screenshots ==

1. WooCommerce payment gateway setting page.
2. Product Data settings.
3. 3G Payment checkout page.
4. Checkout Image settings.

== Configuration ==

1.  Visit the WooCommerce settings page, and click on the Checkout tab.
2.  Click on Direct Pay to edit the settings. If you do not see DPO in the list at the top of the screen make sure you have activated the plugin in the WordPress Plugin Manager.
3.  Enable DPO Method.
4.  In the title field, name it DPO (this will show up on the payment page your customer sees).
5.  Insert Company Token (mandatory). This number you will receive from DPO.
6.  Insert 3G URL (mandatory). This is payment gateway url that you will receive from DPO.
7.  Select PTL Type (optional). Define if PTL( payment time limit ) tag is hours or minutes. Options: Hours or Minutes. By default, option hours will be selected.
8.  Insert PTL (optional). Number of hours to payment time limit. By default, payment time limit is 96 hours.
9.  Enable/ Disable Reduce stock automatically.  By checking the box you allows to reduce the stock automatically after successful payment. Otherwise, after a successful payment the order status will be �on-hold� and you will have to reduce the stock manually.
10. Visit the Products page, and click on "Add Product" button or edit exist product,  move to Product Data and click on Direct Pay Service Type.
11. Service type is mandatory fields. For each product you must insert the service type number according to the options accepted from DPO.
12. Checkout Image checkbox. Checked by default and shows the Direct Pay Checkout Image. To change the image simply uncheck the box and enter new image url to "Image URL" field below or to remove image simply uncheck the box.


== Changelog ==

= 1.0.9 =
  *  Updated DPO default checkout image

= 1.0.8 =
  *  Added to cURL SSL version 6 and DPO URL version to 6

= 1.0.7 =
  *  Banner images and text updated.

= 1.0.6 =
  * Order status bug fixed.

= 1.0.5 =
  * Bug fix.

= 1.0.4 =
 * Added checkout image and image url settings fields to 3G Direct Pay settings.

= 1.0.3 =
 * Bug fix.

= 1.0.2 =
 * Removed service description from product settings.

= 1.0.1 =
 * Add: 3G URL input.
 * Add: PTL Type select box.
 * Add: PTL input.

= 1.0.0 =
 * First Public Release.

== Upgrade Notice ==

= 1.0.6 =
This version fixes an order status related bug. Please, upgrade immediately.

= 1.0.5 =
This version fixes a ssl security related bug. Please, upgrade immediately.