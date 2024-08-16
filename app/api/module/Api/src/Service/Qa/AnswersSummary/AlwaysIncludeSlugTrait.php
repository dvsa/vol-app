<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswersSummary;

use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

trait AlwaysIncludeSlugTrait
{
    /**
     * Whether this answer summary provider should include slug (always returns true)
     *
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function shouldIncludeSlug(QaEntityInterface $qaEntity)
    {
        return true;
    }
}
