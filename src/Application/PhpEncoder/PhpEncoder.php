<?php

namespace Application\PhpEncoder;

use Riimu\Kit\PHPEncoder\PHPEncoder as ParentPhpEncoder;

class PhpEncoder extends ParentPhpEncoder
{
    public function __construct(array $options = [], $encoders = null)
    {
        $options = [
            'array.inline'  => false,
            'array.short'   => true,
            'array.omit'    => true,
            'object.format' => 'export',
            'string.utf8'   => true,
            'whitespace'    => true,
        ];

        parent::__construct($options, $encoders);
    }
}
