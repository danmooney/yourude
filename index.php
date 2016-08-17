<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

if (!isset($_GET['text'])) {
    header('HTTP 1.1 400 Bad Request', true, 400);
    exit;
}

putenv('PATH=' . $_ENV['PATH']. ':/usr/local/bin');

$unaltered_text   = $_GET['text'];
$replacement_text = $_GET['text'];
$text             = escapeshellarg($unaltered_text);
$cmd  = "echo $text | alex 2>&1";

exec($cmd, $output_lines);

$bad_substrings  = ['fuck', 'ass', 'asshole', 'piss', 'shit', 'bullshit', 'cunt', 'twat', 'pussy', 'bitch', 'bastard', 'rape'];
$good_substrings = ['frig', 'donkey', 'donkeyhole', 'urinate', 'poop', 'poop', 'vagina', 'vagina', 'vagina', 'female dog', 'illegitimate child', 'molest'];

foreach ($output_lines as $line) {
    $is_warning = preg_match('#\bwarning\b#', $line);

    if (!$is_warning) {
        continue;
    }

    preg_match('#\bwarning\s+(t use|Reconsider using)?\s*[`"“�]([a-z]+)[`"”�]#i', $line, $words_to_replace);

    if (!count($words_to_replace)) {
        preg_match('#\bwarning.+(t use|Reconsider using|).+[`"“�]([a-z]+)[`"”�]#i', $line, $words_to_replace);
    }

    preg_match('#, use.+[`"“�]+([a-z]+)[`"”�]#i', $line, $suggestions);

    $word_to_replace = array_pop($words_to_replace);
    $suggestion      = array_pop($suggestions);

    if (!$word_to_replace) {
        continue;
    }

    if (!$suggestion) {
        foreach ($bad_substrings as $idx => $bad_substring) {
            if (preg_match("#\b$bad_substring(in?|ed)?\b#", $word_to_replace, $matches)) {
                $suffix           = implode('', array_slice($matches, 1));
                $bad_word         = $bad_substrings[$idx]  . $suffix;
                $good_word        = $good_substrings[$idx] . $suffix;
                $replacement_text = str_replace($bad_word, $good_word, $replacement_text);
            }
        }
    } else {
        $replacement_text = str_replace($word_to_replace, $suggestion, $replacement_text);
    }

}

$change_occurred = $unaltered_text !== $replacement_text;

if (!$change_occurred) {
    $replacement_text = null;
}

echo json_encode(array_filter(['isChanged' => $change_occurred, 'text' => $replacement_text], function ($val) {return $val !== null;}));