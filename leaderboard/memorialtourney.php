<?php
include 'common.php';

require dirname(__FILE__).'/mustache.php-2.12.0/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    /*'cache' => dirname(__FILE__).'/tmp/cache/mustache',*/
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

// Get year parameter
$year = (isset($_GET['year']) ? intval($_GET['year']) : date('Y'));
if ($year < 2000 || $year > 2099) {
    $year = date('Y');
}

$gamedatapath = dirname(__FILE__).'/gamedata';

// Get memorial tournament files for this year
// File name format: memorialtourney_YYYY-MM-DD_HH.MM.SS_Round_N.json
$allfiles = scandir($gamedatapath, SCANDIR_SORT_DESCENDING);
$memorialFiles = array_values(array_filter(array_values($allfiles), function($file) use ($year) {
    return startsWith($file, 'memorialtourney_') && startsWith(substr($file, 16), strval($year));
}));

// Extract unique rounds (1, 2, 3)
$rounds = array();
foreach($memorialFiles as $file) {
    // Extract round number from filename: memorialtourney_YYYY-MM-DD_HH.MM.SS_Round_N.json
    if (preg_match('/Round_(\d+)\.json$/', $file, $matches)) {
        $roundNum = intval($matches[1]);
        if (!isset($rounds[$roundNum])) {
            $rounds[$roundNum] = array();
        }
        $rounds[$roundNum][] = $file;
    }
}

// Sort rounds by round number
ksort($rounds);

// Get map from first round for background
$mapImageLocation = '../browser/maps/default.jpg';
$mapName = '';
if (count($rounds) > 0) {
    $firstRoundFiles = reset($rounds);
    if (count($firstRoundFiles) > 0) {
        $firstRoundFile = $firstRoundFiles[0];
        $data_json = getCachedContents($firstRoundFile);
        if (strlen($data_json) > 0) {
            $mapData = jsonDecode($data_json, true);
            if (isset($mapData['map'])) {
                $mapName = $mapData['map'];
                if (file_exists(dirname(__FILE__)."/../browser/maps/1st/$mapName.jpg")) {
                    $mapImageLocation = "../browser/maps/1st/$mapName.jpg";
                } else if (file_exists(dirname(__FILE__)."/../browser/maps/3rd/$mapName.jpg")) {
                    $mapImageLocation = "../browser/maps/3rd/$mapName.jpg";
                }
            }
        }
    }
}

$pageTitle = 'John Diamond Memorial Tournament '.$year;
$template = $mustache->loadTemplate('scoretemplate');
$detailsTemplate = $mustache->loadTemplate('scoretemplatedetails');
$weaponAccuracyTemplate = $mustache->loadTemplate('weaponaccuracy');
$detailsHtml = "";
$maxPlayersForDetails = 25;
$maxPlayersTopView = 25;

session_start();

if (intval((isset($_GET["deletesession"])) ? $_GET["deletesession"] : NULL) == 1) {
    session_destroy();
    redirect(removeParameters(currentUrl()));
}

// Load rankings to display winners
$rankingsData = loadMemorialRankings($year);
$rankingsNote = isset($rankingsData['note']) ? htmlspecialchars($rankingsData['note']) : '';
$winners = array();
if (isset($rankingsData['rankings']) && count($rankingsData['rankings']) > 0) {
    // Get top 3 players
    for ($i = 0; $i < min(3, count($rankingsData['rankings'])); $i++) {
        $winners[] = array(
            'place' => $i + 1,
            'name' => $rankingsData['rankings'][$i]['name'],
            'score' => isset($rankingsData['rankings'][$i]['score']) ? $rankingsData['rankings'][$i]['score'] : 0
        );
    }
}

$emptySpaceHeight = 30;

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>$pageTitle</title>\n";
echo "    <link rel=\"icon\" type=\"image/x-icon\" href=\"../sharedimages/favicon.ico\">";
echo "    <meta name=\"description\" content=\"$pageTitle\">\n";
echo "    <meta name=\"keywords\" content=\"Alien Arena Memorial Tournament results winners\">\n";
echo "    <link href=\"https://fonts.googleapis.com/css?family=Aldrich\" rel=\"stylesheet\">\n";
echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\">\n";
echo "    <script src=\"../sharedscripts/jquery-3.3.1.min.js\"></script>\n";
echo "    <script src=\"../sharedscripts/parallaxie.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"utils.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"index.js\"></script>\n";
echo "</head>\n";
echo "<body style=\"background-image: url('../sharedimages/site-background.jpg'); background-attachment:fixed; background-repeat:no-repeat; background-size:cover;\">\n";

