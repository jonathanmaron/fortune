<?php
declare(strict_types=1);

namespace App\PhpEncoder;

use Riimu\Kit\PHPEncoder\PHPEncoder as ParentPhpEncoder;

class PhpEncoder extends ParentPhpEncoder
{
    public function __construct(array $options = [], $encoders = null)
    {
        unset($options);

        $options = [
            'array.inline'     => false,
            'array.omit'       => true,
            'array.short'      => true,
            'hex.capitalize'   => false,
            'object.format'    => 'export',
            'recursion.detect' => true,
            'recursion.ignore' => false,
            'recursion.max'    => false,
            'string.utf8'      => true,
            'whitespace'       => true,
        ];

        parent::__construct($options, $encoders);
    }
}
