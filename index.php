<?php

include("util.php");

if(calcBalance() < 0.0){
	die("not enough donations");
}

$results = $db->query("
SELECT *
FROM movies
ORDER BY m_id
");

while ($r = $results->fetchArray()) {
	echo $r['m_name'].":";
	
	$results2 = $db->query("
	SELECT *
	FROM files
	NATURAL JOIN movies
	WHERE m_id = ".$r['m_id']."
	ORDER BY f_id
	");
	
	$dl = "";
	echo "<br/><video controls='controls' preload='none' width='".$r['m_width']."' height='".$r['m_height']."'>";
	while ($r2 = $results2->fetchArray()) {
		$n = urlGet($r['m_id'], $r2['f_id']);
		echo "<source type='".$r2['f_type']."' src='view.php".$n."'>";
		if($r2['f_mime'] === "video/mp4")
			$dl = "<br/><a href='download.php".$n."'>Download</a>";
	}
	echo "</video>";
	echo $dl;
	echo "<br/><br/>";
}

?>
<br/><a href="stats.php">stats</a>