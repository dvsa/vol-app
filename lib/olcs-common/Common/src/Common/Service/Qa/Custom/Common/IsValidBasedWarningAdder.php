<?php

namespace Common\Service\Qa\Custom\Common;

use Common\Form\QaForm;
use Common\Service\Qa\IsValidHandlerInterface;

class IsValidBasedWarningAdder
{
    /**
     * Create service instance
     *
     *
     * @return IsValidBasedWarningAdder
     */
    public function __construct(private WarningAdder $warningAdder)
    {
    }

    /**
     * Add a warning partial to the form if the is valid handler returns false
     *
     * @param string $warningKey
     * @param int $priority
     */
    public function add(
        IsValidHandlerInterface $isValidHandler,
        QaForm $form,
        $warningKey,
        $priority = WarningAdder::DEFAULT_PRIORITY
    ): void {
        if ($isValidHandler->isValid($form)) {
            return;
        }

        $questionFieldset = $form->getQuestionFieldset();
        $questionFieldset->get('warningVisible')->setValue(1);

        $this->warningAdder->add($questionFieldset, $warningKey, $priority);
    }
}
