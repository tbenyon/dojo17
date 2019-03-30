<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_Lockdown extends ICWP_WPSF_Processor_BaseWpsf {

	/**
	 */
	public function run() {
		/** @var ICWP_WPSF_FeatureHandler_Lockdown $oFO */
		$oFO = $this->getMod();

		if ( $oFO->isOptFileEditingDisabled() ) {
			$this->blockFileEditing();
		}

		$sWpVersionMask = $this->getOption( 'mask_wordpress_version' );
		if ( !empty( $sWpVersionMask ) ) {
			global $wp_version;
			$wp_version = $sWpVersionMask;
// 			add_filter( 'bloginfo', array( $this, 'maskWordpressVersion' ), 1, 2 );
// 			add_filter( 'bloginfo_url', array( $this, 'maskWordpressVersion' ), 1, 2 );
		}

		if ( false && $this->getOption( 'action_reset_auth_salts' ) == 'Y' ) {
			add_action( 'init', array( $this, 'resetAuthKeysSalts' ), 1 );
		}

		if ( $oFO->isOpt( 'force_ssl_admin', 'Y' ) && function_exists( 'force_ssl_admin' ) ) {
			if ( !defined( 'FORCE_SSL_ADMIN' ) ) {
				define( 'FORCE_SSL_ADMIN', true );
			}
			force_ssl_admin( true );
		}

		if ( $oFO->isOpt( 'hide_wordpress_generator_tag', 'Y' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		if ( $oFO->isXmlrpcDisabled() ) {
			add_filter( 'xmlrpc_enabled', array( $this, 'disableXmlrpc' ), 1000, 0 );
			add_filter( 'xmlrpc_methods', array( $this, 'disableXmlrpc' ), 1000, 0 );
		}
	}

	private function blockFileEditing() {
		if ( !defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		add_filter( 'user_has_cap',
			/**
			 * @param array $aAllCaps
			 * @param array $cap
			 * @param array $aArgs
			 * @return array
			 */
			function ( $aAllCaps, $cap, $aArgs ) {
				$sRequestedCapability = $aArgs[ 0 ];
				if ( in_array( $sRequestedCapability, [ 'edit_themes', 'edit_plugins', 'edit_files' ] ) ) {
					$aAllCaps[ $sRequestedCapability ] = false;
				}
				return $aAllCaps;
			},
			PHP_INT_MAX, 3
		);
	}

	public function onWpInit() {
		parent::onWpInit();
		if ( !Services::WpUsers()->isUserLoggedIn() ) {
			$this->interceptCanonicalRedirects();

			// hook in before rest API processing. Remember always return $bDo
			add_filter( 'do_parse_request', function ( $bDo ) {
				$this->interceptAnonRestApi();
				return $bDo;
			}, 9 );
		}
	}

	/**
	 * @return array|false
	 */
	public function disableXmlrpc() {
		/** @var ICWP_WPSF_FeatureHandler_Lockdown $oFO */
		$oFO = $this->getMod();
		$oFO->setOptInsightsAt( 'xml_block_at' )
			->setIpTransgressed();
		return ( current_filter() == 'xmlrpc_enabled' ) ? false : array();
	}

	/**
	 * TODO: instead of filtering auth errors, perhaps create a valid json response
	 */
	private function interceptAnonRestApi() {
		/** @var ICWP_WPSF_FeatureHandler_Lockdown $oFO */
		$oFO = $this->getMod();
		$oWpRest = Services::Rest();
		if ( $oWpRest->isRest() && $oFO->isRestApiAnonymousAccessDisabled()
			 && !$oFO->isPermittedAnonRestApiNamespace( $oWpRest->getNamespace() ) ) {
			// 99 so that we jump in just before the always-on WordPress cookie auth.
			add_filter( 'rest_authentication_errors', array( $this, 'disableAnonymousRestApi' ), 99 );
		}
	}

	/**
	 * @uses wp_die()
	 */
	private function interceptCanonicalRedirects() {

		if ( $this->getMod()->isOpt( 'block_author_discovery', 'Y' ) ) {
			$sAuthor = Services::Request()->query( 'author', '' );
			if ( !empty( $sAuthor ) ) {
				Services::WpGeneral()->wpDie( sprintf(
					_wpsf__( 'The "author" query parameter has been blocked by %s to protect against user login name fishing.' )
					.sprintf( '<br /><a href="%s" target="_blank">%s</a>',
						'https://icwp.io/7l',
						_wpsf__( 'Learn More.' )
					),
					$this->getCon()->getHumanName()
				) );
			}
		}
	}

	/**
	 * Understand that if $mCurrentStatus is null, no check has been made. If true, something has
	 * authenticated the request, and if WP_Error, then an error is already present
	 * @param WP_Error|true|null $mStatus
	 * @return WP_Error
	 */
	public function disableAnonymousRestApi( $mStatus ) {

		if ( $mStatus !== true && !is_wp_error( $mStatus ) ) {

			$mStatus = new \WP_Error(
				'shield_block_anon_restapi',
				sprintf( _wpsf__( 'Anonymous access to the WordPress Rest API has been restricted by %s.' ), $this->getCon()
																												  ->getHumanName() ),
				array( 'status' => rest_authorization_required_code() ) );
			$this->addToAuditEntry(
				sprintf( 'Blocked Anonymous API Access through "%s" namespace', Services::Rest()->getNamespace() ),
				1,
				'anonymous_api'
			);

			/** @var ICWP_WPSF_FeatureHandler_Lockdown $oFO */
			$oFO = $this->getMod();
			$oFO->setOptInsightsAt( 'restapi_block_at' );
		}

		return $mStatus;
	}

	/**
	 * Override the original collection to then add plugin statistics to the mix
	 * @param $aData
	 * @return array
	 */
	public function tracking_DataCollect( $aData ) {
		$aData = parent::tracking_DataCollect( $aData );
		$sSlug = $this->getMod()->getSlug();
		$aData[ $sSlug ][ 'options' ][ 'mask_wordpress_version' ]
			= empty( $aData[ $sSlug ][ 'options' ][ 'mask_wordpress_version' ] ) ? 0 : 1;
		return $aData;
	}

	/**
	 * @param $sOutput
	 * @param $sShow
	 * @return string
	 */
	public function maskWordpressVersion( $sOutput, $sShow ) {
// 		if ( $sShow === 'version' ) {
// 			$sOutput = $this->aOptions['mask_wordpress_version'];
// 		}
// 		return $sOutput;
	}

	/**
	 */
	public function resetAuthKeysSalts() {
		$oWpFs = Services::WpFs();

		// Get the new Salts
		$sSaltsUrl = 'https://api.wordpress.org/secret-key/1.1/salt/';
		$sSalts = Services::HttpRequest()->getContent( $sSaltsUrl );

		$sWpConfigContent = $oWpFs->getContent_WpConfig();
		if ( is_null( $sWpConfigContent ) ) {
			return;
		}

		$aKeys = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT'
		);

		$aContent = explode( PHP_EOL, $sWpConfigContent );
		$fKeyFound = false;
		$nStartLine = 0;
		foreach ( $aContent as $nLineNumber => $sLine ) {
			foreach ( $aKeys as $nPosition => $sKey ) {
				if ( strpos( $sLine, $sKey ) === false ) {
					continue;
				}
				if ( $nStartLine == 0 ) {
					$nStartLine = $nLineNumber;
				}
				else {
					unset( $aContent[ $nLineNumber ] );
				}
				$fKeyFound = true;
			}
		}
		$aContent[ $nStartLine ] = $sSalts;
		$oWpFs->putContent_WpConfig( implode( PHP_EOL, $aContent ) );
	}

	/**
	 * @deprecated
	 * @param array $aAllCaps
	 * @param       $cap
	 * @param array $aArgs
	 * @return array
	 */
	public function disableFileEditing( $aAllCaps, $cap, $aArgs ) {
		$sRequestedCapability = $aArgs[ 0 ];
		if ( in_array( $sRequestedCapability, [ 'edit_themes', 'edit_plugins', 'edit_files' ] ) ) {
			$aAllCaps[ $sRequestedCapability ] = false;
		}
		return $aAllCaps;
	}
}