<?php // useful functions


function cleanAlpha($string){
	return preg_replace('/[^a-z0-9\_]/', '', strtolower($string));
}

function setFlash($msg, $type='ok'){
	$_SESSION['msg']=array(
		'content'=>$msg,
		'type'=>$type
	);
}

function renderFlash(){
	if(isset($_SESSION['msg'])){
		tag('div class="msg '.$_SESSION['msg']['type'].'"', $_SESSION['msg']['content']);
		unset($_SESSION['msg']);
	}
}

/* Messenger class */
class WebSession{

	function setMsg($content, $type='info'){
		$_SESSION['msg']=array('content'=>$content,'type'=>$type);	
	}
	
	function renderMsg(){
		if(isset($_SESSION['msg']) && $_SESSION['msg']!=''){
			$msg=$_SESSION['msg'];
			tag('section class='.$msg['type'],$msg['content']);
			unset($_SESSION['msg']);
		}
	}
	
	function restart($msg=null){
		session_destroy();
		session_start();
		if(isset($msg)){
			$this->setMsg($msg);
		}
	}

}


define('START_TIME', microtime(true)); 
define('BASE', getcwd());
include BASE.'/sys/core.php';

$db=$w->db;
$section=$w->section;

/* process post */
if($w->method=='post'){
	$path=ROOT.'/'.$section;
	if($w->access!='admin'){
		$w->setMsg('Access denied.');
		header("Location: $path/$unid");
		die();
	}
	$table=$w->config['tables'][$section];
	$unid=$w->request[1];
	if($unid=='new'){
		$names=array();
		$values=array();
		foreach($_POST as $key=>$value){
			$names[]=$key;
			$values[]="'".mysql_real_escape_string($value)."'";
		}
		$query='INSERT INTO '.$table.' ('.implode(', ', $names).') VALUES('.implode(', ', $values).');';
		$result=$db->query($query);
		$unid=$db->insert_id;
		$w->setMsg('Item has been created.');		
	}else{
		$cols=array();
		foreach($_POST as $key=>$value){
			$cols[]=$key."='".mysql_real_escape_string($value)."'";
		}
		$query="UPDATE $table SET ".implode(', ', $cols)." WHERE unid=$unid";
		$result=$db->query($query);
		if(!$result){
			$w->setMsg('Error updating item: '.mysqli_error($db));
		}else{
			$w->setMsg('Item has been updated.');		
		}
	}
	header('Location: '.ROOT.'/'.$section.'/'.$unid);
	die();
}

/* special actions */
if($w->action=='delete'){
	$table=$w->config['tables'][$section];
	$unid=$w->request[1];
	$return=ROOT.'/'.$section;
	
	if($w->access!='admin'){
		$w->setMsg('Access denied.');
		header("Location: $return/$unid");
		die();
	}
	$query="DELETE FROM $table WHERE unid=$unid LIMIT 1";
	$result=$db->query($query);
	if(!$result){
		$w->setMsg('Error deleting item: '.mysqli_error($db));
	}
	else{
		$w->setMsg('The item has been deleted.');	
	}
	header('Location: '.$return);
	die();
}

/* method=get: render the HTML page */
$page=loadClass('Html_page');
$data=array(
	'htitle'=>'Test site',
	'description'=>'The description',
	'path'=>ROOT
);
$page->start($data);
$page->renderMenu($w->config['topMenu'], $section, $w->access);

/* prepare data */
$data['db']=$db;
$data['section']=$section;
$data['unid']=$w->request[1];
$data['access']=$w->access;
$data['page']=$page;
if(isset($w->config['tables'][$section])){
	$data['table']=$w->config['tables'][$section];
	include BASE.'/sys/crud.php';
}

/* render the page */
out('<article>');
$w->renderMsg();
switch($section){
	case'':
		$result=$db->query('SELECT * FROM posts');
		$page->renderView('Home - Recent posts', $result, ROOT.'/post/');
		break;
	case'post':
	case'tune':
	case'tunebook':
	case'link':
	case'event':
		renderAllPages($data);
		break;
	case'user':
		if($w->access!='admin'){
			tag('p', 'Access denied.');
		}else{
			renderAllPages($data);
		}
		break;
	case'event':
	default:
		tag('p', 'Error: no rule for: '.$section);
}
out('</article>');
$page->end($data);


<?php
define('START_TIME', microtime(true)); 
define('BASE', getcwd());
include BASE.'/sys/core.php';
include BASE.'/sys/admin.php';

$topMenu=array(
	array('Dashboard', '', ''),
	array('Posts', 'post', 'post'),
	array('Tunes', 'tune', 'tune'),
	array('Tunebooks', 'collection', 'collection'),
	array('Links', 'link', 'link'),
	array('Users', 'user', 'user'),
	array('Media', 'media', 'media')
);

