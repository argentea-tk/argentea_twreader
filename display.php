<?php 
class display_timeline
{
	function file_type($url)//pdf検出用
	{
		$filetype = substr(strrchr( $url, '.'),1);
		switch ($filetype){
			case 'pdf':
				break;
			default:
				$filetype = '';
		}
		return $filetype;
	}

	function timeline( $line, $timeline, $since_id_old ='')
	{
		$disp_last = 0;
		for ($i = $line; $i < $line + 200; $i++)
		{
			if (! isset($timeline[$i])){
				echo '<p>[EOF]</p>';
				break;//データがなけりゃ終了
			}

			if ($since_id_old <> '' && $since_id_old >= $timeline[$i]->id_str && $disp_last === 0){
				$disp_last = 1;
				if ($i <> $line || $since_id_old === $timeline[$i]->id_str){
					echo '<div id="last"></div><p>新着:'.$i.'件</p><hr>';
				}
			}//200,400,600件目に新着が来つつsince_idのツイートが消えてると表示されないけど今のとこ仕様

			if (isset($timeline[$i]->retweeted_status->full_text))
			{
				$this->retweet($timeline[$i]);
			}else{
				$this->tweet($timeline[$i]);
			}
			echo '<div class="under"></div><hr>';
		}
		return $disp_last;//#lastの有無
	}
	
	function tweet($timeline)
	{
		//tweet表示
		$icon = $timeline->user->profile_image_url_https;
		$name = $timeline->user->name;
		$scr_name = $timeline->user->screen_name;
		$id_str = $timeline->id_str;
		$user_url = 'https://twitter.com/'.$scr_name.'/status/'.$id_str;
		$text = nl2br($timeline->full_text, false);
		//if (isset($timeline->extended_entities->media[0]->url)){
			//$text = mb_substr( $text, 0, -24);//メディアあれば後ろ23+1文字消去-URLが最後とは限らないので取りやめ
		//}
		$time = $timeline->created_at;
		$retweet = $timeline->retweeted;
		$count_rt = $timeline->retweet_count;
		$fav = $timeline->favorited;
		$count_fav = $timeline->favorite_count;
		if (isset($timeline->entities->urls[0]->expanded_url)){//URL取得
			foreach ($timeline->entities->urls as $url_key => $url_value){
				$url[$url_key] = $url_value->expanded_url;
				if (substr($url[$url_key],0,20) === 'https://twitter.com/' or substr($url[$url_key],0,27) === 'https://mobile.twitter.com/'){//個別ツイートのreader用変換
					$twurl_conv = strripos($url[$url_key],'/status/');
					if ($twurl_conv !== false){
						$twid = mb_substr($url[$url_key], $twurl_conv + 8);
						$twid_garbage = strripos($twid,'?s=');
						if ($twid_garbage !== false){
							$twid = mb_substr($twid,0,$twid_garbage);
						}
						$url[$url_key] =  './tweet.php?id='.$twid;
					}
				}
				$filetype = $this->file_type($url[$url_key]);
				if ($filetype){
					$disp_url[$url_key] = $filetype;
				}
				else{
					$disp_url[$url_key] = $url_value->display_url;
				}
			}
		}
		if (isset($timeline->extended_entities->media[0]->media_url_https)){//メディア取得
			$media_type = $timeline->extended_entities->media[0]->type;
			if ($media_type ==='video'){
				foreach ($timeline->extended_entities->media[0]->video_info->variants as $variants_key => $variants_val){
					if (isset ($variants_val->bitrate)){
						$media[0] = $variants_val->url;
						if ($variants_val->bitrate === 832000){//仮設定:ビットレート832000(他320000/2176000)
							break;
						}
					}
				}
			}elseif ($media_type ==='animated_gif'){
				$media[0] = $timeline->extended_entities->media[0]->video_info->variants[0]->url;
			}
			else{
				foreach( $timeline->extended_entities->media as $media_key => $media_val){
					$media[$media_key] = $media_val->media_url_https;
				}
			}
		}
		echo '<img src="'.$icon.'"><p class="text"><strong><a href="'.$user_url.'" target="_blank">'.$name.'</a></strong><small> : <a href="./user.php?user='.$scr_name.'&id='.$id_str.'" target="_blank">'.$scr_name.'</a> : <a href ="./tweet.php?id='.$id_str.'" target="_blank">'.date('Y/m/d H:i:s', strtotime($time)).'</a></small> RT';

		if ($retweet){
			echo '●';
		}else{
			echo '○';
		}

		echo ':'.$count_rt.' ';
		
		if ($fav){
			echo '★:';
		}else{
			echo '☆:';
		}
		
		echo $count_fav.'<br>'.$text;
		
		if (isset($url)){//URLあれば表示
			foreach ($url as $url_key => $url_value){
				echo ' <a href="'.$url[$url_key].'" target="_blank">'.$disp_url[$url_key].'</a>';
			}
		}
		
		if (isset($media)){//メディアあれば表示
			echo '<br>';
			foreach ($media as $media_key => $media_val){
				echo ' <a href="'.$media_val.'" target="_blank">'.$media_type.($media_key + 1).'</a>';
			}
		}
		echo '</p>';
	}
	
