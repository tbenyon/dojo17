<?php

use FernleafSystems\Wordpress\Services\Services; //TODO: Use after 7.5

class ICWP_WPSF_Processor_AdminAccess_Whitelabel extends ICWP_WPSF_Processor_BaseWpsf {

	/**
	 */
	public function run() {
		/** @var ICWP_WPSF_FeatureHandler_AdminAccessRestriction $oFO */
		$oFO = $this->getMod();
		add_filter( $this->prefix( 'is_relabelled' ), '__return_true' );
		add_filter( $oFO->prefix( 'plugin_labels' ), [ $this, 'doRelabelPlugin' ] );
		add_filter( 'plugin_row_meta', [ $this, 'fRemoveDetailsMetaLink' ], 200, 2 );
		add_action( 'admin_print_footer_scripts-plugin-editor.php', [ $this, 'hideFromPluginEditor' ] );
	}

	public function onWpInit() {
		parent::onWpInit();

		/** @var ICWP_WPSF_FeatureHandler_AdminAccessRestriction $oFO */
		$oFO = $this->getMod();
		$oCon = $this->getCon();

		if ( $oFO->isWlHideUpdates() && $this->isNeedToHideUpdates() && !$oCon->isPluginAdmin() ) {
			$this->hideUpdates();
		}
	}

	/**
	 * Depending on the page, we hide the update data,
	 * or we adjust the number of displayed updates counts
	 */
	protected function hideUpdates() {
		if ( in_array( Services::WpPost()->getCurrentPage(), [ 'plugins.php', 'update-core.php' ] ) ) {
			add_filter( 'site_transient_update_plugins', [ $this, 'hidePluginUpdatesFromUI' ] );
		}
		else {
			add_filter( 'wp_get_update_data', [ $this, 'adjustUpdateDataCount' ] );
		}
	}

	/**
	 * Adjusts the available updates count so as not to include Shield updates if they're hidden
	 * @param array $aUpdateData
	 * @return array
	 */
	public function adjustUpdateDataCount( $aUpdateData ) {

		$sFile = $this->getCon()->getPluginBaseFile();
		if ( \FernleafSystems\Wordpress\Services\Services::WpPlugins()->isUpdateAvailable( $sFile ) ) {
			$aUpdateData[ 'counts' ][ 'total' ]--;
			$aUpdateData[ 'counts' ][ 'plugins' ]--;
		}

		return $aUpdateData;
	}

	public function hideFromPluginEditor() {
		$oCon = $this->getCon();
		$sJs = $this->loadDP()->readFileContentsUsingInclude( $oCon->getPath_AssetJs( 'whitelabel.js' ) );
		echo sprintf( '<script type="text/javascript">%s</script>', sprintf( $sJs, $oCon->getPluginBaseFile() ) );
	}

	/**
	 * @param array $aPluginLabels
	 * @return array
	 */
	public function doRelabelPlugin( $aPluginLabels ) {
		/** @var ICWP_WPSF_FeatureHandler_AdminAccessRestriction $oFO */
		$oFO = $this->getMod();

		$aWhiteLabels = $oFO->getWhitelabelOptions();

		// these are the old white labelling keys which will be replaced upon final release of white labelling.
		$sServiceName = $aWhiteLabels[ 'name_main' ];
		$aPluginLabels[ 'Name' ] = $sServiceName;
		$aPluginLabels[ 'Title' ] = $sServiceName;
		$aPluginLabels[ 'Author' ] = $aWhiteLabels[ 'name_company' ];
		$aPluginLabels[ 'AuthorName' ] = $aWhiteLabels[ 'name_company' ];
		$aPluginLabels[ 'MenuTitle' ] = $aWhiteLabels[ 'name_menu' ];

		$sTagLine = $aWhiteLabels[ 'description' ];
		if ( !empty( $sTagLine ) ) {
			$aPluginLabels[ 'Description' ] = $sTagLine;
		}

		$sUrl = $aWhiteLabels[ 'url_home' ];
		if ( !empty( $sUrl ) ) {
			$aPluginLabels[ 'PluginURI' ] = $sUrl;
			$aPluginLabels[ 'AuthorURI' ] = $sUrl;
		}

		$sIconUrl = $aWhiteLabels[ 'url_icon' ];
		if ( !empty( $sIconUrl ) ) {
			$aPluginLabels[ 'icon_url_16x16' ] = $sIconUrl;
			$aPluginLabels[ 'icon_url_32x32' ] = $sIconUrl;
		}

		$sLogoUrl = $aWhiteLabels[ 'url_dashboardlogourl' ];
		if ( !empty( $sLogoUrl ) ) {
			$aPluginLabels[ 'icon_url_128x128' ] = $sLogoUrl;
		}

		return array_merge( $aWhiteLabels, $aPluginLabels );
	}

	/**
	 * @filter
	 * @param array  $aPluginMeta
	 * @param string $sPluginBaseFileName
	 * @return array
	 */
	public function fRemoveDetailsMetaLink( $aPluginMeta, $sPluginBaseFileName ) {
		if ( $sPluginBaseFileName == $this->getCon()->getPluginBaseFile() ) {
			unset( $aPluginMeta[ 2 ] ); // View details
			unset( $aPluginMeta[ 3 ] ); // Rate 5*
		}
		return $aPluginMeta;
	}

	/**
	 * Hides the update if the page loaded is the plugins page or the updates page.
	 * @param stdClass $oPlugins
	 * @return stdClass
	 */
	public function hidePluginUpdatesFromUI( $oPlugins ) {
		$sFile = $this->getCon()->getPluginBaseFile();
		if ( isset( $oPlugins->response[ $sFile ] ) ) {
			unset( $oPlugins->response[ $sFile ] );
		}
		return $oPlugins;
	}

	/**
	 * @return bool
	 */
	private function isNeedToHideUpdates() {
		return is_admin() && !Services::WpGeneral()->isCron();
	}
}