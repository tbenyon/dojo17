<?php

namespace FernleafSystems\Wordpress\Services\Core;

use FernleafSystems\Wordpress\Services\Services;

/**
 * Class Rest
 * @package FernleafSystems\Wordpress\Services\Core
 */
class Rest {

	/**
	 * @return string|null
	 */
	public function getNamespace() {
		$sNameSpace = null;

		$sPath = $this->getPath();
		if ( !empty( $sPath ) ) {
			$aParts = explode( '/', $sPath );
			if ( !empty( $aParts ) ) {
				$sNameSpace = $aParts[ 0 ];
			}
		}
		return $sNameSpace;
	}

	/**
	 * @return string|null
	 */
	public function getPath() {
		$sPath = null;

		if ( $this->isRest() ) {
			$oReq = Services::Request();
			$oWp = Services::WpGeneral();

			$sPath = $oReq->request( 'rest_route' );
			if ( empty( $sPath ) && $oWp->isPermalinksEnabled() ) {
				$sFullUri = $oWp->getHomeUrl( $oReq->getPath() );
				$sPath = substr( $sFullUri, strlen( get_rest_url( get_current_blog_id() ) ) );
			}
		}
		return $sPath;
	}

	/**
	 * @return bool
	 */
	public function isRest() {
		$bIsRest = ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || !empty( $_REQUEST[ 'rest_route' ] );

		global $wp_rewrite;
		if ( !$bIsRest && function_exists( 'rest_url' ) && is_object( $wp_rewrite ) ) {
			$sRestUrlBase = get_rest_url( get_current_blog_id(), '/' );
			$sRestPath = trim( parse_url( $sRestUrlBase, PHP_URL_PATH ), '/' );
			$sRequestPath = trim( Services::Request()->getPath(), '/' );
			$bIsRest = !empty( $sRequestPath ) && !empty( $sRestPath )
					   && ( strpos( $sRequestPath, $sRestPath ) === 0 );
		}
		return $bIsRest;
	}
}