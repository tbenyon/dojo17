<?php

use FernleafSystems\Wordpress\Services\Services; // TODO: use after Shield 7.5

abstract class ICWP_WPSF_Processor_BaseWpsf extends ICWP_WPSF_Processor_Base {

	const RECAPTCHA_JS_HANDLE = 'icwp-google-recaptcha';

	/**
	 * @var array
	 */
	private $aStatistics;

	/**
	 * @var bool
	 */
	private static $bRecaptchaEnqueue = false;

	/**
	 * @var bool
	 */
	private $bLogRequest;

	/**
	 * Resets the object values to be re-used anew
	 */
	public function init() {
		parent::init();
		$oFO = $this->getMod();
		add_filter( $oFO->prefix( 'collect_stats' ), [ $this, 'stats_Collect' ] );
		add_filter( $oFO->prefix( 'collect_tracking_data' ), [ $this, 'tracking_DataCollect' ] );
	}

	/**
	 * @return int
	 */
	protected function getInstallationDays() {
		$nTimeInstalled = $this->getCon()
							   ->loadCorePluginFeatureHandler()
							   ->getInstallDate();
		if ( empty( $nTimeInstalled ) ) {
			return 0;
		}
		return (int)round( ( $this->loadRequest()->ts() - $nTimeInstalled )/DAY_IN_SECONDS );
	}

	/**
	 * @param WP_User $oUser
	 * @return bool
	 */
	protected function isUserSubjectToLoginIntent( $oUser = null ) {
		$bIsSubject = false;

		if ( !$oUser instanceof WP_User ) {
			$oUser = Services::WpUsers()->getCurrentWpUser();
		}
		if ( $oUser instanceof WP_User ) {
			$bIsSubject = apply_filters( $this->prefix( 'user_subject_to_login_intent' ), false, $oUser );
		}

		return $bIsSubject;
	}

	/**
	 * @return bool
	 */
	protected function getRecaptchaTheme() {
		/** @var ICWP_WPSF_FeatureHandler_BaseWpsf $oFO */
		$oFO = $this->getMod();
		return $this->isRecaptchaInvisible() ? 'light' : $oFO->getGoogleRecaptchaStyle();
	}

