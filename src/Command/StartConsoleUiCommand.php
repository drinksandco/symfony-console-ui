<?php

declare(strict_types=1);

namespace Drinksco\ConsoleUiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class StartConsoleUiCommand extends Command
{
    /** @var array<Process>  */
    private array $activeProcesses = [];

    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('_ui')
            ->setDescription('Start Console UI Application')
            ->addArgument(
                'console_environment',
                InputArgument::OPTIONAL,
                'Select between "local" or "docker" environments',
                'local'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $input->getArgument('console_environment');
        /** @psalm-suppress RedundantCondition */
        Assert::string($environment);

        if ('local' === $environment) {
            try {
                $this->runMercureLocal();
                $this->runQueue();
                $this->runWebServer();
                $this->runElectronApp();

                while (count($this->activeProcesses)) {
                    foreach ($this->activeProcesses as $index => $runningProcess) {
                        // specific process is finished, so we remove it
                        if (! $runningProcess->isRunning()) {
                            unset($this->activeProcesses[$index]);
                            if (!$runningProcess->isSuccessful()) {
                                throw new ProcessFailedException($runningProcess);
                            }
                        }

                        $output->write($runningProcess->getIncrementalOutput());
                        // check every second
                        sleep(1);
                    }
                }
            } catch (ProcessFailedException $exception) {
                $process = $exception->getProcess();
                Assert::isInstanceOf($process, Process::class);
                foreach ($this->activeProcesses as $activeProcess) {
                    $activeProcess->signal(SIGKILL);
                }
                $output->writeln(sprintf(
                    '<error>%s%s</error>',
                    $process->getIncrementalOutput(),
                    $process->getErrorOutput(),
                ));
            }

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            '<error>Given console environment "%s" is not implemented</error>',
            $environment
        ));

        return Command::FAILURE;
    }

    private function runMercureLocal(): void
    {
        $process = new Process(['./mercure'], $this->projectDir, [
            'JWT_KEY' => $_SERVER['MERCURE_JWT_SECRET'],
            'ADDR' => $_SERVER['MERCURE_HOST'],
            'ALLOW_ANONYMOUS' => 1,
            'CORS_ALLOWED_ORIGINS' => '*'
        ]);
        $process->setTimeout(0);
        $process->setPty(true);

        $process->start();

        $this->activeProcesses[] = $process;
    }

    private function runQueue(): void
    {
        $process = new Process(['bin/console', 'enqueue:consume', '--client=console_queue', '-vvv'], $this->projectDir);
        $process->setTimeout(0);
        $process->setPty(true);

        $process->start();

        $this->activeProcesses[] = $process;
    }

    private function runWebServer(): void
    {
        $process = new Process(['php', '-S', 'localhost:3000', '-t', 'public'], $this->projectDir);
        $process->setTimeout(0);
        $process->setPty(true);

        $process->start();

        $this->activeProcesses[] = $process;
    }

    private function runElectronApp(): void
    {
        $process = new Process(['npm', 'run', 'console-ui-start'], $this->projectDir);
        $process->setTimeout(0);
        $process->setPty(true);

        $process->start();

        $this->activeProcesses[] = $process;
    }
}
