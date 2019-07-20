<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Base;

use FernleafSystems\Wordpress\Services;

/**
 * Class RepoBase
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Base
 */
abstract class RepoBase {

	/**
	 * @var string[]
	 */
	private $aDownloadedFiles;

	public function __construct() {
		$this->autoDelete();
	}

	/**
	 * Must be setup when the object is instantiated ie constructor
	 */
	protected function autoDelete() {
		add_action( 'shutdown', function () {
			$oFs = Services\Services::WpFs();
			foreach ( $this->getDownloadedFiles() as $sFile ) {
				if ( $oFs->exists( $sFile ) ) {
					$oFs->deleteFile( $sFile );
				}
			}
		} );
	}

	/**
	 * @param string $sFileFragment
	 * @param string $sVersion
	 * @param bool   $bUseSiteLocale
	 * @return string|null
	 */
	public function downloadFromVcs( $sFileFragment, $sVersion = null, $bUseSiteLocale = true ) {
		$sUrl = $this->getVcsUrlForFileAndVersion( $sFileFragment, $sVersion, $bUseSiteLocale );
		try {
			$sTmpFile = ( new Services\Utilities\HttpUtil() )
				->checkUrl( $sUrl )
				->downloadUrl( $sUrl );
			$this->addToDownloadedFiles( $sTmpFile );
		}
		catch ( \Exception $oE ) {
			$sTmpFile = null;
		}
		return $sTmpFile;
	}

	/**
	 * @param string $sFileFragment - path relative to the root dir of the object being tested. E.g. ABSPATH for
	 *                              WordPress or the plugin dir if it's a plugin.
	 * @param string $sVersion      - leave empty to use the current version
	 * @param bool   $bUseSiteLocale
	 * @return bool
	 */
	public function existsInVcs( $sFileFragment, $sVersion = null, $bUseSiteLocale = true ) {
		$sUrl = $this->getVcsUrlForFileAndVersion( $sFileFragment, $sVersion, $bUseSiteLocale );
		try {
			( new Services\Utilities\HttpUtil() )->checkUrl( $sUrl );
			$bExists = true;
		}
		catch ( \Exception $oE ) {
			$bExists = false;
		}
		return $bExists;
	}

	/**
	 * @param string $sFileFragment
	 * @param string $sVersion
	 * @param bool   $bUseSiteLocale
	 * @return string
	 */
	abstract protected function getVcsUrlForFileAndVersion( $sFileFragment, $sVersion, $bUseSiteLocale = true );

	/**
	 * @param string $sFile
	 * @return $this
	 */
	private function addToDownloadedFiles( $sFile ) {
		$aFiles = $this->getDownloadedFiles();
		$aFiles[] = $sFile;
		$this->aDownloadedFiles = $aFiles;
		return $this;
	}

	/**
	 * @return string[]
	 */
	private function getDownloadedFiles() {
		if ( !is_array( $this->aDownloadedFiles ) ) {
			$this->aDownloadedFiles = [];
		}
		return array_filter( $this->aDownloadedFiles );
	}
}