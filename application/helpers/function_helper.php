<?php

class Functions {

	public static function DateFormat( $value ) {
		return $value ? date_format( date_create( $value ), 'd/m/Y' ) : '';
	}

	public static function MoneyFormat( $value, $symbol = 'R$' ) {
		return $symbol . number_format( $value, 2, ',', '.' ); 
	}

	public static function FloatFormat( $value ) {
		return self::MoneyFormat( $value, '' ); 
	}	

	public static function IntegerFormat( $value ) {
		return number_format( $value, 0, ',', '.' ); 
	}	


}