<?php

namespace FernleafSystems\Wordpress\Services\Core;

use FernleafSystems\Wordpress\Services\Services;

/**
 * Class Fs
 * @package FernleafSystems\Wordpress\Services\Core
 */
class Fs {

	/**
	 * @var \WP_Filesystem_Base
	 */
	protected $oWpfs = null;

	/**
	 * @param string $sBase
	 * @param string $sPath
	 * @return string
	 */
	public function pathJoin( $sBase, $sPath ) {
		return rtrim( $sBase, DIRECTORY_SEPARATOR ).DIRECTORY_SEPARATOR.ltrim( $sPath, DIRECTORY_SEPARATOR );
	}

	/**
	 * @param $sFilePath
	 * @return boolean|null    true/false whether file/directory exists
	 */
	public function exists( $sFilePath ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->exists( $sFilePath ) ) {
			return true;
		}
		return function_exists( 'file_exists' ) ? file_exists( $sFilePath ) : null;
	}

	/**
	 * @param string  $sOriginalNeedle
	 * @param string  $sDir
	 * @param boolean $bIncludeExtension
	 * @param boolean $bCaseSensitive
	 * @return bool
	 */
	public function fileExistsInDir( $sOriginalNeedle, $sDir, $bIncludeExtension = true, $bCaseSensitive = false ) {
		if ( empty( $sNeedle ) || empty( $sDir ) || !$this->canAccessDirectory( $sDir ) ) {
			return false;
		}

		$aAllFiles = $this->getAllFilesInDir( $sDir, false );
		if ( !$bCaseSensitive ) {
			$sNeedle = strtolower( $sOriginalNeedle );
			$aAllFiles = array_map( 'strtolower', $aAllFiles );
		}
		else {
			$sNeedle = $sOriginalNeedle;
		}

		//if the file you're searching for doesn't have an extension, then we don't include extensions in search
		$nDotPosition = strpos( $sNeedle, '.' );
		$bHasExtension = $nDotPosition !== false;
		$bIncludeExtension = $bIncludeExtension && $bHasExtension;
		$sNeedlePreExtension = $bHasExtension ? substr( $sNeedle, 0, $nDotPosition ) : $sNeedle;

		$bFound = false;
		foreach ( $aAllFiles as $sFilename ) {

			if ( $bIncludeExtension ) {
				$bFound = ( $sFilename == $sNeedle );
			}
			else {
				// This is not entirely accurate as it only finds whether a file "starts" with needle, ignoring subsequent characters
				$bFound = ( strpos( $sFilename, $sNeedlePreExtension ) === 0 );
			}

			if ( $bFound ) {
				break;
			}
		}

		return $bFound;
	}

	/**
	 * @param string $sDir
	 * @return bool
	 */
	protected function canAccessDirectory( $sDir ) {
		return !is_null( $this->getDirIterator( $sDir ) );
	}

	/**
	 * @param string $sDir
	 * @param bool   $bIncludeDirs
	 * @return string[]
	 */
	public function getAllFilesInDir( $sDir, $bIncludeDirs = true ) {
		$aFiles = array();
		if ( $this->canAccessDirectory( $sDir ) ) {
			foreach ( $this->getDirIterator( $sDir ) as $oFileItem ) {
				if ( !$oFileItem->isDot() && ( $oFileItem->isFile() || $bIncludeDirs ) ) {
					$aFiles[] = $oFileItem->getFilename();
				}
			}
		}
		return ( empty( $aFiles ) ? array() : $aFiles );
	}

	/**
	 * @param string $sDir
	 * @return \DirectoryIterator|null
	 */
	protected function getDirIterator( $sDir ) {
		try {
			$oIterator = new \DirectoryIterator( $sDir );
		}
		catch ( \Exception $oE ) { //  UnexpectedValueException, RuntimeException, Exception
			$oIterator = null;
		}
		return $oIterator;
	}

	/**
	 * @return string|null
	 */
	public function getContent_WpConfig() {
		return $this->getFileContent( Services::WpGeneral()->getPath_WpConfig() );
	}

	/**
	 * @param string $sContent
	 * @return bool
	 */
	public function putContent_WpConfig( $sContent ) {
		return $this->putFileContent( Services::WpGeneral()->getPath_WpConfig(), $sContent );
	}

	/**
	 * @param string  $sUrl
	 * @param boolean $bSecure
	 * @return boolean
	 */
	public function getIsUrlValid( $sUrl, $bSecure = false ) {
		$sSchema = $bSecure ? 'https://' : 'http://';
		$sUrl = ( strpos( $sUrl, 'http' ) !== 0 ) ? $sSchema.$sUrl : $sUrl;
		return Services::HttpRequest()->get( $sUrl );
	}

	/**
	 * @return bool
	 */
	public function getCanWpRemoteGet() {
		$bCan = false;
		$aUrlsToTest = array(
			'https://www.microsoft.com',
			'https://www.google.com',
			'https://www.facebook.com'
		);
		foreach ( $aUrlsToTest as $sUrl ) {
			if ( Services::HttpRequest()->get( $sUrl ) ) {
				$bCan = true;
				break;
			}
		}
		return $bCan;
	}

	public function getCanDiskWrite() {
		$sFilePath = __DIR__.'/testfile.'.rand().'txt';
		$sContents = "Testing icwp file read and write.";

		// Write, read, verify, delete.
		if ( $this->putFileContent( $sFilePath, $sContents ) ) {
			$sFileContents = $this->getFileContent( $sFilePath );
			if ( !is_null( $sFileContents ) && $sFileContents === $sContents ) {
				return $this->deleteFile( $sFilePath );
			}
		}
		return false;
	}

	/**
	 * @param string $sFilePath
	 * @return int|null
	 */
	public function getModifiedTime( $sFilePath ) {
		return $this->getTime( $sFilePath, 'modified' );
	}

	/**
	 * @param string $sFilePath
	 * @return int|null
	 */
	public function getAccessedTime( $sFilePath ) {
		return $this->getTime( $sFilePath, 'accessed' );
	}

	/**
	 * @param string $sFilePath
	 * @param string $sProperty
	 * @return int|null
	 */
	public function getTime( $sFilePath, $sProperty = 'modified' ) {

		if ( !$this->exists( $sFilePath ) ) {
			return null;
		}

		$oFs = $this->getWpfs();
		switch ( $sProperty ) {

			case 'modified' :
				return $oFs ? $oFs->mtime( $sFilePath ) : filemtime( $sFilePath );
				break;
			case 'accessed' :
				return $oFs ? $oFs->atime( $sFilePath ) : fileatime( $sFilePath );
				break;
			default:
				return null;
				break;
		}
	}

	/**
	 * @param string $sFilePath
	 * @return NULL|boolean
	 */
	public function getCanReadWriteFile( $sFilePath ) {
		if ( !file_exists( $sFilePath ) ) {
			return null;
		}

		$nFileSize = filesize( $sFilePath );
		if ( $nFileSize === 0 ) {
			return null;
		}

		$sFileContent = $this->getFileContent( $sFilePath );
		if ( empty( $sFileContent ) ) {
			return false; //can't even read the file!
		}
		return $this->putFileContent( $sFilePath, $sFileContent );
	}

	/**
	 * @param string $sFilePath
	 * @return string|null
	 */
	public function getFileContent( $sFilePath ) {
		$sContents = null;
		$oFs = $this->getWpfs();
		if ( $oFs ) {
			$sContents = $oFs->get_contents( $sFilePath );
		}

		if ( empty( $sContents ) && function_exists( 'file_get_contents' ) ) {
			$sContents = file_get_contents( $sFilePath );
		}
		return $sContents;
	}

	/**
	 * Use this to reliably read the contents of a PHP file that doesn't have executable
	 * PHP Code.
	 * Why use this? In the name of naive security, silly web hosts can prevent reading the contents of
	 * non-PHP files so we simply put the content we want to have read into a php file and then "include" it.
	 * @param string $sFile
	 * @return string
	 */
	public function getFileContentUsingInclude( $sFile ) {
		ob_start();
		@include( $sFile );
		return ob_get_clean();
	}

	/**
	 * @param $sFilePath
	 * @return bool
	 */
	public function getFileSize( $sFilePath ) {
		$oFs = $this->getWpfs();
		if ( $oFs && ( $oFs->size( $sFilePath ) > 0 ) ) {
			return $oFs->size( $sFilePath );
		}
		return @filesize( $sFilePath );
	}

	/**
	 * @param string                      $sDir
	 * @param int                         $nMaxDepth - set to zero for no max
	 * @param \RecursiveDirectoryIterator $oDirIterator
	 * @return \SplFileInfo[]
	 */
	public function getFilesInDir( $sDir, $nMaxDepth = 1, $oDirIterator = null ) {
		$aList = array();

		try {
			if ( empty( $oDirIterator ) ) {
				$oDirIterator = new \RecursiveDirectoryIterator( $sDir );
				if ( method_exists( $oDirIterator, 'setFlags' ) ) {
					$oDirIterator->setFlags( \RecursiveDirectoryIterator::SKIP_DOTS );
				}
			}

			$oRecurIter = new \RecursiveIteratorIterator( $oDirIterator );
			$oRecurIter->setMaxDepth( $nMaxDepth - 1 ); //since they start at zero.

			/** @var \SplFileInfo $oFile */
			foreach ( $oRecurIter as $oFile ) {
				$aList[] = clone $oFile;
			}
		}
		catch ( \Exception $oE ) { //  UnexpectedValueException, RuntimeException, Exception
		}

		return $aList;
	}

	/**
	 * @param string|null $sBaseDir
	 * @param string      $sPrefix
	 * @param string      $outsRandomDir
	 * @return bool|string
	 */
	public function getTempDir( $sBaseDir = null, $sPrefix = '', &$outsRandomDir = '' ) {
		$sTemp = rtrim( ( is_null( $sBaseDir ) ? get_temp_dir() : $sBaseDir ), DIRECTORY_SEPARATOR ).DIRECTORY_SEPARATOR;

		$sCharset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz0123456789';
		do {
			$sDir = $sPrefix;
			for ( $i = 0 ; $i < 8 ; $i++ ) {
				$sDir .= $sCharset[ ( rand()%strlen( $sCharset ) ) ];
			}
		} while ( is_dir( $sTemp.$sDir ) );

		$outsRandomDir = $sDir;

		$bSuccess = true;
		if ( !@mkdir( $sTemp.$sDir, 0755, true ) ) {
			$bSuccess = false;
		}
		return ( $bSuccess ? $sTemp.$sDir : false );
	}

	/**
	 * @param string $sFilePath
	 * @param string $sContents
	 * @return boolean
	 */
	public function putFileContent( $sFilePath, $sContents ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->put_contents( $sFilePath, $sContents, FS_CHMOD_FILE ) ) {
			return true;
		}

		if ( function_exists( 'file_put_contents' ) ) {
			return file_put_contents( $sFilePath, $sContents ) !== false;
		}
		return false;
	}

	/**
	 * Recursive delete
	 * @param string $sDir
	 * @return bool
	 */
	public function deleteDir( $sDir ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->rmdir( $sDir, true ) ) {
			return true;
		}
		return @rmdir( $sDir );
	}

	/**
	 * @param string $sFilePath
	 * @return boolean|null
	 */
	public function deleteFile( $sFilePath ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->delete( $sFilePath ) ) {
			return true;
		}
		return function_exists( 'unlink' ) ? @unlink( $sFilePath ) : null;
	}

	/**
	 * @param string $sFilePathSource
	 * @param string $sFilePathDestination
	 * @return bool|null
	 */
	public function move( $sFilePathSource, $sFilePathDestination ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->move( $sFilePathSource, $sFilePathDestination ) ) {
			return true;
		}
		return function_exists( 'rename' ) ? @rename( $sFilePathSource, $sFilePathDestination ) : null;
	}

	/**
	 * @param string $sPath
	 * @return bool
	 */
	public function isDir( $sPath ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->is_dir( $sPath ) ) {
			return true;
		}
		return function_exists( 'is_dir' ) ? is_dir( $sPath ) : false;
	}

	/**
	 * @param $sPath
	 * @return bool|mixed
	 */
	public function isFile( $sPath ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->is_file( $sPath ) ) {
			return true;
		}
		return function_exists( 'is_file' ) ? is_file( $sPath ) : null;
	}

	/**
	 * @return bool
	 */
	public function isFilesystemAccessDirect() {
		return ( $this->getWpfs() instanceof \WP_Filesystem_Direct );
	}

	/**
	 * @param string $sPath
	 * @return bool
	 */
	public function mkdir( $sPath ) {
		return wp_mkdir_p( $sPath );
	}

	/**
	 * @param string $sFilePath
	 * @param int    $nTime
	 * @return bool|mixed
	 */
	public function touch( $sFilePath, $nTime = null ) {
		$oFs = $this->getWpfs();
		if ( $oFs && $oFs->touch( $sFilePath, $nTime ) ) {
			return true;
		}
		return function_exists( 'touch' ) ? @touch( $sFilePath, $nTime ) : null;
	}

	/**
	 * @return \WP_Filesystem_Base
	 */
	protected function getWpfs() {
		if ( is_null( $this->oWpfs ) ) {
			$this->initFileSystem();
		}
		return $this->oWpfs;
	}

	/**
	 */
	private function initFileSystem() {
		if ( is_null( $this->oWpfs ) ) {
			$this->oWpfs = false;
			require_once( ABSPATH.'wp-admin/includes/file.php' );
			if ( \WP_Filesystem() ) {
				global $wp_filesystem;
				if ( isset( $wp_filesystem ) && is_object( $wp_filesystem ) ) {
					$this->oWpfs = $wp_filesystem;
				}
			}
		}
	}

	/**
	 * @deprecated
	 * @param string $sUrl
	 * @param array  $aRequestArgs
	 * @return array|bool
	 */
	public function requestUrl( $sUrl, $aRequestArgs = array() ) {
		return Services::HttpRequest()->requestUrl( $sUrl, $aRequestArgs );
	}

	/**
	 * @deprecated
	 * @param string $sUrl
	 * @param array  $aRequestArgs
	 * @return array|false
	 */
	public function getUrl( $sUrl, $aRequestArgs = array() ) {
		return Services::HttpRequest()->requestUrl( $sUrl, $aRequestArgs, 'GET' );
	}

	/**
	 * @deprecated
	 * @param string $sUrl
	 * @param array  $aRequestArgs
	 * @return false|string
	 */
	public function getUrlContent( $sUrl, $aRequestArgs = array() ) {
		return Services::HttpRequest()->getContent( $sUrl, $aRequestArgs );
	}

	/**
	 * @deprecated
	 * @param string $sUrl
	 * @param array  $aRequestArgs
	 * @return array|false
	 */
	public function postUrl( $sUrl, $aRequestArgs = array() ) {
		return Services::HttpRequest()->requestUrl( $sUrl, $aRequestArgs, 'POST' );
	}

	/**
	 * @deprecated
	 * @return string
	 */
	public function getWpConfigPath() {
		return Services::WpGeneral()->getPath_WpConfig();
	}
}