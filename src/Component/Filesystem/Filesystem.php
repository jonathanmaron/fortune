<?php
declare(strict_types=1);

namespace App\Component\Filesystem;

use App\PhpEncoder\PhpEncoder;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem as ParentFilesystem;

class Filesystem extends ParentFilesystem
{
    public function arrayExportFiles(string $path, array $chunks): bool
    {
        $counter = 1;
        foreach ($chunks as $chunk) {
            $name     = sprintf('%s %d', __METHOD__, $counter);
            $uuid5    = Uuid::uuid5(APP_UUID5_NAMESPACE, $name);
            $filename = sprintf("%s/%s.php", $path, $uuid5->toString());
            $this->arrayExportFile($filename, $chunk);
            $counter++;
        }

        return true;
    }

    public function arrayExportFile(string $filename, array $data): bool
    {
        $ret = file_put_contents($filename, $this->serialize($data));

        return is_int($ret) && is_readable($filename);
    }

    private function serialize(array $data): string
    {
        $phpEncoder = new PhpEncoder();

        $format = <<<EOT
            <?php
            declare(strict_types=1);
            
            // This file was programmatically built
            
            // phpcs:disable
            
            return %s;
            
            // phpcs:enable
            EOT;

        return sprintf($format, $phpEncoder->encode($data));
    }
}
