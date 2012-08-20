<?php
/**
 * @package api
 * @subpackage filters.base
 * @abstract
 */
abstract class KalturaFlavorAssetBaseFilter extends KalturaAssetFilter
{
	private $map_between_objects = array
	(
		"flavorParamsIdEqual" => "_eq_flavor_params_id",
		"flavorParamsIdIn" => "_in_flavor_params_id",
		"statusEqual" => "_eq_status",
		"statusIn" => "_in_status",
		"statusNotIn" => "_notin_status",
	);

	private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), $this->map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), $this->order_by_map);
	}

	/**
	 * @var int
	 */
	public $flavorParamsIdEqual;

	/**
	 * @var string
	 */
	public $flavorParamsIdIn;

	/**
	 * @var KalturaFlavorAssetStatus
	 */
	public $statusEqual;

	/**
	 * @var string
	 */
	public $statusIn;

	/**
	 * @var string
	 */
	public $statusNotIn;
}
