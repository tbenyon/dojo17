<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_LoginProtect extends ICWP_WPSF_Processor_BaseWpsf {

	/**
	 */
	public function run() {
		/** @var ICWP_WPSF_FeatureHandler_LoginProtect $oFO */
		$oFO = $this->getMod();

		// XML-RPC Compatibility
		if ( Services::WpGeneral()->isXmlrpc() && $oFO->isXmlrpcBypass() ) {
			return;
		}

		if ( $oFO->isCustomLoginPathEnabled() ) {
			$this->getProcessorWpLogin()->run();
		}

		// Add GASP checking to the login form.
		if ( $oFO->isEnabledGaspCheck() ) {
			$this->getProcessorGasp()->run();
		}

		if ( $oFO->isCooldownEnabled() && Services::Request()->isPost() ) {
			$this->getProcessorCooldown()->run();
		}

		if ( $oFO->isGoogleRecaptchaEnabled() ) {
			$this->getProcessorGoogleRecaptcha()->run();
		}

		$this->getProcessorLoginIntent()->run();
	}

	/**
	 * Override the original collection to then add plugin statistics to the mix
	 * @param $aData
	 * @return array
	 */
	public function tracking_DataCollect( $aData ) {
		$aData = parent::tracking_DataCollect( $aData );
		$sSlug = $this->getMod()->getSlug();
		$aData[ $sSlug ][ 'options' ][ 'email_can_send_verified_at' ]
			= ( $aData[ $sSlug ][ 'options' ][ 'email_can_send_verified_at' ] > 0 ) ? 1 : 0;
		return $aData;
	}

	/**
	 * @param array $aNoticeAttributes
	 */
	public function addNotice_email_verification_sent( $aNoticeAttributes ) {
		/** @var ICWP_WPSF_FeatureHandler_LoginProtect $oFO */
		$oFO = $this->getMod();

		if ( $oFO->isEmailAuthenticationOptionOn() && !$oFO->isEmailAuthenticationActive() && !$oFO->getIfCanSendEmailVerified() ) {
			$aRenderData = array(
				'notice_attributes' => $aNoticeAttributes,
				'strings'           => array(
					'title'             => $this->getCon()->getHumanName()
										   .': '._wpsf__( 'Please verify email has been received' ),
					'need_you_confirm'  => _wpsf__( "Before we can activate email 2-factor authentication, we need you to confirm your website can send emails." ),
					'please_click_link' => _wpsf__( "Please click the link in the email you received." ),
					'email_sent_to'     => sprintf(
						_wpsf__( "The email has been sent to you at blog admin address: %s" ),
						get_bloginfo( 'admin_email' )
					),
					'how_resend_email'  => _wpsf__( "Resend verification email" ),
					'how_turn_off'      => _wpsf__( "Disable 2FA by email" ),
				),
				'ajax'              => [
					'resend_verification_email' => $oFO->getAjaxActionData( 'resend_verification_email', true ),
					'disable_2fa_email'         => $oFO->getAjaxActionData( 'disable_2fa_email', true ),
				]
			);
			$this->insertAdminNotice( $aRenderData );
		}
	}

	/**
	 * @return ICWP_WPSF_Processor_LoginProtect_Intent
	 */
	public function getProcessorLoginIntent() {
		return new ICWP_WPSF_Processor_LoginProtect_Intent( $this->getMod() );
	}

	/**
	 * @return ICWP_WPSF_Processor_LoginProtect_Cooldown
	 */
	protected function getProcessorCooldown() {
		return new ICWP_WPSF_Processor_LoginProtect_Cooldown( $this->getMod() );
	}

	/**
	 * @return ICWP_WPSF_Processor_LoginProtect_Gasp
	 */
	protected function getProcessorGasp() {
		return new ICWP_WPSF_Processor_LoginProtect_Gasp( $this->getMod() );
	}

	/**
	 * @return ICWP_WPSF_Processor_LoginProtect_WpLogin
	 */
	protected function getProcessorWpLogin() {
		return new ICWP_WPSF_Processor_LoginProtect_WpLogin( $this->getMod() );
	}

	/**
	 * @return ICWP_WPSF_Processor_LoginProtect_GoogleRecaptcha
	 */
	protected function getProcessorGoogleRecaptcha() {
		return new ICWP_WPSF_Processor_LoginProtect_GoogleRecaptcha( $this->getMod() );
	}
}