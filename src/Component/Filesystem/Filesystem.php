<?php
declare(strict_types=1);

namespace Application\Component\Filesystem;

use Application\PhpEncoder\PhpEncoder;
use Symfony\Component\Filesystem\Filesystem as ParentFilesystem;

class Filesystem extends ParentFilesystem
{
    public function arrayExportFiles(string $path, string $pattern, array $chunks): bool
    {
        $counter = 1;
        foreach ($chunks as $chunk) {
            $file     = sprintf($pattern, $counter);
            $filename = sprintf("%s/%s", $path, $file);
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
        $options    = [
            'array.align'   => true,  // Documentation is at:
            'array.indent'  => 4,     // https://goo.gl/YTobc2
            'array.inline'  => false,
            'array.omit'    => true,
            'array.short'   => true,
            'object.format' => 'export',
            'string.utf8'   => true,
            'whitespace'    => true,
        ];
        $phpEncoder = new PhpEncoder($options);

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
