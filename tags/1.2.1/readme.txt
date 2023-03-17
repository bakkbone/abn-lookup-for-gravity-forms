=== ABN Lookup for Gravity Forms ===
Contributors: ovann86
Donate link: http://www.itsupportguides.com/
Tags: gravity forms, forms, ajax, abn, australian business number, australian business register
Requires at least: 4.2
Tested up to: 4.4.2
Stable tag: 1.2.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate the Australian Business Register ABN Lookup tool in Gravity Forms

== Description ==

> This plugin is an add-on for the [Gravity Forms](https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380 "Gravity Forms website") plugin. If you haven't already bought Gravity Forms [buy one now](https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380 "Gravity Forms website")!

**What does this plugin do?**

* connect your forms to the [Australian Business Register ABN Lookup tool](http://abr.business.gov.au "Australian Business Register website")
* verify the ABN status and entity details
* prefill ABN status, entity name, entity type, location and GST status into form fields
* use conditional logic and validation to enforce which entities can complete your form

Includes an **easy to use settings page** that allows you to configure:

* enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))
* disable plugin CSS styles - allowing you to create your own styles
* customise error messages and prompts displayed to form users

> See a demo of this plugin at [demo.itsupportguides.com/abn-lookup-for-gravity-forms](http://demo.itsupportguides.com/abn-lookup-for-gravity-forms/ "demo website")

**How to I use the plugin?**

1. Install and activate the plugin.
1. Open the ABN Lookup for Gravity forms settings page (Gravity Forms -> Settings -> ABN Lookup menu) and enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))
1. In your form add or edit a 'Single Line Text' field
1. In the field settings, place a tick next to the 'ABN Lookup field' option

To pre-fill GST status from an ABN Lookup field

1. Add a 'Radio Buttons' field
1. Place a tick next to the 'GST results field' option 
1. Using the 'Link ABN Lookup field' drop down select the ABN Lookup field to link to the field to

**Have a suggestion, comment or request?**

Please leave a detailed message on the support tab. 

**Let me know what you think**

Please take the time to review the plugin. Your feedback is important and will help me understand the value of this plugin.

**Disclaimer**

*Gravity Forms is a trademark of Rocketgenius, Inc.*

*This plugins is provided “as is” without warranty of any kind, expressed or implied. The author shall not be liable for any damages, including but not limited to, direct, indirect, special, incidental or consequential damages or losses that occur out of the use or inability to use the plugin.*

== Installation ==

**Install and configure the plugin**

1. Install and activate the plugin.
1. Open the ABN Lookup for Gravity forms settings page (Gravity Forms -> Settings -> ABN Lookup menu) and enter your unique GUID (necessary to use the plugin features - provided by the Australian Business Register, see [web services registration](http://abr.business.gov.au/webservices.aspx "Australian Business Register web services registration website"))

**Create an ABN Lookup field**

1. In your form add or edit a 'Single Line Text' field
1. In the field settings, place a tick next to the 'ABN Lookup field' option

**To pre-fill GST status from an ABN Lookup field**

1.  Add a 'Radio Buttons' field
1. Place a tick next to the 'GST results field' option 
1. Using the 'Link ABN Lookup field' drop down select the ABN Lookup field to link to the field to

== Frequently Asked Questions ==

**How do I configure the plugin?**

A range of options can be found under the Gravity Forms 'ABN Lookup' settings menu.

== Screenshots ==

1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup field options in the form editor.
1. Shows ABN Lookup for Gravity Forms options page.
1. Shows ABN Lookup field when loading.
1. Shows ABN Lookup field after returning values, complete with pre-filled fields.

== Changelog ==

= 1.2.1 =
* Fix: Resolve issue with GST field settings not saving in form editor.

= 1.2.0 =
* Feature: Change communication method to the Australian Business Register from SOAP to GET.
* Maintenance: Add error handling if an individual entity does not have a middle name.

= 1.1.1 =
* Maintenance: Add check for SOAP client to ensure plugin does not cause the 'white screen of death' is web host does not have SOAP installed and enabled.

= 1.1.0 =
* Feature: Allow ABN Lookup to be triggered by pressing the enter key. If a user presses the enter key inside an ABN Lookup field the default action of submitting the form will be prevented and the ABN Lookup will begin instead.
* Feature: Add timeout, retry and error message. If unable to communicate with Australian Business register after five seconds the script will try again up to three times. After three times an error message is displayed to the user.
* Maintenance: Refine default messages - invalid message now has a link to the Australian Business Register.
* Maintenance: Make error message styling more consistent with Gravity Forms field error messages.

= 1.0.2 =
* FEATURE: Override ABR error message 'Search text is not a valid ABN or ACN' as it is not particularly useful for the end user. If this error message is returned by the ABN Lookup API the 'ABN not valid' error message will be displayed instead. This can be customised in the ABN Lookup for Gravity Forms settings page.

= 1.0.1 =

* FIX: Revise JavaScript to resolve issue with linked fields displaying when ABN is not valid.
* FIX: Revise JavaScript to trigger change event when linked fields are prefilled. This allows Gravity Forms conditional logic to be used against the linked fields.

= 1.0 =

* First public release.