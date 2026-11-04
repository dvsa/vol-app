<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\SystemUser;
use Laminas\Cli\ApplicationFactory;
use Laminas\Cli\ApplicationProvisioner;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Every AWS Batch job the terraform schedules names a command the api still has, with arguments it accepts.
 *
 * Batch jobs are the api's second entrypoint. infra/terraform/modules/service/batch.tf starts each one as
 * `vendor/bin/laminas --container=config/container-cli.php -v <the job's commands>`, and the jobs and their
 * commands are listed per environment in infra/terraform/environments/<env>/main.tf. Nothing else connects
 * the two: rename a command or one of its options, or change what config/container-cli.php builds, and CI
 * stays green while the job fails on its next scheduled run.
 *
 * Each job's command line goes through the console application vendor/bin/laminas builds — laminas-cli's
 * ApplicationFactory and ApplicationProvisioner over the booted command line container — and Symfony
 * Console finds the command and binds and validates the input, as it does before running a command.
 * Nothing is run. Jobs of another type (scripts, liquibase) run their own images and are not checked here.
 *
 * CI's sparse checkout includes infra/terraform for this test (.github/workflows/php.yaml). The api's checks
 * run when app/api or lib changes, not when only the terraform does, so a mistake made in the terraform alone
 * is reported by the next api change.
 */
final class BatchJobTest extends FrameworkTestCase
{
    /**
     * What batch.tf runs ahead of each job's own commands. The harness models this entrypoint
     * (Entrypoint::Cli is config/container-cli.php); if Batch starts jobs differently, change the harness to
     * build what the new command line builds, then this.
     */
    private const array COMMAND_LINE_PREFIX = [
        '/var/www/html/vendor/bin/laminas',
        '--container=/var/www/html/config/container-cli.php',
        '-v',
    ];

    /**
     * Jobs that are wrong in the terraform today, by job name, with the reason this test gives. Each is fixed
     * in infra/terraform, not here; once a job is right in every environment the test fails until it is taken
     * off this list, so the list cannot outlive the problem.
     */
    private const array KNOWN_FAILURES = [
        // Monthly. It runs only because Symfony Console accepts an unambiguous abbreviation, so a new command
        // whose name starts the same way would stop it.
        'create-psv-licence-surrender-task' => '"batch:create-psv-licence-surrender-task" is only an abbreviation'
            . ' of "batch:create-psv-licence-surrender-tasks"',
        // Unscheduled, so it fails only when someone submits it. The laminas-mvc-console route it was meant
        // for, `system-parameter <name> <value>`, went in the move to laminas-cli (March 2024) with no
        // replacement; the job was added to the terraform four months later.
        'system-parameter' => 'Command "batch:system-parameter" is not defined.',
    ];

    public function testJobsStartFromTheCommandLineContainer(): void
    {
        $this->assertSame(self::COMMAND_LINE_PREFIX, self::commandLinePrefix());
    }

    public function testEveryJobRunsACommandThatAcceptsItsArguments(): void
    {
        $environments = self::environments();
        $this->assertNotEmpty($environments, 'No environments found under ' . self::terraformDir());

        $console = self::console();
        $prefix = array_slice(self::commandLinePrefix(), 1);
        $failures = [];
        $stillFailing = [];

        foreach ($environments as $environment => $file) {
            $jobs = self::jobsRunByLaminas($file);
            $this->assertNotEmpty($jobs, 'No vendor/bin/laminas jobs found in ' . $file);

            foreach ($jobs as [$job, $commands]) {
                $failure = self::whyItWouldNotRun($console, [...$prefix, ...$commands]);

                if ($failure !== null && $failure === (self::KNOWN_FAILURES[$job] ?? null)) {
                    $stillFailing[$job] = true;
                } elseif ($failure !== null) {
                    $failures[$environment . ': ' . $job] = implode(' ', $commands) . ' — ' . $failure;
                }
            }
        }

        $this->assertSame([], $failures, 'These Batch jobs would fail before their command ran');
        $this->assertSame(
            [],
            array_keys(array_diff_key(self::KNOWN_FAILURES, $stillFailing)),
            'These jobs no longer fail as KNOWN_FAILURES says: take them off the list'
        );
    }

