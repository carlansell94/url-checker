<?php

require_once '../vendor/autoload.php';

use carlansell94\UrlChecker\{Check, Formatter};

// Check an uploaded file
$result = Check::fileUpload();

// Filter the result
$result = Formatter::filter(
    $result,
    'url',
    'http_code',
    'redirect_count'
);

// Save result to CSV
Formatter::toCsv(
    $result,
    './output.csv'
);

// Print JSON-encoded result
header('Content-Type: application/json');
echo Formatter::toJson(
    $result
);
