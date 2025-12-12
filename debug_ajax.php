<?php
// Mock GET parameters
$_GET['type'] = 'prontuario';
$_GET['q'] = 'a';

// Capture output
ob_start();
include 'ajax_search.php';
$output = ob_get_clean();

echo "--- OUTPUT START ---\n";
echo $output;
echo "\n--- OUTPUT END ---\n";
?>