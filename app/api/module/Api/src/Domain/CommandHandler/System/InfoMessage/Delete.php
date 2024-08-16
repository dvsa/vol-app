<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Handler for DELETE a System info message
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'SystemInfoMessage';
}
