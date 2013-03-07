<?php
/**
 * @package    Core
 * @subpackage KMC
 */
require_once ( "kalturaAction.class.php" );

/**
 * @package Core
 * @subpackage KMC
 */
class previewAction extends kalturaAction
{
	public function execute ( ) 
	{
		// Prevent the page fron being embeded in an iframe
		header( 'X-Frame-Options: SAMEORIGIN' );
		
		$this->uiconf_id = intval($this->getRequestParameter('uiconf_id'));
		if(!$this->uiconf_id)
			KExternalErrors::dieError(KExternalErrors::MISSING_PARAMETER, 'uiconf_id');

		$this->uiConf = uiConfPeer::retrieveByPK($this->uiconf_id);
		if(!$this->uiConf)
			KExternalErrors::dieError(KExternalErrors::UI_CONF_NOT_FOUND);

		$this->partner_id = intval($this->getRequestParameter('partner_id', $this->uiConf->getPartnerId()));
		if(!$this->partner_id)
			KExternalErrors::dieError(KExternalErrors::MISSING_PARAMETER, 'partner_id');

		// Single Player parameters
		$this->entry_id = htmlspecialchars($this->getRequestParameter('entry_id'));
		if( $this->entry_id ) {
			$entry = entryPeer::retrieveByPK($this->entry_id);
			if( $entry ) {
				$this->entry_name = $entry->getName();
				$this->entry_description = $entry->getDescription();
				$this->entry_thumbnail_url = $entry->getThumbnailUrl();
				$this->entry_duration = $entry->getDuration();

				$flavor_tag = $this->getRequestParameter('flavor_tag', 'iphone');
				$flavor_assets = assetPeer::retrieveReadyFlavorsByEntryIdAndTag($this->entry_id, $flavor_tag);
				$flavor_asset = reset($flavor_assets);
				/* @var $flavor_asset flavorAsset */
				$this->flavor_asset_id = null;
				if( $flavor_asset ) {
					$this->flavor_asset_id = $flavor_asset->getId();
				}
			} else {
				$this->entry_id = null;
			}
		}

		$playlist_name = null;
		$embed_host = (kConf::hasParam('cdn_api_host')) ? kConf::get('cdn_api_host') : kConf::get('www_host');
		$embed_host_https = (kConf::hasParam('cdn_api_host_https')) ? kConf::get('cdn_api_host_https') : kConf::get('www_host');

		// Check if HTTPS enabled and set protocol
		$https_enabled = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
		$protocol = ($https_enabled) ? 'https' : 'http';
		$this->framed = (isset($_GET['framed'])) ? true : false;
		$this->embedType = ($this->getRequestParameter('embed')) ? $this->getRequestParameter('embed') : 'legacy';

		$cacheSt = (time()+(60*15));

		// Basic embed options
		$embedParams = array(
			'host'					=>	$embed_host,
			'securedHost'			=>	$embed_host_https,
			'partnerId' 			=>	$this->partner_id,
			'protocol'				=>	$protocol,
			'embedType'				=>	$this->embedType,
			'uiConfId'				=>	$this->uiconf_id,
			'width'					=>	$this->uiConf->getWidth(),
			'height'				=>	$this->uiConf->getHeight(),
			'includeKalturaLinks'	=>	true,
			'cacheSt'				=>	$cacheSt,
		);

		// Add entry Id and Metadata
		if( $this->entry_id ) {
			$embedParams['entryId'] = $this->entry_id;
			$embedParams['includeSeoMetadata'] = true;
			$embedParams['entryMeta'] = array(
				'name'	=> $this->entry_name,
				'description'	=>	$this->entry_description,
				'thumbnailUrl'	=>	$this->entry_thumbnail_url,
				'duration'	=>	$this->entry_duration
			);
		}

		// Add flashVars
		if( isset($_GET['flashvars']) ) {
			$embedParams['flashVars'] = $_GET['flashvars'];
			//Check for playlist name
			if( isset($_GET['flashvars']['kpl0Name']) ) {
				$playlist_name = htmlspecialchars($_GET['flashvars']['kpl0Name']);
			}
		}

		// Export embedParams to our view
		$this->embedParams = $embedParams;

		// Build SWF Path
		$swfPath = "/index.php/kwidget";
		$swfPath .= "/cache_st/" . $cacheSt;
		$swfPath .= "/wid/_" . $this->partner_id;
		$swfPath .= "/uiconf_id/" . $this->uiconf_id;
		if( $this->entry_id ) {
			$swfPath .= "/entry_id/" . $this->entry_id;
		}
		// Set SWF URLs
		$this->swfUrl = 'http://' . $embed_host . $swfPath;
		$this->swfSecureUrl = 'https://' . $embed_host_https . $swfPath;

		// URL to this page
		$port = ($_SERVER["SERVER_PORT"] != "80") ? ":".$_SERVER["SERVER_PORT"] : '';
		$this->pageURL = $protocol . '://' . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];

		// Set flavor Url
		if( isset($this->flavor_asset_id) ) {
			$this->flavorUrl = 'https://' . $embed_host_https . '/p/'. $this->partner_id .'/sp/' . $this->partner_id . '00/playManifest/entryId/' . $this->entry_id . '/flavorId/' . $this->flavor_asset_id . '/format/url/protocol/' . $protocol . '/a.mp4';
		}

		// Set Page name
		if(!$this->entry_id) {
			$this->entry_name = ($playlist_name) ? $playlist_name : 'Kaltura Player';
			$this->entry_description = '';
		}

	}
}
