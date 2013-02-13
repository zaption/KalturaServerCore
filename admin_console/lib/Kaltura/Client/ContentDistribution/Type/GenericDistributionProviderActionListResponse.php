<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ContentDistribution_Type_GenericDistributionProviderActionListResponse extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaGenericDistributionProviderActionListResponse';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		if(empty($xml->objects))
			$this->objects = array();
		else
			$this->objects = Kaltura_Client_Client::unmarshalItem($xml->objects);
		if(count($xml->totalCount))
			$this->totalCount = (int)$xml->totalCount;
	}
	/**
	 * 
	 *
	 * @var array of KalturaGenericDistributionProviderAction
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}
