<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\Elements\Types\Html;
use Common\Form\QaForm;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Common\Service\Qa\DataHandlerInterface;

class InternationalJourneysDataHandler implements DataHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return InternationalJourneysDataHandler
     */
    public function __construct(private IsValidBasedWarningAdder $isValidBasedWarningAdder, private InternationalJourneysIsValidHandler $internationalJourneysIsValidHandler)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setData(QaForm $form): void
    {
        $this->isValidBasedWarningAdder->add(
            $this->internationalJourneysIsValidHandler,
            $form,
            'permits.form.trips.warning',
            20
        );
    }
}
