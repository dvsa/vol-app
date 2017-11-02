<?php


namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\VariationWizardFinalPageControllerTrait;

class ConvictionsPenaltiesController extends AbstractConvictionsPenaltiesController
{
    use VariationWizardFinalPageControllerTrait;

    protected $location = 'external';
    protected $lva = self::LVA_VAR;


    protected function getBaseRoute()
    {
        return 'lva-director_change/convictions_penalties';
    }

    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    protected function handleSubmission()
    {
        return "submission handled";
    }

    public function getStartRoute()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());
        return ['name'=>'lva-licence/people', 'params'=>['licence' =>$licenceId]];
    }

    protected function goToOverview($lvaId = null)
    {
        $route = $this->getStartRoute();
        $this->handleWizardCancel($route);
    }
}
