<?php
/**
 * @package Core
 * @subpackage errors
 */
class kUserException extends kCoreException
{
	const LOGIN_DATA_NOT_FOUND = 'LOGIN_DATA_NOT_FOUND';
	
	const WRONG_PASSWORD = 'WRONG_PASSWORD';
	
	const PASSWORD_STRUCTURE_INVALID = 'PASSWORD_STRUCTURE_INVALID';
	
	const PASSWORD_ALREADY_USED = 'PASSWORD_ALREADY_USED';
	
	const NEW_PASSWORD_HASH_KEY_INVALID = 'NEW_PASSWORD_HASH_KEY_INVALID';
	
	const NEW_PASSWORD_HASH_KEY_EXPIRED = 'NEW_PASSWORD_HASH_KEY_EXPIRED';
	
	const LOGIN_RETRIES_EXCEEDED = 'LOGIN_RETRIES_EXCEEDED';
	
	const LOGIN_BLOCKED = 'LOGIN_BLOCKED';
	
	const PASSWORD_EXPIRED = 'PASSWORD_EXPIRED';
	
	const USER_NOT_FOUND = 'USER_NOT_FOUND';
	
	const USER_IS_BLOCKED = 'USER_IS_BLOCKED';
	
	const ADMIN_LOGIN_USERS_QUOTA_EXCEEDED = 'ADMIN_LOGIN_USERS_QUOTA_EXCEEDED';
	
	const INVALID_EMAIL = 'INVALID_EMAIL';
	
	const INVALID_PARTNER = 'INVALID_PARTNER';

	const USER_ALREADY_EXISTS = 'USER_ALREADY_EXISTS';
	
	const CANNOT_UPDATE_LOGIN_DATA = 'CANNOT_UPDATE_LOGIN_EMAIL';
	
	const USER_ID_MISSING = 'USER_ID_MISSING';
	
	const LOGIN_ID_ALREADY_USED = 'LOGIN_ID_ALREADY_USED';
	
	const USER_LOGIN_ALREADY_ENABLED = 'USER_LOGIN_ALREADY_ENABLED';
	
	const USER_LOGIN_ALREADY_DISABLED = 'USER_LOGIN_ALREADY_DISABLED';
	
	const CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER = 'CANNOT_DELETE_OR_BLOCK_ROOT_ADMIN_USER';
	
	const CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER = 'CANNOT_DISABLE_LOGIN_FOR_ADMIN_USER';

}