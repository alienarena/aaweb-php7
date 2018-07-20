<?php

/*
    ALIEN ARENA WEB SERVER BROWSER
    Copyright (C) 2007 Tony Jackson

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

    Tony Jackson can be contacted at tonyj@cooldark.com
*/

include("flags.php");


function GenerateLiveServerTable(&$control)
{
	global $conn;

	$lastupdated = GetLastUpdated();
	$filename = GetFilename();
	/* Get all servers from last update which responded */
	
		/*  Section to display player list */
	$query  = "SELECT COUNT(name) AS numplayers FROM playerlog WHERE time = '{$lastupdated}' AND ping != '0'";
	$pl_result = mysqli_query($conn,$query);
	$numplayers = mysqli_fetch_array($pl_result);
	$numplayers = $numplayers['numplayers'];
	
	$query  = "SELECT serverlogid, serverid, mapname, realplayers FROM serverlog WHERE time = '{$lastupdated}' ORDER BY realplayers DESC";
	$sv_result = mysqli_query($conn,$query);

	$numservers = mysqli_num_rows($sv_result);

//	echo "<p class=\"cdsubtitle\">{$numservers} live servers</p>\n";

	/* Section to build table of servers */
	echo "<p>\n<table id=cdtable>\n";
	echo "<tr><th>Game server ({$numservers} online)</th><th>Map</th><th colspan=3>Players ({$numplayers} human)</th></tr>\n";

	while($sv_row = mysqli_fetch_array($sv_result, MYSQLI_ASSOC))
	{	
		$query  = "SELECT name, score, ping FROM playerlog WHERE serverlogid = '{$sv_row['serverlogid']}' ORDER BY score DESC";
		$pl_result = mysqli_query($conn,$query);
		$pl_numrows = mysqli_num_rows($pl_result);  /* Get number of players (rows) in mysql result */
		$query  = "SELECT ip, port, hostname, admin, website FROM servers WHERE serverid = '{$sv_row['serverid']}'";
		$svinfo_result = mysqli_query($conn,$query);
		$svinfo_row = mysqli_fetch_array($svinfo_result, MYSQLI_ASSOC);

		if($pl_numrows > 5)
			$rowspan = $pl_numrows;
		else
			$rowspan = 5;
		
	    echo "<tr>";
		//echo "<td>{$svinfo_row['ip']}:{$svinfo_row['port']}</td>";
							
		echo "<td><a href=\"{$filename}?action=serverinfo&amp;id={$sv_row['serverid']}\">".LimitString($svinfo_row['hostname'],40)."</a><br>";
		echo "</td>";
//		echo "<td>Admin: {$svinfo_row['admin']}</td>\n";

		echo "<td rowspan={$rowspan}>";
			ShowMapImage($mapname=$sv_row['mapname'], $thumbnail=1, $addlink=1);
			echo $sv_row['mapname'];
		echo "</td>";
		
		$count = 0;
		while(($pl_row = mysqli_fetch_array($pl_result, MYSQLI_ASSOC)) or $count < 5)
		{
			
			if($count > 0)  /* no tr tag for first row, completing row above */
			{
				echo "<tr>";
				switch($count)
				{
					/* These are the rows below servername, admin etc */
					case 1:
						echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;{$svinfo_row['ip']}:{$svinfo_row['port']}</td>";
					break;
					case 2:
						echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;";
						$cc = GetCountryCode($svinfo_row['ip']);
						ShowCountryFlag($cc);
						echo ' '.GetCountryName($cc);
					break;
					case 3:
						echo "<td>";
						if($svinfo_row['admin'] != '')
							echo "&nbsp;&nbsp;&nbsp;&nbsp;Admin: ".LimitString($svinfo_row['admin'],25);
						echo "</td>"; 
					break;
					case 4:
						echo "<td>";
						if($svinfo_row['website'] != "")
						{
							echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"{$svinfo_row['website']}\" target=\"_blank\"><img border=0 alt=www src=\"img/www.png\"></a>";		
						}
						echo "</td>";
					break;
					default:
						echo "<td></td>"; /* Below servername, admin etc */
					break;
				}
			}
			/* Then image */
			if($pl_row > 0)
			{
				if($pl_row['ping'] == 0) /* Bots ping at 0ms */
					echo "<td>{$pl_row['name']}</td>";					
				else /* Real players ping at > 0ms and are marked in bold */
					echo "<td><b>".GenerateInfoLink("player", $pl_row['name'])."</b></td>";
				echo "<td>score {$pl_row['score']}</td>";
				echo "<td>ping {$pl_row['ping']} ms</td>";
			}
			else
				echo "<td></td><td></td><td></td>";
			echo "</tr>\n";
			$count++;
		}
		/*
				if($svinfo_row['website'] != "")
		{
			echo "<a href=\"{$svinfo_row['website']}\"><img border=0 alt=www src=\"img/www.gif\"></a>";		
		}
		echo " {$svinfo_row['ip']}:{$svinfo_row['port']}";
		*/
		mysqli_free_result($pl_result);
		echo "<tr><td colspan=5></td></tr>";
		echo "<tr><td colspan=5></td></tr>";
	} 

	mysqli_free_result($sv_result);

	echo "</table>\n";
}