	/**
	 * @return string
	 */
	protected function getRecaptchaResponse() {
		return $this->loadRequest()->post( 'g-recaptcha-response' );
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	protected function checkRequestRecaptcha() {
		/** @var ICWP_WPSF_FeatureHandler_BaseWpsf $oFO */
		$oFO = $this->getMod();

		$sCaptchaResponse = $this->getRecaptchaResponse();

		if ( empty( $sCaptchaResponse ) ) {
			throw new \Exception( __( 'Whoops.', 'wp-simple-firewall' ).' '.__( 'Google reCAPTCHA was not submitted.', 'wp-simple-firewall' ), 1 );
		}
		else {
			$oResponse = ( new \ReCaptcha\ReCaptcha( $oFO->getGoogleRecaptchaSecretKey(), new \FernleafSystems\Wordpress\Plugin\Shield\Utilities\WordpressPost() ) )
				->verify( $sCaptchaResponse, $this->ip() );
			if ( empty( $oResponse ) || !$oResponse->isSuccess() ) {
				$aMsg = [
					__( 'Whoops.', 'wp-simple-firewall' ),
					__( 'Google reCAPTCHA verification failed.', 'wp-simple-firewall' ),
					Services::WpGeneral()->isAjax() ?
						__( 'Maybe refresh the page and try again.', 'wp-simple-firewall' ) : ''
				];
				throw new \Exception( implode( ' ', $aMsg ), 2 );
			}
		}
		return true;
	}

	/**
	 * @return bool
	 */
	protected function getIfLogRequest() {
		return isset( $this->bLogRequest ) ?
			(bool)$this->bLogRequest
			: !\FernleafSystems\Wordpress\Services\Services::WpGeneral()->isCron();
	}

	/**
	 * @param bool $bLog
	 * @return $this
	 */
	protected function setIfLogRequest( $bLog ) {
		$this->bLogRequest = $bLog;
		return $this;
	}

	/**
	 * @return bool
	 */
	protected function isRecaptchaInvisible() {
		/** @var ICWP_WPSF_FeatureHandler_BaseWpsf $oFO */
		$oFO = $this->getMod();
		return ( $oFO->getGoogleRecaptchaStyle() == 'invisible' );
	}

	public function registerGoogleRecaptchaJs() {
		$sJsUri = add_query_arg(
			[
				'hl'     => $this->getGoogleRecaptchaLocale(),
				'onload' => 'onLoadIcwpRecaptchaCallback',
				'render' => 'explicit',
			],
			'https://www.google.com/recaptcha/api.js'
		);
		wp_register_script( self::RECAPTCHA_JS_HANDLE, $sJsUri, [], false, true );
		wp_enqueue_script( self::RECAPTCHA_JS_HANDLE );

		// This also gives us the chance to remove recaptcha before it's printed, if it isn't needed
		add_action( 'wp_footer', [ $this, 'maybeDequeueRecaptcha' ], -100 );
		add_action( 'login_footer', [ $this, 'maybeDequeueRecaptcha' ], -100 );

		\FernleafSystems\Wordpress\Services\Services::Includes()
													->addIncludeAttribute( self::RECAPTCHA_JS_HANDLE, 'async', 'async' )
													->addIncludeAttribute( self::RECAPTCHA_JS_HANDLE, 'defer', 'defer' );
		/**
		 * Change to recaptcha implementation now means
		 * 1 - the form will not submit unless the recaptcha has been executed (either invisible or manual)
		 */
	}

	/**
	 * A filter used to collect all the stats gathered in the plugin.
	 *
	 * @param array $aStats
	 * @return array
	 */
	public function stats_Collect( $aStats ) {
		if ( !is_array( $aStats ) ) {
			$aStats = [];
		}
		$aThisStats = $this->stats_Get();
		if ( !empty( $aThisStats ) && is_array( $aThisStats ) ) {
			$aStats[] = $aThisStats;
		}
		return $aStats;
	}

	/**
	 * @param string $sStatKey
	 * @return $this
	 */
	private function stats_Increment( $sStatKey ) {
		$aStats = $this->stats_Get();
		if ( !isset( $aStats[ $sStatKey ] ) ) {
			$aStats[ $sStatKey ] = 0;
		}
		$aStats[ $sStatKey ] = $aStats[ $sStatKey ] + 1;
		$this->aStatistics = $aStats;
		return $this;
	}

	/**
	 * @return array
	 */
	public function stats_Get() {
		if ( !isset( $this->aStatistics ) || !is_array( $this->aStatistics ) ) {
			$this->aStatistics = [];
		}
		return $this->aStatistics;
	}

	/**
	 * Filter used to collect plugin data for tracking.  Fired from the plugin processor only if the option is enabled
	 * - it is not enabled by default.
	 * Note that in this case we "mask" options that have been identified as "sensitive" - i.e. could contain
	 * identifiable data.
	 *
	 * @param $aData
	 * @return array
	 */
	public function tracking_DataCollect( $aData ) {
		if ( !is_array( $aData ) ) {
			$aData = [];
		}
		$oFO = $this->getMod();
		$aData[ $oFO->getSlug() ] = [ 'options' => $oFO->collectOptionsForTracking() ];
		return $aData;
	}

	/**
	 * This is the preferred method over $this->stat_Increment() since it handles the parent stat key
	 *
	 * @param string $sStatKey
	 * @param string $sParentStatKey
	 * @return $this
	 */
	protected function doStatIncrement( $sStatKey, $sParentStatKey = '' ) {
		if ( empty( $sParentStatKey ) ) {
			$sParentStatKey = $this->getMod()->getSlug();
		}
		return $this->stats_Increment( $sStatKey.':'.$sParentStatKey );
	}

	/**
	 * @param string $sMsg
	 * @param int    $nCategory
	 * @param string $sEvent
	 * @param array  $aData
	 * @return $this
	 */
	public function addToAuditEntry( $sMsg = '', $nCategory = 1, $sEvent = '', $aData = [] ) {
		$this->createNewAudit( 'wpsf', $sMsg, $nCategory, $sEvent, $aData );
		return $this;
	}

	/**
	 * If recaptcha is required, it prints the necessary snippet and does not remove the enqueue
	 *
	 * @throws \Exception
	 */
	public function maybeDequeueRecaptcha() {

		if ( $this->isRecaptchaEnqueue() ) {
			/** @var ICWP_WPSF_FeatureHandler_BaseWpsf $oFO */
			$oFO = $this->getMod();
			echo $this->loadRenderer( $this->getCon()->getPath_Templates() )
					  ->setTemplateEnginePhp()
					  ->setRenderVars(
						  [
							  'sitekey' => $oFO->getGoogleRecaptchaSiteKey(),
							  'size'    => $this->isRecaptchaInvisible() ? 'invisible' : '',
							  'theme'   => $this->getRecaptchaTheme(),
							  'invis'   => $this->isRecaptchaInvisible(),
						  ]
					  )
					  ->setTemplate( 'snippets/google_recaptcha_js' )
					  ->render();
		}
		else {
			wp_dequeue_script( self::RECAPTCHA_JS_HANDLE );
		}
	}

	/**
	 * @return bool
	 */
	public function isRecaptchaEnqueue() {
		return self::$bRecaptchaEnqueue;
	}

	/**
	 * Note we don't provide a 'false' option here as if it's set to be needed somewhere,
	 * it shouldn't be unset anywhere else.
	 *
	 * @return $this
	 */
	public function setRecaptchaToEnqueue() {
		self::$bRecaptchaEnqueue = true;
		return $this;
	}

	/**
	 * @return bool
	 * @deprecated
	 */
	protected function getIfIpTransgressed() {
		/** @var ICWP_WPSF_FeatureHandler_BaseWpsf $oFO */
		$oFO = $this->getMod();
		return $oFO->getIfIpTransgressed();
	}
}