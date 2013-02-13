<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_TvComDistribution_Type_TVComDistributionProviderFilter extends Kaltura_Client_TvComDistribution_Type_TVComDistributionProviderBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaTVComDistributionProviderFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}
