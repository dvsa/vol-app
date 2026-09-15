<?php

namespace Dvsa\Olcs\Transfer\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/publication/link")
 * @Transfer\Method("DELETE")
 */
class DeletePublicationLink extends AbstractDeleteCommand
{
    //
}
