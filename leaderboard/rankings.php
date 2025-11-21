<?php
include 'common.php';

require dirname(__FILE__).'/mustache.php-2.12.0/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    /*'cache' => dirname(__FILE__).'/tmp/cache/mustache',*/
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

$pageTitle = 'Alien Arena Tournament Leaderboard';
$orderByDeathMatch = 1;
$orderByInstagib = 2;
$orderByRocketArena = 3;
$orderByTotal = 0; // default
$orderby = intval((isset($_GET['orderby']) ? $_GET['orderby'] : NULL));
$lastyear = intval((isset($_GET['lastyear']) ? $_GET['lastyear'] : NULL));

$tourneyStartDate = '2019-01-27';

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>$pageTitle</title>\n";
echo "    <link rel=\"icon\" type=\"image/x-icon\" href=\"../sharedimages/favicon.ico\">";
echo "    <meta name=\"description\" content=\"$pageTitle\">\n";
echo "    <meta name=\"keywords\" content=\"Alien Arena Warriors of Mars tournament rankings leaderboard scores deathmatch instagib rocket arena statistics stats\">\n";
echo "    <link href=\"https://fonts.googleapis.com/css?family=Aldrich\" rel=\"stylesheet\">\n";
echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\">\n";
echo "    <script src=\"../sharedscripts/jquery-3.3.1.min.js\"></script>\n";
echo "    <script src=\"../sharedscripts/parallaxie.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"utils.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"rankings.js\"></script>\n";
echo "</head>\n";
echo "<body style=\"background-image: url('../sharedimages/site-background.jpg'); background-attachment:fixed; background-repeat:no-repeat; background-size:cover;\">\n";

/* Background with parallax-effect */
/* https://github.com/TheUltrasoft/Parallaxie */	
echo "<div id=\"content\" class=\"parallaxie\" style=\"background-image: url('../sharedimages/purgatory.jpg');\" data-parallaxie='{\"speed\": 0.8, \"size\": \"auto\"}'>\n";

echo "  <center>\n";
echo "    <div style=\"height: 30px\"></div>\n";
echo "    <div class=\"pagetitle\">$pageTitle</div>\n";
echo "    <div class=\"menu\"><a href=\"index.php\">Matches</a><span class=\"navdisabled\">&nbsp;|&nbsp;</span><span class=\"active\">Rankings</span></div>\n";

renderRankings();

echo "    <script type=\"text/javascript\">\n";
echo "        $(document).ready(function() {\n";
echo "           documentReady();\n";
echo "           if (!window.isUsedOnMobile()) {\n";
echo "              $(\".parallaxie\").parallaxie();\n"; 
echo "           } else {\n";
echo "              $('#content').removeAttr('class');\n";
echo "              $('#content').css('background-image', '');\n";
echo "              $('body').css('background-image', 'url(../sharedimages/purgatory.jpg)');\n";
echo "           }\n";
echo "        });\n";
echo "    </script>\n";

echo "    <div style=\"height: 30px\"></div>\n";
echo "  </center>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

function renderRankings()
{
    global $mustache, $tourneyStartDate, $lastyear;
    
    $template = $mustache->loadTemplate('rankingstemplate');

    $rankingsFile = $lastyear == 1 ? "gamedata/rankings.lastyear.json" : "gamedata/rankings.json";
    $json = fileGetContents($rankingsFile);
    $data = jsonDecode($json, true);

    if (strlen($data['dateFrom']) == 0) {
        $data['dateFrom'] = $tourneyStartDate;
    }
    $data['rankingsTitle'] = 'Rankings from '.dateToString($data['dateFrom']).' until '.dateToString($data['dateTo']);
    
    handleOrderBy($data);

    calculateRatio($data['rankings']);

    $table = "<td style=\"vertical-align: top; height: \"100%\";\">\n";
    $table = $table.$template->render($data);
    $table = $table."</td>\n";

    echo $table;
    echo renderDetails($data);
}

function renderDetails($data)
{
    global $mustache;
    $template = $mustache->loadTemplate('rankingdetailstemplate');

    $table = $table.$template->render($data);

    echo $table;
}

