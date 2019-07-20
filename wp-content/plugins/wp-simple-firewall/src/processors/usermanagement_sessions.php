<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_UserManagement_Sessions extends ICWP_WPSF_Processor_BaseWpsf {

	public function run() {
		if ( $this->isReadyToRun() ) {
			parent::run();
			add_filter( 'wp_login_errors', [ $this, 'addLoginMessage' ] );
			add_filter( 'auth_cookie_expiration', [ $this, 'setMaxAuthCookieExpiration' ], 100, 1 );
		}
	}

	/**
	 * Cron callback
	 */
	public function runDailyCron() {
		$this->cleanExpiredSessions();
	}

	/**
	 * @return bool
	 */
	public function isReadyToRun() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		return ( parent::isReadyToRun() && $oFO->getSessionsProcessor()->isReadyToRun() );
	}

	/**
	 * @param string  $sUsername
	 * @param WP_User $oUser
	 */
	public function onWpLogin( $sUsername, $oUser ) {
		if ( !$oUser instanceof WP_User ) {
			$oUser = Services::WpUsers()->getUserByUsername( $sUsername );
		}
		$this->enforceSessionLimits( $oUser );
	}

	/**
	 * @param string $sCookie
	 * @param int    $nExpire
	 * @param int    $nExpiration
	 * @param int    $nUserId
	 */
	public function onWpSetLoggedInCookie( $sCookie, $nExpire, $nExpiration, $nUserId ) {
		$this->enforceSessionLimits( Services::WpUsers()->getUserById( $nUserId ) );
	}

	/**
	 */
	public function onWpLoaded() {
		if ( $this->isReadyToRun() && Services::WpUsers()->isUserLoggedIn() && !Services::Rest()->isRest() ) {
			$this->checkCurrentSession();
		}
	}

	/**
	 */
	private function checkCurrentSession() {
		$oWp = Services::WpGeneral();
		$oWpUsers = Services::WpUsers();

		try {
			$bSessionInvalid = !$this->assessCurrentSession();
			$nCode = 0;
			$sMessage = '';
		}
		catch ( \Exception $oE ) {
			$bSessionInvalid = true;
			$nCode = $oE->getCode();
			$sMessage = $oE->getMessage();
		}

		if ( $bSessionInvalid ) { // it's not admin, but the user looks logged into WordPress and not to Shield

			if ( is_admin() ) { // prevent any admin access on invalid Shield sessions.

				switch ( $nCode ) {

					case 1:
						$this->addToAuditEntry(
							$sMessage.' '.__( 'Logging out.', 'wp-simple-firewall' ), 2, 'um_session_expired_timeout'
						);
						$oWpUsers->logoutUser( true );
						break;

					case 2:
						$this->addToAuditEntry(
							$sMessage.' '.__( 'Logging out.', 'wp-simple-firewall' ), 2, 'um_session_idle_timeout'
						);
						$oWpUsers->logoutUser( true );
						break;

					case 3:
						$this->addToAuditEntry(
							sprintf( 'Access to an established user session from a new IP address "%s". Redirecting request.', $this->ip() ),
							2,
							'um_session_ip_lock_redirect'
						);
						$oWp->redirectToHome();
						break;

					case 4:
						$this->addToAuditEntry(
							$sMessage.' '.__( 'Logging out.', 'wp-simple-firewall' ), 2, 'um_session_no_valid_found'
						);
						$oWpUsers->forceUserRelogin( [ 'wpsf-forcelogout' => $nCode ] );
						break;

					case 7:
						$oWpUsers->logoutUser( true );
						$this->addToAuditEntry(
							sprintf( 'Browser signature has changed for this user "%s" session. Redirecting request.', $oWpUsers->getCurrentWpUser()->user_login ),
							2,
							'um_session_browser_lock_redirect'
						);
						$oWp->redirectToLogin();
						break;

					default:
						$this->addToAuditEntry(
							'Unable to verify the current User Session. Forcefully logging out session and redirecting to login.',
							2,
							'um_session_not_found_redirect'
						);
						$oWpUsers->forceUserRelogin( [ 'wpsf-forcelogout' => $nCode ] );
						break;
				}
			}
			else {
				$this->addToAuditEntry(
					'Unable to verify the current User Session. Forcefully logging out session.',
					2,
					'um_session_not_found'
				);
				$oWpUsers->logoutUser();
			}
		}
	}

	public function cleanExpiredSessions() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		/** @var \FernleafSystems\Wordpress\Plugin\Shield\Databases\Session\Delete $oTerminator */
		$oTerminator = $oFO->getSessionsProcessor()
						   ->getDbHandler()
						   ->getQueryDeleter();

		// We use 14 as an outside case. If it's 2 days, WP cookie will expire anyway.
		// And if User Management is active, then it'll draw in that value.
		$oTerminator->forExpiredLoginAt( $this->getLoginExpiredBoundary() );

		// Default is ZERO, so we don't want to terminate all sessions if it's never set.
		if ( $oFO->hasSessionIdleTimeout() ) {
			$oTerminator->forExpiredLoginIdle( $this->getLoginIdleExpiredBoundary() );
		}
	}

	/**
	 * @return int
	 */
	public function countActiveSessions() {
		return $this->getActiveSessionsQuerySelector()->count();
	}

	/**
	 * @return \FernleafSystems\Wordpress\Plugin\Shield\Databases\Session\EntryVO[]|mixed
	 */
	public function getActiveSessions() {
		return $this->getActiveSessionsQuerySelector()->query();
	}

	/**
	 * @return \FernleafSystems\Wordpress\Plugin\Shield\Databases\Session\Select
	 */
	private function getActiveSessionsQuerySelector() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		/** @var \FernleafSystems\Wordpress\Plugin\Shield\Databases\Session\Select $oSel */
		$oSel = $oFO->getSessionsProcessor()->getDbHandler()->getQuerySelector();

		if ( $oFO->hasMaxSessionTimeout() ) {
			$oSel->filterByLoginNotExpired( $this->getLoginExpiredBoundary() );
		}
		if ( $oFO->hasSessionIdleTimeout() ) {
			$oSel->filterByLoginNotIdleExpired( $this->getLoginIdleExpiredBoundary() );
		}
		return $oSel;
	}

	/**
	 * @return int
	 */
	private function getLoginExpiredBoundary() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		return $this->time() - $oFO->getMaxSessionTime();
	}

	/**
	 * @return int
	 */
	private function getLoginIdleExpiredBoundary() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		return $this->time() - $oFO->getIdleTimeoutInterval();
	}

	/**
	 * @return true
	 * @throws \Exception
	 */
	protected function assessCurrentSession() {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();

		if ( !$oFO->hasSession() ) {
			throw new \Exception( __( 'Valid user session could not be found', 'wp-simple-firewall' ), 4 );
		}
		else {
			$oSess = $oFO->getSession();
			$nTime = $this->time();

			// timeout interval
			if ( $oFO->hasMaxSessionTimeout() && ( $nTime - $oSess->logged_in_at > $oFO->getMaxSessionTime() ) ) {
				$nDays = (int)( $oFO->getMaxSessionTime()/DAY_IN_SECONDS );
				throw new \Exception(
					sprintf(
						__( 'User session has expired after %s', 'wp-simple-firewall' ),
						sprintf( _n( '%s day', '%s days', $nDays, 'wp-simple-firewall' ), $nDays )
					),
					1
				);
			}

			// idle timeout interval
			if ( $oFO->hasSessionIdleTimeout() && ( $nTime - $oSess->last_activity_at > $oFO->getIdleTimeoutInterval() ) ) {
				$oFO->setOptInsightsAt( 'last_idle_logout_at' );
				$nHours = (int)( $oFO->getIdleTimeoutInterval()/HOUR_IN_SECONDS );
				throw new \Exception(
					sprintf(
						__( 'User session has expired after %s', 'wp-simple-firewall' ),
						sprintf( _n( '%s day', '%s days', $nHours, 'wp-simple-firewall' ), $nHours )
					),
					2
				);
			}

			/**
			 * We allow the original session IP, the SERVER_ADDR, and the "what is my IP"
			 */
			if ( $oFO->isLockToIp() ) {
				/** @var ICWP_WPSF_FeatureHandler_Plugin $oPluginMod */
				$oPluginMod = $this->getCon()->getModule( 'plugin' );
				$aPossibleIps = [
					$oSess->ip,
					Services::Request()->getServerAddress(),
					$oPluginMod->getMyServerIp()
				];
				if ( !in_array( $this->ip(), $aPossibleIps ) ) {
					throw new \Exception(
						sprintf( 'Access to an established user session from a new IP address "%s".', $this->ip() ),
						3
					);
				}
			}
		}

		return true;
	}

	/**
	 * @param int $nTimeout
	 * @return int
	 */
	public function setMaxAuthCookieExpiration( $nTimeout ) {
		/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
		$oFO = $this->getMod();
		return $oFO->hasMaxSessionTimeout() ? min( $nTimeout, $oFO->getMaxSessionTime() ) : $nTimeout;
	}

	/**
	 * @param \WP_User $oUser
	 */
	protected function enforceSessionLimits( $oUser ) {

		$nSessionLimit = $this->getOption( 'session_username_concurrent_limit', 1 );
		if ( !$this->isLoginCaptured() && $nSessionLimit > 0 && $oUser instanceof WP_User ) {
			$this->setLoginCaptured();
			/** @var ICWP_WPSF_FeatureHandler_UserManagement $oFO */
			$oFO = $this->getMod();
			try {
				$oFO->getSessionsProcessor()
					->getDbHandler()
					->getQueryDeleter()
					->addWhere( 'wp_username', $oUser->user_login )
					->deleteExcess( $nSessionLimit, 'last_activity_at', true );
			}
			catch ( \Exception $oE ) {
			}
		}
	}

	/**
	 * @param WP_Error $oError
	 * @return WP_Error
	 */
	public function addLoginMessage( $oError ) {

		if ( !$oError instanceof WP_Error ) {
			$oError = new WP_Error();
		}

		$sForceLogout = Services::Request()->query( 'wpsf-forcelogout' );
		if ( $sForceLogout ) {

			switch ( $sForceLogout ) {
				case 1:
					$sMessage = __( 'Your session has expired.', 'wp-simple-firewall' );
					break;

				case 2:
					$sMessage = __( 'Your session was idle for too long.', 'wp-simple-firewall' );
					break;

				case 3:
					$sMessage = __( 'Your session was locked to another IP Address.', 'wp-simple-firewall' );
					break;

				case 4:
					$sMessage = sprintf( __( 'You do not currently have a %s user session.', 'wp-simple-firewall' ), $this->getCon()
																														  ->getHumanName() );
					break;

				case 5:
					$sMessage = __( 'An administrator has terminated this session.', 'wp-simple-firewall' );
					break;

				case 6:
					$sMessage = __( 'Not a user.', 'wp-simple-firewall' );
					break;

				default:
					$sMessage = __( 'Your session was terminated.', 'wp-simple-firewall' );
					break;
			}

			$sMessage .= '<br />'.__( 'Please login again.', 'wp-simple-firewall' );
			$oError->add( 'wpsf-forcelogout', $sMessage );
		}
		return $oError;
	}
}