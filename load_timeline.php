<?php 
class load_timeline
{
	function load_home()
	{
		$timeline_json = file_exists('./data/home_j') ? file_get_contents('./data/home_j') : false;//無ければfalse
		$timeline_data = json_decode($timeline_json);
		return $timeline_data;
	}
}
?>