function handleOrderBy(&$data) {
    global $orderby, $orderByDeathMatch, $orderByInstagib, $orderByRocketArena, $orderByTotal;
     
    $deathMatchHeader = 'DEATHMATCH';
    $instagibHeader = 'INSTAGIB';
    $rocketArenaHeader = 'ROCKET ARENA';
    $totalHeader = 'TOTAL';

    switch ($orderby) {
        case $orderByDeathMatch:
            $data['orderByDeathMatch'] = 'selected';
            $data['deathMatchHeader'] = $deathMatchHeader.' ▼';
            $data['instagibHeader'] = $instagibHeader;
            $data['rocketArenaHeader'] = $rocketArenaHeader;
            $data['totalHeader'] = $totalHeader;
            usort($data['rankings'], "sortByDeathMatch");
            break;
        case $orderByInstagib:
            $data['orderByInstagib'] = 'selected';
            $data['deathMatchHeader'] = $deathMatchHeader;
            $data['instagibHeader'] = $instagibHeader.' ▼';
            $data['rocketArenaHeader'] = $rocketArenaHeader;
            $data['totalHeader'] = $totalHeader;
            usort($data['rankings'], "sortByInstagib");
            break;
        case $orderByRocketArena:
            $data['orderByRocketArena'] = 'selected';
            $data['deathMatchHeader'] = $deathMatchHeader;
            $data['instagibHeader'] = $instagibHeader;
            $data['rocketArenaHeader'] = $rocketArenaHeader.' ▼';
            $data['totalHeader'] = $totalHeader;
            usort($data['rankings'], "sortByRocketArena");
            break;
        default:
            $data['orderByTotal'] = 'selected';
            $data['deathMatchHeader'] = $deathMatchHeader;
            $data['instagibHeader'] = $instagibHeader;
            $data['rocketArenaHeader'] = $rocketArenaHeader;
            $data['totalHeader'] = $totalHeader.' ▼';
            usort($data['rankings'], "sortByTotalPoints");
    }

    // If not ordering by total, show sequence number,
    // but shouldn't the official rank be shown as well?
    if ($orderby != $orderByTotal) {
        for ($i = 0; $i < count($data['rankings']); $i++) {
            $data['rankings'][$i]['rank'] = $i + 1;
        }            
    }
}

function sortByDeathMatch($a, $b) {
    if ($b['deathMatchPoints'] == $a['deathMatchPoints']) {
        return sortByRank($a, $b);
    }

    return $b['deathMatchPoints'] < $a['deathMatchPoints'] ? -1 : 1;
}

function sortByInstagib($a, $b) {
    if ($b['instagibPoints'] == $a['instagibPoints']) {
        return sortByRank($a, $b);
    }

    return $b['instagibPoints'] < $a['instagibPoints'] ? -1 : 1;
}

function sortByRocketArena($a, $b) {
    if ($b['rocketArenaPoints'] == $a['rocketArenaPoints']) {
        return sortByRank($a, $b);
    }

    return $b['rocketArenaPoints'] < $a['rocketArenaPoints'] ? -1 : 1;
}

function sortByTotalPoints($a, $b) {
    if ($b['points'] == $a['points']) {
        return sortByRank($a, $b);
    }

    return $b['points'] < $a['points'] ? -1 : 1;
}

function sortByRank($a, $b) {
    if ($b['rank'] == $a['rank']) {
        return 0;
    }

    return $b['rank'] < $a['rank'] ? 1 : -1;
}

function calculateRatio(&$rankings) {
    for ($i = 0; $i < count($rankings); $i++) {
        $mainRoundWins = floatval($rankings[$i]['mainRoundWins']);
        $mainRoundsPlayed = floatval($rankings[$i]['mainRoundsPlayed']);

        $funroundWins = floatval($rankings[$i]['funroundWins']);
        $funroundsPlayed = floatval($rankings[$i]['funroundsPlayed']);

        $rankings[$i]['mainRoundWinRatio'] =
            $mainRoundsPlayed > 0
                ? strval(round(100 * $mainRoundWins / $mainRoundsPlayed, 0)).'%'
                : '0%';

        $rankings[$i]['funroundWinRatio'] =
            $funroundsPlayed > 0
                ? strval(round(100 * $funroundWins / $funroundsPlayed, 0)).'%'
                : '0%';
        
        // Format all numbers with two decimals
        $rankings[$i]['deathMatchPoints'] = sprintf('%0.2f', $rankings[$i]['deathMatchPoints']);
        $rankings[$i]['instagibPoints'] = sprintf('%0.2f', $rankings[$i]['instagibPoints']);
        $rankings[$i]['rocketArenaPoints'] = sprintf('%0.2f', $rankings[$i]['rocketArenaPoints']);
        $rankings[$i]['points'] = sprintf('%0.2f', $rankings[$i]['points']);
    }
}
?>
