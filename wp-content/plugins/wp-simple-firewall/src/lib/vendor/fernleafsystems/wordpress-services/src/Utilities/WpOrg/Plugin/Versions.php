<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin;

use FernleafSystems\Wordpress\Services\Services;
use FernleafSystems\Wordpress\Services\Utilities\HttpUtil;

class Versions {

	use Base;

	/**
	 * @return string[]
	 */
	public function allFallback() {
		$aV = [];
		$sSvnVersionsContent = Services::HttpRequest()
									   ->getContent( Repo::GetUrlForPluginVersions( $this->getWorkingSlug() ) );

		if ( !empty( $sSvnVersionsContent ) ) {
			$oSvnDom = new \DOMDocument();
			$oSvnDom->loadHTML( $sSvnVersionsContent );

			foreach ( $oSvnDom->getElementsByTagName( 'a' ) as $oElem ) {
				/** @var \DOMElement $oElem */
				$sHref = $oElem->getAttribute( 'href' );
				if ( $sHref != '../' && !filter_var( $sHref, FILTER_VALIDATE_URL ) ) {
					$aV[] = trim( $sHref, '/' );
				}
			}
		}
		return $aV;
	}

	/**
	 * @return string[]
	 */
	public function all() {
		try {
			$oInfo = ( new Api() )
				->setWorkingSlug( $this->getWorkingSlug() )
				->getPluginInfo();

			if ( !empty( $oInfo->versions ) ) {
				$aVersions = array_filter(
					array_keys( $oInfo->versions ),
					function ( $sVersion ) {
						return strpos( $sVersion, '.' );
					}
				);
			}
			else {
				$aVersions = $this->allFallback();
			}
		}
		catch ( \Exception $oE ) {
			$aVersions = [];
		}

		usort( $aVersions, 'version_compare' );
		return $aVersions;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function latest() {
		return ( new Api() )
			->setWorkingSlug( $this->getWorkingSlug() )
			->getPluginInfo()
			->version;
	}

	/**
	 * @param string $sVersion
	 * @param bool   $bVerifyUrl
	 * @return bool
	 */
	public function exists( $sVersion, $bVerifyUrl = false ) {
		$bExists = in_array( $sVersion, $this->all() );
		if ( $bExists && $bVerifyUrl ) {
			try {
				( new HttpUtil() )->checkUrl( Repo::GetUrlForPluginVersion( $this->getWorkingSlug(), $sVersion ) );
			}
			catch ( \Exception $oE ) {
				$bExists = false;
			};
		}
		return $bExists;
	}
}