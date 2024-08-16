<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

class AnswersSummaryFactory
{
    /**
     * Create and return a AnswersSummary instance
     *
     * @return AnswersSummary
     */
    public function create()
    {
        return new AnswersSummary();
    }
}
