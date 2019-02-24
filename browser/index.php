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

global $conn;

include '../config.php';
// connect here : handle $conn visible througout includes
$conn = new mysqli($CONFIG['dbHost'], $CONFIG['dbUser'], $CONFIG['dbPass'],$CONFIG['dbName']) or die ('Cannot connect to the database because: ' . mysqli_error());

include 'support.php';
include 'servers.php';
include 'maps.php';
include 'players.php';

$control = BuildControl();  /* Get config from URL line */

Generate_HTML_Headers($CONFIG['baseurl'].'browser/', $CONFIG['title']);

$steamWidgetCookieName = "SteamWidgetShown";
$steamWidgetCookieExpirationSeconds = 60 * 60 * 24 * 365 * 10; // Ten years
$showSteamWidget = $_COOKIE[$steamWidgetCookieName] != "true";

echo "<script>\n";
echo "$(document).ready(function() {\n";	
echo "   if (!window.isUsedOnMobile()) {\n";
echo "      $(\".parallaxie\").parallaxie();\n";
echo "   } else {\n";
echo "      $('#content').removeAttr('class');\n";
echo "      $('#content').css('background-image', '');\n";
echo "      $('body').css('background-image', 'url(../sharedimages/background.jpg)');\n";
echo "   }\n";
if ($showSteamWidget)
{
	echo "   showSteamWidget();\n";
	setcookie($steamWidgetCookieName, "true", time() + $steamWidgetCookieExpirationSeconds);
}
echo "});\n";
echo "</script>\n";

$filename = GetFilename();

// InsertAds();


//$conn = mysql_connect($CONFIG['dbHost'], $CONFIG['dbUser'], $CONFIG['dbPass']) or die ('Cannot connect to the database because: ' . mysql_error());

echo '<div class="container">';
echo '<div class="menu">';
echo "<a href=\"{$filename}?action=liveservers\">Live games</a>";
echo " | <a href=\"{$filename}?action=liveplayers\">Live players</a>";
echo " | <a href=\"{$filename}?action=serverstats\">Server stats</a>";
echo " | <a href=\"{$filename}?action=playerstats\">Player stats</a>";
echo " | <a href=\"{$filename}?action=mapstats\">Map stats</a>";
echo " | <a href=\"".$CONFIG['baseurl']."leaderboard/\" target=\"_blank\">Leaderboard</a>";
echo "</div>\n";
echo "</div>\n";

CheckDBLive();

switch ($control['action'])
{
	case 'liveservers':
		ShowCollapsibleGraphs();
		GenerateLiveServerTable($control);
		break;
	case 'liveplayers':
		ShowCollapsibleGraphs();
		GenerateLivePlayerTable($control);
		break;
	case 'serverstats':
		/* Section to build table of servers with most playertime*/
		ShowServerHistoryGraph();
		GenerateTotalServers($control);
		GenerateServerTable($control);
		GenerateNumResultsSelector($control);
		GenerateSearchInput("serversearch", "Server search");
		break;
	case 'playerstats':
		ShowPlayerHistoryGraph();
		GenerateTotalPlayers($control);
		GeneratePlayerTable($control);
		GenerateNumResultsSelector($control);
		GenerateSearchInput("playersearch", "Player search");
		break;
	case 'mapstats':
		/* Get list of most played maps */
		echo "<div class=\"cdsubtitle\">Map usage in the last {$control['history']} hours</div>\n";
		GenerateMapTable($control);
		GenerateNumResultsSelector($control);
		GenerateSearchInput("mapsearch", "Map search");
		break;
	
	case 'serverinfo':
		GenerateServerInfo($control);
		GenerateSearchInput("serversearch", "Find another server");
		break;
	case 'playerinfo':
		GeneratePlayerInfo($control);
		GenerateSearchInput("playersearch", "Find another player");
		break;
	case 'mapinfo':
		GenerateMapInfo($control);
		GenerateSearchInput("mapsearch", "Find another map");
		break;

	case 'serversearch':
		DoServerSearch($control);
		GenerateSearchInput("serversearch", "Search for another server");
		break;
	case 'playersearch':
		DoPlayerSearch($control);
		GenerateSearchInput("playersearch", "Search for another player");
		break;
	case 'mapsearch':
		DoMapSearch($control);
		GenerateSearchInput("mapsearch", "Search for another map");
		break;

	default:
		break;
}

