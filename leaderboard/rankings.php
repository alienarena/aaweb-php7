<?php
include 'common.php';

require dirname(__FILE__).'/mustache.php-2.12.0/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    /*'cache' => dirname(__FILE__).'/tmp/cache/mustache',*/
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

$orderByDeathMatch = 1;
$orderByInstagib = 2;
$orderByRocketArena = 3;
$orderByTotal = 0; // default
$orderby = intval($_GET['orderby']);

$tourneyStartDate = '2029-01-27';

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Alien Arena tournament leaderboard</title>\n";
echo "    <meta name=\"description\" content=\"Alien Arena tournament leaderboard\">\n";
echo "    <meta name=\"keywords\" content=\"Alien Arena Warriors of Mars tournament leaderboard scores statistics stats\">\n";
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
echo "    <div class=\"pagetitle\">Alien Arena tournament leaderboard</div>\n";
echo "    <div class=\"menu\"><a href=\"index.php\">Results</a><span class=\"navdisabled\">&nbsp;|&nbsp;</span><span class=\"active\">Rankings</span></div>\n";

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
    global $mustache, $tourneyStartDate;
    
    $template = $mustache->loadTemplate('rankingstemplate');

    $json = fileGetContents("gamedata/rankings.json");
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

    switch ($orderby) {
        case $orderByDeathMatch:
            $data['orderByDeathMatch'] = 'selected';
            break;
        case $orderByInstagib:
            $data['orderByInstagib'] = 'selected';
            break;
        case $orderByRocketArena:
            $data['orderByRocketArena'] = 'selected';
            break;
        default:
            $data['orderByTotal'] = 'selected';
    }

    usort($data['rankings'], "doSorting");
}

function doSorting($a, $b) {
    global $orderby, $orderByDeathMatch, $orderByInstagib, $orderByRocketArena, $orderByTotal;

    switch ($orderby) {
        case $orderByDeathMatch:
            return $b['deathMatchPoints'] - $a['deathMatchPoints'];
        case $orderByInstagib:
            return $b['instagibPoints'] - $a['instagibPoints'];
        case $orderByRocketArena:
            return $b['rocketArenaPoints'] - $a['rocketArenaPoints'];
        default:
            return $b['points'] - $a['points'];
    }
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
    }
}
?>
