<?php

/**
 * Permit Controller
 */

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Form\Model\Form\IrhpPermitFilter as FilterForm;
use Zend\View\Model\ViewModel;

class PermitController extends AbstractInternalController implements
    LeftViewProvider,
    LicenceControllerInterface,
    ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_PERMITS
        ],
    ];

    protected $navigationId = 'licence_irhp_permits-permit';

    // Maps the licence route parameter into the ListDTO as licence => value
    protected $listVars = ['licence'];
    protected $listDto = GetListByLicence::class;
    protected $filterForm = FilterForm::class;

    protected $tableName = 'issued-permits';

    // Scripts to include when rendering actions.
    protected $inlineScripts = [
        'indexAction' => ['forms/filter'],
    ];

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();

        $view->setTemplate('sections/irhp-permit/partials/left');

        return $view;
    }
}