if ($showSteamWidget)
{
	// Steam widget pop-up
	echo "<iframe id=\"buyit\" onmouseover=\"hide = false;\" onmouseenter=\"hide = false;\" onmousemove=\"hide = false;\" onmouseout=\"hide = true\" "; 
	echo "   style=\"display:none; position:fixed; bottom:0px; right:0px; margin-right: 20px; margin-bottom: 10px;\" ";
	echo "   src=\"https://store.steampowered.com/widget/629540/\" width=\"646\" height=\"190\" frameborder=\"0\" scrolling=\"no\"></iframe>\n";
}

Generate_HTML_Footers();
// mysql_close($conn);

function CollapsiblePlayerGraph()
{
	global $CONFIG;

	$html = "<tr>\n";
	$html = $html."<td id=\"showplayergraph\" class=\"showhidegraph\" onclick=\"showHidePlayerGraph();\">&#9658;</td>\n";
	$html = $html."<td class=\"collapsiblegraphheader\" onclick=\"showHidePlayerGraph();\">Player activity</td>\n";
	$html = $html."<td class=\"collapsiblegraphheader\" onclick=\"showHidePlayerGraph();\" style=\"width: 16px\"></td>\n";
	$html = $html."</tr>\n";
	$html = $html."<tr><td class=\"graph\" colspan=\"3\">\n";
	$html = $html."   <img id=\"playergraph\" class=\"graph\" style=\"display: none\" alt=\"Player graph\" width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} src=\"graph.php?show=players\">\n";
	$html = $html."</td></tr>\n";

	return $html;
}

function CollapsibleServerGraph()
{
	global $CONFIG;

	$html = "<tr>\n";
	$html = $html."<td id=\"showservergraph\" class=\"showhidegraph\" onclick=\"showHideServerGraph();\">&#9658;</td>\n";
	$html = $html."<td class=\"collapsiblegraphheader\" onclick=\"showHideServerGraph();\">Server usage</td>\n";
	$html = $html."<td class=\"collapsiblegraphheader\" onclick=\"showHideServerGraph();\" style=\"width: 16px\"></td>\n";
	$html = $html."</tr>\n";
	$html = $html."<tr><td class=\"graph\" colspan=\"3\">\n";
	$html = $html."   <img id=\"servergraph\" class=\"graph\" style=\"display: none\" alt=\"Server graph\" width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} src=\"graph.php?show=servers\">\n";
	$html = $html."</td></tr>\n";

	return $html;
}

function ShowCollapsibleGraphs()
{
	echo "<br/>\n";
	echo "<table class=\"graph\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo CollapsiblePlayerGraph();
	echo "<tr><td colspan=\"3\" style=\"height: 5px\"></td></tr>\n";
	echo CollapsibleServerGraph();
	echo "</table>\n";
}

function ShowPlayerHistoryGraph()
{
	global $CONFIG;
	global $control;

	echo "<br/>\n";
	echo "<table class=\"graph\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "<tr>\n";
	echo "   <td class=\"graphheader\">Player activity in the last {$control['history']} hours</td>\n";
	echo "</tr>\n";
	echo "<tr><td class=\"graph\">\n";
	echo "   <img class=\"graph\" alt=\"Player graph\" width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} src=\"graph.php?show=players&amp;history={$control['history']}\">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
}

function ShowServerHistoryGraph()
{
	global $CONFIG;
	global $control;

	echo "<br/>\n";
	echo "<table class=\"graph\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "<tr>\n";
	echo "   <td class=\"graphheader\">Server usage in the last {$control['history']} hours</td>\n";
	echo "</tr>\n";
	echo "<tr><td class=\"graph\">\n";
	echo "   <img class=\"graph\" alt=\"Server graph\" width={$CONFIG['graphwidth']} height={$CONFIG['graphheight']} src=\"graph.php?show=servers&amp;history={$control['history']}\">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
}

function InsertAds()
{
	ob_start(); // start buffer
	include ("../adcode.txt");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean(); // end buffer and remove buffer contents
	echo $content;
}

?>

