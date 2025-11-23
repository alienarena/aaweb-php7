<?php
include 'common.php';

require dirname(__FILE__).'/mustache.php-2.12.0/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    /*'cache' => dirname(__FILE__).'/tmp/cache/mustache',*/
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

// Global variables
$pageNumber = intval((isset($_GET['page']) ? $_GET['page'] : NULL));
if ($pageNumber < 1 || $pageNumber > 100) {
    $pageNumber = 1;
}

$gamedatapath = dirname(__FILE__).'/gamedata';
// Get game files.
// The file name must be in the format: 
// "gamereport_2017-11-30_01.17.17_Martian_Supremacy_Tournament.json"
// or "gamereport_2017-11-30_01.17.17.json"
// The title is optional. Without title it will display "- No title -".
$allfiles = scandir($gamedatapath, SCANDIR_SORT_DESCENDING);
$files = array_values(array_filter(array_values($allfiles), function($file) {
    return startsWith($file, 'gamereport_');
}));

//$onlyLastTourney is used from the main web site to show only the contents of the last tourney
$onlyLastTourney = false;

// Tourney date in format YYYYMMDD
$dateFilter = (isset($_GET['date']) ? $_GET['date'] : NULL);
if ($dateFilter == 'last') {
    $dateFilter = substr($files[0], 11, 10);
    $onlyLastTourney = true;
} else if ($dateFilter < 20190127 || $dateFilter > 20991231) {
    $dateFilter = '';
} else {
    $dateFilter = substr($dateFilter, 0, 4).'-'.substr($dateFilter, 4, 2).'-'.substr($dateFilter, 6, 2);
}
$singleTourneyMode = strlen($dateFilter) > 0;
// Map name as it exists  in browser/maps/1st and browser/maps/3rd
$map = (isset($_GET['map']) ? $_GET['map'] : NULL);
if (strlen($map) > 0) {
    $map = strtolower(preg_replace('/([^a-zA-Z0-9\-]+)/', '', $map));
}

$details = false;
// TODO:
// Use one variable with three different possible values: All, Single, Details
// Don't show funround in mode All
$maxPlayersForDetails = 25;
$maxPlayersTopView = $singleTourneyMode ? 25 : 10;
$gameReportsPerPage = 20;
$template = $mustache->loadTemplate('scoretemplate');
$detailsTemplate = $mustache->loadTemplate('scoretemplatedetails');
$weaponAccuracyTemplate = $mustache->loadTemplate('weaponaccuracy');
$detailsHtml = "";
$leaderboardCols = $singleTourneyMode ? 2 : 4;
$leaderboardWidth = $singleTourneyMode ? 500 : 1000;
$emptySpaceHeight = 30;

session_start();

if (intval((isset($_GET["deletesession"])) ? $_GET["deletesession"] : NULL) == 1) {
    session_destroy();
    redirect(removeParameters(currentUrl()));
}

$files = array_values(array_filter(array_values($allfiles), function($file) {
    global $dateFilter, $singleTourneyMode, $onlyLastTourney;
    return startsWith($file, 'gamereport_'.$dateFilter) && ($singleTourneyMode || $onlyLastTourney || strtolower(substr($file, 31, strlen($file) - 36)) != 'funround');
}));

$pageTitle = 'Alien Arena Tournament Leaderboard';
$subTitle = '';

if (count($files) == 1 && !$singleTourneyMode) {
    $details = true;
}

if (count($files) > 1 || $singleTourneyMode) {
    for($i = 0; $i < count($files); $i++) {
        $tourneyTitle = htmlspecialchars(strlen($files[$i]) <= 35 ? '- No title -' : str_replace('_', ' ', substr($files[$i], 31, strlen($files[$i]) - 36)));
        $tourneyDateString = substr($files[$i], 11, 10);

        if ($singleTourneyMode) {
            if (strtolower($tourneyTitle) != 'funround') {
                $subTitle = $tourneyTitle.' - '.dateToString($tourneyDateString);
            } else if ($i < count($files) - 1) {
                $mainRoundTitle = htmlspecialchars(strlen($files[$i + 1]) <= 35 ? '- No title -' : str_replace('_', ' ', substr($files[$i + 1], 31, strlen($files[$i + 1]) - 36)));
                $subTitle = $mainRoundTitle.' - '.dateToString($tourneyDateString);
            }
        }

        if(strtolower($tourneyTitle) == 'funround' && $i < count($files) && array_key_exists($i + 1, $files)) {
            if ($tourneyDateString == substr($files[$i + 1], 11, 10)) {
                // Switch order to show the funround after the main round
                $temp = $files[$i];
                $files[$i] = $files[$i + 1];
                $files[$i + 1] = $temp;
            }
        }
    }
}

