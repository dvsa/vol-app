<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Common\Service\Qa\DataHandlerInterface;

class PermitUsageDataHandler implements DataHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return PermitUsageDataHandler
     */
    public function __construct(private IsValidBasedWarningAdder $isValidBasedWarningAdder, private PermitUsageIsValidHandler $permitUsageIsValidHandler)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setData(QaForm $form): void
    {
        $this->isValidBasedWarningAdder->add(
            $this->permitUsageIsValidHandler,
            $form,
            'qanda.bilaterals.permit-usage.warning'
        );
    }
}
