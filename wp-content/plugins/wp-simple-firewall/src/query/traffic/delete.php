<?php

if ( class_exists( 'ICWP_WPSF_Query_TrafficEntry_Delete', false ) ) {
	return;
}

require_once( __DIR__.'/common.php' );
require_once( dirname( __DIR__ ).'/base/delete.php' );

class ICWP_WPSF_Query_TrafficEntry_Delete extends ICWP_WPSF_Query_BaseDelete {

	use ICWP_WPSF_Query_TrafficEntry_Common;

	/**
	 * @return ICWP_WPSF_Query_TrafficEntry_Count
	 */
	protected function getCounter() {
		require_once( __DIR__.'/count.php' );
		$oCounter = new ICWP_WPSF_Query_TrafficEntry_Count();
		return $oCounter->setTable( $this->getTable() );
	}
}