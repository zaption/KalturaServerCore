<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.enum
 */
class KalturaSystemPartnerLimitType extends KalturaStringEnum
{
	const ENTRIES = 'ENTRIES';
	const MONTHLY_STREAM_ENTRIES = 'MONTHLY_STREAM_ENTRIES';
	const MONTHLY_BANDWIDTH = 'MONTHLY_BANDWIDTH';
	const PUBLISHERS = 'PUBLISHERS';
	const ADMIN_LOGIN_USERS = 'ADMIN_LOGIN_USERS';
	const LOGIN_USERS = 'LOGIN_USERS';
	const USER_LOGIN_ATTEMPTS = 'USER_LOGIN_ATTEMPTS';
	const BULK_SIZE = 'BULK_SIZE';
	const MONTHLY_STORAGE = 'MONTHLY_STORAGE';
	const MONTHLY_STORAGE_AND_BANDWIDTH = 'MONTHLY_STORAGE_AND_BANDWIDTH';
	const END_USERS = 'END_USERS';	
	const ACCESS_CONTROLS = 'ACCESS_CONTROLS';	
}