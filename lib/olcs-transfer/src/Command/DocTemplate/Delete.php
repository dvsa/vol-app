<?php

/**
 * Delete Document Template
 */

namespace Dvsa\Olcs\Transfer\Command\DocTemplate;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

#[Transfer\RouteName('backend/doc-template/single')]
#[Transfer\Method('DELETE')]
final class Delete extends AbstractDeleteCommand
{
}
