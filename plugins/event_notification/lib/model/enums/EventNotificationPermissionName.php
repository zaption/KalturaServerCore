<?php
/**
 * @package plugins.eventNotification
 * @subpackage model.enum
 */ 
class EventNotificationPermissionName implements IKalturaPluginEnum, PermissionName
{
	const SYSTEM_ADMIN_EVENT_NOTIFICATION_BASE = 'SYSTEM_ADMIN_EVENT_NOTIFICATION_BASE';
	const SYSTEM_ADMIN_EVENT_NOTIFICATION_MODIFY = 'SYSTEM_ADMIN_EVENT_NOTIFICATION_MODIFY';
	const EVENT_NOTIFICATION_DISPATCH = 'EVENT_NOTIFICATION_DISPATCH';
	const EVENT_NOTIFICATION_BASE = 'EVENT_NOTIFICATION_BASE';
	const FEATURE_EVENT_NOTIFICATION = 'FEATURE_EVENT_NOTIFICATION';
	
	public static function getAdditionalValues()
	{
		return array
		(
			'SYSTEM_ADMIN_EVENT_NOTIFICATION_BASE' => self::SYSTEM_ADMIN_EVENT_NOTIFICATION_BASE,
			'SYSTEM_ADMIN_EVENT_NOTIFICATION_MODIFY' => self::SYSTEM_ADMIN_EVENT_NOTIFICATION_MODIFY,
			'EVENT_NOTIFICATION_DISPATCH' => self::EVENT_NOTIFICATION_DISPATCH,
			'EVENT_NOTIFICATION_BASE' => self::EVENT_NOTIFICATION_BASE,
			'FEATURE_EVENT_NOTIFICATION' => self::FEATURE_EVENT_NOTIFICATION,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
