<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Handler for DELETE a Phone contact
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'PhoneContact';
}
