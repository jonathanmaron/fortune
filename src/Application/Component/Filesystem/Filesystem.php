<?php

declare(strict_types=1);

namespace Application\Component\Filesystem;

use Application\PhpEncoder\PhpEncoder;
use Symfony\Component\Filesystem\Filesystem as ParentFilesystem;

class Filesystem extends ParentFilesystem
{
    public function dumpFiles(string $path, string $pattern, array $chunks): bool
    {
        $counter = 1;
        foreach ($chunks as $key => $chunk) {
            $file     = sprintf($pattern, $counter);
            $filename = sprintf("%s/%s", $path, $file);
            $this->dumpFile($filename, $chunk);
            $counter++;
        }

        return true;
    }

    public function dumpFile($filename, $data)
    {
        return parent::dumpFile($filename, $this->serialize($data));
    }

    private function serialize(array $data): string
    {
        $phpEncoder = new PhpEncoder();

        return sprintf("<?php\n\ndeclare(strict_types=1);\n\nreturn %s;\n", $phpEncoder->encode($data));
    }
}
