<?php
/* Basic remote portal for Alien Arena database - outputs number of servers and players */

include ('config.php');

/* This variable selects, at output time, if the data is presented as a flattened array, or put out as simple text */
$settings['format'] = 0;  /* 0 = default, flattened array, 1 = text */

/* Case-sensitive parameters to parse result for */
$parameters = array('format');
/* Parse url for valid parameters */
foreach ($_GET as $key => $value)
{
	if(!(array_search($key, $parameters) === FALSE))
		$settings[$key] = $value;
}

$data = array();

// +++++++++ DB section start ++++++++++ //
$conn = mysqli_connect($CONFIG['dbHost'], $CONFIG['dbUser'], $CONFIG['dbPass'],$CONFIG['dbName']) or die ('Error connecting to database');

/* Get time of last database update */
$query  = "SELECT lastupdated FROM stats WHERE id = '0'";
$result = mysqli_query($conn,$query);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
$data['lastupdated'] = $lastupdated = $row['lastupdated'];
mysqli_free_result($result);

/* Get all servers from last update which responded */
$query  = "SELECT serverid FROM serverlog WHERE time = '{$lastupdated}'";
$sv_result = mysqli_query($conn,$query);
$data['servers'] = mysqli_num_rows($sv_result);
mysqli_free_result($sv_result);

/* Get list of all players from last update */
$query  = "SELECT name, score, ping FROM playerlog WHERE time = '{$lastupdated}' AND ping != '0'";
$pl_result = mysqli_query($conn,$query);
$data['players'] = mysqli_num_rows($pl_result);
mysqli_free_result($pl_result);

mysqli_close($conn);
// ++++++++++ DB section end ++++++++++ //

if($settings['format'] == 0)
	echo serialize($data);
else
{
	foreach($data as $key => $value)
		echo $key."=".$value."\n";
}

?> 
