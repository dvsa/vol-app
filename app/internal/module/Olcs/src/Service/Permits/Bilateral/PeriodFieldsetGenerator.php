<?php

namespace Olcs\Service\Permits\Bilateral;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalElement;
use Zend\Form\Factory as FormFactory;
use Zend\Form\Fieldset;

/**
 * Period fieldset generator
 */
class PeriodFieldsetGenerator
{
   /** @var FormFactory */
    protected $formFactory;

    /** @var NoOfPermitsElementGenerator */
    protected $noOfPermitsElementGenerator;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param NoOfPermitsElementGenerator $noOfPermitsElementGenerator
     *
     * @return PeriodFieldsetGenerator
     */
    public function __construct(FormFactory $formFactory, NoOfPermitsElementGenerator $noOfPermitsElementGenerator)
    {
        $this->formFactory = $formFactory;
        $this->noOfPermitsElementGenerator = $noOfPermitsElementGenerator;
    }

    /**
     * Return a Fieldset element corresponding to the provided data
     *
     * @param array $period
     *
     * @return Fieldset
     */
    public function generate(array $period)
    {
        $periodName = 'period' . $period['id'];

        $periodFieldset = $this->formFactory->create(
            [
                'type' => Fieldset::class,
                'name' => $periodName,
                'attributes' => [
                    'id' => $periodName,
                    'data-role' => 'period',
                ]
            ]
        );

        $periodFieldset->add(
            [
                'type' => BilateralNoOfPermitsCombinedTotalElement::class,
                'name' => 'combinedTotal'
            ]
        );

        foreach ($period['fields'] as $field) {
            $periodFieldset->add(
                $this->noOfPermitsElementGenerator->generate($field)
            );
        }

        return $periodFieldset;
    }
}
