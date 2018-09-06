<?php
require dirname(__FILE__).'/mustache.php-2.12.0/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    'cache' => dirname(__FILE__).'/tmp/cache/mustache',
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

// Global variables
$details = false;
$maxPlayersForDetails = 25;    
$maxPlayersTopView = 12;    
$template = $mustache->loadTemplate('scoretemplate');
$detailsTemplate = $mustache->loadTemplate('scoretemplatedetails');
$weaponAccuracyTemplate = $mustache->loadTemplate('weaponaccuracy');
$detailsHtml = "";

// Get game files.
// The file name must be in the format: 
// "gamereport_2017-11-30_01.17.17_Martian Supremacy Tournament.json"
// or "gamereport_2017-11-30_01.17.17.json"
// The title is optional (with or without spaces). Without title it will display "- No title -".
$path = dirname(__FILE__).'/gamedata';
$files = array_diff(scandir($path), array('.', '..'));

if (count($files) == 1)
{
    $details = true;
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Alien Arena tournament leaderboard</title>\n";
echo "    <link href=\"https://fonts.googleapis.com/css?family=Aldrich\" rel=\"stylesheet\">\n";
echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\">\n";
echo "    <script src=\"../sharedscripts/jquery-3.3.1.min.js\"></script>\n";
echo "    <script src=\"../sharedscripts/parallaxie.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"index.js\"></script>\n";
echo "    <script type=\"text/javascript\" src=\"utils.js\"></script>\n";
echo "</head>\n";
echo "<body style=\"background-image: url('../sharedimages/site-background.jpg'); background-attachment:fixed; background-repeat:no-repeat; background-size:cover;\">\n";

/* Background with parallax-effect */
/* https://github.com/TheUltrasoft/Parallaxie */	
echo "<div id=\"content\" class=\"parallaxie\" style=\"background-image: url('../sharedimages/purgatory.jpg');\" data-parallaxie='{\"speed\": 0.8, \"size\": \"auto\"}'>\n";

echo "  <center>\n";
echo "    <div style=\"height: 30px\"></div>\n";
echo "    <div id=\"overlay\" style=\" border: none; display:none; z-index: 100; position: absolute; \n";
echo "        top: 0px; left: 0px; height: 1250px; width: 100%; background: rgb(0, 4, 8); opacity: 0.85;\" \n";
echo "        onclick=\"hidePopup();\"></div>\n";
echo "    <div class=\"pagetitle\">Alien Arena tournament leaderboard</div>\n";
echo "    <table border=\"0\" style=\"width: 750px; display: none;\" id=\"leaderboardtable\">\n";

echo "        <tr>\n";

$colCount = 0;
foreach($files as $file) 
{
    if ($colCount > 0 && $colCount % 3 == 0)
    {
        echo "</tr>\n";
        echo "<tr>\n";
    }

    renderScoreListAndPrepareDetails($file);

    $colCount++;
}

echo "        </tr>\n";

echo "    </table>\n";
echo $detailsHtml;
echo "    <script type=\"text/javascript\">\n";
echo "        $(document).ready(function() {\n";
echo "           documentReady();\n";
echo "              $(\".parallaxie\").parallaxie();\n";
if (!$details) 
{
    echo "           $(\"table.scoretable\").css(\"cursor\", \"pointer\");\n";
    echo "           $(document).keyup(function(e) {\n";
    echo "               if (e.keyCode == 27) {\n"; 
    echo "                   hidePopup();\n";
    echo "               }\n";
    echo "           });\n";
}
echo "        });\n";
echo "    </script>\n";
echo "  </center>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";


function renderScoreListAndPrepareDetails($file)
{
    global $details, $maxPlayersForDetails, $maxPlayersTopView;
    global $template, $detailsTemplate, $weaponAccuracyTemplate, $detailsHtml;
    
    $data_json = fileGetContents("gamedata/$file");
    $data = jsonDecode($data_json, true);
    array_splice($data['players'], $maxPlayersForDetails);
        
    $data = enrichData($file, $data);

    // Copy into short list of players
    $shortlist = $data;
    array_splice($shortlist['players'], $maxPlayersTopView);
    
    $height = $details ? '950px' : '480px';
    $popupId = 'popup'.$data['tourney_id'];
    $onclick = !$details ? " onclick=\"showPopup('$popupId');\"" : "";
    echo "<td style=\"vertical-align: top; height: $height;\" $onclick>\n";
    echo $details ? $detailsTemplate->render($data) : $template->render($shortlist);
    echo "</td>\n";
    
    if (!$details) 
    {
        $detailsHtml = $detailsHtml."<div class=\"details\" id=\"$popupId\" style=\"display: none; z-index: 200; ";
        $detailsHtml = $detailsHtml."  position: absolute; top: 90px; left: 50%; margin-left: -363px; height: 950px; width: 725px;\" ";
        $detailsHtml = $detailsHtml."  onclick=\"hidePopup();\">\n";
        $detailsHtml = $detailsHtml.$detailsTemplate->render($data);
        $detailsHtml = $detailsHtml."</div>\n";    
    }
    $detailsHtml = $detailsHtml.$weaponAccuracyTemplate->render($data);
}

function enrichData($file, $data)
{
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
   
    $tourneyTitle = strlen($file) <= 35 ? '- No title -' : substr($file, 31, strlen($file) - 36);
    $dateString = substr($file, 11, 10);
    $tourneyId = str_replace(' ', '', $tourneyTitle).$dateString;
    
    // Fill tourney title, tourney id and tourney date which are used in the templates
    $data['tourney_title'] = $tourneyTitle;
    $data['tourney_id'] = $tourneyId;
    $data['tourney_date'] = date('F j, Y', strtotime($dateString));
    
    return $data;
}

function fileGetContents($path)
{
    $data_json = file_get_contents($path);

    // This will remove unwanted characters.
    // Check http://www.php.net/chr for details
    for ($i = 0; $i <= 31; ++$i) 
    { 
        $data_json = str_replace(chr($i), '', $data_json); 
    }
    $data_json = str_replace(chr(127), '', $data_json);

    // This is the most common part
    // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
    // here we detect it and we remove it, basically it's the first 3 characters 
    if (0 === strpos(bin2hex($data_json), 'efbbbf')) 
    {
        $data_json = substr($data_json, 3);
    }
    return $data_json;
}

function jsonDecode($json, $assoc = false)
{
    $ret = json_decode($json, $assoc);
    if ($error = json_last_error())
    {
        $errorReference = [
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
            JSON_ERROR_SYNTAX => 'Syntax error.',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
        ];
        $errStr = isset($errorReference[$error]) ? $errorReference[$error] : "Unknown error ($error)";
        throw new \Exception("JSON decode error ($error): $errStr");
    }
    return $ret;
}
?>