$boxWidth = '1777px';
$boxHeight = '1000px';
$boxBackgroundStyle = "background-size: cover; background-position: center; background-repeat: no-repeat; display: flex; flex-direction: column; align-items: center; max-width: $boxWidth; height: $boxHeight;";

echo "<div id=\"overlay\" style=\" border: none; display:none; z-index: 100; position: absolute; top: 0px; left: 0px; height: 100%; width: 100%; background: rgb(0, 4, 8); opacity: 0.5;\" onclick=\"hidePopup();\"></div>\n";

echo "    <center>\n";
echo "        <div style=\"height: ".$emptySpaceHeight."px\"></div>\n";
echo "        <div class=\"pagetitle\">$pageTitle</div>\n";

// Display rankings note if it exists (above the map)
if (strlen($rankingsNote) > 0) {
    echo "        <div style=\"margin: 15px auto 20px auto; padding: 10px 20px; background-color: rgba(255, 165, 0, 0.3); border: 1px solid #FFA500; border-radius: 5px; color: #FFD700; font-size: 14px; max-width: 600px;\">\n";
    echo "            ".$rankingsNote."\n";
    echo "        </div>\n";
}

// Map image container with overlaid tables and winners
echo "        <div id=\"mapImage\" style=\"background-image: url('$mapImageLocation'); $boxBackgroundStyle\">\n";

// Display score tables overlaid on map
echo "            <table border=\"0\" cellspacing=\"12\" style=\"width: 1000px; padding-top: 90px;\" id=\"leaderboardtable\">\n";
echo "                <tr>\n";

// Render each round
$roundCount = 0;
foreach($rounds as $roundNum => $roundFiles) {
    foreach($roundFiles as $file) {
        renderMemorialFile($file, 'Round '.$roundNum);
        $roundCount++;
    }
}

if ($roundCount == 0) {
    echo "                    <td colspan=\"4\" style=\"text-align: center; padding: 30px; font-size: 16px;\">\n";
    echo "                        No memorial tournament data found for $year.\n";
    echo "                    </td>\n";
}

echo "                </tr>\n";
echo "            </table>\n";

// Display winners
if (count($winners) > 0) {
    echo "            <div style=\"margin: 15px 0;\">\n";
    echo "                <div style=\"font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #00ff00;\">FINAL RESULTS</div>\n";
    echo "                <div style=\"display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; position: relative; z-index: 10;\">\n";
    
    $medalStyles = array(
        1 => "color: #FFD700;", // Gold
        2 => "color: #C0C0C0;", // Silver
        3 => "color: #CD7F32;"  // Bronze
    );
    
    foreach($winners as $winner) {
        $place = $winner['place'];
        $style = isset($medalStyles[$place]) ? $medalStyles[$place] : "";
        $medal = array('1st 🥇', '2nd 🥈', '3rd 🥉');
        echo "                    <div style=\"text-align: center;\">\n";
        echo "                        <div style=\"font-size: 20px; font-weight: bold; $style\">".$medal[$place-1]."</div>\n";
        echo "                        <div style=\"font-size: 18px; margin-top: 10px;\" class=\"winner-name\">".$winner['name']."</div>\n";
        echo "                        <div style=\"font-size: 14px; margin-top: 5px;\">Total score: ".$winner['score']."</div>\n";
        echo "                    </div>\n";
    }
    
    echo "                </div>\n";
    echo "            </div>\n";
}

echo "        </div>\n";
echo $detailsHtml;

echo "        <script type=\"text/javascript\">\n";
echo "            $(document).ready(function() {\n";
echo "               documentReady();\n";
echo "               // Colorize winner names\n";
echo "               $('div.winner-name').each(function(index, elem) {\n";
echo "                   colorize(elem);\n";
echo "               });\n";
echo "               $(\"table.scoretable\").css(\"cursor\", \"pointer\");\n";
echo "               $(document).keyup(function(e) {\n";
echo "                   if (e.keyCode == 27) {\n";
echo "                       hidePopup();\n";
echo "                   }\n";
echo "               });\n";
echo "               if (window.isUsedOnMobile()) {\n";
echo "                  $('#mapImage').removeAttr('style');\n";
echo "                  $('#mapImage').css('background-image', '');\n";
echo "                  $('body').css('background-image', 'url(../sharedimages/purgatory.jpg)');\n";
echo "               }\n";
echo "            });\n";
echo "        </script>\n";

