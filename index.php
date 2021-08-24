<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php
if (get_browser(null)->platform === 'Android' or get_browser(null)->platform === 'iOS'){
	echo '<link rel="stylesheet" type="text/css" href="./css/phone.css">';
}else{
	echo '<link rel="stylesheet" type="text/css" href="./css/def.css">';
}
?>
<title>null_TimeLine</title>
</head>
<body>
<header>
<?php
require './get_timeline.php';
require './load_timeline.php';
require './display.php';

$since_id = file_exists('./data/since_home_j') ? file_get_contents('./data/since_home_j') : false;//無ければfalse
$line = (isset ($_POST['action'])) ? (int)$_POST['action'] : 0;

if (isset($_POST['reflesh'])){
	$get_home_timeline = new get_home_timeline;
	if($_POST['reflesh'] === 'full' or $since_id === false){
		$line = $get_home_timeline->get_timeline();//全更新
	}else{
		$line = $get_home_timeline->get_timeline('',$since_id);//新着更新
	}
}
?>

<form action="./#last" method="POST">
	<button class="u_button" type="submit" name="reflesh" value="new">新着更新</button>
	<button class="u_button" type="submit" name="reflesh" value="full">全更新</button>
	<small>last:
<?php
if (file_exists('./data/home_j')){
	echo date('Y/m/d H:i:s', filemtime('./data/home_j'));
}else{
	echo 'none.';
}
?>
</small></form>
</header>
<div id="start"></div>
<div id="main">
<?php
$load_timeline = new load_timeline;
$timeline = $load_timeline->load_home();

$since_id_old = file_exists('./data/since_home_j_old') ? file_get_contents('./data/since_home_j_old') : false;//無ければfalse
$display = new display_timeline();
$disp_last = $display->timeline($line,$timeline,$since_id_old);

if (! $disp_last){
	echo '<div id="last"></div>';
}
?>
</div>
<div id="end"></div>
<?php
$footer_last = new footer();
echo '<footer>';
echo '<form action="./#end" method="post">';
if($line <> 0){
	echo '<button class="u_button" type="submit" name="action" value="0">1～200</button>';
}else{
	echo '<strong><a href ="#start">　1</a>　';
	echo $footer_last->footer_last($disp_last);
	echo '　<a href ="#end">200</a></strong>';
}
echo ' / ';

if($line <> 200){
	echo '<button class="u_button" type="submit" name="action" value="200">201～400</button>';
}else{
	echo '<strong><a href ="#start">201</a>　';
	echo $footer_last->footer_last($disp_last);
	echo '　<a href ="#end">400</a></strong>';
}
echo ' / ';

if($line <> 400){
	echo '<button class="u_button" type="submit" name="action" value="400">401～600</button>';
}else{
	echo '<strong><a href ="#start">401</a>　';
	echo $footer_last->footer_last($disp_last);
	echo '　<a href ="#end">600</a></strong>';
}
echo ' / ';

if($line <> 600){
	echo '<button class="u_button" type="submit" name="action" value="600">601～800</button>';
}else{
	echo '<strong><a href ="#start">601</a>　';
	echo $footer_last->footer_last($disp_last);
	echo '　<a href ="#end">800</a></strong>';
}
echo '</form></footer>';
?>
</body>
</html>