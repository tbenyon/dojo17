<?php

namespace FernleafSystems\Wordpress\Plugin\Shield\Modules\IPs\BotTrack;

use FernleafSystems\Wordpress\Plugin\Shield;

abstract class Base {

	use Shield\AuditTrail\Auditor,
		Shield\Modules\ModConsumer;
	const OPT_KEY = '';

	public function run() {
		$this->process();
	}

	protected function doTransgression() {
		/** @var \ICWP_WPSF_FeatureHandler_Ips $oFO */
		$oFO = $this->getMod();

		if ( $oFO->isTrackOptImmediateBlock( static::OPT_KEY ) ) {
			$oFO->setIpBlocked();
		}
		else if ( $oFO->isTrackOptTransgression( static::OPT_KEY ) ) {
			$oFO->setIpTransgressed( $oFO->isTrackOptDoubleTransgression( static::OPT_KEY ) ? 2 : 1 );
		}

		$this->writeAudit();
	}

	/**
	 * @return $this
	 */
	protected function writeAudit() {
		$this->createNewAudit( 'wpsf', $this->getAuditMsg(), 2, 'bot'.static::OPT_KEY );
		return $this;
	}

	abstract protected function process();

	/**
	 * @return $this
	 */
	abstract protected function getAuditMsg();
}
