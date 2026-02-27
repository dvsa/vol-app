<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Stub;

use Olcs\FormService\Form\Lva\AbstractOverviewSubmission;

/**
 * Stub Class form AbstractOverviewSubmission
 */
class AbstractOverviewSubmissionStub extends AbstractOverviewSubmission
{
    #[\Override]
    public function alterForm(\Laminas\Form\FormInterface $form, array $data, array $params): void
    {
        parent::alterForm($form, $data, $params);
    }

    #[\Override]
    public function hasSectionsWithStatus($status): bool
    {
        return parent::hasSectionsWithStatus($status);
    }
}
