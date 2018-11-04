<?php

require_once( dirname( __FILE__ ).'/ICWP_WPSF_BaseEntryVO.php' );

/**
 * Class ICWP_WPSF_NoteVO
 * @property string note
 * @property string wp_username
 */
class ICWP_WPSF_NoteVO extends ICWP_WPSF_BaseEntryVO {

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @return int
	 */
	public function getUsername() {
		return $this->wp_username;
	}
}