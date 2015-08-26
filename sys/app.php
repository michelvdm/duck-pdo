<?php defined('BASE') or die('No access');

class App{
	public $db;
	public $route;

	function run(){
		$this->db_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$this->router();
	}

	function db_connect($host, $user, $pass, $name){
		try{
			$this->db=new PDO("mysql:host=$host;dbname=$name", $user, $pass);
			$this->db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch( PDOException $e ){
			echo $e->getMessage();
		}
	}

	function getNav($here){
		$menu=$this->db->query( 'SHOW TABLES' )->fetchAll( PDO::FETCH_COLUMN, 0 );
		out( '<ul>' );
		tag('li', '<a href="'.ROOT.'/"'.( ($here=='')?' class="on"':'' ).'>Dashboard</a>' );
		foreach ($menu as $value){
			tag('li', '<a href="'.ROOT.'/'.$value.'"'.( ($here==$value)?' class="on"':'' ).'>'.ucfirst( $value ).'</a>' );
		}
		out( '</ul>' );
	}

	function getValue($val, $type){
		switch( $type ){
			case'password':
				return '*****';
				break;
			default:
				return $val;
		}
	}

	function getView($table){
		out( '<div class="act">' );
			$this->getActionLink('Add '.$this->getSingular( $table ), $table.'/new', 'new-icon');
			out( '</div>' );
		tag('h1', ucfirst( $table ) );
		$list=$this->db->query( 'SELECT * FROM '.$table )->fetchAll( PDO::FETCH_ASSOC );
		if(count($list)>0){
			out( '<table class="view">' );
			out(' <tr>' );
			foreach( $list[0] as $key=>$value ) tag( 'th', $key );
			out(' </tr>' );
			foreach( $list as $item ){
				out(' <tr> ');
				$link=ROOT.'/'.$table.'/item/'.$item['id'];
				foreach( $item as $key=>$value ) tag( 'td', '<a href="'.$link.'">'.$this->getValue( $value, $key ).'</a>' );
				out(' </tr>' );
			}
			out( '</table>' );
		}else{
			tag('p', 'No items found.');
		}
	}

	function getActionLink( $label, $link, $icon ){
		$path=ROOT;
		tag('a href="'.ROOT.'/'.$link.'"', '<svg><use xlink:href="'.ROOT.'/inc/icons.svg#'.$icon.'"></use></svg>'.$label);
	}

	function getSingular($val){
		return substr( $val, 0, -1 );
	}

	function getSubject($item){
		debug($item);
		return isset( $item['subject'] )?$item['subject']:isset( $item['name'] ) ?$item['name']:'#'.$item['id'];
	}

	function getNew( $table ){
		tag( 'h1', 'New '.$this->getSingular($table) );
		tag( 'p', '[under construction]' );
	}

	function getItem( $table, $id){
		$item=$this->db->query( 'SELECT * FROM '.$table )->fetch( PDO::FETCH_ASSOC );
		tag( 'h1', ucfirst( $this->getSingular($table) ).': '.$this->getSubject( $item ) );

	}

	function router(){
		$act=isset( $_GET[ 'url' ] )?htmlspecialchars( $_GET[ 'url' ] ):'';
		$this->route=explode( '/', $act.'////' );
		$here=$this->route[0];

		require( __DIR__.'/html_header.php' );
		out('<aside>');
		$this->getNav($here);
		out('</aside>');
		out('<article>');
		switch($here){
			case'':
				out( '<div class="act">' );
				$this->getActionLink('Add table', 'new', 'new-icon');
				out( '</div>' );
				tag( 'h1', 'Dashboard' );
				tag( 'p', '[under construction]' );
				break;
			case'new':
				tag( 'h1', 'New table' );
				tag( 'p', '[under construction]' );
				break;
			default:
				$here2=$this->route[1];
				switch( $here2 ){
					case'':
						$this->getView( $here );
						break;
					case'new':
						$this->getNew( $here );
						break;
					case'item':
						$this->getItem($here, $this->route[2]);
						break;
					default:
						tag('h1', 'Error');
						tag('p', 'No action for: '.$here2);
				}
				
		}
	out('</article>');
		require( __DIR__.'/html_footer.php' );
	}

}
