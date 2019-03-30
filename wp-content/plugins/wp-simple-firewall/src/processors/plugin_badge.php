<?php

use FernleafSystems\Wordpress\Services\Services;

class ICWP_WPSF_Processor_Plugin_Badge extends ICWP_WPSF_Processor_BaseWpsf {

	/**
	 */
	public function run() {
		/** @var ICWP_WPSF_FeatureHandler_Plugin $oFO */
		$oFO = $this->getMod();
		if ( $oFO->isDisplayPluginBadge() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'includeJquery' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'includeJquery' ) );
			add_action( 'wp_footer', array( $this, 'printPluginBadge' ), 100 );
			add_action( 'login_footer', array( $this, 'printPluginBadge' ), 100 );
		}
		add_action( 'widgets_init', array( $this, 'addPluginBadgeWidget' ) );
		add_filter( $oFO->prefix( 'dashboard_widget_content' ), array( $this, 'gatherPluginWidgetContent' ), 100 );
	}

	public function includeJquery() {
		wp_enqueue_script( 'jquery', null, array(), false, true );
	}

	/**
	 * @param array $aContent
	 * @return array
	 */
	public function gatherPluginWidgetContent( $aContent ) {
		/** @var ICWP_WPSF_FeatureHandler_Plugin $oFO */
		$oFO = $this->getMod();
		$oCon = $this->getCon();

		$aLabels = $oCon->getLabels();
		$sFooter = sprintf( _wpsf__( '%s is provided by %s' ), $oCon->getHumanName(),
			sprintf( '<a href="%s">%s</a>', $aLabels[ 'AuthorURI' ], $aLabels[ 'Author' ] )
		);

		$aDisplayData = array(
			'sInstallationDays' => sprintf( _wpsf__( 'Days Installed: %s' ), $this->getInstallationDays() ),
			'sFooter'           => $sFooter,
			'sIpAddress'        => sprintf( _wpsf__( 'Your IP address is: %s' ), $this->ip() )
		);

		if ( !is_array( $aContent ) ) {
			$aContent = array();
		}
		$aContent[] = $oFO->renderTemplate( 'snippets/widget_dashboard_plugin.php', $aDisplayData );
		return $aContent;
	}

	/**
	 * https://wordpress.org/support/topic/fatal-errors-after-update-to-7-0-2/#post-11169820
	 */
	public function addPluginBadgeWidget() {
		/** @var ICWP_WPSF_FeatureHandler_Plugin $oFO */
		$oFO = $this->getMod();
		if ( !empty( $oFO ) && Services::WpGeneral()->getWordpressIsAtLeastVersion( '4.6.0' )
			 && !class_exists( 'Tribe_WP_Widget_Factory' ) ) {
			register_widget( new ICWP_WPSF_Processor_Plugin_BadgeWidget( $oFO ) );
		}
	}

	/**
	 * @uses echo
	 */
	public function printPluginBadge() {
		/** @var ICWP_WPSF_FeatureHandler_Plugin $oFO */
		$oFO = $this->getMod();
		try {
			echo $oFO->renderPluginBadge();
		}
		catch ( \Exception $oE ) {
		}
	}
}