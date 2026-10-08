<?php

$seed = 20261008;
$files = glob(dirname(__DIR__) . '/Unit/*Test.php');
sort($files);

mt_srand($seed);
$count = count($files);
for ($i = $count - 1; $i > 0; --$i) {
    $j = mt_rand(0, $i);
    $tmp = $files[$i];
    $files[$i] = $files[$j];
    $files[$j] = $tmp;
}

$relativeFiles = array();
foreach ($files as $file) {
    $relativeFiles[] = 'Unit/' . basename($file);
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<phpunit bootstrap="bootstrap.php" colors="true" backupGlobals="false">' . "\n";
$xml .= '    <testsuites>' . "\n";
$xml .= '        <testsuite name="ps_cashondelivery-shuffle-' . $seed . '">' . "\n";
foreach ($relativeFiles as $relative) {
    $xml .= '            <file>' . $relative . '</file>' . "\n";
}
$xml .= '        </testsuite>' . "\n";
$xml .= '    </testsuites>' . "\n";
$xml .= '</phpunit>' . "\n";

$output = dirname(__DIR__) . '/phpunit-shuffle-20261008.xml';
file_put_contents($output, $xml);

echo $output . "\n";
