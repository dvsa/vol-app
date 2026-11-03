<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Si;

use Dvsa\Olcs\Transfer\Command\AbstractXmlCommand;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Process a compliance episode
 */
#[Transfer\RouteName('backend/msi/compliance-episode')]
#[Transfer\Method('POST')]
class ComplianceEpisode extends AbstractXmlCommand
{
}
