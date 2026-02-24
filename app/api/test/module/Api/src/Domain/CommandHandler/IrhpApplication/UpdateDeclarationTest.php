<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateDeclaration;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCallEntityMethodTestCase;

class UpdateDeclarationTest extends AbstractCallEntityMethodTestCase
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'makeDeclaration';
    protected $entityClass = 'IrhpApplication';
    protected $repoClass = IrhpApplicationRepo::class;
    protected $sutClass = UpdateDeclaration::class;
}
