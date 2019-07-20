<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_CommentsFilter extends ICWP_WPSF_Processor_BaseWpsf {

	/**
	 */
	public function run() {
	}

	public function onWpInit() {
		parent::onWpInit();
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		$oUser = Services::WpUsers()->getCurrentWpUser();
		if ( !$oFO->isUserTrusted( $oUser ) ) {
			if ( $oFO->isEnabledGaspCheck() ) {
				$this->getSubProGasp()->run();
			}
			if ( $oFO->isEnabledHumanCheck() && $this->loadWpComments()->isCommentPost() ) {
				$this->getSubProHuman()->run();
			}
			if ( $oFO->isGoogleRecaptchaEnabled() ) {
				$this->getSubProRecaptcha()->run();
			}

			add_filter( 'pre_comment_approved', [ $this, 'doSetCommentStatus' ], 1 );
			add_filter( 'pre_comment_content', [ $this, 'doInsertCommentStatusExplanation' ], 1, 1 );
			add_filter( 'comment_notification_recipients', [ $this, 'clearCommentNotificationEmail' ], 100, 1 );
		}
	}

	/**
	 * @return array
	 */
	protected function getSubProMap() {
		return [
			'gasp'      => 'ICWP_WPSF_Processor_CommentsFilter_AntiBotSpam',
			'human'     => 'ICWP_WPSF_Processor_CommentsFilter_HumanSpam',
			'recaptcha' => 'ICWP_WPSF_Processor_CommentsFilter_GoogleRecaptcha',
		];
	}

	/**
	 * @return ICWP_WPSF_Processor_CommentsFilter_AntiBotSpam
	 */
	private function getSubProGasp() {
		return $this->getSubPro( 'gasp' );
	}

	/**
	 * @return ICWP_WPSF_Processor_CommentsFilter_AntiBotSpam
	 */
	private function getSubProHuman() {
		return $this->getSubPro( 'human' );
	}

	/**
	 * @return ICWP_WPSF_Processor_CommentsFilter_AntiBotSpam
	 */
	private function getSubProRecaptcha() {
		return $this->getSubPro( 'recaptcha' );
	}

	/**
	 * @param array $aNoticeAttributes
	 */
	protected function addNotice_akismet_running( $aNoticeAttributes ) {
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		// We only warn when the human spam filter is running
		if ( $oFO->isEnabledHumanCheck() ) {

			$oWpPlugins = Services::WpPlugins();
			$sPluginFile = $oWpPlugins->findPluginBy( 'Akismet', 'Name' );
			if ( $oWpPlugins->isActive( $sPluginFile ) ) {
				$aRenderData = [
					'notice_attributes' => $aNoticeAttributes,
					'strings'           => [
						'title'                   => 'Akismet is Running',
						'appears_running_akismet' => __( 'It appears you have Akismet Anti-SPAM running alongside the our human Anti-SPAM filter.', 'wp-simple-firewall' ),
						'not_recommended'         => __( 'This is not recommended and you should disable Akismet.', 'wp-simple-firewall' ),
						'click_to_deactivate'     => __( 'Click to deactivate Akismet now.', 'wp-simple-firewall' ),
					],
					'hrefs'             => [
						'deactivate' => $oWpPlugins->getUrl_Deactivate( $sPluginFile )
					]
				];
				$this->insertAdminNotice( $aRenderData );
			}
		}
	}

	/**
	 * We set the final approval status of the comments if we've set it in our scans, and empties the notification email
	 * in case we "trash" it (since WP sends out a notification email if it's anything but SPAM)
	 * @param $sApprovalStatus
	 * @return string
	 */
	public function doSetCommentStatus( $sApprovalStatus ) {
		$sStatus = apply_filters( $this->getMod()->prefix( 'cf_status' ), '' );
		return empty( $sStatus ) ? $sApprovalStatus : $sStatus;
	}

	/**
	 * @param string $sCommentContent
	 * @return string
	 */
	public function doInsertCommentStatusExplanation( $sCommentContent ) {

		$sExplanation = apply_filters( $this->getMod()->prefix( 'cf_status_expl' ), '' );

		// If either spam filtering process left an explanation, we add it here
		if ( !empty( $sExplanation ) ) {
			$sCommentContent = $sExplanation.$sCommentContent;
		}
		return $sCommentContent;
	}

	/**
	 * When you set a new comment as anything but 'spam' a notification email is sent to the post author.
	 * We suppress this for when we mark as trash by emptying the email notifications list.
	 * @param array $aEmails
	 * @return array
	 */
	public function clearCommentNotificationEmail( $aEmails ) {
		$sStatus = apply_filters( $this->getMod()->prefix( 'cf_status' ), '' );
		if ( in_array( $sStatus, [ 'reject', 'trash' ] ) ) {
			$aEmails = [];
		}
		return $aEmails;
	}
}