echo "        </center>\n";
echo "</body>\n";
echo "</html>\n";

function renderMemorialFile($file, $roundLabel) {
    global $template, $detailsTemplate, $weaponAccuracyTemplate, $detailsHtml;
    global $maxPlayersForDetails, $maxPlayersTopView;

    $data_json = getCachedContents($file);
    if (strlen($data_json) == 0) {
        echo "Warning, empty file: ".$file;
        return;
    }

    $data = jsonDecode($data_json, true);
    if (count($data['players']) > $maxPlayersForDetails) {
        $data['players'] = array_slice($data['players'], 0, $maxPlayersForDetails);
    }
    
    $data = enrichMemorialData($file, $data, $roundLabel);

    // Copy into short list of players for main display
    $shortlist = $data;
    if (count($shortlist['players']) > $maxPlayersTopView) {
        $shortlist['players'] = array_slice($shortlist['players'], 0, $maxPlayersTopView);
    }

    // If more players than actually shown then show extra row with dots
    if (count($data['players']) > count($shortlist['players'])) {
        array_push($shortlist['players'], ["name" => "...", "score" => "..."]);
    }
    
    $height = '400px';
    $popupId = 'popup'.$data['tourney_id'];
    $title = " title=\"Click for more details\"";
    $onclick = " onclick=\"showPopup('$popupId');\"";
    
    $table = "<td class=\"mainpagetourney\" style=\"height: $height;\"$onclick$title>\n";
    $table = $table.$template->render($shortlist);
    $table = $table."</td>\n";
    
    // Details popup with full details - using class="details" to match index.php
    $detailsHtml = $detailsHtml."<div class=\"details\" id=\"$popupId\" style=\"display: none; z-index: 200; position: fixed; top: 220px; left: 50%; transform: translateX(-50%);\" onclick=\"hidePopup();\">\n";
    
    // Display round note if it exists
    if (isset($data['round_note']) && strlen($data['round_note']) > 0) {
        $detailsHtml = $detailsHtml."<div style=\"margin-bottom: 10px; padding: 8px 15px; background-color: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; border-radius: 3px; color: #00ff00; font-size: 12px; text-align: center;\">\n";
        $detailsHtml = $detailsHtml.$data['round_note']."\n";
        $detailsHtml = $detailsHtml."</div>\n";
    }
    
    $detailsHtml = $detailsHtml.$detailsTemplate->render($data);
    $detailsHtml = $detailsHtml."</div>\n";    
    
    $detailsHtml = $detailsHtml.$weaponAccuracyTemplate->render($data);

    echo $table;
}

function enrichMemorialData($file, $data, $roundLabel)
{
    // Define {{index}} to show player number
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
   
    $tourneyTitle = htmlspecialchars(strlen($file) <= 40 ? '- No title -' : str_replace('_', ' ', substr($file, 36, strlen($file) - 41)));
    $tourneyDateString = substr($file, 16, 10);
    $tourneyId = str_replace(' ', '', $tourneyTitle).$tourneyDateString;
    
    // Fill tourney title, tourney link, tourney id and tourney date which are used in the templates
    $data['tourney_title'] = $roundLabel;
    $data['tourney_link'] = '#';
    $data['tourney_id'] = $tourneyId;
    $data['tourney_date'] = dateToString($tourneyDateString);
    
    // Preserve note if it exists
    if (!isset($data['round_note']) && isset($data['note'])) {
        $data['round_note'] = htmlspecialchars($data['note']);
    }
    
    return $data;
}

function loadMemorialRankings($year)
{
    $gamedatapath = dirname(__FILE__).'/gamedata';
    $rankingsFile = $gamedatapath.'/memorialtourneyrankings_'.$year.'.json';
    
    if (file_exists($rankingsFile)) {
        $json = fileGetContents($rankingsFile);
        $data = jsonDecode($json, true);
        return $data;
    }
    
    // If rankings file doesn't exist, return empty rankings
    return array('rankings' => array());
}

?>
