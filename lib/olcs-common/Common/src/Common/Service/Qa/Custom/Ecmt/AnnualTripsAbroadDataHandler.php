<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Common\Service\Qa\DataHandlerInterface;
use Laminas\View\Helper\Partial;

class AnnualTripsAbroadDataHandler implements DataHandlerInterface
{
    /**
     * Create service instance
     *
     *
     * @return AnnualTripsAbroadDataHandler
     */
    public function __construct(private IsValidBasedWarningAdder $isValidBasedWarningAdder, private AnnualTripsAbroadIsValidHandler $annualTripsAbroadIsValidHandler)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setData(QaForm $form): void
    {
        $this->isValidBasedWarningAdder->add(
            $this->annualTripsAbroadIsValidHandler,
            $form,
            'permits.form.trips.warning'
        );
    }
}
