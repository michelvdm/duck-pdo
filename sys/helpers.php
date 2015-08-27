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

function getArrayValue($arr, $key, $default=''){
	return isset( $arr[ $key ] )?$arr[ $key ]:$default;
}

function db_connect($host, $user, $pass, $name){
	try{
		$db=new PDO("mysql:host=$host;dbname=$name", $user, $pass);
		$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $db;
	}catch( PDOException $e ){
		echo $e->getMessage();
		return null;
	}
}

call_user_func(function(){
	$_GET=array_map( 'htmlspecialchars', $_GET );
	$_POST=array_map( 'htmlspecialchars', $_POST );
	$route=explode( '/', getArrayValue( $_GET, 'url' ).'////' );
	$app=($route[0]=='admin')?'Admin':'Site';
	( new $app( $route ) );
});
