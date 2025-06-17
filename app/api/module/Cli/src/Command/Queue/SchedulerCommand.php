<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'queue:scheduler';

    private array $schedules = [
        [
            'name' => 'process_queue_general',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => [
                '--exclude', 'que_typ_ch_compare,que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing,que_typ_print,que_typ_disc_printing_print,que_typ_create_com_lic,que_typ_remove_deleted_docs,que_typ_permit_generate,que_typ_permit_print,que_typ_run_ecmt_scoring,que_typ_accept_ecmt_scoring,que_typ_irhp_permits_allocate'
            ]
        ],
        [
            'name' => 'process_queue_community_licences',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_create_com_lic']
        ],
        [
            'name' => 'process_queue_disc_generation',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing']
        ],
        [
            'name' => 'process_queue_print',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_print']
        ],
        [
            'name' => 'process_queue_permit_generation',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_permit_generate']
        ],
        [
            'name' => 'process_queue_ecmt_accept',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_accept_ecmt_scoring']
        ],
        [
            'name' => 'process_queue_irhp_allocate',
            'interval' => 90,
            'command' => 'queue:process-queue',
            'args' => ['--type', 'que_typ_run_ecmt_scoring']
        ],
        [
            'name' => 'transxchange_consumer',
            'interval' => 90,
            'command' => 'queue:transxchange-consumer',
            'local_enabled' => false,
            'args' => []
        ],
        [
            'name' => 'process_company_profile',
            'interval' => 5,
            'command' => 'queue:process-company-profile',
            'local_enabled' => false,
            'args' => []
        ]
    ];

    private array $runningProcesses = [];
    private bool $shutdown = false;

    public function __construct(CommandHandlerManager $commandHandlerManager)
    {
        parent::__construct($commandHandlerManager);
    }

    protected function configure(): void
    {
        $this->setDescription('Continuous queue processor scheduler');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $activeSchedules = $this->getActiveSchedules();
        $this->output->writeln('<info>Starting queue scheduler with ' . count($activeSchedules) . ' schedules</info>');

        // Register signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        $nextRuns = [];
        foreach ($activeSchedules as $index => $schedule) {
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

            foreach ($activeSchedules as $index => $schedule) {
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
            'scheduled_jobs' => count($this->schedules),
            'status' => 'healthy'
        ];

        file_put_contents('/tmp/scheduler-health.json', json_encode($health));
    }

    private function getActiveSchedules(): array
    {
        $environment = getenv('ENVIRONMENT_NAME') ?: 'local';
        $isLocal = strtolower($environment) === 'local';

        $activeSchedules = [];
        foreach ($this->schedules as $schedule) {
            // If local_enabled is not set, default to true (backwards compatibility)
            $localEnabled = $schedule['local_enabled'] ?? true;

            if ($isLocal && !$localEnabled) {
                $this->output->writeln("<comment>Skipping '{$schedule['name']}' (disabled for local environment)</comment>");
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