$page=new AdminPage();
$path=ROOT;
$here=$_GET['sub'];
$navIndex=getIndex($topMenu, $here);
$appTitle='Music Admin';
$title=$topMenu[$navIndex][0];

$data=array(
	'path'=>$path,
	'here'=>$here,
	'appTitle'=>$appTitle,
	'topMenu'=>$topMenu,
	'title'=>$title,
	'db'=>$w->db
);

$page->start($data);

switch($here){
	case'':
		tag('h1', $title);
		tag('p', 'Work in progress');
		break;
	case'media':
		tag('h1', $title);
		tag('p', 'Work in progress');
		break;

	default:
		$table=$here.'s';
		
		if(isset($_GET['unid'])){
			$unid=$_GET['unid'];
			$page->renderItem($data, $table, $unid);		
		}else{
			$page->renderView($data, $table);	
		}

}

$page->end($data);


<?php
defined('BASE') or die('Access denied.');


class Html_page{
	
	function __construct(){}
		
	function start($data){
		extract($data);
		echo <<<EOT
<!DOCTYPE html><html><meta charset=utf-8>
<title>$htitle</title>
<meta name=description content=$description>
<link rel=stylesheet href=$path/inc/default.css>
<body>
EOT;
	}

	function end($data){
		extract($data);
		$rt=number_format(microtime(TRUE)-START_TIME, 3);
		echo <<<EOT
<footer>Rendered in $rt sec.</footer>
<script src=$path/inc/core.js></script>
</body></html>
EOT;
	}

	function renderMenu($arr, $here, $acc='', $opt=''){
		out('<header>');
		tag('b', 'The Folk Brigade');
		out('<nav'.$opt.'><ul>');
		foreach($arr as $item){
			if($item['access']=='' || $item['access']==$acc){
				$on=($item['key']==$here)?' class=on':'';
				out('<li><a href='.ROOT.'/'.$item['key'].$on.'>'.$item['label'].'</a></li>');			
			}
		}
		out('</ul></nav>');
		out('</header>');
	}
	
	function renderView($title, $result, $path){
		tag('h1', $title);		
		if($result->num_rows==0){
			tag('p', 'No entries.');
			return;
		}
		out('<table>');
		echo('<tr>');
		$info=$result->fetch_fields();
		foreach ($info as $val) {
			echo('<th>'.$val->name.'</th>');	
		}
		out('</tr>');
		while($row=$result->fetch_object()){
			$link=$path.$row->unid;
			echo('<tr>');
			foreach($row as $key=>$value){
				echo('<td><a href='.$link.'>'.htmlspecialchars(truncate($value,200)).'</a></td>');					
			}
			out('</tr>');
		}
		out('</table>');
	}
	
	function renderItem($result, $path){
		$row=$result->fetch_object();
		if(isset($row->subject)){
			tag('h1', $row->subject);
		}else{
			tag('h1', 'Item #'.$row->unid);
		}
		$tmp=array();
		out('<table class=item>');
		$info=$result->fetch_fields();
		foreach ($info as $val) {
			$tmp[]=('<td>'.strLabel($val->name).': </td>');	
		}
		$i=0;
		foreach($row as $key=>$value){
			echo('<tr>');
			echo($tmp[$i]);
			$i++;
			echo('<td>'.htmlspecialchars($value).'</td>');	
			out('</tr>');			
		}

		out('</table>');
	}

}


<?php
defined('BASE') or die('Access denied.');

function getIndex($menu, $here){
	foreach ($menu as $i=>$value){
		if($value[2]==$here){
			return $i;
		}
	}
	return(-1);
}

/* pagination */
class Pagination{
	var $query,$total,$count,$numPages,$here, $db;
	
	function __construct($base, $db, $query, $start, $count=30){
		$this->base=$base;
		$this->db=$db;
		$this->query=$query;
		$result=$db->query($query);
		$this->total=$result->num_rows;
		$this->count=$count;
		$this->numPages=ceil($this->total/$this->count);
		if($start==0)$start=1;
		$this->here=$start;
	}
	
	function query(){
		$page=$this->here;
		$num=$this->count;
		$q=$this->query.' limit '.($page-1)*$num.','.$num;
		return $this->db->query($q);	
	}
	
	function link($num){
		return $this->base.'&amp;page='.$num;
	}
	
	function tag($num){
		if($num==$this->here)return "<span class=\"on\">$num</span>";
		else return '<a href='.$this->link($num).'>'.$num.'</a>';
	}
	
