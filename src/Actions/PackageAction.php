<?php

namespace Drmovi\MonorepoGenerator\Actions;

use Drmovi\MonorepoGenerator\Contracts\Operation;
use Drmovi\MonorepoGenerator\Dtos\ActionDto;
use Drmovi\MonorepoGenerator\Dtos\PackageData;
use Drmovi\MonorepoGenerator\Dtos\PackageDto;
use Drmovi\MonorepoGenerator\Factories\FrameworkOperationFactory;
use Drmovi\MonorepoGenerator\Services\ComposerFileService;
use Drmovi\MonorepoGenerator\Services\PhpstanNeonService;
use Drmovi\MonorepoGenerator\Services\PhpUnitXmlFileService;
use Drmovi\MonorepoGenerator\Services\SkaffoldYamlFileService;
use Drmovi\MonorepoGenerator\Utils\FileUtil;
use Symfony\Component\Console\Command\Command;

abstract class PackageAction implements Operation
{

    protected PackageData $packageData;

    public function __construct(protected readonly ActionDto $actionDto)
    {
        $this->packageData = new PackageData(
            rootComposerFileService: new ComposerFileService(getcwd(), $this->actionDto->composerService),
            rootPhpunitXmlFileService: new PhpUnitXmlFileService(getcwd()),
            rootSkaffoldYamlFileService: new SkaffoldYamlFileService(getcwd()),
            phpstanNeonFileService: new PhpstanNeonService(getcwd() . DIRECTORY_SEPARATOR . $this->actionDto->configs->getDevConfPath()),
            packageName: $packageName = $this->actionDto->input->getArgument('name'),
            packageRelativePath: $packageRelativePath = ($this->isSharedPackage() ? $this->actionDto->configs->getSharedPackagesPath() : $this->actionDto->configs->getPackagesPath()) . DIRECTORY_SEPARATOR . $packageName,
            packageAbsolutePath: getcwd() . DIRECTORY_SEPARATOR . $packageRelativePath,
            packageNamespace: $this->getPackageNamespace($packageName),
            packageSkaffoldYamlFileRelativePath: $packageRelativePath . '/k8s/skaffold.yaml',
            packageComposerName: $this->getComposerPackageName($packageName),
            isSharedPackage: $this->isSharedPackage()
        );

    }




    abstract protected function _exec(): void;

    abstract public function backup(): void;

    abstract public function rollback(): void;


    private function getComposerPackageName(string $packageName): string
    {
        return "{$this->actionDto->configs->getVendorName()}/{$packageName}";
    }

    private function isSharedPackage(): bool
    {
        return (bool)$this->actionDto->input->getArgument('shared');
    }

    protected function getPackageNamespace(string $packageName): string
    {
        return implode('\\', [
            ucwords($this->actionDto->configs->getVendorName()),
            ucwords($packageName)
        ]);
    }


    protected function copyStubFiles(
        string $source,
        string $destination,
        string $composerName,
        string $packageNamespace,
        string $packageName,
        string $appPath,
        string $packagePath,
        string $sharedPackagePath
    ): void
    {
        FileUtil::copyDirectory(
            source: __DIR__ . '/../../stubs/' . $source,
            destination: $destination,
            replacements: [
                '{{APP_PATH}}' => $appPath,
                '{{PACKAGES_PATH}}' => $packagePath,
                '{{SHARED_PACKAGES_PATH}}' => $sharedPackagePath,
                '{{PROJECT_COMPOSER_NAME}}' => $composerName,
                '{{PROJECT_VERSION}}' => '1.0.0',
                '{{PROJECT_DESCRIPTION}}' => 'This is a package generated by drmovi PHP Package Generator',
                '{{PROJECT_COMPOSER_NAMESPACE}}' => str_replace('\\', '\\\\', $packageNamespace),
                '{{PROJECT_NAMESPACE}}' => $packageNamespace,
                '{{PROJECT_CLASS_NAME}}' => ucwords($packageName),
                '{{PROJECT_FILE_NAME}}' => strtolower($packageName),
            ]);
    }

}
