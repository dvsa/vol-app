<?php


namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\RefData;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Application\Grant;
use Olcs\Controller\Lva\Traits\VariationWizardFinalPageControllerTrait;

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
     * @return mixed
     */
    protected function submitAction()
    {
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
     * Overridden method to redirect to start
     *
     * @param null $lvaId licence or application id
     *
     * @return void|\Zend\Http\Response
     */
    protected function goToOverview($lvaId = null)
    {
        $route = $this->getStartRoute();
        $this->handleWizardCancel($route);
    }

    /**
     * Get the form
     *
     * @param array $data form data
     * @params array $params parameters for form
     *
     * @return mixed]
     */
    protected function getConvictionsPenaltiesForm(array $data = [], $params = [])
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
        return $table;
    }
}
