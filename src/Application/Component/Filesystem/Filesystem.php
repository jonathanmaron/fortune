<?php

namespace Application\Component\Filesystem;

use Application\PhpEncoder\PhpEncoder;
use Symfony\Component\Filesystem\Filesystem as ParentFilesystem;

class Filesystem extends ParentFilesystem
{
    public function dumpFiles($path, $pattern, $chunks)
    {
        $counter = 1;

        foreach ($chunks as $key => $chunk) {

            $file     = sprintf($pattern, $counter);
            $filename = sprintf("%s/%s", $path, $file);

            $this->dumpFile($filename, $chunk);

            $counter++;
        }
    }

    public function dumpFile($filename, $data)
    {
        return parent::dumpFile($filename, $this->serialize($data));
    }

    private function serialize($data)
    {
        $phpEncoder = new PhpEncoder();

        return sprintf("<?php\n\nreturn %s;\n", $phpEncoder->encode($data));
    }
}
