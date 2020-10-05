<?php

class EmptyObject {
	
	private $_msg = array();
	
	public function __construct() {
		$msg = 'このクラスは DefineSystem で定義された基礎クラスです。';
		
		$this->setMsg($msg);
	}
	
	public function setMsg($msg) {
		$this->_msg[] = $msg;
	}
	
	public function getMsg() {
		return $this->_msg;
	}
}
