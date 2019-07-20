<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin;

use FernleafSystems\Wordpress\Services\Utilities\HttpUtil;

class Download {

	use Base;

	/**
	 * @return string|null
	 * @throws \Exception
	 */
	public function latest() {
		try {
			$sUrl = ( new Api() )
				->setWorkingSlug( $this->getWorkingSlug() )
				->getPluginInfo()
				->download_link;
			$sTmpFile = ( new HttpUtil() )->downloadUrl( $sUrl );
		}
		catch ( \Exception $oE ) {
			$sTmpFile = null;
		}
		return $sTmpFile;
	}

	/**
	 * @param string $sVersion
	 * @return string
	 * @throws \Exception
	 */
	public function version( $sVersion ) {
		$sTmpFile = null;
		try {
			$aVersions = ( new Api() )
				->setWorkingSlug( $this->getWorkingSlug() )
				->getPluginInfo()
				->versions;
			if ( !empty( $aVersions[ $sVersion ] ) ) {
				$sTmpFile = ( new HttpUtil() )->downloadUrl( $aVersions[ $sVersion ] );
			}
		}
		catch ( \Exception $oE ) {
		}
		return $sTmpFile;
	}
}