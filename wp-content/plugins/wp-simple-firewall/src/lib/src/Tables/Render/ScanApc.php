<?php

namespace FernleafSystems\Wordpress\Plugin\Shield\Tables\Render;

use FernleafSystems\Wordpress\Plugin\Shield\Scans;

class ScanApc extends ScanBase {

	/**
	 * @param array $aItem
	 * @return string
	 */
	public function column_plugin( $aItem ) {
		$aButtons = [
			$this->getActionButton_Ignore( $aItem[ 'id' ] ),
		];
		return $aItem[ 'plugin' ].$this->buildActions( $aButtons );
	}

	/**
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array(
			'ignore' => 'Ignore'
		);
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'         => '&nbsp;',
			'plugin'     => 'Item',
			'status'     => 'Status',
			'created_at' => 'Discovered',
		];
	}
}