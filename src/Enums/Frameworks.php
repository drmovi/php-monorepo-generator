<?php

namespace Drmovi\MonorepoGenerator\Enums;

use Drmovi\MonorepoGenerator\Utils\EnumUtils;

enum Frameworks : string
{
    use EnumUtils;
    case LARAVEL = 'laravel';
}
