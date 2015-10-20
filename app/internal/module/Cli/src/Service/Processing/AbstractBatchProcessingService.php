<?php

/**
 * Abstract batch processing service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\Log\Logger;

/**
 * Abstract batch processing service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractBatchProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const EXIT_CODE_ERROR = 1;
    const EXIT_CODE_SUCCESS = 0;

    /**
     * Console adapter to output info (if set)
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    private $consoleAdapter;

    /**
     * Set the console adapter
     *
     * @param ConsoleAdapter $adapter
     */
    public function setConsoleAdapter(ConsoleAdapter $adapter)
    {
        $this->consoleAdapter = $adapter;
    }

    /**
     * Get the console adapter
     *
     * @return ConsoleAdapter
     */
    public function getConsoleAdapter()
    {
        return $this->consoleAdapter;
    }

    /**
     * Output a line to the console adapter
     *
     * @param string $text Text to output
     *
     * @return void
     */
    protected function outputLine($text)
    {
        if ($this->getConsoleAdapter()) {
            $this->getConsoleAdapter()->writeLine($text);
        }
    }

    public function log($message, $priority = Logger::INFO, $extra = array())
    {
        $this->outputLine($message);
        return \Olcs\Logging\Log\Logger::log($priority, $message, $extra);
    }
}
