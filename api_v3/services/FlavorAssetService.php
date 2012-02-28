<?php

/**
 * Retrieve information and invoke actions on Flavor Asset
 *
 * @service flavorAsset
 * @package api
 * @subpackage services
 */
class FlavorAssetService extends KalturaAssetService
{
	protected function kalturaNetworkAllowed($actionName)
	{
		if(
			$actionName == 'add' ||
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getByEntryId' ||
			$actionName == 'getUrl' ||
			$actionName == 'getDownloadUrl' ||
			$actionName == 'getWebPlayableByEntryId' ||
			$actionName == 'getFlavorAssetsWithParams' ||
			$actionName == 'convert' ||
			$actionName == 'reconvert' ||
			$actionName == 'setContent'
			)
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::kalturaNetworkAllowed($actionName);
	}
	
    /**
     * Add flavor asset
     *
     * @action add
     * @param string $entryId
     * @param KalturaFlavorAsset $flavorAsset
     * @return KalturaFlavorAsset
     * @throws KalturaErrors::ENTRY_ID_NOT_FOUND
     * @throws KalturaErrors::FLAVOR_ASSET_ALREADY_EXISTS
     */
    function addAction($entryId, KalturaFlavorAsset $flavorAsset)
    {
    	$dbEntry = entryPeer::retrieveByPK($entryId);
    	if(!$dbEntry || $dbEntry->getType() != KalturaEntryType::MEDIA_CLIP || !in_array($dbEntry->getMediaType(), array(KalturaMediaType::VIDEO, KalturaMediaType::AUDIO)))
    		throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
    	
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
    	if(!is_null($flavorAsset->flavorParamsId))
    	{
    		$dbFlavorAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $flavorAsset->flavorParamsId);
    		if($dbFlavorAsset)
    			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ALREADY_EXISTS, $dbFlavorAsset->getId(), $flavorAsset->flavorParamsId);
    	}
    	
    	$dbFlavorAsset = new flavorAsset();
    	$dbFlavorAsset = $flavorAsset->toInsertableObject($dbFlavorAsset);
    	
    	if(!is_null($flavorAsset->flavorParamsId))
    	{
    		$flavorParams = assetParamsPeer::retrieveByPK($flavorAsset->flavorParamsId);
    		if($flavorParams && $flavorParams->hasTag(flavorParams::TAG_SOURCE))
    			$dbFlavorAsset->setIsOriginal(true);
    	}
    	
		$dbFlavorAsset->setEntryId($entryId);
		$dbFlavorAsset->setPartnerId($dbEntry->getPartnerId());
		$dbFlavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$dbFlavorAsset->save();
    	
		$flavorAsset = new KalturaFlavorAsset();
		$flavorAsset->fromObject($dbFlavorAsset);
		return $flavorAsset;
    }
    
    /**
     * Update flavor asset
     *
     * @action update
     * @param string $id
     * @param KalturaFlavorAsset $flavorAsset
     * @return KalturaFlavorAsset
     * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
     */
    function updateAction($id, KalturaFlavorAsset $flavorAsset)
    {
   		$dbFlavorAsset = assetPeer::retrieveById($id);
   		if (!$dbFlavorAsset || !($dbFlavorAsset instanceof flavorAsset))
   			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbFlavorAsset->getentry();
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $dbFlavorAsset->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
    	$dbFlavorAsset = $flavorAsset->toUpdatableObject($dbFlavorAsset);
   		$dbFlavorAsset->save();
		
		$flavorAsset = new KalturaFlavorAsset();
		$flavorAsset->fromObject($dbFlavorAsset);
		return $flavorAsset;
    }
    
    /**
     * Update content of flavor asset
     *
     * @action setContent
     * @param string $id
     * @param KalturaContentResource $contentResource
     * @return KalturaFlavorAsset
     * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws KalturaErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws KalturaErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED 
     */
    function setContentAction($id, KalturaContentResource $contentResource)
    {
   		$dbFlavorAsset = assetPeer::retrieveById($id);
   		if (!$dbFlavorAsset || !($dbFlavorAsset instanceof flavorAsset))
   			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbFlavorAsset->getentry();
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $dbFlavorAsset->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
		$contentResource->validateEntry($dbFlavorAsset->getentry());
		$kContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbFlavorAsset, $kContentResource);
		$contentResource->entryHandled($dbFlavorAsset->getentry());
		kEventsManager::raiseEvent(new kObjectDataChangedEvent($dbFlavorAsset));
		
    	$newStatuses = array(
    		flavorAsset::FLAVOR_ASSET_STATUS_READY,
    		flavorAsset::FLAVOR_ASSET_STATUS_QUEUED,
    		flavorAsset::FLAVOR_ASSET_STATUS_TEMP,
    	);
    	
    	if(in_array($dbFlavorAsset->getStatus(), $newStatuses))
   			kEventsManager::raiseEvent(new kObjectAddedEvent($dbFlavorAsset));
   		
		$flavorAsset = new KalturaFlavorAsset();
		$flavorAsset->fromObject($dbFlavorAsset);
		return $flavorAsset;
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(flavorAsset $flavorAsset, $fullPath, $copyOnly = false)
	{
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		$flavorAsset->setFileExt($ext);
		$flavorAsset->setSize(filesize($fullPath));
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		
		try 
		{
			kFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		}
		catch (Exception $e) 
		{
        	if($flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_QUEUED || $flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
        	{
				$flavorAsset->setDescription($e->getMessage());
				$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
				$flavorAsset->save();
        	}												
			throw $e;
		}
		
        if($flavorAsset->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
			$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
			
		$flavorAsset->save();
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param string $url
	 * @param kImportJobData $importJobData
	 */
	protected function attachUrl(flavorAsset $flavorAsset, $url, kImportJobData $importJobData = null)
	{
		$flavorAsset->save();
		
		kJobsManager::addImportJob(null, $flavorAsset->getEntryId(), $this->getPartnerId(), $url, $flavorAsset, null, $importJobData);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param kUrlResource $contentResource
	 */
	protected function attachUrlResource(flavorAsset $flavorAsset, kUrlResource $contentResource)
	{
    	$this->attachUrl($flavorAsset, $contentResource->getUrl(), $contentResource->getImportJobData());
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param KalturaSearchResultsResource $contentResource
	 */
	protected function KalturaSearchResultsResource(flavorAsset $flavorAsset, KalturaSearchResultsResource $contentResource)
	{
    	$contentResource->validatePropertyNotNull('result');
     	$contentResource->result->validatePropertyNotNull("searchSource");
     	
		if ($contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_KALTURA ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_KALTURA_PARTNER ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_KALTURA_PARTNER_KSHOW ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_KALTURA_KSHOW ||
			$contentResource->result->searchSource == entry::ENTRY_MEDIA_SOURCE_KALTURA_USER_CLIPS)
		{
			$srcFlavorAsset = assetPeer::retrieveOriginalByEntryId($contentResource->result->id); 
			$this->attachAsset($flavorAsset, $srcFlavorAsset);
		}
		else
		{
			$this->attachUrl($flavorAsset, $contentResource->result->url);
		}
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param kLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(flavorAsset $flavorAsset, kLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($flavorAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
//		I'm not sure about it...
//				
//		$lowerStatuses = array(
//			entryStatus::ERROR_CONVERTING,
//			entryStatus::ERROR_IMPORTING,
//			entryStatus::PENDING,
//			entryStatus::NO_CONTENT,
//		);
//		
//		$entry = $flavorAsset->getentry();
//		if(in_array($entry->getStatus(), $lowerStatuses))
//		{
//			$entry->setStatus(entryStatus::IMPORT);
//			$entry->save();
//		}
    		
		$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
		$flavorAsset->save();
		
		$contentResource->attachCreatedObject($flavorAsset);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(flavorAsset $flavorAsset, FileSyncKey $srcSyncKey)
	{
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
        $newSyncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
        kFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
                
        $fileSync = kFileSyncUtils::getLocalFileSyncForKey($newSyncKey, false);
        $fileSync = kFileSyncUtils::resolve($fileSync);
        
        if($flavorAsset->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
			$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
		
		$flavorAsset->setSize($fileSync->getFileSize());
		$flavorAsset->save();
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param kFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(flavorAsset $flavorAsset, kFileSyncResource $contentResource)
	{
    	$syncable = kFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($flavorAsset, $srcSyncKey);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(flavorAsset $flavorAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();
		$flavorAsset->setFileExt($contentResource->getFileExt());
		$flavorAsset->incrementVersion();
		$flavorAsset->save();
		
        $syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = kFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
		
		if($flavorAsset->getIsOriginal())
			$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_QUEUED);
		else
			$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_READY);
			
		$flavorAsset->save();
		
		kBusinessPostConvertDL::handleConvertFinished(null, $flavorAsset);
    }
    
	/**
	 * @param flavorAsset $flavorAsset
	 * @param kContentResource $contentResource
	 * @throws KalturaErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws KalturaErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws KalturaErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(flavorAsset $flavorAsset, kContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'kUrlResource':
			case 'kSshUrlResource':
				return $this->attachUrlResource($flavorAsset, $contentResource);
				
			case 'kLocalFileResource':
				return $this->attachLocalFileResource($flavorAsset, $contentResource);
				
			case 'kFileSyncResource':
				return $this->attachFileSyncResource($flavorAsset, $contentResource);
				
			case 'kRemoteStorageResource':
			case 'kRemoteStorageResources':
				return $this->attachRemoteStorageResource($flavorAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				KalturaLog::err($msg);
				
				if($flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_QUEUED || $flavorAsset->getStatus() == flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE)
				{
					$flavorAsset->setDescription($msg);
					$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_ERROR);
					$flavorAsset->save();
				}
				
				throw new KalturaAPIException(KalturaErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
    
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'getFlavorAssetsWithParams') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}
	
	/**
	 * Get Flavor Asset by ID
	 * 
	 * @action get
	 * @param string $id
	 * @return KalturaFlavorAsset
	 */
	public function getAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		$flavorAsset = new KalturaFlavorAsset();
		$flavorAsset->fromObject($flavorAssetDb);
		return $flavorAsset;
	}
	
	/**
	 * Get Flavor Assets for Entry
	 * 
	 * @action getByEntryId
	 * @param string $entryId
	 * @return KalturaFlavorAssetArray
	 * @deprecated Use thumbAsset.list instead
	 */
	public function getByEntryIdAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$flavorAssetsDb = assetPeer::retrieveFlavorsByEntryId($entryId);
		$flavorAssets = KalturaFlavorAssetArray::fromDbArray($flavorAssetsDb);
		return $flavorAssets;
	}
	
	/**
	 * List Flavor Assets by filter and pager
	 * 
	 * @action list
	 * @param KalturaAssetFilter $filter
	 * @param KalturaFilterPager $pager
	 * @return KalturaFlavorAssetListResponse
	 */
	function listAction(KalturaAssetFilter $filter = null, KalturaFilterPager $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
	    
		if (!$filter)
			$filter = new KalturaAssetFilter();

		if (!$pager)
			$pager = new KalturaFilterPager();
			
		$flavorAssetFilter = new AssetFilter();
		
		$filter->toObject($flavorAssetFilter);

		$c = new Criteria();
		$flavorAssetFilter->attachToCriteria($c);
		
		$flavorTypes = KalturaPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::FLAVOR);
		$c->add(assetPeer::TYPE, $flavorTypes, Criteria::IN);
		
		$totalCount = assetPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = assetPeer::doSelect($c);
		
		$list = KalturaFlavorAssetArray::fromDbArray($dbList);
		$response = new KalturaFlavorAssetListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;    
	}
	
	/**
	 * Get web playable Flavor Assets for Entry
	 * 
	 * @action getWebPlayableByEntryId
	 * @param string $entryId
	 * @return KalturaFlavorAssetArray
	 */
	public function getWebPlayableByEntryIdAction($entryId)
	{
		// entry could be "display_in_search = 2" - in that case we want to pull it although KN is off in services.ct for this action
		$c = new Criteria();
		$c->addAnd(entryPeer::ID, $entryId);
		$criterionPartnerOrKn = $c->getNewCriterion(entryPeer::PARTNER_ID, $this->getPartnerId());
		$criterionPartnerOrKn->addOr($c->getNewCriterion(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_KALTURA_NETWORK));
		$c->addAnd($criterionPartnerOrKn);
		// there could only be one entry because the query is by primary key.
		// so using doSelectOne is safe.
		$dbEntry = entryPeer::doSelectOne($c);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		// if the entry does not belong to the partner but "display_in_search = 2"
		// we want to turn off the criteria over the assetPeer
		if($dbEntry->getPartnerId() != $this->getPartnerId() &&
		   $dbEntry->getDisplayInSearch() == mySearchUtils::DISPLAY_IN_SEARCH_KALTURA_NETWORK)
		{
			assetPeer::setUseCriteriaFilter(false);
		}
		$flavorAssetsDb = assetPeer::retrieveReadyWebByEntryId($entryId);
		if (count($flavorAssetsDb) == 0)
			throw new KalturaAPIException(KalturaErrors::NO_FLAVORS_FOUND);
		
		// re-set default criteria to avoid fetching "private" flavors in laetr queries.
		// this should be also set in baseService, but we better do it anyway.
		assetPeer::setUseCriteriaFilter(true);
			
		$flavorAssets = KalturaFlavorAssetArray::fromDbArray($flavorAssetsDb);
		
		return $flavorAssets;
	}
	
	/**
	 * Add and convert new Flavor Asset for Entry with specific Flavor Params
	 * 
	 * @action convert
	 * @param string $entryId
	 * @param int $flavorParamsId
	 */
	public function convertAction($entryId, $flavorParamsId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
			
		$this->checkIfUserAllowedToUpdateEntry($dbEntry);
		
		$flavorParamsDb = assetParamsPeer::retrieveByPK($flavorParamsId);
		assetParamsPeer::setUseCriteriaFilter(false);
		if (!$flavorParamsDb)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $flavorParamsId);
				
		$validStatuses = array(
			entryStatus::ERROR_CONVERTING,
			entryStatus::PRECONVERT,
			entryStatus::READY,
		);
		
		if (!in_array($dbEntry->getStatus(), $validStatuses))
			throw new KalturaAPIException(KalturaErrors::INVALID_ENTRY_STATUS);
			
		$originalFlavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
		if (is_null($originalFlavorAsset) || $originalFlavorAsset->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::ORIGINAL_FLAVOR_ASSET_IS_MISSING);
		
		$srcSyncKey = $originalFlavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		// if the file sync isn't local (wasn't synced yet) proxy request to other datacenter
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
		if(!$fileSync)
		{
			throw new KalturaAPIException(KalturaErrors::FILE_DOESNT_EXIST);
		}	
		else if(!$local)
		{
			kFile::dumpApiRequest(kDataCenterMgr::getRemoteDcExternalUrl($fileSync));
		}
		$err = "";
		
		$dynamicFlavorAttributes = $dbEntry->getDynamicFlavorAttributesForAssetParams($flavorParamsDb->getId());
		
		kBusinessPreConvertDL::decideAddEntryFlavor(null, $dbEntry->getId(), $flavorParamsId, $err, null, $dynamicFlavorAttributes);
	}
	
	/**
	 * Reconvert Flavor Asset by ID
	 * 
	 * @action reconvert
	 * @param string $id Flavor Asset ID
	 */
	public function reconvertAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		if ($flavorAssetDb->getIsOriginal())
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_RECONVERT_ORIGINAL);
			
		$flavorParamsId = $flavorAssetDb->getFlavorParamsId();
		$entryId = $flavorAssetDb->getEntryId();
		
		return $this->convertAction($entryId, $flavorParamsId);
	} 
	
	/**
	 * Delete Flavor Asset by ID
	 * 
	 * @action delete
	 * @param string $id
	 */
	public function deleteAction($id)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);
			
		$entry = $flavorAssetDb->getEntry();
		if (!$entry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $flavorAssetDb->getEntryId());
			
		$this->checkIfUserAllowedToUpdateEntry($entry);
		
		$flavorAssetDb->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_DELETED);
		$flavorAssetDb->setDeletedAt(time());
		$flavorAssetDb->save();
		
		$entry->removeFlavorParamsId($flavorAssetDb->getFlavorParamsId());
		$entry->save();
	}
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @return string
	 * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::FLAVOR_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::FLAVOR_ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_IS_NOT_READY);

		if($storageId)
			return $assetDb->getExternalUrl($storageId);

		//files grater then 1.8GB can't be downloaded from cdn.
		$flavorSizeKB = $assetDb->getSize();
		$useCdn = true;
		if ($flavorSizeKB > kConf::get("max_file_size_downloadable_from_cdn_in_KB"))
			$useCdn = false;
		return $assetDb->getDownloadUrl($useCdn);
	}
	
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return KalturaRemotePathListResponse
	 * @throws KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 * @throws KalturaErrors::FLAVOR_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::FLAVOR_ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_IS_NOT_READY);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::FLAVOR_ASSET);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$c->add(FileSyncPeer::OBJECT_ID, $id);
		$c->add(FileSyncPeer::VERSION, $assetDb->getVersion());
		$c->add(FileSyncPeer::PARTNER_ID, $assetDb->getPartnerId());
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$fileSyncs = FileSyncPeer::doSelect($c);
			
		$listResponse = new KalturaRemotePathListResponse();
		$listResponse->objects = KalturaRemotePathArray::fromFileSyncArray($fileSyncs);
		$listResponse->totalCount = count($listResponse->objects);
		return $listResponse;
	}
	
	/**
	 * Get download URL for the Flavor Asset
	 * 
	 * @action getDownloadUrl
	 * @param string $id
	 * @param bool $useCdn
	 * @return string
	 * @deprecated use getUrl instead
	 */
	public function getDownloadUrlAction($id, $useCdn = false)
	{
		$flavorAssetDb = assetPeer::retrieveById($id);
		if (!$flavorAssetDb || !($flavorAssetDb instanceof flavorAsset))
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_ID_NOT_FOUND, $id);

		if ($flavorAssetDb->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_ASSET_IS_NOT_READY);

		return $flavorAssetDb->getDownloadUrl($useCdn);
	}
	
	/**
	 * Get Flavor Asset with the relevant Flavor Params (Flavor Params can exist without Flavor Asset & vice versa)
	 * 
	 * @action getFlavorAssetsWithParams
	 * @param string $entryId
	 * @return KalturaFlavorAssetWithParamsArray
	 */
	public function getFlavorAssetsWithParamsAction($entryId)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new KalturaAPIException(KalturaErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		// get all the flavor params of partner 0 and the current partner (note that partner 0 is defined as partner group in service.ct)
		$c = new Criteria();
		$flavorTypes = KalturaPluginManager::getExtendedTypes(assetParamsPeer::OM_CLASS, assetType::FLAVOR);
		$c->add(assetParamsPeer::TYPE, $flavorTypes, Criteria::IN);
		
		$flavorParamsDb = assetParamsPeer::doSelect($c);
		
		// get the flavor assets for this entry
		$c = new Criteria();
		
		$flavorTypes = KalturaPluginManager::getExtendedTypes(assetPeer::OM_CLASS, assetType::FLAVOR);
		$c->add(assetPeer::TYPE, $flavorTypes, Criteria::IN);
		
		$c->add(assetPeer::ENTRY_ID, $entryId);
		$c->add(assetPeer::STATUS, array(flavorAsset::FLAVOR_ASSET_STATUS_DELETED, flavorAsset::FLAVOR_ASSET_STATUS_TEMP), Criteria::NOT_IN);
		$flavorAssetsDb = assetPeer::doSelect($c);
		
		// find what flavot params are required
		$requiredFlavorParams = array();
		foreach($flavorAssetsDb as $item)
			$requiredFlavorParams[$item->getFlavorParamsId()] = true;
		
		// now merge the results, first organize the flavor params in an array with the id as the key
		$flavorParamsArray = array();
		foreach($flavorParamsDb as $item)
		{
			$flavorParams = $item->getId();
			$flavorParamsArray[$flavorParams] = $item;
			
			if(isset($requiredFlavorParams[$flavorParams]))
				unset($requiredFlavorParams[$flavorParams]);
		}

		// adding missing required flavors params to the list
		if(count($requiredFlavorParams))
		{
			$flavorParamsDb = assetParamsPeer::retrieveByPKsNoFilter(array_keys($requiredFlavorParams));
			foreach($flavorParamsDb as $item)
				$flavorParamsArray[$item->getId()] = $item;
		}
		
		$usedFlavorParams = array();
		
		// loop over the flavor assets and add them, if it has flavor params add them too
		$flavorAssetWithParamsArray = new KalturaFlavorAssetWithParamsArray();
		foreach($flavorAssetsDb as $flavorAssetDb)
		{
			$flavorParamsId = $flavorAssetDb->getFlavorParamsId();
			$flavorAssetWithParams = new KalturaFlavorAssetWithParams();
			$flavorAssetWithParams->entryId = $entryId;
			$flavorAsset = new KalturaFlavorAsset();
			$flavorAsset->fromObject($flavorAssetDb);
			$flavorAssetWithParams->flavorAsset = $flavorAsset;
			if (isset($flavorParamsArray[$flavorParamsId]))
			{
				$flavorParamsDb = $flavorParamsArray[$flavorParamsId];
				$flavorParams = KalturaFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
				$flavorParams->fromObject($flavorParamsDb);
				$flavorAssetWithParams->flavorParams = $flavorParams;

				// we want to log which flavor params are in use, there could be more
				// than one flavor asset using same params
				$usedFlavorParams[$flavorParamsId] = $flavorParamsId;
			}
//			else if ($flavorAssetDb->getIsOriginal())
//			{
//				// create a dummy flavor params
//				$flavorParams = new KalturaFlavorParams();
//				$flavorParams->name = "Original source";
//				$flavorAssetWithParams->flavorParams = $flavorParams;
//			}
			
			$flavorAssetWithParamsArray[] = $flavorAssetWithParams;
		}
		
		// copy the remaining params
		foreach($flavorParamsArray as $flavorParamsId => $flavorParamsDb)
		{
			if(isset($usedFlavorParams[$flavorParamsId]))
			{
				// flavor params already exists for a flavor asset, not need
				// to list it one more time
				continue;
			}
			$flavorParams = KalturaFlavorParamsFactory::getFlavorParamsInstance($flavorParamsDb->getType());
			$flavorParams->fromObject($flavorParamsDb);
			
			$flavorAssetWithParams = new KalturaFlavorAssetWithParams();
			$flavorAssetWithParams->entryId = $entryId;
			$flavorAssetWithParams->flavorParams = $flavorParams;
			$flavorAssetWithParamsArray[] = $flavorAssetWithParams;
		}
		
		return $flavorAssetWithParamsArray;
	}
	
	
	/**
	 * @action export
	 * 
	 * Action for manually exporting an asset
	 * @param string $assetId
	 * @param int $storageProfileId
	 * @throws KalturaErrors::INVALID_FLAVOR_ASSET_ID
	 * @throws KalturaErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws KalturaErrors::INTERNAL_SERVERL_ERROR
	 * @return KalturaFlavorAsset The exported asset
	 */
	public function exportAction ( $assetId , $storageProfileId )
	{
	    return parent::exportAction($assetId, $storageProfileId);
	}
	
}