// Set $files to current page, do this after reordering above
$pages = array_chunk($files, $gameReportsPerPage);
$files = $pages[$pageNumber - 1];


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>$pageTitle</title>\n";
echo "    <link rel=\"icon\" type=\"image/x-icon\" href=\"../sharedimages/favicon.ico\">";
echo "    <meta name=\"description\" content=\"$pageTitle\">\n";
echo "    <meta name=\"keywords\" content=\"Alien Arena Warriors of Mars tournament leaderboard matches scores statistics stats\">\n";
echo "    <link href=\"https://fonts.googleapis.com/css?family=Aldrich\" rel=\"stylesheet\">\n";
echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\">\n";
echo "    <script src=\"../sharedscripts/jquery-3.3.1.min.js\"></script>\n";
echo "    <script src=\"../sharedscripts/parallaxie.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"index.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"utils.js\"></script>\n";
echo "</head>\n";
if ($onlyLastTourney) {
    echo "<body style=\"background-color: transparent;\">\n";
} else {
    echo "<body style=\"background-image: url('../sharedimages/site-background.jpg'); background-attachment:fixed; background-repeat:no-repeat; background-size:cover;\">\n";
}

if (!$singleTourneyMode) {
    /* Background with parallax-effect */
    /* https://github.com/TheUltrasoft/Parallaxie */	
    echo "<div id=\"content\" class=\"parallaxie\" style=\"background-image: url('../sharedimages/purgatory.jpg');\" data-parallaxie='{\"speed\": 0.8, \"size\": \"auto\"}'>\n";
}

if (!$onlyLastTourney) {
    echo "  <center>\n";
}

if ($singleTourneyMode) {
    $mapImageLocation = '';
    $boxWidth = '1333px';
    $boxHeight = '750px';
    $boxBackgroundStyle = "background-size: cover; background-position: center; background-repeat: no-repeat; display: flex; flex-direction: column; align-items: center; max-width: $boxWidth; height: $boxHeight;";

    if (strlen($map) == 0) {
        $map = getMapFromTourney();
    }
    if (strlen($map) == 0) {
        $mapImageLocation = '../browser/maps/default.jpg';
    } else if (file_exists(dirname(__FILE__)."/../browser/maps/1st/$map.jpg")) {
        $mapImageLocation = "../browser/maps/1st/$map.jpg";
    } else if (file_exists(dirname(__FILE__)."/../browser/maps/3rd/$map.jpg")) {
        $mapImageLocation = "../browser/maps/3rd/$map.jpg";
    } else {
        $map = '';
        $mapImageLocation = '../browser/maps/default.jpg';
    }
}

if (!$onlyLastTourney) {
    echo "    <div style=\"height: ".$emptySpaceHeight."px\"></div>\n";
}
if ($singleTourneyMode) {
    echo "    <div id=\"overlay\" style=\" border: none; display:none; z-index: 100; position: absolute; \n";
    echo "        top: 0px; left: 0px; height: 100%; width: 100%; background: rgb(0, 4, 8); opacity: 0.5;\" \n";
    echo "        onclick=\"hidePopup();\"></div>\n";
}
if (!$onlyLastTourney) {
    echo "    <div class=\"pagetitle\">$pageTitle</div>\n";
}
if ($singleTourneyMode) {
    if (!$onlyLastTourney) {
        echo "    <div class=\"menu\"><a href=\"index.php\">Matches</a><span class=\"navdisabled\">&nbsp;|&nbsp;</span><a href=\"rankings.php\">Rankings</a></div>\n";
    }
    echo "    <div id=\"mapImage\" style=\"background-image: url('$mapImageLocation'); $boxBackgroundStyle\">\n";
    echo "       <div id=\"mapTitle\" class=\"mapTitle\">map:&nbsp;<b>".(strlen($map) > 0 ? strtoupper($map) : "unknown")."</b></div>\n";
    echo "    <div class=\"tourneytitle\">$subTitle</div>\n";
} else {
    echo "    <div class=\"menu\"><span class=\"active\">Matches</span><span class=\"navdisabled\">&nbsp;|&nbsp;</span><a href=\"rankings.php\">Rankings</a></div>\n";
}
if ($singleTourneyMode || $onlyLastTourney) {
    echo "    <table border=\"0\" style=\"position: relative: top: 70px; display: none;\" id=\"leaderboardtable\" class=\"tourneytable\">\n";
} else {
    echo "    <table border=\"0\" cellspacing=\"12\" style=\"width: 1000px; display: none;\" id=\"leaderboardtable\">\n";
}