function GenerateTotalServers(&$control)
{
  global $conn;
	$query = 'select count(distinct serverid) as total_servers from serverlog;';
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	echo '<div class="cdsubtitle">'.$row['total_servers'].' unique servers in the last '.$control['history'].' hours</div>';
}

function GenerateServerTable(&$control)
{
  	global $conn;
	$filename = GetFilename();
  	$endtime = GetLastUpdated();
  	$starttime = $endtime - $control['history']*60*60;
	
	$query = 'SELECT serverid , SUM( realplayers ) AS playertime , COUNT( serverid ) AS uptime , MAX( realplayers ) AS maxplayers '
	        . ' FROM serverlog '
	        . ' WHERE time > '.$starttime.' AND time <= '.$endtime
	        . ' GROUP BY serverid '
	        . ' ORDER BY '.$control['orderby'].' '.$control['sort'].' LIMIT 0, '.$control['results'];
					
	$svlog_result = mysqli_query($conn,$query);
	if($svlog_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}
	
	echo "<table id=cdtable>\n";
	echo "<tr>";
	echo "<th>Server</th>";
	echo "<th>Country</th>";
	echo "<th>Admin</th>";

	Insert_Table_Sorter($control, $display = 'Uptime', $orderby = 'uptime'); 
	Insert_Table_Sorter($control, $display = 'Total player time', $orderby = 'playertime'); 
	Insert_Table_Sorter($control, $display = 'Most players at once', $orderby = 'maxplayers'); 
	
	echo "</tr>\n";

	while($svlog_row = mysqli_fetch_array($svlog_result, MYSQLI_ASSOC))
	{
		$query  = "SELECT ip, port, hostname, admin, website FROM servers WHERE serverid = '{$svlog_row['serverid']}'";
		$sv_result = mysqli_query($conn,$query);
		$sv_row = mysqli_fetch_array($sv_result, MYSQLI_ASSOC);

    $uptime=round(100*$svlog_row['uptime']/($control['history']*60), 1);
    if($uptime > 100)
    	$uptime = 100;

    	echo "<tr>";
		echo "<td><a href=\"".$filename."?action=serverinfo&amp;id=".$svlog_row['serverid']."\">".$sv_row['hostname']."</a> ";
		echo "</td>";

		echo '<td>';
		$cc = GetCountryCode($sv_row['ip']);
		ShowCountryFlag($cc);
		echo '  '.GetCountryName($cc);
		echo '</td>';

		echo "<td>{$sv_row['admin']}</td>";
		echo "<td>".$uptime."%</td>";
		echo "<td>".MinutesToString($svlog_row['playertime'])."</td>";
		echo "<td>{$svlog_row['maxplayers']}</td>";
		echo "</tr>\n";
		mysqli_free_result($sv_result);
	} 
	mysqli_free_result($svlog_result);
	echo "</table>\n";
}

