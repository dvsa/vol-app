<?php

namespace OlcsTest\Util;

/**
 * XhProf listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class XhProf implements \PHPUnit_Framework_TestListener
{
    protected $runs = [];

    protected $options = [];

    protected $suites = 0;

    protected $st;

    protected $et;

    /**
     * Constructor.
     *
     * @param array $options
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
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(\PHPUnit_Framework_Test $test, Exception $e, $time)
    {

    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {

    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {

    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {

    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {

    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
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
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->et = microtime(true);
        $data = xhprof_disable();

        $execTime = (int)($this->et - $this->st) * 100;

        if ($execTime > 200) {

            $runs = new \XHProfRuns_Default();
            $run = $runs->save_run($data, $this->options['appNamespace']);

            $this->runs[$execTime][] = [
                'class' => get_class($test),
                'test' => $test->getName(),
                'url' => $this->options['xhprofWeb'] . '?run=' . $run . '&source=' . $this->options['appNamespace']
            ];
        }
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
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
