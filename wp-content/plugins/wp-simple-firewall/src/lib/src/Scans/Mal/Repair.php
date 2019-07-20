<?php

namespace FernleafSystems\Wordpress\Plugin\Shield\Scans\Mal;

use FernleafSystems\Wordpress\Plugin\Shield\Scans;
use FernleafSystems\Wordpress\Services;
use FernleafSystems\Wordpress\Services\Utilities\WpOrg;

/**
 * Class Repair
 * @package FernleafSystems\Wordpress\Plugin\Shield\Scans\Mal
 */
class Repair extends Scans\Base\BaseRepair {

	/**
	 * @param ResultItem $oItem
	 * @return bool
	 */
	public function repairItem( $oItem ) {
		$bSuccess = false;

		if ( Services\Services::CoreFileHashes()->isCoreFile( $oItem->path_fragment ) ) {
			$oFiles = Services\Services::WpGeneral()->isClassicPress() ? new WpOrg\Cp\Files() : new WpOrg\Wp\Files();
			try {
				$oFiles->replaceFileFromVcs( $oItem->path_fragment );
			}
			catch ( \InvalidArgumentException $oE ) {
			}
		}
		else {
			$oFiles = new WpOrg\Plugin\Files();
			try {
				$oPlugin = $oFiles->findPluginFromFile( $oItem->path_fragment );
				if ( $oPlugin instanceof Services\Core\VOs\WpPluginVo ) {
					if ( $oFiles->isValidFileFromPlugin( $oItem->path_fragment ) ) {
						$bSuccess = $oFiles->replaceFileFromVcs( $oItem->path_fragment );
					}
					else {
						$bSuccess = Services\Services::WpFs()->deleteFile( $oItem->path_full );
					}
				}
			}
			catch ( \InvalidArgumentException $oE ) {
			}
		}

		return $bSuccess;
	}
}