	function retweet($timeline)
	{
		//retweet表示
		$rt_icon = $timeline->retweeted_status->user->profile_image_url_https;
		$rt_name = $timeline->retweeted_status->user->name;
		$scr_name =$timeline->user->screen_name;
		$rt_scr_name = $timeline->retweeted_status->user->screen_name;
		$id_str = $timeline->id_str;//since_id用
		$user_url = 'https://twitter.com/'.$scr_name.'/status/'.$id_str;
		$rt_id_str = $timeline->retweeted_status->id_str;
		$name = $timeline->user->name;
		$rt_text = nl2br($timeline->retweeted_status->full_text, false);
		//if (isset($timeline->retweeted_status->extended_entities->media[0]->url)){
		//	$rt_text = mb_substr( $rt_text, 0, -24);//メディアあれば後ろ23+1文字消去-URLが最後とは限らないので取りやめ
		//}
		$rt_time = $timeline->retweeted_status->created_at;
		$rt_retweet = $timeline->retweeted_status->retweeted;
		$rt_count_rt = $timeline->retweeted_status->retweet_count;
		$rt_fav = $timeline->retweeted_status->favorited;
		$rt_count_fav = $timeline->retweeted_status->favorite_count;
		if (isset($timeline->retweeted_status->entities->urls[0]->expanded_url)){//URL取得
			foreach ($timeline->retweeted_status->entities->urls as $rt_url_key => $rt_url_value){
				$rt_url[$rt_url_key] = $rt_url_value->expanded_url;
				if (substr($rt_url[$rt_url_key],0,20) === 'https://twitter.com/' or substr($rt_url[$rt_url_key],0,27) === 'https://mobile.twitter.com/'){//個別ツイートのreader用変換
					$rt_twurl_conv = strripos($rt_url[$rt_url_key],'/status/');
					if ($rt_twurl_conv !== false){
						$rt_twid = mb_substr($rt_url[$rt_url_key], $rt_twurl_conv + 8);
						$rt_twid_garbage = strripos($rt_twid,'?s=');
						if ($rt_twid_garbage !== false){
							$rt_twid = mb_substr($rt_twid,0,$rt_twid_garbage);
						}
						$rt_url[$rt_url_key] =  './tweet.php?id='.$rt_twid;
					}
				}
				$filetype = $this->file_type($rt_url[$rt_url_key]);
				if ($filetype){
					$rt_disp_url[$rt_url_key] = $filetype;
				}
				else{
				$rt_disp_url[$rt_url_key] =$rt_url_value->display_url;
				}
			}
		}
		
		if (isset($timeline->retweeted_status->extended_entities->media[0]->media_url_https)){//メディア取得
			$rt_media_type = $timeline->retweeted_status->extended_entities->media[0]->type;
			if ($rt_media_type ==='video'){
				foreach ($timeline->retweeted_status->extended_entities->media[0]->video_info->variants as $variants_key => $variants_val){
					if (isset ($variants_val->bitrate)){
						$rt_media[0] = $variants_val->url;
						if ($variants_val->bitrate === 832000){//仮設定:ビットレート832000(他320000/2176000)
							break;
						}
					}
				}
			}elseif ($rt_media_type ==='animated_gif'){
				$rt_media[0] = $timeline->retweeted_status->extended_entities->media[0]->video_info->variants[0]->url;
			}else{
				foreach ($timeline->retweeted_status->extended_entities->media as $media_key => $media_val){
					$rt_media[$media_key] = $media_val->media_url_https;
				}
			}
		}
		echo '<img src="'.$rt_icon.'"><p class="text"><strong><a href="'.$user_url.'" target="_blank">'.$rt_name.'</a></strong><small> : <a href="./user.php?user='.$rt_scr_name.'&id='.$rt_id_str.'" target="_blank">'.$rt_scr_name.'</a> : <a href ="./tweet.php?id='.$rt_id_str.'" target="_blank">'.date('Y/m/d H:i:s', strtotime($rt_time)).'</a>&lt;-RT:'.$name.'</small> RT';
		if ($rt_retweet){
			echo '●';
		}else{
			echo '○';
		}

		echo ':'.$rt_count_rt.' ';

		if ($rt_fav){
			echo '★:';
		}else{
			echo '☆:';
		}
		
		echo $rt_count_fav.'<br>'.$rt_text;
		
		if (isset($rt_url)){//URLあれば表示
			foreach ($rt_url as $rt_url_key => $rt_url_value){
				echo ' <a href="'.$rt_url[$rt_url_key].'" target="_blank">'.$rt_disp_url[$rt_url_key].'</a>';
			}
		}
		
		if (isset($rt_media)){//メディアあれば表示
			echo '<br>';
			foreach ($rt_media as $media_key => $media_val){
				echo ' <a href="'.$media_val.'" target="_blank">'.$rt_media_type.($media_key + 1).'</a>';
			}
		}
		echo '</p>';
	}
}

class footer
{
	function footer_last($disp_last)
	{
		if($disp_last){
			$last_link = '<a href ="#last">～</a>';
		}else{
			$last_link = '～';
		}
		return $last_link;
	}
}
?>