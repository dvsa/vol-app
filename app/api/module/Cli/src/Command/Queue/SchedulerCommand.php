<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'queue:scheduler';
    
    private array $runningProcesses = [];
    private bool $shutdown = false;
    private array $activeSchedules = [];
    
    public function __construct(
        CommandHandlerManager $commandHandlerManager,
        private array $config
    ) {
        parent::__construct($commandHandlerManager);
    }
    
    protected function configure(): void
    {
        $this->setDescription('Continuous queue processor scheduler');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        // Get schedules from config and filter based on environment
        $this->activeSchedules = $this->getActiveSchedules();
        $this->output->writeln('<info>Starting queue scheduler with ' . count($this->activeSchedules) . ' schedules</info>');

        // Register signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        $nextRuns = [];
        foreach ($this->activeSchedules as $index => $schedule) {
            $nextRuns[$index] = $this->calculateNextRun($schedule['interval']);
            $this->output->writeln(sprintf(
                '  - %s: every %d seconds (next: %s)',
                $schedule['name'],
                $schedule['interval'],
                $nextRuns[$index]->format('H:i:s')
            ));
        }

        while (!$this->shutdown) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            $now = new \DateTime();

            foreach ($this->activeSchedules as $index => $schedule) {
                if ($now >= $nextRuns[$index]) {
                    $this->runScheduledJob($schedule);
                    $nextRuns[$index] = $this->calculateNextRun($schedule['interval']);
                }
            }

            $this->cleanupProcesses();
            $this->writeHealthCheck();

            usleep(500000); // half a second
        }

        return 0;
    }

    private function calculateNextRun(int $intervalSeconds): \DateTime
    {
        $now = new \DateTime();
        $next = clone $now;
        
        // Get current timestamp and round up to next interval
        $currentTimestamp = $now->getTimestamp();
        $nextTimestamp = ceil($currentTimestamp / $intervalSeconds) * $intervalSeconds;
        
        $next->setTimestamp($nextTimestamp);
        
        // If we've passed this time, go to next interval
        if ($next <= $now) {
            $next->modify("+{$intervalSeconds} seconds");
        }

        return $next;
    }

    private function runScheduledJob(array $schedule): void
    {
        $name = $schedule['name'];

        // Check if already running
        if (isset($this->runningProcesses[$name])) {
            $process = $this->runningProcesses[$name];
            if ($this->isProcessRunning($process)) {
                $this->output->writeln("<comment>[$name] Still running, skipping this iteration</comment>");
                return;
            }
        }

        $cmd = [
            PHP_BINARY,
            '-d', 'memory_limit=2048M',
            '/var/www/html/vendor/bin/laminas',
            '--container=/var/www/html/config/container-cli.php',
            $schedule['command']
        ];

        foreach ($schedule['args'] as $arg) {
            $cmd[] = $arg;
        }

        $this->output->writeln("<info>[$name] Starting: " . implode(' ', array_slice($cmd, 3)) . "</info>");

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (is_resource($process)) {
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);

            $this->runningProcesses[$name] = [
                'process' => $process,
                'pipes' => $pipes,
                'started' => new \DateTime()
            ];
        } else {
            $this->output->writeln("<error>[$name] Failed to start process</error>");
        }
    }

    private function cleanupProcesses(): void
    {
        foreach ($this->runningProcesses as $name => $data) {
            $status = proc_get_status($data['process']);

            $stdout = stream_get_contents($data['pipes'][1]);
            $stderr = stream_get_contents($data['pipes'][2]);

            if ($stdout) {
                foreach (explode("\n", trim($stdout)) as $line) {
                    if (!empty($line)) {
                        $this->output->writeln("<info>[$name]</info> " . $line);
                    }
                }
            }
            if ($stderr) {
                foreach (explode("\n", trim($stderr)) as $line) {
                    if (!empty($line)) {
                        $this->output->writeln("<error>[$name]</error> " . $line);
                    }
                }
            }

            if (!$status['running']) {
                $exitCode = $status['exitcode'];
                $runtime = (new \DateTime())->diff($data['started'])->format('%i:%s');

                $this->output->writeln(sprintf(
                    "<%s>[$name] Finished with exit code %d (runtime: %s)</%s>",
                    $exitCode === 0 ? 'info' : 'error',
                    $exitCode,
                    $runtime,
                    $exitCode === 0 ? 'info' : 'error'
                ));

                foreach ($data['pipes'] as $pipe) {
                    fclose($pipe);
                }
                proc_close($data['process']);

                unset($this->runningProcesses[$name]);
            }
        }
    }

    private function isProcessRunning(array $processData): bool
    {
        $status = proc_get_status($processData['process']);
        return $status['running'];
    }

    private function writeHealthCheck(): void
    {
        $health = [
            'timestamp' => time(),
            'running_jobs' => array_keys($this->runningProcesses),
            'scheduled_jobs' => count($this->activeSchedules),
            'status' => 'healthy'
        ];

        file_put_contents('/tmp/scheduler-health.json', json_encode($health));
    }

    private function getActiveSchedules(): array
    {
        $scheduleConfig = $this->config['queue_scheduler']['schedules'] ?? [];
        $environment = getenv('ENVIRONMENT_NAME') ?: 'local';
        $isLocal = strtolower($environment) === 'local';

        $activeSchedules = [];
        foreach ($scheduleConfig as $name => $schedule) {
            // Add the schedule name to the config
            $schedule['name'] = $name;
            
            // If local_enabled is not set, default to true (backwards compatibility)
            $localEnabled = $schedule['local_enabled'] ?? true;

            if ($isLocal && !$localEnabled) {
                continue;
            }

            $activeSchedules[] = $schedule;
        }

        return $activeSchedules;
    }

    public function handleSignal($signo): void
    {
        $this->output->writeln("\n<comment>Received shutdown signal, cleaning up...</comment>");
        $this->shutdown = true;

        foreach ($this->runningProcesses as $name => $data) {
            $this->output->writeln("<comment>[$name] Terminating process</comment>");
            proc_terminate($data['process']);
        }

        sleep(2);

        foreach ($this->runningProcesses as $name => $data) {
            proc_terminate($data['process'], 9);
        }
    }
}