<?php

$db = new SQLite3("sqlite.db");

function urlGet($mid, $fid){
	return "?m=".$mid."&f=".$fid."&n=".nounce($mid, $fid);
}

function nounce($mid, $fid){
	//random salt once per installation
	$h = "WTx8a9mmRFApb/7YiS8ZWBv24k1Mwz";
	for($i = 0; $i < 5; $i++) $h = hash("sha512", $h);
	
	//only for this movie (not needed, because file is unique)
	//$h = $h.$mid;
	//for($i = 0; $i < 5; $i++) $h = hash("sha512", $h);
	
	//only usable for this file
	$h = $fid.$h;
	for($i = 0; $i < 5; $i++) $h = hash("sha512", $h);
	
	//can only be used within the same browser
	$h = $h.$_SERVER['HTTP_USER_AGENT'];
	for($i = 0; $i < 5; $i++) $h = hash("sha512", $h);
	
	//stays valid only for a few days 
	//(not that pure, because the user might get a link on the last day at 23:59 but clicks it at 00:01)
	$z = ((int)date("z"));
	$z -= $z % 5;
	$h = $z.$h;
	for($i = 0; $i < 5; $i++) $h = hash("sha512", $h);
	
	//security parameter (makes brute force less feasible, through more computation power)
	for($i = 0; $i < 35; $i++) $h = hash("sha512", $h);
	
	//despite having 128 characters, only use 32
	return substr($h, 0, 32);
	
}

function increment($table, $field, $idfield, $id){
	global $db;
	//update counter
	$results = $db->query("
	UPDATE $table
	SET $field = $field + 1
	WHERE $idfield = $id
	");
}

// views ++
function incViews($fi){
	increment("movies", "m_views", "m_id", $fi['mid']);
}

// downloads ++
function incDLs($fi){
	increment("movies", "m_downloads", "m_id", $fi['mid']);
}

function getFileInfo(){
	//GET Parameter cast to int (prevent sql injection)
	$mid = (int) $_GET['m'];
	$fid = (int) $_GET['f'];
	//cast and nounce correct?
	if( $mid === 0 || $fid === 0 || $_GET['n'] !== nounce($mid, $fid) ){
		http_response_code(400);
		die("400 ERROR: Bad Request");
	}
	//get filename and mime info
	$fmime = "";
	$fname = "";
	global $db;
	$results = $db->query("
	SELECT f_mime, f_name
	FROM movies
	NATURAL JOIN files
	WHERE m_id = $mid AND f_id = $fid
	");
	while( $r = $results->fetchArray() ) {
		$fmime = $r['f_mime'];
		$fname = $r['f_name'];
	}
	//id combination correct?
	if( $fmime === "" || $fname === "" ){
		http_response_code(400);
		die("400 ERROR: Bad Request");
	}
	//file exists?
	if( ! file_exists("files/".$fname) ){
		http_response_code(404);
		die("404 ERROR: File Not Found");
	}
	//not enough balance
	if(calcBalance() < 0.0){
		http_response_code(509);
		die("509 ERROR: negative balance");
	}
	
	return array('mid' => $mid, 'fid' => $fid, 'fmime' => $fmime, 'fname' => $fname);
}

function outputFile($fi){
	//tell the browser how to interpret the payload
	header("Content-Type: ".$fi['fmime']);
	header('Content-Description: File Transfer');
	header('Content-Transfer-Encoding: binary');
	//let the browser know the filename
	header("Content-Disposition: attachment; filename=\"".$fi['fname']."\"");
	
	$fn = "files/".$fi['fname'];
	
	//send payload (http://stackoverflow.com/questions/11786734/readfile-and-large-files)
	set_time_limit(0);
	//readfile($fn); // speicher zu klein
	//header("X-Sendfile: $fn"); // mod_xsendfile
	flush();
	$handle = fopen($fn, "rb");
	while (!feof($handle)){
		//TODO: check if the client is still reading
		echo fread($handle, 8192);
	}
	fclose($handle);
}

function calcBalance(){
	global $db;
	
	$total = 0.0;
	$donations = 0.0;

	$results = $db->query("
	SELECT SUM(m_views * m_viewcost + m_downloads * m_dlcost) as cost
	FROM movies
	");
	while ($r = $results->fetchArray()) {
		$total += (double)$r['cost'];
	}

	$results = $db->query("SELECT SUM(d_amount) as don FROM donations");
	while ($r = $results->fetchArray()) {
		$donations += (double)$r['don'];
	}

	return $donations - $total;
}
