<?php
declare(strict_types=1);

use Application\Component\Filesystem\Filesystem;

define('APPLICATION_ROOT', realpath(dirname(__DIR__)));

require_once APPLICATION_ROOT . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';

error_reporting(E_ALL);
set_time_limit(0);

$inputFilename  = __DIR__ . '/fortunes';
$outputFilename = $inputFilename . '.php';

$fortunes = [];

$buffer = file_get_contents($inputFilename);
$stack  = explode('%', $buffer);

foreach ($stack as $key => $quote) {

    $quote  = str_trim($quote);
    $author = multi_strrchr($quote, ['―', '—']);

    if (!empty($author)) {

        $quote = str_replace($author, '', $quote);

        $author = trim($author);
        $author = mb_substr($author, 1);
        $author = trim($author);
    }

    $quote = trim($quote);
    $uuid  = uuid($quote);

    if (array_key_exists($uuid, $fortunes)) {
        continue;
    }

    if (empty($quote)) {
        continue;
    }

    if (empty($author)) {
        $author = 'Unknown';
    }

    $fortunes[$uuid] = [
        $quote,
        $author,
    ];
}

$filesystem = new Filesystem();

$filesystem->dumpFile($outputFilename, $fortunes);

echo sprintf('Written to "%s"', $outputFilename);
echo PHP_EOL;
