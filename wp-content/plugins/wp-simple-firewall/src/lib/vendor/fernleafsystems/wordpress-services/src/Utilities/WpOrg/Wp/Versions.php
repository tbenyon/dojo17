<?php

namespace FernleafSystems\Wordpress\Services\Utilities\WpOrg\Wp;

use FernleafSystems\Wordpress\Services;

/**
 * Class Versions
 * @package FernleafSystems\Wordpress\Services\Utilities\WpOrg\Wp
 */
class Versions extends Services\Utilities\WpOrg\Base\VersionsBase {

	/**
	 * @param string $sVersion
	 * @param bool   $bVerifyUrl
	 * @return bool
	 */
	public function exists( $sVersion, $bVerifyUrl = false ) {
		$bExists = in_array( $sVersion, $this->all() );
		if ( $bExists && $bVerifyUrl ) {
			try {
				( new Services\Utilities\HttpUtil() )->checkUrl( Repo::GetUrlForVersion( $sVersion ) );
			}
			catch ( \Exception $oE ) {
				$bExists = false;
			};
		}
		return $bExists;
	}

	/**
	 * @return array
	 */
	protected function downloadVersions() {
		$aV = [];
		$sSvnVersionsContent = Services\Services::HttpRequest()->getContent( Repo::GetUrlForVersions() );

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
}