<?php

/**
 * Update Undertakings Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Update Undertakings Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateUndertakingsStatus extends AbstractUpdateStatus implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $section = 'Undertakings';

    /**
     * Is section completed
     *
     * @param Application $application Application entity
     *
     * @return bool
     */
    protected function isSectionValid(Application $application)
    {
        if ($this->isInternalUser()) {
            return $application->getAuthSignature();
        }

        $verified = (string)$application->getSignatureType() === Application::SIG_DIGITAL_SIGNATURE
            && $application->getDigitalSignature() !== null;

        return $application->getDeclarationConfirmation() === 'Y' || $verified;
    }
}
