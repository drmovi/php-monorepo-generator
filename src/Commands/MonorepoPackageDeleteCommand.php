<?php

namespace Drmovi\MonorepoGenerator\Commands;

use Composer\Console\Input\InputArgument;
use Drmovi\MonorepoGenerator\Actions\DeletePackageAction;
use Drmovi\MonorepoGenerator\Dtos\ActionDto;
use Drmovi\MonorepoGenerator\Dtos\Configs;
use Drmovi\MonorepoGenerator\Services\ComposerFileService;
use Drmovi\MonorepoGenerator\Services\ComposerService;
use Drmovi\MonorepoGenerator\Services\RootComposerFileService;
use Drmovi\MonorepoGenerator\Utils\FileUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'monorepo:package:delete',
    description: 'Delete a package.',
    hidden: false
)]
class MonorepoPackageDeleteCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of your package', null)
            ->addArgument('shared', InputArgument::OPTIONAL, 'Package is shared one', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $composerService = new ComposerService($output);
        $configs = Configs::loadFromComposer(new RootComposerFileService(getcwd(), $composerService));
        $this->checkPackageName($input, $io, $configs);
        $this->checkIfPackageIsShared($input, $configs);
        return (new DeletePackageAction(new ActionDto(
            command: $this,
            input: $input,
            output: $output,
            io: $io,
            composerService: $composerService,
            configs: $configs,
        )))->exec();
    }


    private function checkPackageName(InputInterface $input, SymfonyStyle $io, Configs $configs): void
    {
        $name = $input->getArgument('name');
        if (!is_null($name)) {
            try {
                $this->validatePackageName($name, $configs, $input);
                return;
            } catch (\Throwable $e) {
                $io->error($e->getMessage());
            }
        }
        $input->setArgument('name', $io->ask('Name of your package', null, function ($answer, $configs, $input) {
            $this->validatePackageName($answer, $configs, $input);
            return $answer;
        }));
    }

    private function validatePackageName(string $name, Configs $configs): void
    {
        if (FileUtil::directoryExist(getcwd() . DIRECTORY_SEPARATOR . $configs->getPackagesPath() . DIRECTORY_SEPARATOR . $name)) {
            return;
        }
        if (FileUtil::directoryExist(getcwd() . DIRECTORY_SEPARATOR . $configs->getSharedPackagesPath() . DIRECTORY_SEPARATOR . $name)) {
            return;
        }
        throw new \RuntimeException("Package with name $name already exists !!");
    }

    private function checkIfPackageIsShared(InputInterface $input, Configs $configs): void
    {
        if (FileUtil::directoryExist(getcwd() . DIRECTORY_SEPARATOR . $configs->getSharedPackagesPath() . DIRECTORY_SEPARATOR . $input->getArgument('name'))) {
            $input->setArgument('shared', true);
        }
    }

}
