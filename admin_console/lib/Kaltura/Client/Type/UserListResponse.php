<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_UserListResponse extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaUserListResponse';
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
	 * @var array of KalturaUser
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
