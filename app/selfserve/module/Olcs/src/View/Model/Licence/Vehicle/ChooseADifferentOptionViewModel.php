<?php

declare(strict_types=1);

namespace Olcs\View\Model\Licence\Vehicle;

use Olcs\View\Model\Element\AnchorViewModel;
use Olcs\View\Model\Partial\ContentWithPartialsViewModel;

class ChooseADifferentOptionViewModel extends ContentWithPartialsViewModel
{
    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables[parent::CONTENT_VARIABLE] = $variables[parent::CONTENT_VARIABLE] ?? 'licence.vehicle.partial.choose-different-option.text';
        $variables[parent::PARTIALS_VARIABLE] = [
            $variables['anchor1'] ?? new AnchorViewModel([
                'route' => ['lva-licence/vehicles', [], [], true],
                'title' => 'licence.vehicle.partial.choose-different-option.action_1.title',
                'label' => 'licence.vehicle.partial.choose-different-option.action_1.label',
            ]),
            $variables['anchor2'] ?? new AnchorViewModel([
                'route' => ['lva-licence', [], [], true],
                'title' => 'licence.vehicle.partial.choose-different-option.action_2.title',
                'label' => 'licence.vehicle.partial.choose-different-option.action_2.label',
            ]),
        ];
        parent::__construct($variables, $options);
    }
}
