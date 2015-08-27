<?php defined('BASE') or die('No access');

class Admin_page{
	private $actions=Array();

	function getSubject($item){
		$fields=array('subject', 'name');
		foreach ($fields as $key){
			if(isset($item[$key])) return $item[$key];
		}
		return '#'.$item['id'];
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

	function renderHead(){
		require( __DIR__.'/html_header.php' );
	}

	function renderActions(){
		if(count($this->actions)>0){
			out( '<div class="act">' );
			echo implode( $this->actions, PHP_EOL ), PHP_EOL;
			out( '</div>' );
		}
	}

	function renderMessage( $title, $text ){
		tag( 'h1', $title );
		tag( 'p', $text );
	}

	function renderView( $table, $list ){
		tag( 'h1', ucfirst( $table ) );
		if(count($list)>0){
			out( '<table class="view">' );
			out(' <tr>' );
			foreach( $list[0] as $key=>$value ) tag( 'th', $key );
			out(' </tr>' );
			foreach( $list as $item ){
				out(' <tr> ');
				$link=ROOT.'/admin/'.$table.'/item/'.$item['id'];
				foreach( $item as $key=>$value ) tag( 'td', '<a href="'.$link.'">'.$this->getValue( $value, $key ).'</a>' );
				out(' </tr>' );
			}
			out( '</table>' );
		}else{
			tag('p', 'No items found.');
		}
	}

	function renderField($key, $value){
		switch($key){
			case 'id':
				tag('li', '<span class="label">'.ucfirst($key).': </span>'.$value);	
				break;
			case 'description':
				tag('li', '<label for="f'.$key.'">'.ucfirst($key).': </label><textarea name="'.$key.'" id="f'.$key.'">'.$value.'</textarea>');	
				break;
			default: 
				tag('li', '<label for="f'.$key.'">'.ucfirst($key).': </label><input name="'.$key.'" id="f'.$key.'" value="'.$value.'">');	
		}
	}

	function renderItem( $type, $item ){
		out('<form>');
		tag( 'h1', ucfirst( $type ).': '.$this->getSubject( $item ) );
		out( '<ul>' );
		foreach ($item as $key => $value) {
			$this->renderField($key, $value);
		}
		out( '</ul>' );
		out('</form>');
	}

	function renderFoot(){
		require( __DIR__.'/html_footer.php' );
	}

	function renderAside( $menu, $here ){
		out('<aside>');
		out( '<ul>' );
		tag('li', '<a href="'.ROOT.'/admin/"'.( ($here=='')?' class="on"':'' ).'>Dashboard</a>' );
		foreach( $menu as $value ){
			tag('li', '<a href="'.ROOT.'/admin/'.$value.'"'.( ($here==$value)?' class="on"':'' ).'>'.ucfirst( $value ).'</a>' );
		}
		out( '</ul>' );
		out('</aside>');
	}

	function addAction( $label, $link, $icon ){
		$this->actions[]='<a href="'.ROOT.'/admin/'.$link.'"><svg><use xlink:href="'.ROOT.'/inc/icons.svg#'.$icon.'"></use></svg>'.$label.'</a>';
	}

	function startArticle(){
		out('<article>');
	}

	function endArticle(){
		out('</article>');
	}

}
