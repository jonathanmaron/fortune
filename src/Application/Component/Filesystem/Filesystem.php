<?php

namespace Application\Component\Filesystem;

use Application\PhpEncoder\PhpEncoder;
use Symfony\Component\Filesystem\Filesystem as ParentFilesystem;

class Filesystem extends ParentFilesystem
{
    public function dumpFiles($path, $pattern, $chunks)
    {
        $phpEncoder = new PhpEncoder();

        $counter = 1;

        foreach ($chunks as $key => $chunk) {

            $file     = sprintf($pattern, $counter);
            $filename = sprintf("%s/%s", $path, $file);
            $data     = sprintf("<?php\n\nreturn %s;\n", $phpEncoder->encode($chunk));

            $this->dumpFile($filename, $data);

            $counter++;
        }
    }
}
