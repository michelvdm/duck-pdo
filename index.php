<?php // index.php
define( 'START_TIME', microtime( true ) );
define( 'BASE', __DIR__ );
require( BASE.'/sys/helpers.php' );
call_user_func( array( new App, 'run' ) );

