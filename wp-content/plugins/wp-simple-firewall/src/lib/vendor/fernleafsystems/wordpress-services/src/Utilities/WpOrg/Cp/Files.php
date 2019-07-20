<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Cp;

use FernleafSystems\Wordpress\Services;

/**
 * Class Files
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Cp
 */
class Files extends Services\Utilities\WpOrg\Wp\Files {

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
}