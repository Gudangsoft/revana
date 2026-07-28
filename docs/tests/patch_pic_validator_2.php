<?php
$filePath = 'd:\LPKD-APJI\SIPERA\app\Http\Controllers\Pic\JournalManagementController.php';
$content = file_get_contents($filePath);

$replacements = [
    "'author2_valid',      // OPTIONAL
                'production_valid'," => "'author2_valid',      // OPTIONAL
                'production_valid',
                'validator_valid',",

    "'author2_valid' => 'Author 2',
                        'production_valid' => 'Production'," => "'author2_valid' => 'Author 2',
                        'production_valid' => 'Production',
                        'validator_valid' => 'Validator',",
];

$count = 0;
foreach ($replacements as $search => $replace) {
    // Also try checking with \r\n vs \n
    $searchWindows = str_replace("\n", "\r\n", $search);
    $replaceWindows = str_replace("\n", "\r\n", $replace);

    if (strpos($content, $searchWindows) !== false) {
        $content = str_replace($searchWindows, $replaceWindows, $content);
        $count++;
    } elseif (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        $count++;
    } else {
        echo "Failed to replace: \n" . substr($search, 0, 50) . "...\n\n";
    }
}

file_put_contents($filePath, $content);
echo "Replaced $count occurrences.\n";
