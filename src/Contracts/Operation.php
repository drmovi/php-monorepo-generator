<?php

namespace Drmovi\PackageGenerator\Contracts;

interface Operation extends State
{
    public function exec(): void;

    public function init(): void;
}
