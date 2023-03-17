<?php
/**
 * @author Justin Swan - 16 August 2012
 * extends php soap client to utilize the Australian Government ABN Lookup web service 
 * requires php 5 or greater and lib-xml enabled/compiled with apache
 * @link	http://www.php.net/manual/en/book.soap.php
 * @link	http://abr.business.gov.au/Webservices.aspx
 * 
 * @param string $guid - get a guid id by registering @ http://abr.business.gov.au/Webservices.aspx
 * 
 */


class abnlookup extends SoapClient{

    private $guid = ""; 

	public function __construct($guid)
    {
		$this->guid = $guid;
		$params = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => true,
			'trace' => 1,
			'cache_wsdl' => WSDL_CACHE_NONE
		); 

		parent::__construct('http://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?WSDL', $params);
    }
	
	public function searchByAbn($abn, $historical = 'N'){
		$params = new stdClass();
		$params->searchString				= $abn;
		$params->includeHistoricalDetails	= $historical;
		$params->authenticationGuid			= $this->guid;
		return $this->ABRSearchByABN($params);
	}
}