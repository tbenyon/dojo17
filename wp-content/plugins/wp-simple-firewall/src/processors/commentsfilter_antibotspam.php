<?php

use FernleafSystems\Wordpress\Plugin\Shield\Databases\Comments;
use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_CommentsFilter_AntiBotSpam extends ICWP_WPSF_BaseDbProcessor {

	/**
	 * The unique comment token assigned to this page
	 * @var string
	 */
	protected $sFormId;

	/**
	 * @var string
	 */
	protected $sCommentStatus;

	/**
	 * @var string
	 */
	protected $sCommentStatusExplanation;

	/**
	 * @param ICWP_WPSF_FeatureHandler_CommentsFilter $oModCon
	 */
	public function __construct( ICWP_WPSF_FeatureHandler_CommentsFilter $oModCon ) {
		parent::__construct( $oModCon, $oModCon->getDef( 'spambot_comments_filter_table_name' ) );
	}

	/**
	 */
	public function run() {
		if ( $this->isReadyToRun() ) {
			// Add GASP checking to the comment form.
			add_action( 'wp', [ $this, 'setupForm' ] );
			add_filter( 'preprocess_comment', [ $this, 'doCommentChecking' ], 5 );
			add_filter( $this->getMod()->prefix( 'cf_status' ), [ $this, 'getCommentStatus' ], 1 );
			add_filter( $this->getMod()->prefix( 'cf_status_expl' ), [ $this, 'getCommentStatusExplanation' ], 1 );
		}
	}

	public function setupForm() {
		if ( !Services::Request()->isPost() ) {
			add_action( 'comment_form', [ $this, 'printGaspFormItems' ], 1 );
		}
	}

	/**
	 * A private plugin filter that lets us return up the newly set comment status.
	 * @param $sCurrentCommentStatus
	 * @return string
	 */
	public function getCommentStatus( $sCurrentCommentStatus ) {
		return empty( $sCurrentCommentStatus ) ? $this->sCommentStatus : $sCurrentCommentStatus;
	}

	/**
	 * A private plugin filter that lets us return up the newly set comment status explanation
	 * @param $sCurrentCommentStatusExplanation
	 * @return string
	 */
	public function getCommentStatusExplanation( $sCurrentCommentStatusExplanation ) {
		return empty( $sCurrentCommentStatusExplanation ) ? $this->sCommentStatusExplanation : $sCurrentCommentStatusExplanation;
	}

	/**
	 * @param array $aCommData
	 * @return array
	 */
	public function doCommentChecking( $aCommData ) {
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		$nPostId = $aCommData[ 'comment_post_ID' ];
		if ( $oFO->getIfDoCommentsCheck( $nPostId, $aCommData[ 'comment_author_email' ] ) ) {

			$this->doGaspCommentCheck( $nPostId );

			// Now we check whether comment status is to completely reject and then we simply redirect to "home"
			if ( $this->sCommentStatus == 'reject' ) {
				Services::Response()->redirectToHome();
			}
		}

		return $aCommData;
	}

	/**
	 * Performs the actual GASP comment checking
	 * @param $nPostId
	 */
	protected function doGaspCommentCheck( $nPostId ) {

		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		// Check that we haven't already marked the comment through another scan
		if ( !empty( $this->sCommentStatus ) ) {
			return;
		}

		$bIsSpam = true;
		$sStatKey = '';
		$sExplanation = '';

		$oReq = Services::Request();
		$sFieldCheckboxName = $oReq->post( 'cb_nombre' );
		$sFieldHoney = $oReq->post( 'sugar_sweet_email' );
		$sCommentToken = $oReq->post( 'comment_token' );

		// we have the cb name, is it set?
		if ( !$sFieldCheckboxName || !$oReq->post( $sFieldCheckboxName ) ) {
			$sExplanation = sprintf( __( 'Failed GASP Bot Filter Test (%s)', 'wp-simple-firewall' ), __( 'checkbox', 'wp-simple-firewall' ) );
			$sStatKey = 'checkbox';
		}
		// honeypot check
		else if ( !empty( $sFieldHoney ) ) {
			$sExplanation = sprintf( __( 'Failed GASP Bot Filter Test (%s)', 'wp-simple-firewall' ), __( 'honeypot', 'wp-simple-firewall' ) );
			$sStatKey = 'honeypot';
		}
		// check the unique comment token is present
		else if ( $oFO->getIfCheckCommentToken() && !$this->checkCommentToken( $sCommentToken, $nPostId ) ) {
			$sExplanation = sprintf( __( 'Failed GASP Bot Filter Test (%s)', 'wp-simple-firewall' ), __( 'comment token failure', 'wp-simple-firewall' ) );
			$sStatKey = 'token';
		}
		else {
			$bIsSpam = false;
		}

		if ( $bIsSpam ) {
			$this->doStatIncrement( sprintf( 'spam.gasp.%s', $sStatKey ) );
			$this->sCommentStatus = $this->getOption( 'comments_default_action_spam_bot' );
			$this->setCommentStatusExplanation( $sExplanation );
			$oFO->setOptInsightsAt( 'last_comment_block_at' )
				->setIpTransgressed();
		}
	}

	public function printGaspFormItems() {
		$oToken = $this->initCommentFormToken();
		if ( $oToken instanceof Comments\EntryVO ) {
			echo $this->getGaspCommentsHookHtml( $oToken );
			echo $this->getGaspCommentsHtml();
		}
	}

	/**
	 * @return Comments\EntryVO|null
	 */
	protected function initCommentFormToken() {
		/** @var Comments\EntryVO $oToken */
		$oToken = $this->getDbHandler()->getVo();
		$oToken->post_id = Services::WpPost()->getCurrentPostId();
		$oToken->unique_token = md5( $this->getCon()->getUniqueRequestId( false ) );
		return $this->getDbHandler()
					->getQueryInserter()
					->insert( $oToken ) ? $oToken : null;
	}

	/**
	 * @param Comments\EntryVO $oToken
	 * @return string
	 */
	protected function getGaspCommentsHookHtml( $oToken ) {
		$aHtml = [
			'<p id="'.$this->getUniqueFormId().'"></p>', // we use this unique <p> to hook onto using javascript
			'<input type="hidden" id="_sugar_sweet_email" name="sugar_sweet_email" value="" />',
			sprintf( '<input type="hidden" id="_comment_token" name="comment_token" value="%s" />',
				$oToken->unique_token )
		];
		return implode( '', $aHtml );
	}

	/**
	 * @return string
	 */
	protected function getGaspCommentsHtml() {
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		$sId = $this->getUniqueFormId();
		$sConfirm = $oFO->getTextOpt( 'custom_message_checkbox' );
		$sAlert = $oFO->getTextOpt( 'custom_message_alert' );
		$sCommentWait = $oFO->getTextOpt( 'custom_message_comment_wait' );
		$sCommentReload = $oFO->getTextOpt( 'custom_message_comment_reload' );

		$nCooldown = $oFO->getTokenCooldown();
		$nExpire = $oFO->getTokenExpireInterval();

		$sJsCommentWait = '"'.str_replace( '%s', '"+nRemaining+"', $sCommentWait ).'"';
		$sCommentWait = str_replace( '%s', $nCooldown, $sCommentWait ); // don't use sprintf for errors.

		$sReturn = "
			<script type=\"text/javascript\">
				
				function cb_click$sId() {
					cb_name$sId.value=cb$sId.name;
				}
				function check$sId() {
					if( cb$sId.checked != true ) {
						alert( \"$sAlert\" ); return false;
					}
					return true;
				}
				function reenableButton$sId() {
					nTimerCounter{$sId}++;
					nRemaining = $nCooldown - nTimerCounter$sId;
					subbutton$sId.value	= $sJsCommentWait;
					if ( nTimerCounter$sId >= $nCooldown ) {
						subbutton$sId.value = origButtonValue$sId;
						subbutton$sId.disabled = false;
						clearInterval( sCountdownTimer$sId );
					}
				}
				function redisableButton$sId() {
					subbutton$sId.value		= \"$sCommentReload\";
					subbutton$sId.disabled	= true;
				}
				
				var $sId				= document.getElementById('$sId');
				var cb$sId				= document.createElement('input');
				cb$sId.type				= 'checkbox';
				cb$sId.id				= 'checkbox$sId';
				cb$sId.name				= 'checkbox$sId';
				cb$sId.style.width		= '25px';
				cb$sId.onclick			= cb_click$sId;
			
				var label$sId			= document.createElement( 'label' );
				var labelspan$sId		= document.createElement( 'span' );
				label$sId.htmlFor		= 'checkbox$sId';
				labelspan$sId.innerHTML	= \"$sConfirm\";

				var cb_name$sId			= document.createElement('input');
				cb_name$sId.type		= 'hidden';
				cb_name$sId.name		= 'cb_nombre';

				$sId.appendChild( label$sId );
				label$sId.appendChild( cb$sId );
				label$sId.appendChild( labelspan$sId );
				$sId.appendChild( cb_name$sId );

				var frm$sId					= cb$sId.form;
				frm$sId.onsubmit			= check$sId;

				".(
			( $nCooldown > 0 || $nExpire > 0 ) ?
				"
					var subbuttonList$sId = frm$sId.querySelectorAll( 'input[type=\"submit\"]' );
					
					if ( typeof( subbuttonList$sId ) != \"undefined\" ) {
						subbutton$sId = subbuttonList{$sId}[0];
						if ( typeof( subbutton$sId ) != \"undefined\" ) {
						
						".(
				( $nCooldown > 0 ) ?
					"
							subbutton$sId.disabled		= true;
							origButtonValue$sId			= subbutton$sId.value;
							subbutton$sId.value			= \"$sCommentWait\";
							nTimerCounter$sId			= 0;
							sCountdownTimer$sId			= setInterval( reenableButton$sId, 1000 );
							"
					: ''
				).(
				( $nExpire > 0 ) ? "sTimeoutTimer$sId			= setTimeout( redisableButton$sId, ".( 1000*$nExpire - 1000 )." );" : ''
				)."
						}
					}
					" : ''
			)."
			</script>
		";
		return $sReturn;
	}

	/**
	 * @return string
	 */
	protected function getUniqueFormId() {
		if ( !isset( $this->sFormId ) ) {
			$oDp = Services::Data();
			$sId = $oDp->generateRandomLetter().$oDp->generateRandomString( rand( 7, 23 ), 7 );
			$this->sFormId = preg_replace(
				'#[^a-zA-Z0-9]#', '',
				apply_filters( 'icwp_shield_cf_gasp_uniqid', $sId ) );
		}
		return $this->sFormId;
	}

	/**
	 * @param string $sToken
	 * @param        $sPostId
	 * @return bool
	 */
	protected function checkCommentToken( $sToken, $sPostId ) {
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();

		$bValidToken = false;

		$oToken = $this->getPostCommentToken( $sToken, $sPostId );
		if ( $oToken instanceof Comments\EntryVO ) {
			// Did sufficient time pass and is it not-expired?
			$nAge = $this->time() - $oToken->getCreatedAt();
			$nExpires = $oFO->getTokenExpireInterval();

			$bValidToken = ( $nAge > $oFO->getTokenCooldown() )
						   && ( $nExpires < 1 || $nAge < $nExpires );

			// Tokens are 1 time only.
			$this->getDbHandler()
				 ->getQueryDeleter()
				 ->deleteEntry( $oToken );
		}

		return $bValidToken;
	}

	/**
	 * @param string $sCommentToken
	 * @param int    $sPostId
	 * @return Comments\EntryVO|null
	 */
	private function getPostCommentToken( $sCommentToken, $sPostId ) {
		/** @var Comments\Select $oSel */
		$oSel = $this->getDbHandler()->getQuerySelector();
		return $oSel->getTokenForPost( $sCommentToken, $sPostId, $this->ip() );
	}

	/**
	 * @return string
	 */
	public function getCreateTableSql() {
		return "CREATE TABLE %s (
			id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id int(11) NOT NULL DEFAULT 0,
			unique_token VARCHAR(32) NOT NULL DEFAULT '',
			ip varchar(40) NOT NULL DEFAULT '0',
			created_at int(15) UNSIGNED NOT NULL DEFAULT 0,
			deleted_at int(15) UNSIGNED NOT NULL DEFAULT 0,
 			PRIMARY KEY  (id)
		) %s;";
	}

	/**
	 * @return array
	 */
	protected function getTableColumnsByDefinition() {
		$aDef = $this->getMod()->getDef( 'spambot_comments_filter_table_columns' );
		return is_array( $aDef ) ? $aDef : [];
	}

	/**
	 * @param $sExplanation
	 */
	protected function setCommentStatusExplanation( $sExplanation ) {
		$this->sCommentStatusExplanation =
			'[* '.sprintf(
				__( '%s plugin marked this comment as "%s".', 'wp-simple-firewall' ).' '.__( 'Reason: %s', 'wp-simple-firewall' ),
				$this->getCon()->getHumanName(),
				$this->sCommentStatus,
				$sExplanation
			)." *]\n";
	}

	/**
	 * @return int
	 */
	protected function getAutoExpirePeriod() {
		/** @var ICWP_WPSF_FeatureHandler_CommentsFilter $oFO */
		$oFO = $this->getMod();
		return $oFO->getTokenExpireInterval();
	}

	/**
	 * @return \FernleafSystems\Wordpress\Plugin\Shield\Databases\Comments\Handler
	 */
	protected function createDbHandler() {
		return new \FernleafSystems\Wordpress\Plugin\Shield\Databases\Comments\Handler();
	}
}