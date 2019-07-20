<?php

namespace FernleafSystems\Wordpress\Services\Utilities;

/**
 * Class HttpUtil
 * @package FernleafSystems\Wordpress\Services\Utilities
 */
class HttpUtil {

	/**
	 * TODO: use HTTPRequest
	 * @param string $sUrl
	 * @param array  $aValidResponseCodes
	 * @return $this
	 * @throws \Exception
	 */
	public function checkUrl( $sUrl, $aValidResponseCodes = [ 200 ] ) {
		$aResponse = wp_remote_head( $sUrl );
		if ( is_wp_error( $aResponse ) ) {
			throw new \Exception( $aResponse->get_error_message() );
		}

		/** @var \WP_HTTP_Requests_Response $oResp */
		$oResp = $aResponse[ 'http_response' ];
		if ( !in_array( $oResp->get_response_object()->status_code, $aValidResponseCodes ) ) {
			throw new \Exception( 'Head Request Failed. Likely the version does not exist.' );
		}

		return $this;
	}

	/**
	 * @param string $sUrl
	 * @return string
	 * @throws \Exception
	 */
	public function downloadUrl( $sUrl ) {
		/** @var string|\WP_Error $sFile */
		$sFile = download_url( $sUrl );
		if ( is_wp_error( $sFile ) ) {
			throw new \Exception( $sFile->get_error_message() );
		}
		if ( !realpath( $sFile ) ) {
			throw new \Exception( 'Downloaded could not be found' );
		}
		return $sFile;
	}
}