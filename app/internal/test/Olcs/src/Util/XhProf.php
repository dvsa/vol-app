<?php

declare(strict_types=1);

namespace OlcsTest\Util;

/**
 * XhProf listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class XhProf implements \PHPUnit\Framework\TestListener
{
    protected $runs = [];

    protected $options = [];

    protected $suites = 0;

    protected $st;

    protected $et;

    /**
     * Constructor.
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['appNamespace'])) {
            throw new \InvalidArgumentException('The "appNamespace" option is not set.');
        }

        if (!isset($options['xhprofLibFile']) || !file_exists($options['xhprofLibFile'])) {
            throw new \InvalidArgumentException(
                'The "xhprofLibFile" option is not set or the configured file does not exist'
            );
        }

        if (!isset($options['xhprofRunsFile']) || !file_exists($options['xhprofRunsFile'])) {
            throw new \InvalidArgumentException(
                'The "xhprofRunsFile" option is not set or the configured file does not exist'
            );
        }

        require_once($options['xhprofLibFile']);
        require_once($options['xhprofRunsFile']);

        $this->options = $options;
    }

    /**
     * An error occurred.
     *
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(\PHPUnit\Framework\Test $test, Exception $e, mixed $time): void
    {
    }

    /**
     * A failure occurred.
     *
     * @param float                                  $time
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, mixed $time): void
    {
    }

    /**
     * Incomplete test.
     *
     * @param float                  $time
     */
    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Exception $e, mixed $time): void
    {
    }

    /**
     * Risky test.
     *
     * @param float                  $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Exception $e, mixed $time): void
    {
    }

    /**
     * Skipped test.
     *
     * @param float                  $time
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Exception $e, mixed $time): void
    {
    }

    /**
     * A test started.
     */
    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        $this->st = microtime(true);

        if (!isset($this->options['xhprofFlags'])) {
            $flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;
        } else {
            $flags = 0;
            foreach (explode(',', $this->options['xhprofFlags']) as $flag) {
                $flags += constant($flag);
            }
        }

        xhprof_enable($flags);
    }

    /**
     * A test ended.
     *
     * @param float                  $time
     */
    public function endTest(\PHPUnit\Framework\Test $test, mixed $time): void
    {
        $this->et = microtime(true);
        $data = xhprof_disable();

        $execTime = (int)($this->et - $this->st) * 100;

        if ($execTime > 200) {
            $runs = new \XHProfRuns_Default();
            $run = $runs->save_run($data, $this->options['appNamespace']);

            $this->runs[$execTime][] = [
                'class' => $test::class,
                'test' => $test->getName(),
                'url' => $this->options['xhprofWeb'] . '?run=' . $run . '&source=' . $this->options['appNamespace']
            ];
        }
    }

    /**
     * A test suite started.
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        $this->suites--;
        if ($this->suites == 0) {
            krsort($this->runs);

            foreach ($this->runs as $time => $runs) {
                foreach ($runs as $run) {
                    echo '(' . $time . 's) ' . $run['class'] . '::' . $run['test'] . "\n   " . $run['url'] . "\n\n";
                }
            }
            echo "\n";
        }
    }
}
