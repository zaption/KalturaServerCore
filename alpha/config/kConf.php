<?php
setlocale(LC_ALL, 'en_US.UTF-8');

require_once(dirname(__file__).'/kConfLocal.php');

class kConf extends kConfLocal
{
	// self::$map is in kConfLocal
	
	private static function init()
	{
		if (self::$map) return;
		
		self::$map = array();		
		kConf::addConfig();
		kConfLocal::addConfig();
	}

	
	
	protected static function addConfig()
	{
		self::$map = array_merge(
			self::$map,
			array (
                 // take over the symfony config (sfConfig)
 			    "sf_debug" => false,
				"sf_logging_enabled" => true,
				"sf_root_dir" => dirname(__FILE__).'/../',

				"delivery_block_countries" => "", // comma separated
				
				"enable_cache" => true,
			
				// params that should be ignored from cache. can be used for tweaking
				// production environment hit by unexpected random parameters
				"v3cache_ignore_params" => array(),
			
				// list of partner ids for which we don't remove the contextDataParams:referrer parameter from
				// the cache key. should be used for partners using access control with only site restriction.
				"v3cache_include_referrer_in_key" => array(),

		     	//xslt_enabled_php_functions.can be used in as a parameter in registerPHPFunctions
			    "xslt_enabled_php_functions" =>array(
			       	 'date', 'gmdate'),
			
				// actions that can be cached although an admin ks is used
				// due to bad integration by the partner
				"v3cache_ignore_admin_ks" => array(),
							
				"terms_of_use_uri" => "index.php/terms",
			
				"server_api_v2_path" => "/api/" ,
						

				"default_duplication_time_frame" => 60 ,								
				"job_duplication_time_frame" => array(
					1 => 7200, //BatchJobType::IMPORT
				) ,
			
				"default_job_execution_attempt" => 3 ,
				"job_execution_attempt" => array(
					16 => 5, //BatchJobType::NOTIFICATION
					4 => 3, //BatchJobType::BULK_UPLOAD
					23 => 2, //BatchJobType::STORAGE_EXPORT
					28 => 10, //BatchJobType::METADATA_TRANSFORM
				) ,
			
				"default_job_retry_interval" => 60 ,
				"job_retry_intervals" => array(
					16 => 600, // BatchJobType::NOTIFICATION
					15 => 150, // BatchJobType::MAIL
					1 => 300, // BatchJobType::IMPORT
					23 => 300, // BatchJobType::STORAGE_EXPORT
					4 => 180, // BatchJobType::BULKUPLOAD
					10 => 1800, // BatchJobType::CONVERT_PROFILE
					29 => 300, // BatchJobType::FILESYNC_IMPORT
				) ,
				
				"ignore_cdl_failure" => false,
							
				"batch_ignore_duplication" => true ,
				"priority_percent" => array(1 => 33, 2 => 27, 3 => 20, 4 => 13, 5 => 7),
				"priority_time_range" => 600,
		
				"system_allow_edit_kConf" => false,
				"testmeconsole_state" => true,
				
				"flash_root_url" => "",
				"uiconf_root_url" => "",
				"content_root_url" => "",
			
						
				
			
				/* kmc tabs rules */
				
				"kmc_display_account_tab" => true,
				"kmc_display_customize_tab" => true, // DONT REMOVE  REQUIRED FOR KMC1 
				"kmc_content_enable_commercial_transcoding" => true, 
				"kmc_content_enable_live_streaming" => true,
				"kmc_login_show_signup_link" => false,
				"kmc_display_developer_tab" => false,
				"kmc_display_server_tab" => false,
				"kmc_account_show_usage" => true,			

			/* kmc applications versions */
				"kmc_content_version" => 'v3.2.12.2',
				"kmc_account_version" => 'v3.1.3',
				"kmc_appstudio_version" => 'v2.2.3',
				"kmc_rna_version" => 'v1.1.8.4',
				"kmc_dashboard_version" => 'v1.0.14.2',
				"kmc_login_version" => 'v1.1.11.1',
				"kcw_flex_wrapper_version" => 'v1.2',
				"editors_flex_wrapper_version" => 'v1.01',
				"kdp_wrapper_version" => 'v11.0',
				"kdp3_wrapper_version" => 'v34.0',
				"html5_version" => 'v1.6.10.2',
				"clipapp_version" => 'v1.0.5',
				"kmc_secured_login" => false,
				
				"kmc_version" => 'v4.2.22',
				"new_partner_kmc_version" => 4,
				
				"paypal_data" => array (),
				
				"limelight_madiavault_password" => "",
				"level3_authentication_key" => "",
				"akamai_auth_smooth_param" => "",
				"akamai_auth_smooth_salt" => "",
				"akamai_auth_smooth_seconds" => 300,
				
				"marketo_access_key" => "", 
				"marketo_secret_key" => "",
							
				'kdpwrapper_track_url' => "",
				"kaltura_partner_id" => "",
				
				
				"template_partner_id" => 99,
				
				"url_managers" => array(), /* should be filled up if installations supports adding CDNs */
                                
		
				"kaltura_email_hash" => "admin",
				
				"default_live_stream_entry_source_type" => "EntrySourceType::AKAMAI_LIVE",
                                
				"default_plugins" => array(
					"MetadataPlugin" => "MetadataPlugin", // Should always be enabled
					"DocumentPlugin" => "DocumentPlugin", // Should be enabled for document entries
					"SphinxSearchPlugin" => "SphinxSearchPlugin", // Should always be enabled
				),
				
				"event_consumers" => array(
                	"kFlowManager",
                	"kStorageExporter",
                    "kObjectCopyHandler",
                    "kObjectDeleteHandler",
					"kPermissionManager",
                ),
                "event_consumers_default_priority" => 5,
				"event_consumers_priorities" => array(
					'kVirusScanFlowManager' => 7,
                ),
		"search_indexes" => array(
			'entry' => 10,
                ),
		
                
				"cache_root_path" => dirname(__FILE__).'/../../cache/',
				"general_cache_dir" => dirname(__FILE__).'/../../cache/general',
                'response_cache_dir' => dirname(__FILE__).'/../../cache/response/',
                
                // should be overidden in kConfLocal with shared storage folder as a unified cache path (used by getFeed service)
                "global_cache_dir" => dirname(__FILE__).'/../../cache/', 
                
                'query_cache_enabled' => false,
				"query_cache_invalidate_on_change" => false,
                
                'apc_cache_ttl' => 900, // 15 minutes in seconds - ttl for apc cache values
                
				"exec_sphinx" => true, // Should be set to false in multiple data centers environments
                
                'user_login_set_password_hash_key_validity' => 60*60*24, /* 24 hours */
                'user_login_max_wrong_attempts' => 5000,
                'user_login_block_period' => 0,
                'user_login_num_prev_passwords_to_keep' => 0,
                'user_login_password_replace_freq' => 60*60*24*5000, /* 5000 days */
                'user_login_password_structure' => array(
					'/^.{8,14}$/',
					'/[0-9]+/',
					'/[a-z]+/',
					'/[~!@#$%^*=+?\(\)\-\[\]\{\}]+/',
					'/^[^<>]*$/',
				),
				
				'disable_url_hashing' => 'true',
				'report_partner_registration' => false, // whether to report partner registration
				
				"usage_tracking_url" => "http://corp.kaltura.com/index.php/events/usage_tracking",
				
				"no_save_of_last_login_partner_for_partner_ids" => array(0, -1, -2, 99),

				"temp_folder" => '/opt/kaltura/tmp',
				
				"max_file_size_downloadable_from_cdn_in_KB" => 1.8 * 1024 * 1024, // files grater then 1.8GB can't be downloaded from cdn
				
				'ps2_actions_not_blocked_by_permissions' => array(
					// should list action class names lowercase!
					'contactsalesforceaction',
					'mymultirequest',
					'adminloginaction',
					'resetadminpasswordaction',
					'executeplaylistaction',
					'reporterroraction',
					'searchautodataaction',
					'addentryaction',
					'searchmediainfoaction',
					'checknotificationsaction',
					'getdataentryaction',
					'getentryaction',
					'getkshowaction',
					'getallentriesaction',
					'updatedataentryaction',
					'getentriesaction',
					'listmyentriesaction',
					'getallentriesaction',
					'getmetadataaction',
					'setmetadataaction',
					'setroughcutnameaction',
					'getrelatedkshowsaction',
					'setentrythumbnailaction',
					'collectstatsaction',
					'reporterroraction',
					'addentryaction',
					'getuiconfaction',
					'uploadjpegaction',
					'getentryaction',
					'getkshowaction',
					'registerpartneraction',
				),
				
				'syndication_core_xsd_path' => dirname(__FILE__) . '/syndication.core.xsd',
				
				'default_streamer_type' => 'http',
				'default_media_protocol' => 'http',
				
				'fields_with_priorities_in_sphinx' => array('PARTNER_ID' => 1),
				
				'video_file_ext' => array('flv','asf','qt','mov','mpg','mpeg','avi','wmv','mp4','m4v','3gp','vob','f4v','mkv','mxf','mts'),
				'image_file_ext' => array('jpg','jpeg','bmp','png','gif','tif','tiff'),
				'audio_file_ext' => array('flv','asf','wmv','qt','mov','mpg','avi','mp3','wav','mp4','wma','3gp','vob','amr'),

				'remote_addr_whitelisted_hosts' => array(),
			)
		);
		
	}
	
	public static function get ( $param_name )
	{
		self::init();
		$res = self::$map [ $param_name ];
		// for now - throw an exception if now param in config - it will help prevent typos 
		if ( $res === null ) throw new Exception ( "Cannot find [$param_name] in config" ) ;
		// KalturaLog::log( "kConf [$param_name]=[$res]" );
		return $res; 
	}
	
	public static function hasParam($param_name)
	{
		self::init();
		return array_key_exists($param_name, self::$map);
	}

	public static function getDB()
	{
		return parent::getDB();
	}
}

