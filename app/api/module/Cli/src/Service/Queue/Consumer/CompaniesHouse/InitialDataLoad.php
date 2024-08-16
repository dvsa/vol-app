<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\InitialLoad as Cmd;

/**
 * Companies House Initial Data Load Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InitialDataLoad extends AbstractConsumer
{
    /**
     * @var string the command to handle processing
     */
    protected $commandName = Cmd::class;
}
