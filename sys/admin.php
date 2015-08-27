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
		$table=$this->route[ 1 ];
		switch( $this->route[ 2 ] ) {
			case'new':
				$arr=array();
				$arr2=array();
				$val=array();
				foreach( $_POST as $key => $value ){
					$arr[]=$key;
					$arr2[]=':'.$key;
					$val[':'.$key]=$value;
				}
				$q='INSERT INTO '.$table.' ('.join( $arr, ', ' ).') VALUES ('.join( $arr2, ', ' ).')';
				$this->query( $q, $val );
				$id=$this->db->lastInsertId();
				$this->setFlash( ucfirst( $this->getSingular( $table ) ).' #'.$id.' has been added.' );
				header('Location: '.ROOT.'/admin/'.$table.'/item/'.$id );
				break;
			case'item':
				$id=$this->route[ 3 ];
				$arr=array();
				$val=array( ':id'=>$id );
				foreach( $_POST as $key => $value ){
					$arr[]=$key.'=:'.$key;
					$val[':'.$key]=$value;
				}
				$this->query( 'UPDATE '.$table.' SET '.join( $arr, ', ' ).' WHERE id=:id', $val );
				$this->setFlash( ucfirst( $this->getSingular( $table ) ).' #'.$id.' has been updated.' );
				header('Location: '.ROOT.'/admin/'.$table.'/item/'.$id );
				break;
			case'delete':
				$id=$this->route[ 3 ];
				$this->query('DELETE FROM '.$table.' WHERE id=:id', array( ':id'=>$id ) );
				$this->setFlash( ucfirst( $this->getSingular( $table ) ).' #'.$id.' has been deleted.' );
				header('Location: '.ROOT.'/admin/'.$table );
				break;
			default: 
				$this->setFlash( 'No rule for POST '.$this->route[ 2 ], 'error' );
		}
	}

	function setFlash($msg, $type='ok'){
		$_SESSION['msg']=array(
			'content'=>$msg,
			'type'=>$type
		);
	}

	function renderFlash( $page ){
		if(isset($_SESSION['msg'])){
			$page->renderFlash($_SESSION['msg']);
			unset($_SESSION['msg']);
		}
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
		$this->renderFlash( $page );
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
					case 'delete':
						$id=$this->route[ 3 ];
						$q=$this->query('SELECT * FROM '.$table.' WHERE id=:id', array( ':id'=> $id ) );
						$item=$q->fetch( PDO::FETCH_ASSOC );
						$page->renderDelete( $this->getSingular( $table ), $item , $table);
						break;
					case'item':
						$id=$this->route[ 3 ];
						$page->addAction( 'Delete this '.$this->getSingular( $table ), $table.'/delete/'.$id, 'delete-icon' );
						$page->renderActions();
						$q=$this->query('SELECT * FROM '.$table.' WHERE id=:id', array( ':id'=> $id ) );
						$item=$q->fetch( PDO::FETCH_ASSOC );
						$page->renderItem( $this->getSingular( $table ), $item , $table);
						break;
					default:
						$page->renderMessage( 'Error', 'No action for: '.$here2 );
				}
		}
		$page->endArticle();
		$page->renderFoot();
	}
}

