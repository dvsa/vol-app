<?php


namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;

class ConvictionsPenaltiesController extends AbstractConvictionsPenaltiesController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;

    protected $location = 'external';

    protected $lva = self::LVA_VAR;

    protected function getNextPageRouteName()
    {
        return 'lva-director_change/financial_history';
    }

    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }
}