echo "        <tr>\n";

$colCount = 0;
$renderedFileCount = 0;
foreach($files as $file)
{
    renderFile($file);
}
if ($renderedFileCount < $gameReportsPerPage) {
    // Fill up empty gaps from empty files with files from the next page
    if (array_key_exists($pageNumber, $pages)) {
        $files = $pages[$pageNumber];
        foreach($files as $file)
        {
            if ($renderedFileCount < $gameReportsPerPage) {
                renderFile($file);
            } else {
                break;
            }
        }    
    }
}

echo "        </tr>\n";

// Pagination
if ($pageNumber > 1 || $pageNumber < count($pages)) {
    echo "<tr class=\"navigation\"><td class=\"navigation\" colspan=\"".$leaderboardCols."\">";
    if ($pageNumber > 1) {
        echo "<span class=\"active\"><a title=\"Previous page\" href=\"index.php?page=".strval($pageNumber - 1)."\">◄</a></span>";
    } else {
        echo "<span class=\"navdisabled\">◄<span>";
    }
    echo "<span class=\"navdisabled\">&nbsp;|&nbsp;</span>";
    if ($pageNumber < count($pages)) {
        echo "<span class=\"active\"><a title=\"Next page\" href=\"index.php?page=".strval($pageNumber + 1)."\">►</a></span>";
    } else {
        echo "<span class=\"navdisabled\">►<span>";
    }
    echo "</td></tr>\n";
}

echo "    </table>\n";
echo $detailsHtml;
echo "    <script type=\"text/javascript\">\n";
echo "        $(document).ready(function() {\n";
echo "           documentReady();\n";
if ($singleTourneyMode) {
    echo "           if (window.isUsedOnMobile()) {\n";
    echo "              $('#content').removeAttr('class');\n";
    echo "              $('#content').css('background-image', '');\n";
    echo "              $('body').css('background-image', 'url(../sharedimages/purgatory.jpg)');\n";
    echo "           }\n";
} else {
    echo "           if (!window.isUsedOnMobile()) {\n";
    echo "              $(\".parallaxie\").parallaxie();\n"; 
    echo "           } else {\n";
    echo "              $('#content').removeAttr('class');\n";
    echo "              $('#content').css('background-image', '');\n";
    echo "              $('body').css('background-image', 'url(../sharedimages/purgatory.jpg)');\n";
    echo "           }\n";
}
if (!$details) {
    if (!$onlyLastTourney) {
        echo "           $(\"table.scoretable\").css(\"cursor\", \"pointer\");\n";
    }
    echo "           $(document).keyup(function(e) {\n";
    echo "               if (e.keyCode == 27) {\n"; 
    echo "                   hidePopup();\n";
    echo "               }\n";
    echo "           });\n";
}
echo "        });\n";
echo "    </script>\n";
echo "</div>\n";
if (!$onlyLastTourney) {
     echo "  </center>\n";
}
echo "</body>\n";
echo "</html>\n";

function renderFile($file) {
    global $colCount, $leaderboardCols, $renderedFileCount;

    if ($colCount > 0 && $colCount % $leaderboardCols == 0)
    {
        echo "</tr>\n";
        echo "<tr>\n";
    }

    if (renderScoreListAndPrepareDetails($file)) {
        $colCount++;
        $renderedFileCount++;
    }

}

