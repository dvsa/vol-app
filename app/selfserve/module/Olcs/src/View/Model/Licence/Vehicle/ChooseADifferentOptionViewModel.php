<?php

declare(strict_types=1);

namespace Olcs\View\Model\Licence\Vehicle;

use Laminas\View\Model\ViewModel;
use Olcs\View\Model\AnchorViewModel;

class ChooseADifferentOptionViewModel extends ViewModel
{
    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables['content'] = $variables['content'] ?? 'licence.vehicle.partial.choose-different-option.text';

        $variables['anchors'] = [];

        $variables['anchors'][] = $variables['anchor1'] ?? new AnchorViewModel([
            'route' => ['licence/vehicle/GET', [], [], true],
            'title' => 'licence.vehicle.partial.choose-different-option.action_1.title',
            'label' => 'licence.vehicle.partial.choose-different-option.action_1.label',
        ]);

        $variables['anchors'][] = $variables['anchor2'] ?? new AnchorViewModel([
            'route' => ['lva-licence', [], [], true],
            'title' => 'licence.vehicle.partial.choose-different-option.action_2.title',
            'label' => 'licence.vehicle.partial.choose-different-option.action_2.label',
        ]);

        parent::__construct($variables, $options);

        $this->setTemplate('partials/content-with-anchors');
    }
}
