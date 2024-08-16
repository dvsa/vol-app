<?php

namespace Dvsa\Olcs\Api\Service\Lva\Variation;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * PublishValidationService
 *
 * Publish variation validation
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishValidationService
{
    public const ERROR_NOT_PUBLISHABLE = 'APP-PUB-NOT-PUBLISHABLE';

    /**
     * Validate the variation for publishing
     *
     *
     * @return array of validation error messages
     */
    public function validate(ApplicationEntity $application)
    {
        $errors = [];

        if (!$application->isPublishable()) {
            $errors[self::ERROR_NOT_PUBLISHABLE] = 'Variation is not publishable';
        }

        return $errors;
    }
}
