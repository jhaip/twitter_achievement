<?php


	$cachefile = 'achieve_cache.html';
	$cachetime = 5*60; //don't update more than every 5 minutes
	// Serve from the cache if it is younger than $cachetime
	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
		include($cachefile);
		echo "<!-- Cached copy, generated ".date('H:i', filemtime($cachefile))."-->\n";
		exit;
	}
	ob_start(); // Start the output buffer
	

	include 'check.php';

	function read_database($fn) {
	
		$databaseContents = file($fn);
		
		//echo "Reading database... <br />";
		
		if ( count($databaseContents) == 0 ) {
			echo "<strong>No achievements!</strong><br />";
			return;
		}
		
		//echo "database contains " . count($databaseContents) . " entries <br />";
		
		$counter = 1;
		foreach ($databaseContents as $dbe) {
		
			$a = json_decode($dbe,true);
			$t = date('n/j/y', $a["timestamp"]);
			echo '<div class="a_main">';
			echo '<div class="a_n">' . $counter . '</div>';
			echo '<div class="a_date"><strong>' . $t . '</strong></div>';
			echo '<div class="a_text">' . $a["text"] . '</div>';
			echo '<div class="clearer"></div>';
			echo '</div>';
			
			$counter++;
			
		}
		
		return;
	}

?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="reset.css" >
<link rel="stylesheet" type="text/css" href="style.css" >
</head>
<body>
<div id="mama">
<h1>Achievements</h1>
<?
read_database($databaseFilename);
?>
</div>
</body>
</html>

<?

// Cache the contents to a file
$cached = fopen($cachefile, 'w');
fwrite($cached, ob_get_contents());
fclose($cached);
ob_end_flush(); // Send the output to the browser


?>