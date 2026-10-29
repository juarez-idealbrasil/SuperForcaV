<?php
/**
 * Define uma classe de exceção
 */
class CustomException extends Exception {

	private $extendedMessage;

   	public function __construct($message, $extendedMessage = null, $code = 0, Exception $previous = null) {    
    	parent::__construct($message, $code, $previous);
    	$this->extendedMessage = $extendedMessage;
    }	

	public function getExtendedMessage() {
        return $this->extendedMessage;
    }    

}