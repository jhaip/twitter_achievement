<?php

	function GetJsonFeed($url) {
		$response = file_get_contents($url);
		return json_decode($response);
	}
	
	function request_tweets($screen_name,$count = "",$since_id = "",$max_id = "") {
		$requestString = "http://api.twitter.com/1/statuses/user_timeline.json?screen_name=" . $screen_name;
		if ( strcmp($count,"") != 0 ) {
			$requestString = $requestString . "&count=" . $count;
		}
		if ( strcmp($since_id,"") != 0 ) {
			$requestString = $requestString . "&since_id=" . $since_id;
		}
		if ( strcmp($max_id,"") != 0 ) {
			$requestString = $requestString . "&max_id=" . $max_id;
		}
		return GetJsonFeed($requestString);
	}
	
	function update_since_id($fn,&$si) {
	
		//open the file where since_id is saved to check where tweets should start being processed
		$sinceIdFileContents = file($fn);
		
		if ( count($sinceIdFileContents) == 0 ) {
			echo "empty since_id saver! <br />";
		} else {
			$si = $sinceIdFileContents[0];
		}
		
	}
	
	function save_since_id($fn,$s) {
	
		$sinceIdFile = fopen($fn, 'w');
		
		if ( $sinceIdFile == FALSE ) {
			echo "couldn't write to file to save since_id <br />";
		}
		else {
			fwrite($sinceIdFile,$s);
		}
		
		fclose($sinceIdFile);
		
	}
	
	function show_tweet_info($ti,$ts,$txt) {
		echo $i . "&nbsp;&nbsp;";
		echo date('F j, Y, g:i a', $ts) . '&nbsp;&nbsp;';
		echo $txt . '<br />';
	}
	
	$screen_name = "jhaip";
	$count = 10;
	$max_id = -1;
	$since_id = 183228868860182530; //taken from my tweet about bookmarklet to google chrome extension on Fri, March 23 2012
	$sinceIdFilename = "sinceIdSaver.txt";
	$databaseFilename = "achievements.txt";
	$writeString = ""; //string to hold the string that will be written to the database
	
	//updates since_id to the saved value
	update_since_id($sinceIdFilename,$since_id);
	//echo "Setting since_id to: " . $since_id . "<br /><br />";
	
	$databaseFile = fopen($databaseFilename, 'a');
	if ( $databaseFile == FALSE ) { 
		echo "couldn't open databasefile! <br />"; 
	}
	
	$newSinceId = $since_id;
	$emptyResults = False;
	while ( $emptyResults == False ) { //while results not empty
	
		//echo "<br />Request for tweets made <br />";
		
		if ( $max_id == -1 ) {
			$tweets = request_tweets($screen_name,$count,$since_id);
		} else {
			$tweets = request_tweets($screen_name,$count,$since_id,$max_id);
		}
		
		if ( count($tweets) == 0 ) {
			//echo "No tweets to process <br />";
			$emptyResults = True;
			break;
		}
		
		foreach ($tweets as $tweet) {
		
			$timestamp = strtotime($tweet -> created_at);
			$tweetId = $tweet -> id;
			$text = $tweet -> text;
			
			$pos = strripos($text,"#step");
			
			if ( $pos != False ) { //if tweet contains #step
			
				$text = str_ireplace("#step","",$text);
				
				//add achievement to string to be written to the database
				$entry = Array("tweet_id" => $tweetId,"timestamp" => $timestamp,"text" => $text);
				$writeString = json_encode($entry) . PHP_EOL . $writeString;		
				
			}
			
			//show_tweet_info($tweetId,$timestamp,$text);
				
			if ( $max_id == -1 ) {
				$max_id = $tweetId;
			} else if ( $tweetId < $max_id ) {
				$max_id = $tweetId;
			}
			
			if ( $tweetId > $newSinceId ) {
				$newSinceId = $tweetId;
			}
			
		}
		
		//update max_id
		$max_id = $max_id - 1;
	
	}
	
	$since_id = $newSinceId;
	//echo "<br /> since_id = " . $since_id . "<br />";
	save_since_id($sinceIdFilename,$since_id);
	//echo "since_id saved as " . $since_id . "<br />";
	
	///
	if ( strcmp($writeString,"") == 0 ) {
		//echo "<strong>No new achievements</strong><br />";
	} else {
		//echo "<strong>New Achievements added!</strong><br />";
		fwrite($databaseFile,$writeString);
	}
	fclose($databaseFile);
	///
	
?>