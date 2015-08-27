<?php defined('BASE') or die('No access');

class Admin{
	private $route;
	private $db;

	function __construct( $route ){
		$this->route=$route;
		$this->db=db_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME );

		if($_SERVER['REQUEST_METHOD']=='POST'){
			$this->processPost();
		}else{
			$this->renderPage();
		}
	}

	function processPost(){
		$table=$this->route[1];
		$id=$this->route[3];
		$arr=array();
		$val=array( ':id'=>$id );
		foreach( $_POST as $key => $value ){
			$arr[]=$key.'=:'.$key;
			$val[':'.$key]=$value;
		}
		$this->query( 'UPDATE '.$table.' SET '.join( $arr, ', ' ).' WHERE id=:id', $val );

	}

	function getSingular($val){
		return substr( $val, 0, -1 );
	}

	function query($query, $params){
		$q=$this->db->prepare( $query );
		$q->execute( $params );
		return $q;
	}

	function renderPage(){
		$menu=$this->db->query( 'SHOW TABLES' )->fetchAll( PDO::FETCH_COLUMN, 0 );
		$here=$this->route[1];
		$page=new Admin_page;
		$page->renderHead();
		$page->renderAside( $menu, $here );
		$page->startArticle();
		switch($here){
			case'':
				$page->addAction( 'Add table', 'new', 'new-icon' );
				$page->renderActions();
				$page->renderMessage( 'Dashboard', '[under construction]' );
				break;
			case'new':
				$page->renderMessage( 'New table', '[under construction]' );
				break;
			default:
				$table=$here;
				$here2=$this->route[ 2 ];
				switch( $here2 ){
					case'':
						$page->addAction( 'Add '.$this->getSingular( $table ), $table.'/new', 'new-icon' );
						$page->addAction( 'Config ', $table.'/config', 'config-icon' );
						$page->renderActions();
						$list=$this->db->query( 'SELECT * FROM '.$table )->fetchAll( PDO::FETCH_ASSOC );
						$page->renderView($table, $list);
						break;
					case'config':
						$fields=$this->db->query( 'DESCRIBE '.$table )->fetchAll( PDO::FETCH_ASSOC );
						$page->renderTable( $table, $fields );
						break;
					case'new':
						$fields=$this->db->query( 'DESCRIBE '.$table )->fetchAll( PDO::FETCH_ASSOC );
						$page->renderNewItem( $this->getSingular($table), $fields, $table );
						break;
					case'item':
						$id=$this->route[ 3 ];
						$q=$this->query('SELECT * FROM '.$table.' WHERE id=:id', array( ':id'=> $id ) );
						$item=$q->fetch( PDO::FETCH_ASSOC );
						$type=$this->getSingular( $table );
						$page->renderItem( $type, $item , $table);
						break;
					default:
						$page->renderMessage( 'Error', 'No action for: '.$here2 );
				}
		}
		$page->endArticle();
		$page->renderFoot();
	}
}

