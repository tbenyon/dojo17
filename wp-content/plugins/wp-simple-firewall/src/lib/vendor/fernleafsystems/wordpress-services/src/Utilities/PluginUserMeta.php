<?php

namespace FernleafSystems\Wordpress\Services\Utilities;

use FernleafSystems\Utilities\Data\Adapter\StdClassAdapter;
use FernleafSystems\Wordpress\Services\Services;

/**
 * Class PluginUserMeta
 * @property string $prefix
 * @property int    $user_id
 * @property string $flash_msg
 */
class PluginUserMeta {

	use StdClassAdapter {
		__set as __adapterSet;
		__unset as __adapterUnset;
	}

	/**
	 * @var PluginUserMeta[]
	 */
	static private $aMetas;

	/**
	 * @param string $sPrefix
	 * @param int    $nUserId
	 * @return PluginUserMeta
	 * @throws \Exception
	 */
	static public function Load( $sPrefix, $nUserId = 0 ) {
		if ( !is_array( self::$aMetas ) ) {
			self::$aMetas = array();
		}
		if ( empty( $nUserId ) ) {
			$nUserId = Services::WpUsers()->getCurrentWpUserId();
		}
		if ( empty( $nUserId ) ) {
			throw new \Exception( 'Attempting to get meta of non-logged in user.' );
		}

		if ( !isset( self::$aMetas[ $nUserId ] ) ) {
			self::$aMetas[ $nUserId ] = new static( $sPrefix, $nUserId );
		}

		return self::$aMetas[ $nUserId ];
	}

	/**
	 * @param string $sPrefix
	 * @param int    $nUserId
	 */
	public function __construct( $sPrefix, $nUserId = 0 ) {
		$this->init( $sPrefix, $nUserId );
		add_action( 'shutdown', array( $this, 'save' ) );
	}

	/**
	 * Cannot use Data store (__get()) yet
	 * @param int $sPrefix
	 * @param int $nUserId
	 * @return $this
	 */
	private function init( $sPrefix, $nUserId ) {
		$aStore = Services::WpUsers()->getUserMeta( $sPrefix.'-meta', $nUserId );
		if ( !is_array( $aStore ) ) {
			$aStore = array();
		}
		$this->applyFromArray( $aStore );
		$this->prefix = $sPrefix;
		$this->user_id = $nUserId;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function delete() {
		if ( $this->user_id > 0 ) {
			Services::WpUsers()->deleteUserMeta( $this->getStorageKey(), $this->user_id );
			remove_action( 'shutdown', array( $this, 'save' ) );
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function save() {
		if ( $this->user_id > 0 ) {
			Services::WpUsers()->updateUserMeta(
				$this->getStorageKey(), $this->getRawDataAsArray(), $this->user_id );
		}
		return $this;
	}

	/**
	 * @param string $sKey
	 * @param mixed  $mValue
	 * @return $this
	 */
	public function __set( $sKey, $mValue ) {
		return $this->__adapterSet( $sKey, $mValue )->save();
	}

	/**
	 * @param string $sKey
	 * @return $this
	 */
	public function __unset( $sKey ) {
		return $this->__adapterUnset( $sKey )->save();
	}

	/**
	 * @return string
	 */
	private function getStorageKey() {
		return $this->prefix.'-meta';
	}
}