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

$user_screen_name = (isset ($_GET['user'])) ? $_GET['user'] : '';
$max_id = (isset ($_GET['id'])) ? $_GET['id'] : '';

$get_user_timeline = new get_onetime_user_timeline;
$timeline = $get_user_timeline->get_onetime_timeline( $user_screen_name, $max_id);

$line = 0;//display_timeline渡し用

echo '<form action="./user.php?user='.$user_screen_name.'" method="POST">
	<button class="u_button" type="submit" name="reflesh" value="new">更新</button>';
?>
</form>
</header>
<div id="start"></div>
<div id="main">
<?php
$display = new display_timeline();
$display->timeline($line,$timeline);
?>

</div>
<div id="end"></div>

</body>
</html>