function renderScoreListAndPrepareDetails($file)
{
    global $details, $maxPlayersForDetails, $maxPlayersTopView;
    global $template, $detailsTemplate, $weaponAccuracyTemplate, $detailsHtml;
    global $singleTourneyMode, $onlyLastTourney;

    $detailsTop = $singleTourneyMode ? '270px' : '90px';

    $data_json = getCachedContents($file);
    if (strlen($data_json) == 0) {
        echo "Warning, empty file: ".$file;
        return false;
    }

    $data = jsonDecode($data_json, true);
    array_splice($data['players'], $maxPlayersForDetails);
        
    $data = enrichData($file, $data);

    // Copy into short list of players
    $shortlist = $data;
    array_splice($shortlist['players'], $maxPlayersTopView);

    // If more players than actually shown then show extra row with dots
    if (count($data['players']) > count($shortlist['players'])) {
        array_push($shortlist['players'], ["name" => "...", "score" => "..."]);
    }
    
    $height = $details ? '950px' : '400px';
    $popupId = 'popup'.$data['tourney_id'];
    $title = !$details && !$onlyLastTourney ? " title=\"Click for more details\"" : "";
    if (!$details && !$onlyLastTourney) {
        if ($singleTourneyMode) {
            $onclick = " onclick=\"showPopup('$popupId');\"";
        } else {
            $onclick = " onclick=\"window.location = '".$data['tourney_link']."'\";";
        }    
    }
    
    if ($singleTourneyMode || $onlyLastTourney) {
        $padLeft = strtolower($data['tourney_title']) == 'funround' ? 'padding-left: 30px;' : '';
        $table = "<td style=\"".$padLeft."vertical-align: top; height: $height;\" $onclick $title>\n";
    } else {
        $table = "<td class=\"mainpagetourney\" style=\"height: $height;\" $onclick $title>\n";
    }
    if ($details) {
        $table = $table.$detailsTemplate->render($data);
    } else {
        $table = $table.$template->render($shortlist);
    }
    $table = $table."</td>\n";
    
    if (!$details)
    {
        $detailsHtml = $detailsHtml."<div class=\"details\" id=\"$popupId\" style=\"display: none; z-index: 200; ";
        $detailsHtml = $detailsHtml."  position: fixed; top: ".$detailsTop.";\" ";
        $detailsHtml = $detailsHtml."  onclick=\"hidePopup();\">\n";
        $detailsHtml = $detailsHtml.$detailsTemplate->render($data);
        $detailsHtml = $detailsHtml."</div>\n";    
    }
    $detailsHtml = $detailsHtml.$weaponAccuracyTemplate->render($data);

    echo $table;

    return true;
}

function enrichData($file, $data)
{
    global $singleTourneyMode, $onlyLastTourney;

    // Define {{index}} to show player number (also needed if not shown, for the weapon accuracy table)
    for ($i = 0; $i < count($data['players']); $i++) 
    {
        $data['players'][$i]['index'] = $i + 1;

        // Calculate total hits and total shots based on weapon skill stats               
        $hits = 0;
        $shots = 0;
        for ($j = 0; $j < count($data['players'][$i]['weapon_skill']); $j++) 
        {
            $hits = $hits + $data['players'][$i]['weapon_skill'][$j]['hits'];
            $shots = $shots + $data['players'][$i]['weapon_skill'][$j]['shots'];
        }
        $data['players'][$i]['totalhits'] = $hits;
        $data['players'][$i]['totalshots'] = $shots;
    }
   
    $tourneyTitle = htmlspecialchars(strlen($file) <= 35 ? '- No title -' : str_replace('_', ' ', substr($file, 31, strlen($file) - 36)));
    $tourneyDateString = substr($file, 11, 10);
    $tourneyId = str_replace(' ', '', $tourneyTitle).$tourneyDateString;
    
    // Fill tourney title, tourney link, tourney id and tourney date which are used in the templates
    $data['tourney_title'] = ($singleTourneyMode || $onlyLastTourney) && strtolower($tourneyTitle) != 'funround' ? 'Main round' : $tourneyTitle;
    $data['tourney_link'] = 'index.php?date='.str_replace('-', '', $tourneyDateString);
    $data['tourney_id'] = $tourneyId;
    $data['tourney_date'] = dateToString($tourneyDateString);
    
    return $data;
}

// Gets map from tourney main round in single tourney mode and in case it is not passed in the url
function getMapFromTourney()
{
    global $files, $dateFilter;
    
    foreach($files as $file)
    {
        if (startsWith($file, 'gamereport_'.$dateFilter) && !endsWith($file, 'Funround.json'))
        {
            $data_json = getCachedContents($file);
            $data = jsonDecode($data_json, true);
            return $data['map'];
        }
    }
    return null;
}
?>