	function render(){
		$here=$this->here;
		$lastpage=$this->numPages;
		$adjacents=3;
		$aa=$adjacents*2;
		
		if($this->numPages>1){
			echo '<div class="pgn">';
			if($here>1){echo '<a href="'.$this->link($here-1).'" class="prev">« Previous</a>';}
			else{echo '<span class="prev">« Previous</span>';}
			
			if($lastpage<$aa+7){ //not enough pages to break it up
				for($i=1;$i<=$lastpage;$i++){echo $this->tag($i);}
			}
			else{
				if($here<$aa+1){ //close to beginning; only hide later pages
					for ($i=1;$i<4+$aa;$i++){echo $this->tag($i);}
					echo '<span class="break">...</span>'; 
					echo $this->tag($lastpage-1);
					echo $this->tag($lastpage);
				}
				elseif($lastpage-$aa>$here && $here>$aa){ //in middle; hide some front and some back
					echo $this->tag(1);
					echo $this->tag(2);
					echo '<span class="break">...</span>'; 
					for ($i=$here-$adjacents;$i<=$here+$adjacents;$i++){echo $this->tag($i);}
					echo '<span class="break">...</span>'; 
					echo $this->tag($lastpage-1);
					echo $this->tag($lastpage);
				}
				else{ //close to end; only hide earlier pages
					echo $this->tag(1);
					echo $this->tag(2);
					echo '<span class="break">...</span>'; 
					for($i=$lastpage-(2+$aa);$i<=$lastpage;$i++){echo $this->tag($i);}
				}
			}
					
			if($here<$this->numPages){echo '<a href="'.$this->link($here+1).'" class="next">Next »</a>';}
			else{echo '<span class="next">Next »</span>';}
			echo '</div>';
		}
	}
}

class AdminPage{
	
	function start($data){
		extract($data);
		echo <<<EOT
<!DOCTYPE html><html><meta charset=utf-8>
<title>$appTitle</title>
<link rel=stylesheet href=$path/inc/admin.css>
<body>
EOT;
		out("<header><b>$appTitle</b>");
		out('<nav>');
		foreach($topMenu as $item){
			$on=($item[2]==$here)?' class=on':'';
			out('<a href='.ROOT.'/mv_admin.php?sub='.$item[1].$on.'>'.$item[0].'</a>');
		}
		out('</nav>');
		out('</header>');
		out('<article>');
	}
	
	function renderView($data, $table){
		extract($data);
		$baseLink=ROOT.'/mv_admin.php?sub='.$here;
		out('<div class=act>');
		tag('a href='.$baseLink.'&amp;act=new', 'New '.$here);
		out('</div>');				
		tag('h1', $title);
		$query="SELECT * FROM $table";

		if(isset($_GET['page'])){
			$start=$_GET['page'];
		}else{
			$start=1;
		}
		$pgn=new Pagination($baseLink, $db, $query, $start, 5);
		$result=$pgn->query();
		if($result->num_rows==0){
			tag('p', 'No items found');
		}else{
			out('<table>');
			echo('<tr>');
			$info=$result->fetch_fields();
			foreach ($info as $val) {
				echo('<th>'.$val->name.'</th>');	
			}
			out('</tr>');
			while($row=$result->fetch_object()){
				$link=$baseLink.'&unid='.$row->unid;
				echo('<tr>');
				foreach($row as $key=>$value){
					echo('<td><a href='.$link.'>'.htmlspecialchars(truncate($value,200)).'</a></td>');					
				}
				out('</tr>');
			}
			out('</table>');
			$pgn->render();
		}
	}
	
	function renderItem($data, $table, $unid){
		extract($data);
		$baseLink=ROOT.'/mv_admin.php?sub='.$here;
		$query="SELECT * FROM $table WHERE unid=$unid";
		$result=$db->query($query);
		$row=$result->fetch_object();
		out('<div class=act>');
		tag('a href='.$baseLink.'&amp;unid='.$unid.'&amp;act=edit', 'Edit');
		tag("a href=\"javascript:J.ask('Delete this item?', '$baseLink&amp;unid=$unid&amp;act=delete');\"", 'Delete');
		tag('a href='.$baseLink, 'Close');
		out('</div>');	
		if(isset($row->subject)){
			tag('h1', $row->subject);
		}else{
			tag('h1', 'Item #'.$row->unid);
		}
		$tmp=array();
		out('<table class=item>');
		$info=$result->fetch_fields();
		foreach ($info as $val) {
			$tmp[]=('<td>'.strLabel($val->name).': </td>');	
		}
		$i=0;
		foreach($row as $key=>$value){
			echo('<tr>');
			echo($tmp[$i]);
			$i++;
			echo('<td>'.htmlspecialchars($value).'</td>');	
			out('</tr>');			
		}
		out('</table>');
	}
	
