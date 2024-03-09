=== ABN Lookup for Gravity Forms ===
Contributors: bakkbone,ovann86
Donate link: https://ko-fi.com/bakkbone_scott
Tags: gravity forms, forms, abn, australian business number, australian business register
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.1.1
License: GNU General Public License (GPL) 3.0
License URI: https://www.gnu.org/licenses/gpl.html

Integrate the Australian Business Register ABN Lookup tool in Gravity Forms

== Description ==

> This plugin is an add-on for the Gravity Forms plugin. If you don't yet own a license for Gravity Forms - <a href="https://rocketgenius.pxf.io/bakkbone" target="_blank">buy one now</a>! (affiliate link)

### What does this plugin do?

* connect your forms to the [Australian Business Register ABN Lookup tool](http://abr.business.gov.au "Australian Business Register website")
* verify the ABN status and entity details
* pre-fill ABN status, entity name, entity type, location, GST status, GST registered date, entity date, business names into form fields
* use conditional logic and validation to enforce which entities can complete your form

Includes an **easy to use settings page** that allows you to configure:

* enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))
* disable plugin CSS styles - allowing you to create your own styles
* customise error messages and prompts displayed to form users

> See a demo of this plugin at [staging.bkbn.au/abn-lookup-for-gravity-forms](https://staging.bkbn.au/abn-lookup-for-gravity-forms/ "Demonstration Site")

### Have a suggestion, comment or request?

Please leave a detailed message on the support tab.

### Let us know what you think

Please take the time to review the plugin. Your feedback is important and will help us understand the value of this plugin.

### Disclaimer

*Gravity Forms is a trademark of Rocketgenius, Inc.*

*This plugin is provided “as is” without warranty of any kind, expressed or implied. The author shall not be liable for any damages, including but not limited to, direct, indirect, special, incidental or consequential damages or losses that occur out of the use or inability to use the plugin.*

== Installation ==

### Install and configure the plugin

1. Install and activate the plugin.
1. Open the ABN Lookup for Gravity forms settings page (Gravity Forms -> Settings -> ABN Lookup menu) and enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))

### Create an ABN Lookup field

1. In your form add or edit a 'Single Line Text' field
1. In the field settings, place a tick next to the 'ABN Lookup field' option

### To pre-fill GST status from an ABN Lookup field

1. Add a 'Radio Buttons' field
1. Place a tick next to the 'GST results field' option
1. Using the 'Link ABN Lookup field' drop down select the ABN Lookup field to link to the field to

== Frequently Asked Questions ==

= How do I configure the plugin? =

A range of options can be found under the Gravity Forms 'ABN Lookup' settings menu.

= How do I use the plugin? =

1. Install and activate the plugin.
1. Open the ABN Lookup for Gravity forms settings page (Gravity Forms -> Settings -> ABN Lookup menu) and enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))
1. In your form add or edit a 'Single Line Text' field
1. In the field settings, place a tick next to the 'ABN Lookup field' option

To pre-fill GST status from an ABN Lookup field:

1. Add a 'Radio Buttons' field
1. Place a tick next to the 'GST results field' option
1. Using the 'Link ABN Lookup field' drop down select the ABN Lookup field to link to the field to

= How do I change the value attribute of the GST result field? =

Two filters are available for customising the 'value' attribute for the GST result field:

itsg_gf_abnlookup_gst_value_yes

itsg_gf_abnlookup_gst_value_no

Example usage:

Please note: there appears to be an issue with returning a '0' value - so in this case return '00'.

`add_filter( 'itsg_gf_abnlookup_gst_value_yes', 'my_itsg_gf_abnlookup_gst_value_yes', 10, 2 );

function my_itsg_gf_abnlookup_gst_value_yes( $text_yes, $form_id ) {
	return '10';
}

add_filter( 'itsg_gf_abnlookup_gst_value_no', 'my_itsg_gf_abnlookup_gst_value_no', 10, 2 );

function my_itsg_gf_abnlookup_gst_value_no( $text_no, $form_id ) {
	return '00';
}`

== Screenshots ==

1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup for Gravity Forms options page.
1. Shows ABN Lookup field when loading.
1. Shows ABN Lookup field after returning values, complete with pre-filled fields.

