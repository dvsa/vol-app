<?php

/**
 * Upload content of scoring log
 *
 */

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Upload content of scoring log
 */
final class UploadScoringLog extends AbstractCommand
{
   /**
     * @var string
     */
    protected $logContent;

    /**
     * Gets the value of logContent
     *
     * @return string
     */
    public function getLogContent()
    {
        return $this->logContent;
    }
}
