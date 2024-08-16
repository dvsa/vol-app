<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCallEntityMethod;

/**
 * Update declaration
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class UpdateDeclaration extends AbstractCallEntityMethod
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'makeDeclaration';
}
