<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin;

use FernleafSystems\Wordpress\Services;

/**
 * Class Files
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin
 */
class Files {

	use Base;

	/**
	 * Given a full root path on the file system for a file, locate the plugin to which this file belongs.
	 * @param string $sFullFilePath
	 * @return Services\Core\VOs\WpPluginVo|null
	 */
	public function findPluginFromFile( $sFullFilePath ) {
		$oThePlugin = null;

		$sFragment = $this->getPluginPathFragmentFromPath( $sFullFilePath );
		$oWpPlugins = Services\Services::WpPlugins();

		if ( !empty( $sFragment ) && strpos( $sFragment, '/' ) > 0 ) {
			list( $sThisPluginDir, $sPluginPathFragment ) = explode( '/', $sFragment, 2 );
			foreach ( $oWpPlugins->getInstalledPluginFiles() as $sPluginFile ) {
				if ( $sThisPluginDir == dirname( $sPluginFile ) ) {
					$oThePlugin = $oWpPlugins->getPluginAsVo( $sPluginFile );
					break;
				}
			}
		}
		return $oThePlugin;
	}

	/**
	 * Verifies the file exists on the SVN repository for the particular version that's installed.
	 * @param string $sFullFilePath
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function isValidFileFromPlugin( $sFullFilePath ) {

		$oThePlugin = $this->findPluginFromFile( $sFullFilePath );
		if ( !$oThePlugin instanceof Services\Core\VOs\WpPluginVo ) {
			throw new \InvalidArgumentException( 'Not actually a plugin file.', 1 );
		}
		if ( !$oThePlugin->isWpOrg() ) {
			throw new \InvalidArgumentException( 'Not a WordPress.org plugin.', 2 );
		}

		return ( new Repo() )
			->setWorkingSlug( $oThePlugin->slug )
			->existsInVcs( $this->getRelativeFilePathFromItsPluginDir( $sFullFilePath ) );
	}

	/**
	 * @param string $sFullFilePath
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function replaceFileFromVcs( $sFullFilePath ) {
		$sTmpFile = $this->getOriginalFileFromVcs( $sFullFilePath );
		return !empty( $sTmpFile ) && Services\Services::WpFs()->move( $sTmpFile, $sFullFilePath );
	}

	/**
	 * Verifies the file exists on the SVN repository for the particular version that's installed.
	 * @param string $sFullFilePath
	 * @return bool
	 * @throws \InvalidArgumentException - not actually a plugin file / not a WordPress.org plugin
	 */
	public function verifyFileContents( $sFullFilePath ) {
		$bVerified = false;
		if ( $this->isValidFileFromPlugin( $sFullFilePath ) ) {
			$sTmpFile = $this->getOriginalFileFromVcs( $sFullFilePath );
			if ( !empty( $sTmpFile ) ) {
				$bVerified = $this->getOriginalFileMd5FromVcs( $sFullFilePath ) === md5_file( $sFullFilePath );
			}
		}
		return $bVerified;
	}

	/**
	 * @param string $sFullFilePath
	 * @return string|null
	 * @throws \InvalidArgumentException
	 */
	public function getOriginalFileFromVcs( $sFullFilePath ) {
		$sTmpFile = null;
		if ( $this->isValidFileFromPlugin( $sFullFilePath ) ) {
			$oThePlugin = $this->findPluginFromFile( $sFullFilePath );
			$sTmpFile = ( new Repo() )
				->setWorkingSlug( $oThePlugin->slug )
				->downloadFromVcs( $this->getRelativeFilePathFromItsPluginDir( $sFullFilePath ) );
		}
		return $sTmpFile;
	}

	/**
	 * @param string $sFullFilePath
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getOriginalFileMd5FromVcs( $sFullFilePath ) {
		$sFile = $this->getOriginalFileFromVcs( $sFullFilePath );
		return empty( $sFile ) ? null : md5_file( $sFile );
	}

	/**
	 * @param string $sFile - can either be absolute, or relative to ABSPATH
	 * @return string|null - the path to the file relative to Plugins Dir.
	 */
	public function getPluginPathFragmentFromPath( $sFile ) {
		$sFragment = null;

		if ( !path_is_absolute( $sFile ) ) { // assume it's relative to ABSPATH
			$sFile = path_join( ABSPATH, $sFile );
		}
		$sFile = wp_normalize_path( $sFile );
		$sPluginsDir = wp_normalize_path( WP_PLUGIN_DIR );

		if ( strpos( $sFile, $sPluginsDir ) === 0 ) {
			$sFragment = ltrim( str_replace( $sPluginsDir, '', $sFile ), '/' );
		}

		return $sFragment;
	}

	/**
	 * Gets the path of the plugin file relative to its own home plugin dir. (not wp-content/plugins/)
	 * @param string $sFile
	 * @return string
	 */
	private function getRelativeFilePathFromItsPluginDir( $sFile ) {
		$sPluginsDirFragment = $this->getPluginPathFragmentFromPath( $sFile );
		list( $sThisPluginDir, $sPluginPathFragment ) = explode( '/', $sPluginsDirFragment, 2 );
		return $sPluginPathFragment;
	}
}