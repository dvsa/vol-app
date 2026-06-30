<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Common Goods Vehicles Filters Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommonGoodsVehiclesFilters
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Get Form
     *
     * @return \Laminas\Form\FormInterface
     */
    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\VehicleFilter', false);

        // @NOTE Might as well hard code this list rather than generating it using range, array_combine and array_merge
        // everytime, as the values will never change
        $vrmOptions = [
            'All' => 'All',
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'H' => 'H',
            'I' => 'I',
            'J' => 'J',
            'K' => 'K',
            'L' => 'L',
            'M' => 'M',
            'N' => 'N',
            'O' => 'O',
            'P' => 'P',
            'Q' => 'Q',
            'R' => 'R',
            'S' => 'S',
            'T' => 'T',
            'U' => 'U',
            'V' => 'V',
            'W' => 'W',
            'X' => 'X',
            'Y' => 'Y',
            'Z' => 'Z',
        ];

        $form->get('vrm')->setValueOptions($vrmOptions);

        return $form;
    }
}
