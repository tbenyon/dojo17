<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Wp;

use FernleafSystems\Wordpress\Services;

/**
 * Class Files
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Wp
 */
class Files {

	/**
	 * @param string $sFilePath
	 * @return string|null
	 * @throws \InvalidArgumentException
	 */
	public function getOriginalFileFromVcs( $sFilePath ) {
		$sTmpFile = null;
		$oHashes = Services\Services::CoreFileHashes();
		if ( !$oHashes->isCoreFile( $sFilePath ) ) {
			throw new \InvalidArgumentException( 'File provided is not actually a core file.' );
		}
		return ( new Repo() )->downloadFromVcs( $oHashes->getFileFragment( $sFilePath ) );
	}

	/**
	 * @param string $sFilePath
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function replaceFileFromVcs( $sFilePath ) {
		$oHashes = Services\Services::CoreFileHashes();
		if ( !$oHashes->isCoreFile( $sFilePath ) ) {
			throw new \InvalidArgumentException( 'File provided is not actually a core file.' );
		}

		$sTmp = $this->getOriginalFileFromVcs( $sFilePath );
		return !empty( $sTmp )
			   && Services\Services::WpFs()->move( $sTmp, $oHashes->getAbsolutePathFromFragment( $sFilePath ) );
	}
}