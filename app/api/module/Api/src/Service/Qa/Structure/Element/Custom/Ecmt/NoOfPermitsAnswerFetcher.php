<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;

class NoOfPermitsAnswerFetcher
{
    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsAnswerFetcher
     */
    public function __construct(private readonly NamedAnswerFetcher $namedAnswerFetcher)
    {
    }

    /**
     * Derive the number of permits for a given emissions type from the post data
     *
     * @param ApplicationStepEntity $applicationStep
     * @param string $elementName
     * @return int
     */
    public function fetch(ApplicationStepEntity $applicationStepEntity, array $postData, $elementName)
    {
        try {
            $permitsRequired = $this->namedAnswerFetcher->fetch($applicationStepEntity, $postData, $elementName);
        } catch (NotFoundException) {
            $permitsRequired = 0;
        }

        if ($permitsRequired == '') {
            $permitsRequired = 0;
        }

        return $permitsRequired;
    }
}