	function end($data){
		extract($data);
		$rt=number_format(microtime(TRUE)-START_TIME, 3);
		echo <<<EOT
</article>
<footer>Rendered in $rt sec.</footer>
<script src=$path/inc/core.js></script>
</body></html>
EOT;
	}

}


defined('BASE') or die('Access denied.');
include BASE.'/mv_config.php';

function out($val){
	echo $val."\n";
}
function tag($tag, $val){
	$tmp=explode(' ', $tag);
	$end=$tmp[0];
	out("<$tag>$val</$end>");
}
function debug($val){
	out('<pre>');
	out(var_dump($val));
	out('</pre>');
}
function loadClass($name, $data=NULL){
	$file=BASE.'/sys/classes/'.strtolower($name).'.php';
	if(file_exists($file)){
		require($file);
		return new $name($data);		
	}
}
function truncate($text, $limit){
   if(strlen($text)>$limit) $text=trim(substr($text,0,$limit)).'...'; 
    return $text;
}
function firstKey($arr){
	foreach($arr as $key => $value){
	 	return $key;
	 }
	 return'';	
}
function strLabel($val){
	return ucfirst(str_replace('_', ' ', $val));
}

class Web_session{
	
	function __construct($data){
		$this->config=$data;
		session_start();
		
		/* friendly url request parameters */		
		$url=parse_url($_SERVER['REQUEST_URI']);
		$this->request=explode('/', $url['path'].'//////////');
		array_shift($this->request);
		
		/* request method */
		$this->method=strtolower($_SERVER['REQUEST_METHOD']);
		$this->action=firstKey($_GET);
		list($this->section,$this->id)=$this->request;
		
		/* access level */
		$this->access='admin';
		$this->user='Michel Van der Meiren';
		
		$this->db=$this->getDb($this->config['db']);
	}

	function getDb($data){
		extract($data);
		return new mysqli($host,$user,$password,$db);
	}

	function setMsg($val){
		$_SESSION['msg']=$val;
	}
	
	function renderMsg(){
		if(isset($_SESSION['msg'])){
			$tmp=$_SESSION['msg'];
			tag('p class=msg', $tmp);
			
		}
		unset($_SESSION['msg']);
	}
}

class Form{

	function __construct(){}
	
	function start($action){
		out("<form method=post action=$action>");
	}
	
	function end($back){
		out('<input type=submit value="Submit">');
		out("<a href=$back>Cancel</a>");
		out('</form>');
	}
	
	function field($label, $name, $val='', $opt=''){
		out("<li><label for=f$name>$label</label><input name=$name id=f$name value=\"$val\" $opt></li>");
	}
	
