<?php


namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\RefData;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Variation\GrantDirectorChange;
use Olcs\Controller\Lva\Traits\VariationWizardFinalPageControllerTrait;
use Zend\Http\Response;

/**
 * Class ConvictionsPenaltiesController
 *
 * @package Olcs\Controller\Lva\DirectorChange
 */
class ConvictionsPenaltiesController extends AbstractConvictionsPenaltiesController
{
    use VariationWizardFinalPageControllerTrait;

    protected $location = 'external';
    protected $lva = self::LVA_VAR;

    /**
     * Get the required previous sections
     *
     * @return array required previous sections;
     */
    protected function getRequiredSections()
    {
        return ['peopleStatus', 'financialHistoryStatus'];
    }


    /**
     * Return the route the wizard lives under
     *
     * @return null|string
     */
    protected function getBaseRoute()
    {
        return 'lva-director_change/convictions_penalties';
    }

    /**
     * get the variation type
     *
     * @return string variation type
     */
    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    /**
     * Method to complete the process
     *
     * @return Response
     */
    protected function submit()
    {
        $response = $this->handleCommand(GrantDirectorChange::create(['id'=>$this->getIdentifier()]));
        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
        return $this->goToOverview();
    }

    /**
     * Get the start of the start of the wizard
     *
     * @return array
     */
    public function getStartRoute()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());
        return ['name' => 'lva-licence/people', 'params' => ['licence' => $licenceId]];
    }

    /**
     * Get the form
     *
     * @param array $data   form data
     * @param array $params parameters for form
     *
     * @return mixed]
     */
    protected function getConvictionsPenaltiesForm($data, $params = [])
    {
        $params['variationType'] = $this->getVariationType();
        $params['organisationType'] = $this->fetchDataForLva()['licence']['organisation']['type']['id'];
        return parent::getConvictionsPenaltiesForm($data, $params);
    }

    /**
     * Alter the table button
     *
     * @param array $data data
     *
     * @return mixed
     */
    protected function getConvictionsPenaltiesTable($data)
    {
        /**
         * @var TableBuilder table
         */
        $table = parent::getConvictionsPenaltiesTable($data);
        $table->setSetting(
            'crud',
            [
                'actions' => [
                    'add' => [
                        'label' => 'Add a conviction'
                    ]
                ]
            ]
        );
        $table->setVariable('empty_message', 'selfserve-app-subSection-previous-history-criminal-conviction-hasConv-hint-director-change');
        return $table;
    }
}
