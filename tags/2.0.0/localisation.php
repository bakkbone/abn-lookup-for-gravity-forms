<?php

/**
 * @author BAKKBONE Australia
 * @package AbrLocalisation
 * @license GNU General Public License (GPL) 3.0
**/

if ( ! defined(  'ABSPATH' ) ) {
	die();
}

define('ABR_PLUGIN_TITLE', __('ABN Lookup for Gravity Forms', 'abn-lookup-for-gravity-forms'));
define('ABR_1', __('View ABN Lookup Settings', 'abn-lookup-for-gravity-forms'));
define('ABR_SETTINGS', __('Settings', 'abn-lookup-for-gravity-forms'));
define('ABR_EMPTY_VALUE', __('Empty ABN value passed.', 'abn-lookup-for-gravity-forms'));
define('ABR_UNCONFIGURED', __('ABN Lookup for Gravity Forms has not been configured. The GUID necessary to communicate with the Australian Business Register has not been specified.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_NOT_VALID', __('The ABN provided is not valid. Check the number entered and try again, or use the https://abr.business.gov.au/ website to confirm your ABN.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_ACTIVEABN', __('The ABN provided is not active. Entities that do not have an active ABN cannot complete this form.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_REGGST', __('The ABN provided is not registered for GST. Entities that are not registered for GST cannot complete this form.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_NOTREGGST', __('The ABN provided is registered for GST. Entities that are registered for GST cannot complete this form.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_11_CHAR', __('The information entered does not match a valid ABN. ABNs need to be 11 digits.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_LOADING', __('Checking ABN with the Australian Business Register.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_MESSAGE_ERROR_COMMUNICATING', __('Error communicating with the Australian Business Register.', 'abn-lookup-for-gravity-forms'));
define('ABR_REQUIRE_GF', __('The plugin %s requires Gravity Forms to be installed.', 'abn-lookup-for-gravity-forms'));
define('ABR_UPDATE_GF', __('Please %sdownload the latest version%s of Gravity Forms and try again.', 'abn-lookup-for-gravity-forms'));
define('ABR_REQ_GUID', __('The plugin %s requires a GUID to communicate with the Australian Business Register.', 'abn-lookup-for-gravity-forms'));
define('ABR_GET_GUID', __('To receive a GUID see %sweb services registration%s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms'));
define('ABR_USE_GUID', __('Once you have a GUID you will need to enter it in the %sABN Lookup for Gravity Forms Settings%s page.', 'abn-lookup-for-gravity-forms'));
define('ABR_CHECKING', __('Checking', 'abn-lookup-for-gravity-forms'));
define('ABR_CHECK_ABN', __('Check ABN', 'abn-lookup-for-gravity-forms'));
define('ABR_YES', __('Yes', 'abn-lookup-for-gravity-forms'));
define('ABR_NO', __('No', 'abn-lookup-for-gravity-forms'));
define('ABR_ENABLE', __('Enable ABN Lookup', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION', __('ABN Lookup Field Validation', 'abn-lookup-for-gravity-forms'));
define('ABR_GST_LINK', __('Enable ABN Lookup GST', 'abn-lookup-for-gravity-forms'));
define('ABR_LINK', __('Link ABN Lookup field', 'abn-lookup-for-gravity-forms'));
define('ABR_RESULT', __('ABN Lookup results field', 'abn-lookup-for-gravity-forms'));
define('ABR_ENABLE_TOOLTIP', __('Check this box to integrate this field with the Australian Government ABN Lookup tool.', 'abn-lookup-for-gravity-forms'));
define('ABR_VALIDATION_TOOLTIP', __('Choose the level of validation required for the ABN Lookup field.', 'abn-lookup-for-gravity-forms'));
define('ABR_GST_LINK_TOOLTIP', __('Check this box to link the field with an ABN Lookup field.', 'abn-lookup-for-gravity-forms'));
define('ABR_LINK_TOOLTIP', __('Select the ABN Lookup field to link to.', 'abn-lookup-for-gravity-forms'));
define('ABR_RESULT_TOOLTIP', __('Check this box to link the field with an ABN Lookup field.', 'abn-lookup-for-gravity-forms'));
define('ABR_LOOKUP', __('ABN Lookup', 'abn-lookup-for-gravity-forms'));
define('ABR_FIELD', __('ABN Lookup Field', 'abn-lookup-for-gravity-forms'));
define('ABR_NONE', __('None', 'abn-lookup-for-gravity-forms'));
define('ABR_VALID', __('Valid ABN', 'abn-lookup-for-gravity-forms'));
define('ABR_ACTIVE', __('Active ABN', 'abn-lookup-for-gravity-forms'));
define('ABR_REG', __('Registered for GST', 'abn-lookup-for-gravity-forms'));
define('ABR_NOTREG', __('Not Registered for GST', 'abn-lookup-for-gravity-forms'));
define('ABR_TYPE', __('Entity Type', 'abn-lookup-for-gravity-forms'));
define('ABR_NAME', __('Entity Name', 'abn-lookup-for-gravity-forms'));
define('ABR_STATUS', __('ABN Status', 'abn-lookup-for-gravity-forms'));
define('ABR_POSTCODE', __('Entity Postcode', 'abn-lookup-for-gravity-forms'));
define('ABR_STATE', __('Entity State/Territory', 'abn-lookup-for-gravity-forms'));
define('ABR_GST_RESULT', __('GST Results Field', 'abn-lookup-for-gravity-forms'));
define('ABR_ENTITY_FROM', __('Entity Effective From', 'abn-lookup-for-gravity-forms'));
define('ABR_GST_FROM', __('GST Effective From', 'abn-lookup-for-gravity-forms'));
define('ABR_GUID', __('GUID', 'abn-lookup-for-gravity-forms'));
define('ABR_INC_CSS', __('Include CSS styles', 'abn-lookup-for-gravity-forms'));
define('ABR_INC_CSS_TOOLTIP', __('This option allows you to control whether to use the CSS styles provided in the plugin. If this is not enabled you will need to apply styles through your theme.', 'abn-lookup-for-gravity-forms'));
define('ABR_TIMEOUT', __('Lookup timeout (seconds)', 'abn-lookup-for-gravity-forms'));
define('ABR_TIMEOUT_TOOLTIP', __('This option controls the amount of time, in seconds, before a request to the ABR lookup system will timeout.', 'abn-lookup-for-gravity-forms'));
define('ABR_RETRIES', __('Lookup retries', 'abn-lookup-for-gravity-forms'));
define('ABR_RETRIES_TOOLTIP', __('his options controls the number of retries when a request to the ABR lookup system has failed. When all retries have been used the field will return the "Error communicating message" error message.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_MSG', __('Validation Messages', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOT_VALID', __('ABN not valid', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOT_VALID_TOOLTIP', __('This message is displayed to the user if they enter an ABN that is not valid.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOT_ACTIVE', __('ABN not active', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOT_ACTIVE_TOOLTIP', __('This message is displayed to the user if they enter an ABN that is not active.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOTREG', __('ABN not registered for GST', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_NOTREG_TOOLTIP', __('This message is displayed to the user if they enter a ABN that is not registered for GST and the field validation is set to only allow ABNs that are GST registered.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_REG', __('ABN registered for GST', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_REG_TOOLTIP', __('This message is displayed to the user if they enter a ABN is registered for GST and the field validation is set to only allow ABNs that are not registered for GST.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_LENGTH', __('ABN not correct length', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_LENGTH_TOOLTIP', __('This message is displayed to the user if they enter a value into the ABN field that does not contain the required 11 characters that make up an ABN.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_LOADING', __('Loading message', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_LOADING_TOOLTIP', __('This message is displayed to the user when the ABN Lookup is running.', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_ERROR', __('Error communicating message', 'abn-lookup-for-gravity-forms'));
define('ABR_VAL_ERROR_TOOLTIP', __('This message is displayed to the user when the ABN Lookup script has failed to communicate with the Australian Business Register more than three times.', 'abn-lookup-for-gravity-forms'));
define('ABR_WARNING', __( 'Warning', 'abn-lookup-for-gravity-forms' ));