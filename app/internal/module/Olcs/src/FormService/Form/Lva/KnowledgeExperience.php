<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\KnowledgeExperience as CommonKnowledgeExperience;

final class KnowledgeExperience extends CommonKnowledgeExperience
{
    #[\Override]
    protected function alterForm($form): void
    {
        parent::alterForm($form);

        $form
            ->get('form-actions')
            ->get('save')
            ->setLabel('internal.save.button');
    }
}