    /**
     * Symfony Console's checks before it runs a command (Application::find(), then Command::run()'s binding
     * and validation), short of running it.
     *
     * @param list<string> $arguments the command line after the executable
     */
    private static function whyItWouldNotRun(ConsoleApplication $console, array $arguments): ?string
    {
        $input = new ArgvInput(['laminas', ...$arguments]);
        $name = $input->getFirstArgument();

        if ($name === null) {
            return 'no command is named';
        }

        try {
            $command = $console->find($name);
        } catch (ExceptionInterface $e) {
            return strtok($e->getMessage(), "\n");
        }

        // find() also accepts an unambiguous abbreviation, which a new command can make ambiguous at any time
        if ($command->getName() !== $name && !in_array($name, $command->getAliases(), true)) {
            return sprintf('"%s" is only an abbreviation of "%s"', $name, $command->getName());
        }

        $command->mergeApplicationDefinition();

        try {
            $input->bind($command->getDefinition());
            $input->validate();
        } catch (ExceptionInterface $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * The console application vendor/bin/laminas builds, over the command line container.
     */
    private static function console(): ConsoleApplication
    {
        $app = ApiApplication::bootstrap(Entrypoint::Cli, SystemUser::answerTheBootstrapLookup(...));

        return (new ApplicationProvisioner())((new ApplicationFactory())(), $app->serviceManager);
    }

    /**
     * @return list<string>
     */
    private static function commandLinePrefix(): array
    {
        $file = self::terraformDir() . '/modules/service/batch.tf';
        $source = self::read($file);

        $found = preg_match(
            '/\bcommand\s*=\s*\(try\(job\.type,\s*"default"\)\s*==\s*"default"\s*\?\s*concat\(\[([^\]]*)\],\s*job\.commands\)/',
            $source,
            $match
        );
        self::assertSame(1, $found, 'Could not find how ' . $file . ' builds the command line of a default job');

        return self::strings($match[1], $file);
    }

    /**
     * @return array<string, string> environment => its main.tf
     */
    private static function environments(): array
    {
        $environments = [];

        foreach (glob(self::terraformDir() . '/environments/*/main.tf') ?: [] as $file) {
            $environments[basename(dirname($file))] = $file;
        }

        return $environments;
    }

    /**
     * The jobs of the default type, which batch.tf runs through vendor/bin/laminas.
     *
     * Each job is an object literal in the `jobs` list, with no braces inside it, so they are read with a
     * regular expression; the count of `commands =` keeps that honest.
     *
     * @return list<array{string, list<string>}> [job name, its commands]
     */
    private static function jobsRunByLaminas(string $file): array
    {
        $source = self::read($file);
        $declared = preg_match_all('/\bcommands\s*=/', $source);
        preg_match_all('/\{([^{}]*)\}/', $source, $objects);

        $read = 0;
        $jobs = [];

        foreach ($objects[1] as $object) {
            if (preg_match('/\bcommands\s*=\s*\[([^\]]*)\]/', $object, $commands) !== 1) {
                continue;
            }

            $read++;
            self::assertSame(1, preg_match('/\bname\s*=\s*"([^"]+)"/', $object, $name), 'A job in ' . $file . ' has no name');
            $type = preg_match('/\btype\s*=\s*"([^"]+)"/', $object, $typeMatch) === 1 ? $typeMatch[1] : 'default';

            if ($type === 'default') {
                $jobs[] = [$name[1], self::strings($commands[1], $file)];
            }
        }

        self::assertSame($declared, $read, 'Could not read every job in ' . $file);

        return $jobs;
    }

    /**
     * The quoted strings of an HCL list body, which must hold nothing else.
     *
     * @return list<string>
     */
    private static function strings(string $list, string $file): array
    {
        preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $list, $matches);
        $rest = preg_replace('/"((?:[^"\\\\]|\\\\.)*)"/', '', $list);
        self::assertMatchesRegularExpression('/^[\s,]*$/', (string) $rest, 'Unexpected list content in ' . $file);

        $strings = [];

        foreach ($matches[1] as $string) {
            self::assertStringNotContainsString('${', $string, 'Cannot evaluate an interpolation in ' . $file);
            // HCL's escapes are JSON's
            $strings[] = json_decode('"' . $string . '"', flags: JSON_THROW_ON_ERROR);
        }

        return $strings;
    }

    private static function terraformDir(): string
    {
        $dir = dirname(ApiApplication::root(), 2) . '/infra/terraform';
        self::assertDirectoryExists($dir, 'infra/terraform is missing; CI\'s sparse checkout must include it');

        return $dir;
    }

    private static function read(string $file): string
    {
        $source = file_get_contents($file);
        self::assertIsString($source);

        return $source;
    }
}
