<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin;

/**
 * Class Base
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin
 */
trait Base {

	/**
	 * @var string
	 */
	private $sWorkingPluginSlug;

	/**
	 * @return string
	 */
	public function getWorkingSlug() {
		return $this->sWorkingPluginSlug;
	}

	/**
	 * @param string $sSlug
	 * @return $this
	 */
	public function setWorkingSlug( $sSlug ) {
		$this->sWorkingPluginSlug = $sSlug;
		return $this;
	}
}