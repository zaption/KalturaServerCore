<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ExampleDistribution_Type_ExampleDistributionProviderFilter extends Kaltura_Client_ExampleDistribution_Type_ExampleDistributionProviderBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaExampleDistributionProviderFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}
