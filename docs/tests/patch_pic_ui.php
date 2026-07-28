<?php
$filePath = 'd:\LPKD-APJI\SIPERA\resources\views\pic\submissions\show.blade.php';
$content = file_get_contents($filePath);

$replacements = [
    "'author2' => 'Author 2', 'production' => 'Production'," => "'author2' => 'Author 2', 'production' => 'Production', 'validator' => 'Validator',",
    "<div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->production_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Production</small>
            </div>" => "<div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->production_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Production</small>
            </div>
            <div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->validator_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Validator</small>
            </div>",
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
echo "Replaced $count occurrences in show.blade.php.\n";

$filePath = 'd:\LPKD-APJI\SIPERA\resources\views\pic\submissions\process.blade.php';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    $replaced = false;
    
    // Add Validator to step labels
    $search1 = "'author2' => 'Author 2', 'production' => 'Production',";
    $replace1 = "'author2' => 'Author 2', 'production' => 'Production', 'validator' => 'Validator',";
    if (strpos($content, $search1) !== false || strpos($content, str_replace("\n", "\r\n", $search1)) !== false) {
        $content = str_replace($search1, $replace1, $content);
        $content = str_replace(str_replace("\n", "\r\n", $search1), str_replace("\n", "\r\n", $replace1), $content);
        $replaced = true;
    }

    $search2 = "<div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->production_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Production</small>
            </div>";
    $replace2 = "<div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->production_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Production</small>
            </div>
            <div class=\"col\">
                <div class=\"rounded-circle mx-auto d-flex align-items-center justify-content-center {{ \$submission->validator_valid ? 'bg-success' : 'bg-secondary' }}\" style=\"width: 40px; height: 40px;\">
                    <i class=\"bi bi-check text-white\"></i>
                </div>
                <small>Validator</small>
            </div>";
    
    if (strpos($content, $search2) !== false || strpos($content, str_replace("\n", "\r\n", $search2)) !== false) {
        $content = str_replace($search2, $replace2, $content);
        $content = str_replace(str_replace("\n", "\r\n", $search2), str_replace("\n", "\r\n", $replace2), $content);
        $replaced = true;
    }
    
    if ($replaced) {
        file_put_contents($filePath, $content);
        echo "Replaced occurrences in process.blade.php.\n";
    }
}
