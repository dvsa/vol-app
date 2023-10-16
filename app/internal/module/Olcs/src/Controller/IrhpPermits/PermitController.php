<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Form\Model\Form\IrhpPermitFilter as FilterForm;

class PermitController extends AbstractInternalController implements LeftViewProvider, LicenceControllerInterface
{
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
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation
    ) {
        parent::__construct($translationHelper, $formHelperService, $flashMessenger, $navigation);
    }
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
