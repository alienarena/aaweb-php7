<?php

function redirect($url, $statusCode = 303)
{
   header('Location: ' . $url, true, $statusCode);
   die();
}

function rootUrl() {
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $url = "https://";   
    } else {
        $url = "http://";   
    }
    // Append the host(domain name, ip) to the URL.   
    $url .= $_SERVER['HTTP_HOST'];
    return $url;
}

function currentUrl() {
    return rootUrl().$_SERVER['REQUEST_URI'];
}

function removeParameters($url) {
    $questionMarkPosition = strpos($url, '?');
    if ($questionMarkPosition > 0) {
        return substr($url, 0, $questionMarkPosition);
    }
    return $url;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function getCachedContents($fileName)
{
    $data_json = (isset($_SESSION[$fileName]) ? $_SESSION[$fileName] : NULL);
    if (strlen($data_json) == 0) {
        $data_json = fileGetContents("gamedata/$fileName");
        $_SESSION[$fileName] = $data_json;    
    }
    return $data_json;
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

// Input is a date string in the format yyyy-MM-dd HH:mm:ss, or empty
function dateToString($date = false)
{
    if (strlen($date) > 0)
    {
        return date('F j, Y', strtotime($date));
    } else
    {
        return date('F j, Y');
    }
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
