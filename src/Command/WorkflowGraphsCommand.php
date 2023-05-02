<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Command;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:workflow-graphs',
    description: 'Regenerate the workflow graph(s) from the workflow definition (requires mmdc)',
)]
class WorkflowGraphsCommand extends Command
{
    public function __construct(private readonly Filesystem $filesystem, string $name = null) { parent::__construct($name); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $docFilename = 'doc/membership.svg';
        $assetFilename = 'assets/images/membership.svg';

        $dumpCommand = $this->getApplication()->find('workflow:dump');
        $dumpInput = new ArrayInput(['name' => 'membership', '--dump-format' => 'mermaid']);
        $dumpOutput = new BufferedOutput();
        $dumpCommand->run($dumpInput, $dumpOutput);

        $process = new Process(['mmdc', '-o', $docFilename]);
        $process->setInput($dumpOutput->fetch());
        $process->run();

        $this->filesystem->copy($docFilename, $assetFilename);

        return Command::SUCCESS;
    }
}
