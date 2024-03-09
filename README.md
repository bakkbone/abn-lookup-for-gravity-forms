## Description

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

## Installation

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

## Frequently Asked Questions

### How do I configure the plugin?

A range of options can be found under the Gravity Forms 'ABN Lookup' settings menu.

### How do I use the plugin?

1. Install and activate the plugin.
1. Open the ABN Lookup for Gravity forms settings page (Gravity Forms -> Settings -> ABN Lookup menu) and enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))
1. In your form add or edit a 'Single Line Text' field
1. In the field settings, place a tick next to the 'ABN Lookup field' option

To pre-fill GST status from an ABN Lookup field:

1. Add a 'Radio Buttons' field
1. Place a tick next to the 'GST results field' option
1. Using the 'Link ABN Lookup field' drop down select the ABN Lookup field to link to the field to

### How do I change the value attribute of the GST result field?

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
