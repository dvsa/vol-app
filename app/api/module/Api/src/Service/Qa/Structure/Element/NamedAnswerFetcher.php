<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

class NamedAnswerFetcher
{
    public const QA_FIELDSET_NAME = 'qa';

    public const ERR_NO_ANSWER = 'No answer data found';

    /**
     * Retrieve an answer from the appropriate location within the post data
     *
     * @param array $postData
     * @param string $elementName
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function fetch(ApplicationStepEntity $applicationStep, $postData, $elementName)
    {
        $fieldsetName = $applicationStep->getFieldsetName();

        if (!isset($postData[self::QA_FIELDSET_NAME][$fieldsetName][$elementName])) {
            throw new NotFoundException(self::ERR_NO_ANSWER);
        }

        return $postData[self::QA_FIELDSET_NAME][$fieldsetName][$elementName];
    }
}
