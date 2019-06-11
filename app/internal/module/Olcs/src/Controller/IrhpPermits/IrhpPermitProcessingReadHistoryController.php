<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Audit\ReadLicence;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\IrhpPermitApplicationControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Irhp Permit Processing Read History Controller
 */
class IrhpPermitProcessingReadHistoryController extends AbstractInternalController implements
    IrhpPermitApplicationControllerInterface,
    LeftViewProvider,
    ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_PERMITS
        ],
    ];

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_irhp_permits_processing_read-history';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = ['id' => 'licence'];
    protected $tableName = 'read-history';
    protected $listDto = ReadLicence::class;

    /**
     * get method for left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
