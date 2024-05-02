<?php

namespace Olcs\Service\Permits\Bilateral;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalElement;
use Laminas\Form\Fieldset;

/**
 * Standard fieldset populator
 */
class StandardFieldsetPopulator implements FieldsetPopulatorInterface
{
    /** @var NoOfPermitsElementGenerator */
    protected $noOfPermitsElementGenerator;

    /**
     * Create service instance
     *
     *
     * @return StandardFieldsetPopulator
     */
    public function __construct(NoOfPermitsElementGenerator $noOfPermitsElementGenerator)
    {
        $this->noOfPermitsElementGenerator = $noOfPermitsElementGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(Fieldset $fieldset, array $fields)
    {
        $fieldset->add(
            [
                'type' => BilateralNoOfPermitsCombinedTotalElement::class,
                'name' => 'combinedTotal'
            ]
        );

        foreach ($fields as $field) {
            $fieldset->add(
                $this->noOfPermitsElementGenerator->generate($field)
            );
        }
    }
}