== Changelog ==
### 2.1.1
* DEV: Fix `sprintf()` causing fatal error
### 2.1.0
* DEV: Fix bug where JS was not firing on Ajax forms
* TWEAK: Improve responsiveness of search field upon data entry
### 2.0.1
* TWEAK: Fix JS error when only one business name returned
### 2.0.0
* ADD: Business Names retrieval on Dropdown field
* DEV: Upgrade to API v202001
* TWEAK: Improve display of Check ABN button
* TWEAK: Replace indicator graphics
### 1.9.3
* DEV: Mitigate effect on add-on of `gform_post_conditional_logic` being called by GF core so often
* TEST: Tested WP 6.4
### 1.9.2
* DEV: Fix `wp_kses()` error
### 1.9.1
* Change ownership - BAKKBONE Australia taking on development of this plugin.
* TWEAK: Revert plugin localisation for compatibility.
### 1.9.0
* DEV: Fix known bug with ABN Lookup tab not displaying in form editor.
* TWEAK: Improve plugin localization.
### 1.8.0
* Fix: update plugin to be compatible with PHP 8.0
* Fix: improve compatibility with Gravity Forms 2.5 form editor
* Feature: move field settings to 'ABN Lookup' tab in form editor
### 1.7.0
* Feature: now uses native WordPress transients to store ABN Lookup cache
* Maintenance: general code review and tidy up
### 1.6.6
* Fix: resolve "Fatal error: Using $this when not in object context in" when using PHP 5.6 and below.
### 1.6.5
* Feature: move form admin JavaScript to external file.
* Feature: only show ABN Lookup result options (in form editor) if the form contains an ABN Lookup field.
* Feature: improve handling to minimise someone attempting to make a field BOTH an ABN Lookup field AND result field.
### 1.6.4
* Feature: better handling when an ABN lookup field hasn't been selected for an ABN result field in the form settings.
### 1.6.3
* Maintenance: add keyup() event to radio fields when GST yes/no result cleared. For example, when an existing ABN has been entered and later removed.
### 1.6.2
* Maintenance: add keyup() event to radio fields when GST yes/no result has been updated. Makes it easier to build conditional logic and build number field calculations based on GST status (e.g. GST rate).
### 1.6.1
* Feature: add options to control ABN Lookup timeout and number of retries before lookup fails. Default remains 5 seconds before timeout and 3 retries.
### 1.6.0
* Feature: change field validation so that ABN Lookup fields can be set as mandatory (or not), separate from the ABN validation options (registered for GST, is valid etc)
* Maintenance: change how plugin checks for Gravity Forms being installed and active
* Maintenance: add additional sanitisation for ABNs provided by users and passed to the ABR system
### 1.5.0
* Feature: Add filter to allow custom radio field input values for GST result fields
* Maintenance: improve support for 'List Field Number Format for Gravity Forms' plugin
### 1.4.3
* Fix: Improve JavaScript error handling in form editor.
### 1.4.1
* Fix: Patch to allow scripts to enqueue when loading Gravity Form through wp-admin. Gravity Forms 2.0.3.5 currently has a limitation that stops the required scripts from loading through the addon framework.
* Maintenance: Add minified JavaScript and CSS
* Maintenance: Confirm working with WordPress 4.6.0 RC1
* Maintenance: Update to improve support for Gravity Flow plugin
### 1.4.0
* Feature: Add 'None' option for ABN Lookup validation. This will allow forms to submit with an invalid ABN, for example if an ABN was recently created and not yet available in the Australian Business Register.
* Feature: Add ABN Registered and GST Registered results options, these can be found in a Date field in the form editor. The date format can be controlled using the standard format option as well as apply conditional logic to the results.
* Fix: Resolve JavaScript 'undefined variable' error message seen in Internet Explorer 11.
* Maintenance: Improve translation support.
### 1.3.2
* Maintenance: Add some styling to the options in the form editor.
* Maintenance: Moved JavaScript to external file.
* Maintenance: Change JavaScript and CSS to load using Gravity Forms addon framework.
* Maintenance: Tested against Gravity Forms 2.0 RC1.
* Maintenance: Tested against Gravity PDF 4.0 RC4.
### 1.3.1
* Maintenance: Improve support for PHP version 5.2 and 5.3.
* Maintenance: Improve support for multi-site WordPress installations.
### 1.3.0
* Feature: Added 'Check ABN' button displayed next to ABN Lookup enabled field. Can be used by user to trigger ABN Lookup and will also make them aware that the form field is special.
* Maintenance: Tweaking CSS.
* Maintenance: Improve do_abnlookup function to stop lookups happening when an ABN Lookup field is empty.
### 1.2.4
* Maintenance: Improved JavaScript to trigger ABN Lookup when ABN Lookup results field is empty but the linked ABN Lookup field has an ABN.
* Maintenance: Improved translation support.
* Maintenance: Tidy up of PHP code, working towards WordPress standards.
### 1.2.3
* Fix: Resolve issue with ABN status information appearing in Gravity Forms entry editor.
### 1.2.2
* Maintenance: Improved handling for when ABN Lookup fields are in a section that has conditional logic applied.
### 1.2.1
* Fix: Resolve issue with GST field settings not saving in form editor.
### 1.2.0
* Feature: Change communication method to the Australian Business Register from SOAP to GET.
* Maintenance: Add error handling if an individual entity does not have a middle name.
### 1.1.1
* Maintenance: Add check for SOAP client to ensure plugin does not cause the 'white screen of death' is web host does not have SOAP installed and enabled.
### 1.1.0
* Feature: Allow ABN Lookup to be triggered by pressing the enter key. If a user presses the enter key inside an ABN Lookup field the default action of submitting the form will be prevented and the ABN Lookup will begin instead.
* Feature: Add timeout, retry and error message. If unable to communicate with Australian Business register after five seconds the script will try again up to three times. After three times an error message is displayed to the user.
* Maintenance: Refine default messages - invalid message now has a link to the Australian Business Register.
* Maintenance: Make error message styling more consistent with Gravity Forms field error messages.
### 1.0.2
* FEATURE: Override ABR error message 'Search text is not a valid ABN or ACN' as it is not particularly useful for the end user. If this error message is returned by the ABN Lookup API the 'ABN not valid' error message will be displayed instead. This can be customised in the ABN Lookup for Gravity Forms settings page.
### 1.0.1
* FIX: Revise JavaScript to resolve issue with linked fields displaying when ABN is not valid.
* FIX: Revise JavaScript to trigger change event when linked fields are pre-filled. This allows Gravity Forms conditional logic to be used against the linked fields.
### 1.0.0
* First public release.

== Upgrade Notice ==
### 2.0.0
Upgrades to 2020 API and adds Business Names feature
### 1.9.0
Fixes major display issue in form editor.