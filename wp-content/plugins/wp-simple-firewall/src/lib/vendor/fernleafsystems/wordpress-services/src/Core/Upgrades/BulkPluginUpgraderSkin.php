<?php

namespace FernleafSystems\Wordpress\Services\Core\Upgrades;

require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
require_once( ABSPATH.'wp-admin/includes/class-wp-upgrader.php' );

class BulkPluginUpgraderSkin extends \Bulk_Plugin_Upgrader_Skin {

	/**
	 * @var array
	 */
	public $aErrors;

	/**
	 * @var array
	 */
	public $aFeedback;

	/**
	 * BulkPluginUpgraderSkin constructor.
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array_merge( $args, compact( 'nonce', 'url' ) ) );
		$this->aErrors = array();
		$this->aFeedback = array();
	}

	/**
	 * @param string|array|\WP_Error $errors
	 */
	function error( $errors ) {
		$this->aErrors[] = $errors;

		if ( is_string( $errors ) ) {
			$this->feedback( $errors );
		}
		else if ( is_wp_error( $errors ) && $errors->get_error_code() ) {
			foreach ( $errors->get_error_messages() as $message ) {
				if ( $errors->get_error_data() ) {
					$this->feedback( $message.' '.$errors->get_error_data() );
				}
				else {
					$this->feedback( $message );
				}
			}
		}
	}

	/**
	 * @param string $string
	 */
	function feedback( $string ) {
		if ( isset( $this->upgrader->strings[ $string ] ) ) {
			$string = $this->upgrader->strings[ $string ];
		}

		if ( strpos( $string, '%' ) !== false ) {
			$args = func_get_args();
			$args = array_splice( $args, 1 );
			if ( !empty( $args ) ) {
				$string = vsprintf( $string, $args );
			}
		}
		if ( empty( $string ) ) {
			return;
		}
		$this->aFeedback[] = $string;
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return is_array( $this->aErrors ) ? $this->aErrors : array();
	}

	/**
	 * @return array
	 */
	public function getFeedback() {
		return $this->aFeedback;
	}

	function before( $title = '' ) {
	}

	function after( $title = '' ) {
	}

	function flush_output() {
	}
	/*
	function footer() {
		var_dump(debug_backtrace());
		die( 'testing' );
	}
	*/
}