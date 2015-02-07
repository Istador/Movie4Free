<?php
include("util.php");
$fi = getFileInfo();	//check parameters and get file info
outputFile($fi);		//output file to client
incViews($fi);			//increment view counter
exit(0);
