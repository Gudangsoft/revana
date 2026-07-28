<?php
$filePath = 'd:\LPKD-APJI\SIPERA\app\Http\Controllers\Pic\JournalManagementController.php';
$content = file_get_contents($filePath);

// Define replacements as associative array 'search' => 'replace'
$replacements = [
    // 1. getCurrentRoleForPic
    "'PRODUCTION' => ['petugas_production_id' => 'Production']," => "'PRODUCTION' => ['petugas_production_id' => 'Production'],\n            'VALIDATOR_PROCESS' => ['petugas_validator_id' => 'Validator'],",

    // 2. toggleValidation fields validation
    "'field' => 'required|string|in:editor1_valid,author1_valid,editor2_valid,reviewer1_valid,reviewer2_valid,editor3_valid,author2_valid,production_valid'," => "'field' => 'required|string|in:editor1_valid,author1_valid,editor2_valid,reviewer1_valid,reviewer2_valid,editor3_valid,author2_valid,production_valid,validator_valid',",

    // 3. toggleValidation field map
    "'production_valid' => 'petugas_production_id'," => "'production_valid' => 'petugas_production_id',\n            'validator_valid' => 'petugas_validator_id',",

    // 4. toggleValidation switch
    "case 'production_valid':
                    \$stageName = 'Production';
                    \$stepName = 'production';
                    break;" => "case 'production_valid':
                    \$stageName = 'Production';
                    \$stepName = 'production';
                    break;
                case 'validator_valid':
                    \$stageName = 'Validator';
                    \$stepName = 'validator';
                    break;",

    // 5. toggleValid stage validation
    "'stage' => 'required|string|in:editor1,author1,editor2,reviewer1,reviewer2,editor3,author2,production'," => "'stage' => 'required|string|in:editor1,author1,editor2,reviewer1,reviewer2,editor3,author2,production,validator',",

    // 6. toggleValid stage checking
    "'production' => 'petugas_production_id'," => "'production' => 'petugas_production_id',\n            'validator' => 'petugas_validator_id',",

    // 7. updatePetugas validation
    "petugas_author2_id,petugas_production_id'," => "petugas_author2_id,petugas_production_id,petugas_validator_id',",

    // 8. submissionsMonitoring query
    "->orWhere('petugas_production_id', \$picId);" => "->orWhere('petugas_production_id', \$picId)\n              ->orWhere('petugas_validator_id', \$picId);",

    // 9. urgent mappings
    "'PRODUCTION' => ['petugas_production_id']," => "'PRODUCTION' => ['petugas_production_id'],\n            'VALIDATOR' => ['petugas_validator_id'],\n            'VALIDATOR_PROCESS' => ['petugas_validator_id'],",

    // 10. getNextStatus
    "'AUTHOR2_REVISION' => 'PRODUCTION_PROCESS',
            'PRODUCTION_PROCESS' => 'PUBLISHED',
            'PRODUCTION_REVISION' => 'PUBLISHED'," => "'AUTHOR2_REVISION' => 'PRODUCTION_PROCESS',
            'PRODUCTION_PROCESS' => 'VALIDATOR_PROCESS',
            'PRODUCTION_REVISION' => 'VALIDATOR_PROCESS',
            'VALIDATOR_PROCESS' => 'PUBLISHED',
            'VALIDATOR_REVISION' => 'PUBLISHED',",

    // 11. getValidField
    "'PRODUCTION' => 'production_valid'," => "'PRODUCTION' => 'production_valid',\n            'VALIDATOR' => 'validator_valid',",

    // 12. getStepFromStatus
    "'PRODUCTION' => 'production'," => "'PRODUCTION' => 'production',\n            'VALIDATOR' => 'validator',",

    // 13. isUrgentForPic mapping
    "'PRODUCTION_PROCESS' => ['petugas_production_id'],
            'PRODUCTION_REVISION' => ['petugas_production_id']," => "'PRODUCTION_PROCESS' => ['petugas_production_id'],
            'PRODUCTION_REVISION' => ['petugas_production_id'],
            'VALIDATOR_PROCESS' => ['petugas_validator_id'],",

    // 14. Eager loads in query (optional but good)
    "'petugasReviewer2', 'petugasProduction']" => "'petugasReviewer2', 'petugasProduction', 'petugasValidator']",
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
