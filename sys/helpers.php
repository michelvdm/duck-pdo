<?php defined('BASE') or die('No access');

require( BASE.'/config.php' );

define( 'ROOT', dirname( $_SERVER[ 'PHP_SELF' ] ) );
date_default_timezone_set( TIMEZONE );
session_start();

spl_autoload_register( function( $class, $data=null ){
	$file=str_replace( '\\', '/', BASE.'/sys/'.strtolower( $class ).'.php' );
 	require_once( $file );
});

function debug( $val, $label='Debug' ){
	echo '<h2>', $label, '</h2>', PHP_EOL, '<pre>', PHP_EOL;
	var_dump( $val );
	echo '</pre>', PHP_EOL;
}

function tag( $name, $val='' ){
	echo '<', $name, '>', $val, '</', explode( ' ', $name )[0], '>', PHP_EOL;
}

function out($val=''){
	echo $val, PHP_EOL;	
}
