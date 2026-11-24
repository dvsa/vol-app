<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Subject;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;

final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = Repo::class;
}
