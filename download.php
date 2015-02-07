<?php
include("util.php");
$fi = getFileInfo();	//check parameters and get file info
outputFile($fi);		//output file to client
incDLs($fi);			//increment download counter
exit(0);
