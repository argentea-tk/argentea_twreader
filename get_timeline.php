<?php

//twitteroauth
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
// Timelineの取得

class get_timeline_p{
	protected $connection , $over_flow_count;
	
	function __construct()
	{
		//OAuth
		$consumer_key = ''; // put your key
		$consumer_secret = ''; // put your key
		$access_token = ''; // put your key
		$access_token_secret = ''; // put your key
		
		$this->connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
	}
	
	function digit_check($string){//32bit環境互換max_id桁計算用
		
		$digit = -1;
		$flag = 1;
		do {
			$num = substr($string, $digit, 1);//後ろから〇桁目を取得
			$num--;
			if ($num < 0 ){//取得データ-1が負
				$digit--;//次の桁へ繰り上げ
			}else{//取得データ-1が0以上
				$flag = 0;//ループ抜けフラグ
			}
			
		} while ($flag);
		
		$ans = substr($string, 0, $digit);//後ろdigit桁削ったものを$ansへ
		$ans .= $num;//$ansへdigit桁目の計算結果接続
		$digit++;
		
		while ($digit < 0){//digitが0になるまで$ansに9を並べる
			$ans.= "9";
			$digit++;
		}
		if (substr($ans, 0, 1) === "0"){//最上桁が0だったら削る
			$ans = substr($ans, 1);
		}
		return $ans;
	}
}

class get_home_timeline extends get_timeline_p{
	protected 
	$target_timeline = 'statuses/home_timeline',
	$filename = 'home_j';

	function get_timeline($user_screen_name = '', $since_id = ''){
		$parameter = ['count' => '200', 'tweet_mode' => 'extended'];
		if ($user_screen_name <> ''){//現在未使用
			$parameter['screen_name'] = $user_screen_name;
		}
		
		$open_file = file_put_contents('./data/'.$this->filename, '[');//file作成とヘッダというかなんというか
		if ($open_file === false){//file作成失敗した場合止める
			echo 'file作成失敗';
			return;
		}
		
		for ($i = 0; $i < 4; $i++){//最大4回読み込み
			$timeline = $this->connection->GET($this->target_timeline,$parameter);
			
			if (isset($timeline->errors)){//エラー出たらとりあえず止める
				echo '<p>'.$timeline->errors[0]->message.'</p>';
				if ($i <> 0){
					file_put_contents('./data/'.$this->filename, ']', FILE_APPEND);
				}
				exit;
			}
			
			if (isset($timeline->error)){//鍵垢とか
				echo '<p>'.$timeline->error.'</p>';
				return;
			}
			
			if (! isset($timeline[0])){//読み込むデータなけりゃ抜ける
				break;
			}
			
			$timeline_j = json_encode($timeline,JSON_UNESCAPED_UNICODE);
			
			//json結合処理
			if($i > 0){
				file_put_contents('./data/'.$this->filename, ',', FILE_APPEND);
			}
			
			$timeline_j = mb_substr($timeline_j, 1, -1);//最初の[を削る
			file_put_contents('./data/'.$this->filename, $timeline_j, FILE_APPEND);
			
			$parameter['max_id'] = $this->digit_check($timeline[count($timeline) - 1]->id_str);//max_idに取得した最後のツイートID-1関数
			if ($i === 0){//since_id保存
				if (file_exists('./data/'.'since_'.$this->filename)){//前回のsince_idを_oldに
					rename('./data/'.'since_'.$this->filename, './data/'.'since_'.$this->filename.'_old');
				}
				file_put_contents('./data/'.'since_'.$this->filename, $timeline[0]->id_str);
			}
			$line = $i * 200;
			if ($since_id <> ''){//新着更新の場合の追加読込の要不要
				$since_id_num = mb_strlen($since_id);
				$max_id_num = mb_strlen($parameter['max_id']);
				if ($since_id_num > $max_id_num){//桁数sinceのほうがでかけりゃ終了
					break;
				}elseif ($since_id_num === $max_id_num){//桁数同じ場合
					for ($j = 0; $j < $since_id_num; $j++){//sinceとmax比較
						$since_id_check = mb_substr($since_id, $j, 1);
						$max_id_check = mb_substr($parameter['max_id'],$j,1);
						if ($since_id_check > $max_id_check){//since > maxなら終了
							break 2;
						}elseif ($since_id_check < $max_id_check){
							break;
						}
					}
				}
			}
		}
		file_put_contents('./data/'.$this->filename, ']', FILE_APPEND);
		if ($line > 600){
			$line = 600;
		}
		return $line;
	}
}

class get_user_timeline extends get_timeline_p{//現在未使用
	protected 
	$target_timeline = 'statuses/user_timeline',
	$filename = 'user_j';
}

class get_onetime_user_timeline extends get_timeline_p{
	protected $target_timeline = 'statuses/user_timeline';
	function get_onetime_timeline($user_screen_name, $max_id)
	{
		$parameter = ['count' => '200', 'tweet_mode' => 'extended', 'screen_name' => $user_screen_name];
		if ($max_id !== ''){
			$parameter['count'] = '10';
			$parameter['max_id'] = $max_id;
		}
		$timeline = $this->connection->GET($this->target_timeline,$parameter);
		if (isset($timeline->errors)){//エラー出たらとりあえず止める
			echo '<p>'.$timeline->errors[0]->message.'</p>';
			exit;
		}
		
		if (isset($timeline->error)){//鍵垢とか
			echo '<p>'.$timeline->error.'</p>';
			return;
		}
		return $timeline;
	}
}

class get_tweet extends get_timeline_p{
	protected $target_timeline = 'statuses/show/';
	function get_single_tweet($id)
	{
		$parameter = ['tweet_mode' => 'extended'];
		$tweet = [$this->connection->GET($this->target_timeline.$id,$parameter)];
		if (isset ($tweet[0]->in_reply_to_status_id_str) && $this->over_flow_count < 10){//10chainで打ち止め
			$this->over_flow_count++;
			$get_reply = $this->get_single_tweet($tweet[0]->in_reply_to_status_id_str);
		}
		if (isset ($get_reply)){
			$tweet = array_merge ($tweet, $get_reply);
		}
		if (isset($tweet[0]->errors)){//エラー出たらとりあえず止める
			echo '<p>'.$tweet[0]->errors[0]->message.'</p>';
			return;
		}
		if (isset($tweet[0]->error)){//鍵垢とか
			echo '<p>'.$tweet[0]->error.'</p>';
			return;
		}
		return $tweet;
	}
}
?>