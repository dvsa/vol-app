<?php

declare(strict_types=1);

namespace Olcs\View\Model\Licence\Vehicle;

use Olcs\View\Model\Element\AnchorViewModel;
use Olcs\View\Model\Partial\ContentWithPartialsViewModel;

class ViewAndAmendYourLicenceViewModel extends ContentWithPartialsViewModel
{
    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables[parent::CONTENT_VARIABLE] = $variables[parent::CONTENT_VARIABLE] ?? 'licence.vehicle.switchboard.choose-different-option.text';
        $variables[parent::PARTIALS_VARIABLE] = [
            $variables['anchor'] ?? new AnchorViewModel([
                'route' => ['lva-licence', [], [], true],
                'title' => 'licence.vehicle.switchboard.choose-different-option.action.title',
                'label' => 'licence.vehicle.switchboard.choose-different-option.action.label',
            ])
        ];
        parent::__construct($variables, $options);
    }
}
