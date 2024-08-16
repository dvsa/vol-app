<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;

class GenericAnswerFetcher
{
    public function __construct(private readonly NamedAnswerFetcher $namedAnswerFetcher)
    {
    }

    /**
     * Retrieve an answer from the appropriate location within the post data
     *
     *
     * @return mixed
     */
    public function fetch(ApplicationStepEntity $applicationStep, array $postData)
    {
        return $this->namedAnswerFetcher->fetch($applicationStep, $postData, 'qaElement');
    }
}