function GenerateServerInfo(&$control)
{
	global $conn;
	global $CONFIG;
//	echo "<p class=cdbody>Eventually there will be lots more information on uptimes, peak usage times, players that have played on it here.</p>\n";
  /* Find time of last database update */
	
	$endtime = GetLastUpdated();
	$starttime = $endtime - $control['history']*60*60;
	$serverid = mysqli_real_escape_string($conn, $control['id']);
	

	$query =  'SELECT ip, port, hostname, admin, website, version'
            .' FROM servers '
            .' WHERE serverid = \''.$serverid.'\'';
	$sv_result = mysqli_query($conn,$query);
	if($sv_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}

	$sv_row = mysqli_fetch_array($sv_result, MYSQLI_ASSOC);
	
	$query = 'SELECT SUM( realplayers ) AS playertime , COUNT( serverid ) AS uptime , MAX( realplayers ) AS maxplayers '
	. ' FROM serverlog '
	. ' WHERE serverid = \''.$serverid.'\' '
	. ' AND time > '.$starttime.' AND time <= '.$endtime
	. ' GROUP BY serverid ';
			
	$svlog_result = mysqli_query($conn,$query);	
	
	// TODO
	// $query = 'SELECT SUM( realplayers ) AS playertime , COUNT( serverid ) AS uptime , MAX( realplayers ) AS maxplayers '
	// 	. ' FROM serverlog '
	// 	. ' WHERE serverid = ? '
	// 	. ' AND time > ? AND time <= ? '
	// 	. ' GROUP BY serverid ';
				
	// $statement = mysqli_prepare($conn, $query);
	// mysqli_stmt_bind_param($statement, "iss", $serverid, $starttime, $endtime);
	// mysqli_stmt_execute($statement);
	// $svlog_result = mysqli_stmt_get_result($stmt);
		
	if($svlog_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}

	$svlog_row = mysqli_fetch_array($svlog_result, MYSQLI_ASSOC);
	$hostname = $sv_row['hostname'];
	
	$uptime=round(100*$svlog_row['uptime']/($control['history']*60), 1);
	if($uptime > 100)
		$uptime = 100;

	echo "<br/>\n";
	echo "<table class=\"graph\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "<tr>\n";
	echo "   <td class=\"graphheader\">Server information covering the last {$control['history']} hours</td>\n";
	echo "</tr>\n";
	echo "<tr><td class=\"graph\">\n";
	echo "   <img class=\"graph\" width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} alt=\"Usage graph\" src=\"graph.php?show=server&amp;id={$control['id']}&amp;history={$control['history']}\">\n";
	echo "</td></tr>\n";
	echo "</table>\n";

	echo "<br/>\n";
	echo "<table id=cdtable>";

	echo "<tr><th>Hostname</th>";
	echo "<td>".LimitString($hostname, 40)."</td></tr>";

	echo "<tr><th>IP & port</th>";
	echo "<td>{$sv_row['ip']}:{$sv_row['port']}</td></tr>";

	echo "<tr><th>Website</th>";
	echo "<td><a href={$sv_row['website']} target=\"_blank\">{$sv_row['website']}</a></td></tr>";

	echo "<tr><th>Admin</th>";
	echo "<td>{$sv_row['admin']}</td></tr>";

	echo "<tr><th>Uptime</th>";
	echo "<td>".$uptime."%</td></tr>";

	echo "<tr><th>Total player time</th>";
	echo "<td>".MinutesToString($svlog_row['playertime'])."</td></tr>";

	echo "<tr><th>Most players at once</th>";
	echo "<td>".$svlog_row['maxplayers']."</td></tr>";

	echo "<tr><th>Server version</th>";
	echo "<td>{$sv_row['version']}</td></tr>\n";

	echo "</table>";

	mysqli_free_result($sv_result);	

	/******************************************************
	  Display maps that have been served                           
	 ******************************************************/
	
	$query = 'SELECT mapname , SUM( realplayers ) as playertime , COUNT( realplayers ) as servedtime , MAX( realplayers ) AS maxplayers'
	        . ' FROM serverlog '
			. ' WHERE serverid = \''.$serverid.'\' '
			. ' AND time > '.$starttime.' AND time <= '.$endtime
	        . ' GROUP BY mapname '
	        . ' ORDER BY playertime DESC';
		
		
				
	$svlog_result = mysqli_query($conn,$query);
	$num_maps = mysqli_num_rows($svlog_result);

	// TODO
	// $query = 'SELECT mapname , SUM( realplayers ) as playertime , COUNT( realplayers ) as servedtime , MAX( realplayers ) AS maxplayers'
	//         . ' FROM serverlog '
	// 		. ' WHERE serverid = ? '
	// 		. ' AND time > ? AND time <= ? '
	//         . ' GROUP BY mapname '
	// 		. ' ORDER BY playertime DESC';
			
	// $statement = mysqli_prepare($conn, $query);
	// mysqli_stmt_bind_param($statement, "iss", $serverid, $starttime, $endtime);
	// mysqli_stmt_execute($statement);
	// $svlog_result = mysqli_stmt_get_result($stmt);									
	// $num_maps = mysqli_num_rows($svlog_result) ?? 0;

	if($svlog_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}
	
	echo "<div class=\"cdsubtitle\">{$hostname} has served {$num_maps} maps in the last {$control['history']} hours</div>\n";

	if($num_maps > 50)
			echo "<p class=cdbody>Top 50 results shown</p>";
	
	echo "<table id=cdtable>\n";
	echo "<tr><th>Map</th><th>Served Time</th><th>Played Time</th><th>Peak players</th></tr>\n";

	$count = 0;
	
	while(($svlog_row = mysqli_fetch_array($svlog_result, MYSQLI_ASSOC)) && ($count++ < 50))
	{
		echo "<tr>";
		echo "<td>".GenerateInfoLink("map", $svlog_row['mapname'])."</td>";
		echo "<td>".MinutesToString($svlog_row['servedtime'])."</td>";
		echo "<td>".MinutesToString($svlog_row['playertime'])."</td>";
		echo "<td>{$svlog_row['maxplayers']}</td>";
		echo "</tr>";
	}
	echo "</table>";
	mysqli_free_result($svlog_result);	
	
	/******************************************************
	  Display players that have played on this server
	 ******************************************************/
	
	$query = 'SELECT name , COUNT( name ) as playertime'
	        . ' FROM playerlog '
			. ' WHERE serverid = \''.$serverid.'\' '
			. ' AND time > '.$starttime.' AND time <= '.$endtime
			. ' AND ping > 0 '
	        . ' GROUP BY name '
	        . ' ORDER BY playertime DESC';
				
	$pllog_result = mysqli_query($conn,$query);
	$num_players = mysqli_num_rows($pllog_result);
	if($pllog_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}
	
	echo "<div class=\"cdsubtitle\">{$num_players} players have played on {$hostname} in the last {$control['history']} hours</div>\n";

	if($num_players > 50)
			echo "<p class=cdbody>Top 50 results shown</p>";
	
	echo "<table id=cdtable>\n";
	echo "<tr><th>Name</th><th>Time</th><th>Average ping</th></tr>\n";

	$count = 0;
	
	while(($pllog_row = mysqli_fetch_array($pllog_result, MYSQLI_ASSOC)) && ($count++ < 50))
	{
		$playername = mysqli_real_escape_string($conn, $pllog_row['name']);

		echo "<tr>";
		echo "<td>".GenerateInfoLink("player", $pllog_row['name'])."</td>";
		echo "<td>".MinutesToString($pllog_row['playertime'])."</td>";
		echo "<td>";
		$query = 'SELECT ROUND(AVG(ping),1) as avping'
	    	    . ' FROM playerlog '
				. ' WHERE serverid = \''.$serverid.'\' '
				. ' AND time > '.$starttime.' AND time <= '.$endtime
				. ' AND ping > 0 '
				. ' AND name = \''.$playername.'\' ';
				
		$ping_result = mysqli_query($conn,$query);
		$ping_row = mysqli_fetch_array($ping_result, MYSQLI_ASSOC);
		echo $ping_row['avping'].' ms';
		mysqli_free_result($ping_result);
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	mysqli_free_result($pllog_result);		
}


function OldGenerateServerInfo(&$control)
{
  global $conn;
	global $CONFIG;
//	echo "<p class=cdbody>Eventually there will be lots more information on uptimes, peak usage times, players that have played on it here.</p>\n";
  /* Find time of last database update */
  $endtime = GetLastUpdated();
  $starttime = $endtime - $control['history']*60*60;
	
	$query  = "SELECT ip, port, hostname, admin, website, version FROM servers WHERE serverid = '".$control['id']."'";
	$sv_result = mysqli_query($conn,$query);
	if($sv_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}

	$sv_row = mysqli_fetch_array($sv_result, MYSQLI_ASSOC);
	
	$query = 'SELECT SUM( realplayers ) AS playertime , COUNT( serverid ) AS uptime , MAX( realplayers ) AS maxplayers '
		. ' FROM serverlog '
		. ' WHERE serverid = \''.$control['id'].'\' '
		. ' AND time > '.$starttime.' AND time <= '.$endtime
		. ' GROUP BY serverid ';
				
	//echo $query;
				
	$svlog_result = mysqli_query($conn,$query);
	if($svlog_result === FALSE)
	{
		echo "<p class=\"cdbody\">Unable to display stats at this time.</p>\n";
		return;
	}

	$svlog_row = mysqli_fetch_array($svlog_result, MYSQLI_ASSOC);

	echo "<p class=\"cdbody\">Server information covering the last {$control['history']} hours</p>\n";

	echo "<table id=cdtable>";
	echo "<tr>";
	echo "<th>IP & port</th>";
	echo "<th>Hostname</th>";
	echo "<th>Admin</th>";
	echo "<th>Uptime</th>";
	echo "<th>Total player time</th>";
	echo "<th>Most players at once</th>";
	echo "<th>Server version</th>";
	echo "</tr>\n";

	echo "<tr>";
	echo "<td>{$sv_row['ip']} port {$sv_row['port']}</td>";
	echo "<td>{$sv_row['hostname']} ";
	if($sv_row['website'] != "")
	{
		echo "<a href=\"{$sv_row['website']}\" target=\"_blank\"><img border=0 alt=www src=\"img/www.png\"></a>";
	}
	echo "</td>";
	echo "<td>{$sv_row['admin']}</td>";
	echo "<td>".MinutesToString($svlog_row['uptime'])."</td>";
	echo "<td>".MinutesToString($svlog_row['playertime'])."</td>";
	echo "<td>{$svlog_row['maxplayers']}</td>";
	echo "<td>{$sv_row['version']}</td>";
	echo "</tr>\n";
	echo "</table>";
	mysqli_free_result($sv_result);	
	echo "<br>\n";
	echo "<img width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} alt=\"Usage graph\" src=\"graph.php?show=server&amp;id={$control['id']}&amp;history={$control['history']}\">\n";
	
}

function DoServerSearch(&$control)
{
	global $conn;

	$filename = GetFilename();
	$searchstring = mysqli_real_escape_string($conn, $_POST['searchstring']);
	
	if($searchstring != "")
	{ /* No server to show, but a string to search with */
		
		$query = 'SELECT serverid, hostname '
		        . ' FROM servers '
				. ' WHERE hostname LIKE \'%'.$searchstring.'%\''
		        . ' GROUP BY hostname ';
					
		$sv_result = mysqli_query($conn,$query);
		if($sv_result === FALSE)
		{
			echo "<p class=\"cdbody\">Unable to perform search at this time.</p>\n";
			return;
		}

		echo '<p class=cdsubtitle>'.mysqli_num_rows($sv_result).' results for \''.htmlspecialchars($_POST['searchstring']).'\'</p>';
		
		echo "<p style=cdbody>";
		while($sv_row = mysqli_fetch_array($sv_result, MYSQLI_ASSOC))
		{
			$control['action'] = 'serverinfo';
			$control['id'] = $sv_row['serverid'];
			echo "<a href=\"".Generate_URL($control)."\">";

			echo $sv_row['hostname']."</a><br>\n";
		} 
		mysqli_free_result($sv_result);
		echo "</p>\n";
	}
	else
	{ 	/* User did null search? */
		echo '<p class=cdbody>Oops, looks like you forgot to enter a search string. :)</p>';
	}
}

?>
