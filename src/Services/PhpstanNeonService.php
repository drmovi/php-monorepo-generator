<?php

namespace Drmovi\MonorepoGenerator\Services;

class PhpstanNeonService extends NeonFileService
{

    public function __construct(string $path)
    {
        parent::__construct($this->path = $path . DIRECTORY_SEPARATOR . 'phpstan.neon');
    }

    public function addExtensionRefs(array $paths): void
    {
        $content = $this->getContent();
        foreach ($paths as $path) {
            $content['includes'][] = $path;
        }
        $this->setContent($content);
    }

    public function addExcludePaths(array $paths): void
    {
        $content = $this->getContent();
        foreach ($paths as $path) {
            $content['parameters']['excludePaths'][] = $path;
        }
        $this->setContent($content);
    }

    public function addRules(array $rules):void
    {
        $content = $this->getContent();
        foreach ($rules as $rule) {
            $content['rules'][$rule] = true;
        }
        $this->setContent($content);
    }
}