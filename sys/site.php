<?php defined('BASE') or die('No access');

class Site{
	private $route;

	function __construct( $route ) {
		$this->route=$route;

		require( __DIR__.'/html_header.php' );
		tag( 'h1', 'Site' );
		tag( 'p', '[ under construction ]' );
		require( __DIR__.'/html_footer.php' );
	}

}

