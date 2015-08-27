<?php defined('BASE') or die('No access');

class Admin{
	private $route;
	private $db;

	function __construct( $route ){
		$this->route=$route;
		$this->db=db_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME );
		$this->renderPage();
	}

	function getSingular($val){
		return substr( $val, 0, -1 );
	}

	function renderContent( $here, $page ){
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
						$page->addAction('Add '.$this->getSingular( $table ), $table.'/new', 'new-icon');
						$page->renderActions();
						$list=$this->db->query( 'SELECT * FROM '.$table )->fetchAll( PDO::FETCH_ASSOC );
						$page->renderView($table, $list);
						break;
					case'new':
							$page->renderMessage('New '.$this->getSingular($table), '[under construction]' );
						break;
					case'item':
						$id=$this->route[ 3 ];
						$item=$this->db->query( 'SELECT * FROM '.$table )->fetch( PDO::FETCH_ASSOC );
						$type=$this->getSingular( $table );
						$page->renderItem( $type, $item );
						break;
					default:
						$page->renderMessage( 'Error', 'No action for: '.$here2 );
				}
		}
		$page->endArticle();
	}

	function renderPage(){
		$menu=$this->db->query( 'SHOW TABLES' )->fetchAll( PDO::FETCH_COLUMN, 0 );
		$here=$this->route[1];

		$page=new Admin_page;
		$page->renderHead();
		$page->renderAside( $menu, $here );
		$this->renderContent( $here, $page );
		$page->renderFoot();
	}

}

/*





	

	

	

	


	
	
	
}
*/