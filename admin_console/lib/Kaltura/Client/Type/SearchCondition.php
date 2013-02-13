<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_SearchCondition extends Kaltura_Client_Type_SearchItem
{
	public function getKalturaObjectType()
	{
		return 'KalturaSearchCondition';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		$this->field = (string)$xml->field;
		$this->value = (string)$xml->value;
	}
	/**
	 * 
	 *
	 * @var string
	 */
	public $field = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}
