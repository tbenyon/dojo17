<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_Firewall extends ICWP_WPSF_Processor_BaseWpsf {

	protected $aWhitelist;

	/**
	 * @var array
	 */
	private $aDieMessage;

	/**
	 * @var array
	 */
	protected $aPatterns;

	/**
	 * @var array
	 */
	private $aAuditBlockMessage;

	/**
	 * After any parameter whitelisting has been accounted for
	 *
	 * @var array
	 */
	protected $aPageParams;

	public function run() {
		if ( $this->getIfPerformFirewallScan() && $this->getIfDoFirewallBlock() ) {
			$this->doPreFirewallBlock();
			$this->doFirewallBlock();
		}
	}

	/**
	 * @return bool
	 */
	private function getIfDoFirewallBlock() {
		return !$this->isVisitorRequestPermitted();
	}

	/**
	 * @return bool
	 */
	private function getIfPerformFirewallScan() {
		$bPerformScan = true;
		/** @var ICWP_WPSF_FeatureHandler_Firewall $oFO */
		$oFO = $this->getMod();

		$sPath = Services::Request()->getPath();

		if ( count( $this->getRawRequestParams() ) == 0 ) {
			$bPerformScan = false;
		}
		else if ( empty( $sPath ) ) {
			$sAuditMessage = sprintf( __( 'Skipping firewall checking for this visit: %s.', 'wp-simple-firewall' ), __( 'Parsing the URI failed', 'wp-simple-firewall' ) );
			$this->addToAuditEntry( $sAuditMessage, 2, 'firewall_skip' );
			$bPerformScan = false;
		}
		else if ( count( $this->getParamsToCheck() ) == 0 ) {
			$bPerformScan = false;
		}
		// TODO: are we calling is_super_admin() too early?
		else if ( $oFO->isIgnoreAdmin() && is_super_admin() ) {
			$bPerformScan = false;
		}

		return $bPerformScan;
	}

	/**
	 * @return boolean - true if visitor is permitted, false if it should be blocked.
	 */
	private function isVisitorRequestPermitted() {
		/** @var ICWP_WPSF_FeatureHandler_Firewall $oFO */
		$oFO = $this->getMod();

		$bRequestIsPermitted = true;
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_dir_traversal', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'dirtraversal' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_sql_queries', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'sqlqueries' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_wordpress_terms', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'wpterms' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_field_truncation', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'fieldtruncation' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_php_code', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'phpcode' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_leading_schema', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'schema' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_aggressive', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheck( 'aggressive' );
		}
		if ( $bRequestIsPermitted && $oFO->isOpt( 'block_exe_file_uploads', 'Y' ) ) {
			$bRequestIsPermitted = $this->doPassCheckBlockExeFileUploads();
		}
		return $bRequestIsPermitted;
	}

	/**
	 * @return bool
	 */
	protected function doPassCheckBlockExeFileUploads() {
		$sKey = 'exefile';
		$bFAIL = false;
		if ( isset( $_FILES ) && !empty( $_FILES ) ) {
			$aFileNames = [];
			foreach ( $_FILES as $aFile ) {
				if ( !empty( $aFile[ 'name' ] ) ) {
					$aFileNames[] = $aFile[ 'name' ];
				}
			}
			$aMatchTerms = $this->getFirewallPatterns( 'exefile' );
			if ( isset( $aMatchTerms[ 'regex' ] ) && is_array( $aMatchTerms[ 'regex' ] ) ) {

				$aMatchTerms[ 'regex' ] = array_map( [ $this, 'prepRegexTerms' ], $aMatchTerms[ 'regex' ] );
				foreach ( $aMatchTerms[ 'regex' ] as $sTerm ) {
					foreach ( $aFileNames as $sParam => $mValue ) {
						if ( is_scalar( $mValue ) && preg_match( $sTerm, (string)$mValue ) ) {
							$bFAIL = true;
							break( 2 );
						}
					}
				}
			}
			if ( $bFAIL ) {
				$sAuditMessage = sprintf( __( 'Firewall Trigger: %s.', 'wp-simple-firewall' ), __( 'EXE File Uploads', 'wp-simple-firewall' ) );
				$this->addToAuditEntry( $sAuditMessage, 3, 'firewall_block' );
				$this->doStatIncrement( 'firewall.blocked.'.$sKey );
			}
		}
		return !$bFAIL;
	}

	/**
	 * Returns false when check fails - that is, it should be blocked by the firewall.
	 *
	 * @param string $sBlockKey
	 * @return boolean
	 */
	private function doPassCheck( $sBlockKey ) {

		$aMatchTerms = $this->getFirewallPatterns( $sBlockKey );
		$aParamValues = $this->getParamsToCheck();
		if ( empty( $aMatchTerms ) || empty( $aParamValues ) ) {
			return true;
		}

		$sParam = '';
		$mValue = '';

		$bFAIL = false;
		if ( isset( $aMatchTerms[ 'simple' ] ) && is_array( $aMatchTerms[ 'simple' ] ) ) {

			foreach ( $aMatchTerms[ 'simple' ] as $sTerm ) {
				foreach ( $aParamValues as $sParam => $mValue ) {
					if ( is_scalar( $mValue ) && ( stripos( (string)$mValue, $sTerm ) !== false ) ) {
						$bFAIL = true;
						break( 2 );
					}
				}
			}
		}

		if ( !$bFAIL && isset( $aMatchTerms[ 'regex' ] ) && is_array( $aMatchTerms[ 'regex' ] ) ) {
			$aMatchTerms[ 'regex' ] = array_map( [ $this, 'prepRegexTerms' ], $aMatchTerms[ 'regex' ] );
			foreach ( $aMatchTerms[ 'regex' ] as $sTerm ) {
				foreach ( $aParamValues as $sParam => $mValue ) {
					if ( is_scalar( $mValue ) && preg_match( $sTerm, (string)$mValue ) ) {
						$sParam = sanitize_text_field( $sParam );
						$mValue = sanitize_text_field( $mValue );
						$bFAIL = true;
						break( 2 );
					}
				}
			}
		}

		if ( $bFAIL ) {
			$this->addToFirewallDieMessage( __( "Something in the URL, Form or Cookie data wasn't appropriate.", 'wp-simple-firewall' ) );

			$this->aAuditBlockMessage = [
				sprintf( __( 'Firewall Trigger: %s.', 'wp-simple-firewall' ), $this->getFirewallBlockKeyName( $sBlockKey ) ),
				__( 'Page parameter failed firewall check.', 'wp-simple-firewall' ),
				sprintf( __( 'The offending parameter was "%s" with a value of "%s".', 'wp-simple-firewall' ), $sParam, $mValue )
			];

			$this->addToAuditEntry(
				implode( "\n", $this->aAuditBlockMessage ), 3, 'firewall_block',
				[
					'param'    => $sParam,
					'val'      => $mValue,
					'blockkey' => $sBlockKey,
				]
			);
			$this->doStatIncrement( 'firewall.blocked.'.$sBlockKey );
		}

		return !$bFAIL;
	}

	/**
	 * @param string $sKey
	 * @return array|null
	 */
	protected function getFirewallPatterns( $sKey = null ) {
		if ( !isset( $this->aPatterns ) ) {
			$this->aPatterns = $this->getMod()->getDef( 'firewall_patterns' );
		}
		if ( !empty( $sKey ) ) {
			return isset( $this->aPatterns[ $sKey ] ) ? $this->aPatterns[ $sKey ] : null;
		}
		return $this->aPatterns;
	}

	/**
	 * @param string $sTerm
	 * @return string
	 */
	private function prepRegexTerms( $sTerm ) {
		return '/'.$sTerm.'/i';
	}

	/**
	 */
	private function doPreFirewallBlock() {
		/** @var ICWP_WPSF_FeatureHandler_Firewall $oFO */
		$oFO = $this->getMod();

		switch ( $oFO->getBlockResponse() ) {
			case 'redirect_die':
				$sMessage = __( 'Visitor connection was killed with wp_die()', 'wp-simple-firewall' );
				break;
			case 'redirect_die_message':
				$sMessage = __( 'Visitor connection was killed with wp_die() and a message', 'wp-simple-firewall' );
				break;
			case 'redirect_home':
				$sMessage = __( 'Visitor was sent HOME', 'wp-simple-firewall' );
				break;
			case 'redirect_404':
				$sMessage = __( 'Visitor was sent 404', 'wp-simple-firewall' );
				break;
			default:
				$sMessage = __( 'Unknown', 'wp-simple-firewall' );
				break;
		}

		if ( $oFO->isOpt( 'block_send_email', 'Y' ) ) {

			$sRecipient = $oFO->getPluginDefaultRecipientAddress();
			if ( $this->sendBlockEmail( $sRecipient ) ) {
				$this->addToAuditEntry( sprintf( __( 'Successfully sent Firewall Block email alert to: %s', 'wp-simple-firewall' ), $sRecipient ) );
			}
			else {
				$this->addToAuditEntry( sprintf( __( 'Failed to send Firewall Block email alert to: %s', 'wp-simple-firewall' ), $sRecipient ) );
			}
		}

		$oFO->setOptInsightsAt( 'last_firewall_block_at' )
			->setIpTransgressed();
		$this->addToAuditEntry( sprintf( __( 'Firewall Block Response: %s.', 'wp-simple-firewall' ), $sMessage ) );
	}

	/**
	 */
	private function doFirewallBlock() {
		/** @var ICWP_WPSF_FeatureHandler_Firewall $oFO */
		$oFO = $this->getMod();

		switch ( $oFO->getBlockResponse() ) {
			case 'redirect_die':
				break;
			case 'redirect_die_message':
				Services::WpGeneral()->wpDie( $this->getFirewallDieMessageForDisplay() );
				break;
			case 'redirect_home':
				Services::Response()->redirectToHome();
				break;
			case 'redirect_404':
				Services::Response()->redirect( '404' );
				break;
			default:
				break;
		}
		die();
	}

	/**
	 * @return array
	 */
	protected function getFirewallDieMessage() {
		if ( !isset( $this->aDieMessage ) || !is_array( $this->aDieMessage ) ) {
			$this->aDieMessage = [ $this->getMod()->getTextOpt( 'text_firewalldie' ) ];
		}
		return $this->aDieMessage;
	}

	/**
	 * @return string
	 */
	protected function getFirewallDieMessageForDisplay() {
		$aMessages = apply_filters( $this->getMod()
										 ->prefix( 'firewall_die_message' ), $this->getFirewallDieMessage() );
		if ( !is_array( $aMessages ) ) {
			$aMessages = [];
		}
		return implode( ' ', $aMessages );
	}

	/**
	 * @param string $sMessagePart
	 * @return $this
	 */
	protected function addToFirewallDieMessage( $sMessagePart ) {
		$aMessages = $this->getFirewallDieMessage();
		$aMessages[] = $sMessagePart;
		$this->aDieMessage = $aMessages;
		return $this;
	}

	/**
	 * @return array
	 */
	private function getParamsToCheck() {
		if ( isset( $this->aPageParams ) ) {
			return $this->aPageParams;
		}
		/** @var ICWP_WPSF_FeatureHandler_Firewall $oFO */
		$oFO = $this->getMod();

		$this->aPageParams = $this->getRawRequestParams();
		$aWhitelist = $this->loadDP()->mergeArraysRecursive( $oFO->getDefaultWhitelist(), $oFO->getCustomWhitelist() );

		// first we remove globally whitelisted request parameters
		if ( !empty( $aWhitelist[ '*' ] ) && is_array( $aWhitelist[ '*' ] ) ) {
			foreach ( $aWhitelist[ '*' ] as $sWhitelistParam ) {

				if ( preg_match( '#^/.+/$#', $sWhitelistParam ) ) {
					foreach ( array_keys( $this->aPageParams ) as $sParamKey ) {
						if ( preg_match( $sWhitelistParam, $sParamKey ) ) {
							unset( $this->aPageParams[ $sParamKey ] );
						}
					}
				}
				else if ( isset( $this->aPageParams[ $sWhitelistParam ] ) ) {
					unset( $this->aPageParams[ $sWhitelistParam ] );
				}
			}
		}

		// If the parameters to check is already empty, we return it to save any further processing.
		if ( empty( $this->aPageParams ) ) {
			return $this->aPageParams;
		}

		// Now we run through the list of whitelist pages
		$sRequestPage = Services::Request()->getPath();
		foreach ( $aWhitelist as $sWhitelistPageName => $aWhitelistPageParams ) {

			// if the page is white listed
			if ( strpos( $sRequestPage, $sWhitelistPageName ) !== false ) {

				// if the page has no particular parameters specified there is nothing to check since the whole page is white listed.
				if ( empty( $aWhitelistPageParams ) ) {
					$this->aPageParams = [];
				}
				else {
					// Otherwise we run through any whitelisted parameters and remove them.
					foreach ( $aWhitelistPageParams as $sWhitelistParam ) {
						if ( array_key_exists( $sWhitelistParam, $this->aPageParams ) ) {
							unset( $this->aPageParams[ $sWhitelistParam ] );
						}
					}
				}
				break;
			}
		}

		return $this->aPageParams;
	}

	/**
	 * @return array
	 */
	protected function getRawRequestParams() {
		return Services::Request()->getRawRequestParams( $this->getMod()->isOpt( 'include_cookie_checks', 'Y' ) );
	}

	/**
	 * @param string $sRecipient
	 * @return bool
	 */
	private function sendBlockEmail( $sRecipient ) {

		if ( !empty( $this->aAuditBlockMessage ) ) {
			$sIp = Services::IP()->getRequestIp();
			$aMessage = array_merge(
				[
					sprintf( __( '%s has blocked a page visit to your site.', 'wp-simple-firewall' ), $this->getCon()
																										   ->getHumanName() ),
					__( 'Log details for this visitor are below:', 'wp-simple-firewall' ),
					'- '.sprintf( '%s: %s', __( 'IP Address', 'wp-simple-firewall' ), $sIp ),
				],
				array_map(
					function ( $sLine ) {
						return '- '.$sLine;
					},
					$this->aAuditBlockMessage
				),
				[
					'',
					sprintf( __( 'You can look up the offending IP Address here: %s', 'wp-simple-firewall' ), 'http://ip-lookup.net/?ip='.$sIp )
				]
			);

			return $this->getEmailProcessor()
						->sendEmailWithWrap( $sRecipient, __( 'Firewall Block Alert', 'wp-simple-firewall' ), $aMessage );
		}
		return true;
	}

	/**
	 * @param string $sBlockKey
	 * @return string
	 */
	private function getFirewallBlockKeyName( $sBlockKey ) {
		switch ( $sBlockKey ) {
			case 'dirtraversal':
				$sName = __( 'Directory Traversal', 'wp-simple-firewall' );
				break;
			case 'wpterms':
				$sName = __( 'WordPress Terms', 'wp-simple-firewall' );
				break;
			case 'fieldtruncation':
				$sName = __( 'Field Truncation', 'wp-simple-firewall' );
				break;
			case 'sqlqueries':
				$sName = __( 'SQL Queries', 'wp-simple-firewall' );
				break;
			case 'exefile':
				$sName = __( 'EXE File Uploads', 'wp-simple-firewall' );
				break;
			case 'schema':
				$sName = __( 'Leading Schema', 'wp-simple-firewall' );
				break;
			case 'phpcode':
				$sName = __( 'PHP Code', 'wp-simple-firewall' );
				break;
			case 'aggressive':
				$sName = __( 'Aggressive Rules', 'wp-simple-firewall' );
				break;
			default:
				$sName = __( 'Unknown Rules', 'wp-simple-firewall' );
				break;
		}
		return $sName;
	}
}