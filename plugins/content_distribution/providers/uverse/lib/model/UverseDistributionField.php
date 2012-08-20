<?php
/**
 * @package plugins.uverseDistribution
 * @subpackage model.enum
 */ 
interface UverseDistributionField extends BaseEnum
{
	//item fields	
	const ITEM_GUID							= 'ITEM_GUID';
	const ITEM_TITLE						= 'ITEM_TITLE';
	const ITEM_LINK							= 'ITEM_LINK';
	const ITEM_DESCRIPTION					= 'ITEM_DESCRIPTION';
	const ITEM_MEDIA_RATING					= 'ITEM_MEDIA_RATING';
	const ITEM_MEDIA_CATEGORY				= 'ITEM_MEDIA_CATEGORY';
	const ITEM_PUB_DATE						= 'ITEM_PUB_DATE';
	const ITEM_EXPIRATION_DATE				= 'ITEM_EXPIRATION_DATE';
	const ITEM_MEDIA_KEYWORDS				= 'ITEM_MEDIA_KEYWORDS';
	const ITEM_LIVE_ORIGINAL_RELEASE_DATE	= 'ITEM_LIVE_ORIGINAL_RELEASE_DATE';
	const ITEM_MEDIA_TITLE					= 'ITEM_MEDIA_TITLE';
	const ITEM_MEDIA_DESCRIPTION			= 'ITEM_MEDIA_DESCRIPTION';
	const ITEM_MEDIA_COPYRIGHT				= 'ITEM_MEDIA_COPYRIGHT';
	const ITEM_MEDIA_COPYRIGHT_URL			= 'ITEM_MEDIA_COPYRIGHT_URL';
	//attributes
	const ITEM_THUMBNAIL_CREDIT				= 'ITEM_THUMBNAIL_CREDIT';
	const ITEM_CONTENT_LANG					= 'ITEM_CONTENT_LANG';
}