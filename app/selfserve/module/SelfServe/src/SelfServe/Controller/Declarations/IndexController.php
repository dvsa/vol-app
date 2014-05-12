<?php

/**
 * Declarations Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\Declarations;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;

/**
 * Declarations Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
{

    public function __construct()
    {
        $this->setCurrentSection('declarations');
    }

    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        $form = $this->generateForm('review', null);

        $form->setData($this->mapEntitiesToForm());

        // render the view
        $view = new ViewModel(
            array(
                'completionStatus' => $completionStatus['Results'][0],
                'applicationId' => $applicationId,
                'form' => $form,
            )
        );
        $view->setTemplate('self-serve/declarations/index');

        return $view;
    }

    public function completeAction()
    {

    }

    protected function mapEntitiesToForm()
    {
        $licence = $this->getLicenceEntity();
        return [
            'operator_location' => [
                'operator_location' => $licence['niFlag'] ? 'ni' : 'uk',
            ],
            'operator-type' => [
                'operator-type' => $licence['goodsOrPsv'],
            ],
            'licence-type' => [
                'licence_type' => $licence['licenceType'],
            ]
        ];
    }
}