	function area($label, $name, $val='', $opt=''){
		out("<li><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
	function rtfield($label, $name, $val='', $opt=''){
		$val=htmlspecialchars($val);
		out("<li class=rtf><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
}

$w=new Web_session($app_config);


defined('BASE') or die('Access denied.');

function renderAllPages($data){
	extract($data);
	switch($unid){
		case'':
			if($access=='admin'){
				out('<div class=act>');
				tag("a href=$path/$section/new", "New $section");
				out('</div>');
			}
			$result=$db->query("SELECT * FROM $table");
			$page->renderView("All {$section}s", $result, "$path/$section/");
			break;
			
		case 'new':
			if($access!='admin'){
				tag('p', 'Access denied.');
				exit;
			}
			tag('h1', "New $section");
			$result=$db->query("SELECT * FROM $table LIMIT 0");
			$info=$result->fetch_fields();
			$f=new Form();
			$f->start("$path/$section/new");
			out('<ul>');
			foreach($info as $val){
				$name=$val->name;
				switch($name){
					case'unid':
					case'date_created':
					case'date_modified':
						break;
					case'subject':
						$f->field(strLabel($name).':', $name, '', ' required autofocus');
						break;
					case'posted_date':
						$f->field(strLabel($name).':', $name, date('Y-m-d H:i:s',time()), ' type=datetime');
						break;
					case'description':
						$f->area(strLabel($name).':', $name);
						break;
					default:
						$f->field(strLabel($name).':', $name);
				}
			}
			out('</ul>');
			$f->end("$path/$section/");
			break;
			
		default:
			$result=$db->query("SELECT * FROM $table where unid=$unid");
			if(firstKey($_GET)=='edit'){
				if($access!='admin'){
					tag('p', 'Access denied.');
					return;
				}
				tag('h1', 'Edit');
				$info=$result->fetch_fields();
				$row=$result->fetch_array();
				$f=new Form();
				$f->start("$path/$section/$unid");
				out('<ul>');
				foreach($info as $val){
				$name=$val->name;
				$value=$row[$name];
				switch($name){
					case'unid':
						break;
					case'subject':
						$f->field(strLabel($name).':', $name, $value, ' required autofocus');
						break;
					case'description':
						$f->area(strLabel($name).':', $name, $value);
						break;
					case'body';
						$f->rtfield(strLabel($name).':', $name, $value);
						break;
					default:
						$f->field(strLabel($name).':', $name, $value);
				}
			}
				out('</ul>');
				$f->end("$path/$section/$unid");
			}else{
				if($access=='admin'){
					out('<div class=act>');
					tag("a href=$path/$section/$unid?edit", 'Edit');
					tag("a href=\"javascript:J.ask('Delete this item?','$path/$section/$unid?delete');\"", 'Delete');
					out('</div>');
				}
				$page->renderItem($result, "$path/$section/");					
			}
		}
}

defined('BASE') or die('Access denied.');

class Html{
	
	static function out($val){
		echo $val, PHP_EOL;
	}
	
	static function tag($tag, $val){
		$tmp=explode(' ', $tag);
		$end=$tmp[0];
		echo "<$tag>$val</$end>", PHP_EOL;
	}
	
	static function debug($val){
		echo '<pre>';
		var_dump($val);
		echo '</pre>', PHP_EOL;
	}
	
	static function stylesheet($val){
		echo '<link rel=stylesheet href=', $val ,'>', PHP_EOL;
	}
		
}

class Form{

	static function start($action){
		Html::out("<form method=post action=$action>");
	}
	
	static function end($back){
		Html::out('<div class=fact>');
		Html::out('<input type=submit value="Submit">');
		Html::out("<a href=$back>Cancel</a>");
		Html::out('</div>');
		Html::out('</form>');
	}
	
	static function field($label, $name, $val='', $opt=''){
		Html::out("<li><label for=f$name>$label</label><input name=$name id=f$name value=\"$val\" $opt></li>");
	}
	
	static function area($label, $name, $val='', $opt=''){
		Html::out("<li><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
	
	static function rtfield($label, $name, $val='', $opt=''){
		$val=htmlspecialchars($val);
		Html::out("<li class=rtf><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
}

class Page{
	static $data, $root;
	
	static function start($data){
		self::$data=$data;
		extract($data);
		self::$root=$root;
		Html::out('<!DOCTYPE html><html lang=en><meta charset=utf-8>');
		Html::tag('title', $title);
		Html::stylesheet($root.'/inc/default.css');
		Html::out('<header>');
		Html::tag('a href='.$root, $title);
		Html::out('</header>');
		self::nav($menu, $root, $here);
		Html::out('<div id=main>');
		Page::renderMsg();
	}
	
	static function end(){
		Html::out('</div>');
		Html::out('<footer>');
		Html::out('Rendered in '.Time::diff(START_TIME).' sec.');
		self::userInfo();
		Html::out('</footer>');
	}

	static function nav($menu, $root, $here){
		echo '<nav>', PHP_EOL;
		foreach($menu as $item){
			$on=$item['key']==$here?' class=on':'';
			echo '<a href='.$root.$item['link'].$on.'>'.$item['key'].'</a>', PHP_EOL;
		}
		echo '</nav>', PHP_EOL;
	}
	
	static function userInfo(){
		$user=self::$data['user'];
		$root=self::$root;
		if($user->isLoggedIn()){
			echo $user->name, ' ';
			Html::tag('a href='.$root.'?logout','Log out');
		}
		else{
			Html::tag('a href='.$root.'?login','Log in');
		}
	}
	
	static function renderMsg(){
		if(isset($_SESSION['msg'])){
			extract($_SESSION['msg']);
			Html::tag('div class='.$type, $val);
			unset($_SESSION['msg']);
		}
	}
	
	static function loginForm(){
		$root=self::$root;
		Html::tag('h1', 'Log in');
		Form::start($root.'/index.php?action=login');
		Html::out('<ul>');
		Form::field('User name: ', 'name', '', 'autofocus required');
		Form::field('Password: ','password', '', 'type=password required');
		Html::out('</ul>');
		Form::end($root);
	}
}

<?php
define('START_TIME', microtime(true)); 
define('BASE', getcwd());
include BASE.'/sys/core.php';

include BASE.'/mv_config.php';
session_start();

$root='/';
$title='Demo page';

$w=new WebSession();

$action=firstKey($_GET);
if($action=='logout'){
	$w->restart('You are logged out.');
	header('Location: '.$root);
	die('No redirection...');
}

if($_SERVER['REQUEST_METHOD']=='POST'){
	$action=htmlspecialchars($_POST['action']);
	switch($action){
		case'login':
			$user=htmlspecialchars($_POST['User']);
			$pass=htmlspecialchars($_POST['Pass']);
			if($user==$config['owner']['name'] && md5($pass)== $config['owner']['password']){
				$_SESSION['user']=$user;
				$_SESSION['access']='owner';
				$w->setMsg('You are logged in as '.$user.'.');
				
			} else{
				$w->setMsg('Error: invalid user.');
			}
			header('Location: '.$root);
			break;
		default:
			$w->setMsg('Error: no rule for post action '.$action);
			debug($_POST);
			header('Location: '.$root);	
	}
	die('No redirection...');
}

out('<!DOCTYPE HTML><html><meta charset=utf-8>');
tag('title', $title);
out('<link rel=stylesheet href='.$root.'inc/all.css>');

out('<body>');
out('<header>');
tag('a href='.$root,'Demo site');
out('</header>');

$w->renderMsg();

switch($action){
	case'login';
		tag('h1', 'Log in');
		out('<form method=post action="'.$root.'">');
		out('<input type=hidden name=action value=login>');
		out('<ul>');
		out('<li><label for=fUser>User:</label><input name=User id=fUser autofocus required></li>');
		out('<li><label for=fPass>Password:</label><input name=Pass id=fPass type=password required></li>');
		out('</ul>');
		out('<div class=act><input type=submit value=Submit></div>');
		
		out('</form>');
		break;
	default:
		tag('h1', $title);		
}

out('<footer>');
out('Rendered in '.number_format(microtime(TRUE)-START_TIME, 3).' seconds');
if(isset($_SESSION['user'])){
	echo $_SESSION['user'].' - ';
	tag('a href='.$root.'?logout', 'Log out');
}else{
	tag('a href='.$root.'?login', 'Log in');	
}

out('</footer>');
echo $trackingCode;
out('</body></html>');


<?php
define('START_TIME', microtime(true)); 
define('BASE', getcwd());
require(BASE.'/sys/core.php');
require(BASE.'/sys/admin_tools.php');

/* log out */
if(@$_GET['act']=='logout'){
	session_destroy();
	session_start();
	header('Location: '.ROOT.'admin.php');
	die();
}

/* action: post */
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(@$_GET['act']=='login'){
			$user=mysql_real_escape_string($_POST['user']);
			$pass=md5($_POST['pass']);
			if($user!=Config::$owner['name'] || $pass!=Config::$owner['password']){
				$_SESSION['message']=('Unable to log in.');
			}
			else{
				$_SESSION['user_name']=$user;
				$_SESSION['user_role']='admin';
				Session::$isAdmin=true;
				$_SESSION['message']='You are uccessfully logged in.';
			}
			header('Location: '.ROOT.'admin.php');
			die();
	}
	if(!Session::$isAdmin){
		die('Access denied.');
	}
	Html::debug($_POST);
	die('No redirect after post');
}


/* action: get */
require(BASE.'/sys/admin_template.php');

Admin::render();


<?php defined('BASE') or die('Access denied.');

class Form{

	static function start($action){
		Html::out("<form method=post action=$action>");
	}
	
	static function end($back){
		Html::out('<div class=fact>');
		Html::out('<input type=submit value="Submit">');
		Html::out("<a href=$back>Cancel</a>");
		Html::out('</div>');
		Html::out('</form>');
	}
	
	static function field($label, $name, $val='', $opt=''){
		Html::out("<li><label for=f$name>$label</label><input name=$name id=f$name value=\"$val\" $opt></li>");
	}
	
	static function area($label, $name, $val='', $opt=''){
		Html::out("<li><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
	
	static function rtfield($label, $name, $val='', $opt=''){
		$val=htmlspecialchars($val);
		Html::out("<li class=rtf><label for=f$name>$label</label><textarea name=$name id=f$name $opt>$val</textarea></li>");
	}
}

Class Admin{
	static $request;
	static $menu;
	
	static function init(){
		self::$request=array(
		'action'=>@$_GET['act'],
		'table'=>@$_GET['sub'],
		'unid'=>@$_GET['unid'],
		'page'=>@$_GET['page']			
		);
		$topmenu=array();
		$topmenu[]=array('label'=>'Dashboard', 'link'=>'', 'key'=>'');
		$result=Db::query('SHOW TABLES');
		while($row=$result->fetch_array()){
			$key=$row[0];
			$label=ucfirst($key);
			$topmenu[]=array('label'=>$label, 'link'=>'?sub='.$key, 'key'=>$key);
		}
		$topmenu[]=array('label'=>'Media', 'link'=>'?sub=media', 'key'=>'media');
		self::$menu=$topmenu;	
	}

	static function render(){
		if(isset($_SESSION['message'])){
			Page::$message=$_SESSION['message'];
			unset($_SESSION['message']);
		}
		Page::$topmenu=self::$menu;
		Page::$here=self::$request['table'];
		Page::start();
		if(Session::$isAdmin){
			extract(self::$request);
			switch($table){
				case null:
					self::renderDashboard();
					break;
				case 'media';
					require(BASE.'/sys/file_man.php');
					break;
				default:
					if($action=='new'){
						self::renderForm();
					}else if(isset($unid)){
						$result=Db::query('SELECT * FROM '.$table.' WHERE unid='.$unid);
						if($result->num_rows==1){
							if($action=='edit'){
								self::renderForm($result);
							}else{
								self::renderDetail($result);
							}
						}else{
							self::renderError('MySQL error', 'Item could not be found.');
						}
					}
					else{
						self::renderList($table);
					}
			}
		}else{
			Html::tag('h1', 'Admin log in');
			Form::start(ROOT.'admin.php?act=login');
			Html::out('<ul>');
			Form::field('User name: ', 'user', '', 'required');
			Form::field('Password: ', 'pass', '', 'type=password required');
			Html::out('</ul>');
			Form::end(ROOT);
		}
		Page::end();
	}

	static function renderForm($result=null){
		extract(self::$request);
		$base=ROOT.'admin.php?sub='.$table;
		if($result){
			$row=$result->fetch_assoc();
			if(isset($row['subject'])){
				Html::tag('h1', $row['subject']);
			}else{
				Html::tag('h1', 'Item #'.$unid);
			}
			Form::start("$base&amp;unid=$unid&amp;act=create");
			Html::out('<ul>');
			foreach($row as $key=>$val){
				$label=String::toLabel($key).': ';
				switch($key){
					case'unid':
					case'date_created':
					case'date_modified':
					case'hits':
						break;
					case'subject':
						Form::field($label, $key, $val, ' required autofocus');
						break;
					case'posted_date':
						Form::field($label, $key, date('Y-m-d H:i:s',time()), ' type=datetime');
						break;
					case'description':
						Form::area($label, $key, $val);
						break;
					case'body':
						Form::rtfield($label, $key, $val);
						break;
					default:
						Form::field($label, $key, $val);
				}
			}
			Html::out('</ul>');
			Form::end("$base&amp;unid=$unid");
		}else{
			Html::tag('h1', 'New '.$table.' item');
			$result=Db::query("SELECT * FROM $table LIMIT 0");
			$info=$result->fetch_fields();
			Form::start("$base&amp;act=create");
			Html::out('<ul>');
			foreach($info as $val){
				$name=$val->name;
				$label=String::toLabel($name).': ';
				switch($name){
					case'unid':
					case'date_created':
					case'date_modified':
						break;
					case'subject':
						Form::field($label, $name, '', ' required autofocus');
						break;
					case'posted_date':
						Form::field($label, $name, date('Y-m-d H:i:s',time()), ' type=datetime');
						break;
					case'description':
						Form::area($label, $name);
						break;
					case'body':
						Form::rtfield($label, $key);
						break;
					default:
						Form::field($label, $name);
				}
			}
			Html::out('</ul>');
			Form::end($base);	
		}
	}
	
	static function renderDetail($result){
		extract(self::$request);
		$row=$result->fetch_object();
		$base=ROOT.'admin.php?sub='.$table;
		Html::out('<div class="act clear">');
		Html::tag('a href='.$base.'&amp;act=edit&amp;unid='.$unid, 'Edit');
		Html::tag('a href='.Html::jsConfirm('Do you want to delete this item?',$base.'&amp;unid='.$unid.'&amp;act=edit'), 'Delete');
		Html::tag('a href='.$base, 'Close');
		Html::out('</div>');
		if(isset($row->subject)){
			Html::tag('h1', $row->subject);
		}else{
			Html::tag('h1', 'Item #'.$row->unid);
		}
		$tmp=array();
		Html::out('<table class=item>');
		$info=$result->fetch_fields();
		foreach ($info as $val) {
			$tmp[]=('<td>'.String::toLabel($val->name).': </td>');	
		}
		$i=0;
		foreach($row as $key=>$value){
			echo('<tr>');
			echo($tmp[$i]);
			$i++;
			echo('<td>'.htmlspecialchars($value).'</td>');	
			Html::out('</tr>');			
		}
		Html::out('</table>');		
	}

	static function renderError($title, $text){
		Html::tag('h1', $title);
		Html::tag('p', $text);
	}

	static function renderList($table){
		$base=ROOT.'admin.php?sub='.$table;
		Html::out('<div class="act clear"><a href='.$base.'&amp;act=new>New</a></div>');
		Html::tag('h1', ucfirst($table));
		$result=Db::query('SELECT * FROM '.$table);
		if($result->num_rows==0){
			Html::out('No items found.');
		}else{
			Html::out('<table>');
			echo('<tr>');
			$info=$result->fetch_fields();
			foreach ($info as $val) {
				echo('<th>'.$val->name.'</th>');	
			}
			Html::out('</tr>');
			while($row=$result->fetch_object()){
				$link=$base.'&amp;unid='.$row->unid;
				echo('<tr>');
				foreach($row as $key=>$value){
					echo('<td><a href='.$link.'>'.htmlspecialchars(String::truncate($value,200)).'</a></td>');					
				}
				Html::out('</tr>');
			}
			Html::out('</table>');
		}
	}
	
	static function renderDashboard(){
		Html::tag('h1', 'Dashboard');
		echo <<<EOT
<style>
#rtBody{
	outline:dashed 1px blue;
	padding:5px;
	margin:10px 0;
}
</style>
<div class=btns id=rtAct><b class=i-leaf title=Style></b><b class=bold title=Bold></b><b class=italic title=Italic></b><b class=justifyleft title=Left></b><b class=justifycenter title=Center></b><b class=justifyright title=Right></b><b class=insertunorderedlist title="Unordered list"></b><b class=insertorderedlist title="Ordered list"></b><b class=i-link title=Link></b><b class=i-picture title=Picture></b><b class=i-cog title=Extra></b><b class=i-list-alt title=Source></b></div>
<div id=rtBody contenteditable>
<h2>Header 2</h2>

<p class="dateline smaller"> 3 April 2007</p>

<h4 id="entry1196"><a href="http://radar.oreilly.com/archives/2007/03/call_for_a_blog_1.html" class="external">Call for a Blogger's Code of Conduct</a></h4>
<p>Tim O'Reilly calls for a Blogger Code of Conduct. His proposals are:</p>
<p>An extra paragraph with a <br>break for testing purposes.</p>
<ol>
<li>Take responsibility not just for your own words, but for the comments you allow on your blog.</li>
<li>Label your tolerance level for abusive comments.</li>
<li>Consider eliminating anonymous comments.</li>
<li>Ignore the trolls.</li>
<li>Take the conversation offline, and talk directly, or find an intermediary who can do so.</li>
<li>If you know someone who is behaving badly, tell them so.</li>
<li>Don't say anything online that you wouldn't say in person.</li>
</ol>

<p>I find 1 interesting; I never thought of responsibility for comments, but it makes excellent sense. To me, 2 is something I decide in private, because I find it hard to articulate my exact tolerance, and it depends on my mood anyway. I implement 4 by deleting trolls.</p>

<p class="smaller">(Via <a href="http://www.tbray.org/ongoing/" class="external">Tim Bray</a>.)</p>
<p class="smaller">Society</p>

</div>
<textarea id=fBody></textarea>
EOT;

	}

}
Admin::init();

$words = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
			'Comma-separated list of search stopwords in your language' ) );




class App{
	private $db;
	private $route;

	function run(){
		$this->db=db_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME );
  		$_GET=array_map( 'htmlspecialchars', $_GET );
		$_POST=array_map( 'htmlspecialchars', $_POST );
		$this->router();
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
		tag('a href="'.ROOT.'/'.$link.'"', '<svg><use xlink:href="'.ROOT.'/inc/icons.svg#'.$icon.'"></use></svg>'.$label);
	}

	function getSingular($val){
		return substr( $val, 0, -1 );
	}

	function getSubject($item){
		$fields=array('subject', 'name');
		foreach ($fields as $key){
			if(isset($item[$key])) return $item[$key];
		}
		return '#'.$item['id'];
	}

	function getNew( $table ){
		tag( 'h1', 'New '.$this->getSingular($table) );
		tag( 'p', '[under construction]' );
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

	function getItem( $table, $id){
		$item=$this->db->query( 'SELECT * FROM '.$table )->fetch( PDO::FETCH_ASSOC );
		out('<form>');
		tag( 'h1', ucfirst( $this->getSingular($table) ).': '.$this->getSubject( $item ) );
		out( '<ul>' );
		foreach ($item as $key => $value) {
			$this->renderField($key, $value);
		}
		out( '</ul>' );
		out('</form>');
	}


	function router(){
		$this->route=explode( '/', getArrayValue( $_GET, 'url' ).'////' );
		$here=$this->route[ 0 ];

		// no post? -> render admin or html page
		if($here=='admin'){

		}else{
			$page=new Html();
			$page->renderHead();
			$page->renderMessage('Html site view', '[under construction]');
			$page->renderFoot();
		}

		return;

		$page=new Html();
		$page->renderHead();
		$page->renderAside();


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
				$here2=$this->route[ 1 ];
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


