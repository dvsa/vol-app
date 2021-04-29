<?php

declare(strict_types=1);

namespace Olcs\View\Model\Licence\Vehicle;

use Laminas\View\Model\ViewModel;
use Olcs\View\Model\AnchorViewModel;

class ViewAndAmendYourLicenceViewModel extends ViewModel
{
    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables['content'] = $variables['content'] ?? 'licence.vehicle.switchboard.choose-different-option.text';

        $variables['anchors'] = [];

        $variables['anchors'][] = $variables['anchor'] ?? new AnchorViewModel([
            'route' => ['lva-licence', [], [], true],
            'title' => 'licence.vehicle.switchboard.choose-different-option.action.title',
            'label' => 'licence.vehicle.switchboard.choose-different-option.action.label',
        ]);

        parent::__construct($variables, $options);

        $this->setTemplate('partials/content-with-anchors');
    }
}
