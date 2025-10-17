<?php

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Reset permits test data
 *
 * Clears existing permits data and populates with fresh test data
 * for VFT regression testing in non-production environments.
 */
final class ResetPermitsTestData extends AbstractCommand
{
}
