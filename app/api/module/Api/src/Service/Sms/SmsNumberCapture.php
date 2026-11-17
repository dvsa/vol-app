<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

/**
 * Single place that decides whether an SMS number must be given. Saving one is never gated.
 */
final class SmsNumberCapture
{
    public function __construct(private readonly ToggleService $toggleService)
    {
    }

    public function isRequired(): bool
    {
        return $this->toggleService->isEnabled(FeatureToggle::SMS_NUMBER_CAPTURE);
    }

    /**
     * True when a selfserve user has to give an SMS number for this licence and has not.
     */
    public function isMissing(Licence $licence, bool $isExternalUser): bool
    {
        return $isExternalUser
            && SmsNumber::forLicence($licence) === null
            && $this->isRequired();
    }

    /**
     * True when a selfserve user's new application can't be paid for or submitted yet.
     *
     * @param callable(): bool $isExternalUser only called for new applications
     */
    public function blocksApplication(Application $application, callable $isExternalUser): bool
    {
        return !$application->isVariation()
            && $this->isMissing($application->getLicence(), $isExternalUser());
    }

    /**
     * Reports Addresses as not done on a query result when a selfserve user's new application had it
     * completed before capture went on and there is still no SMS number. The stored status is left alone.
     *
     * @param callable(): bool $isExternalUser only called when everything else matches
     */
    public function markStaleAddressesIncomplete(Result $result, Application $application, callable $isExternalUser): Result
    {
        if (
            $this->isRequired()
            && !$application->isVariation()
            && $application->getStatus()?->getId() === Application::APPLICATION_STATUS_NOT_SUBMITTED
            && $application->getApplicationCompletion()?->getAddressesStatus() === ApplicationCompletion::STATUS_COMPLETE
            && SmsNumber::forLicence($application->getLicence()) === null
            && $isExternalUser()
        ) {
            $result->setValue('applicationCompletion', ['addressesStatus' => ApplicationCompletion::STATUS_INCOMPLETE]);
        }

        return $result;
    }
}
