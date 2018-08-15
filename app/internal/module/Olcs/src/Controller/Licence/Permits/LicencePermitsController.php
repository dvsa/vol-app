<?php

namespace Olcs\Controller\Licence\Permits;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Licence Permits Controller
 *
 * @author Andy Newton
 */
class LicencePermitsController extends LicenceController implements LeftViewProvider
{
    use Traits\PermitSearchTrait,
        Traits\PermitActionTrait;

    /**
     * Table to use
     *
     * @see \Olcs\Controller\Traits\PermitActionTrait
     * @return string
     */
    protected function getPermitTableName()
    {
        return 'permits';
    }

    /**
     * Route (prefix) for permit action redirects
     *
     * @see \Olcs\Controller\Traits\PermitActionTrait
     * @return string
     */
    protected function getPermitRoute()
    {
        return 'licence/permits';
    }

    /**
     * Route params for permit action redirects
     *
     * @see \Olcs\Controller\Traits\PermitActionTrait
     * @return array
     */
    protected function getPermitRouteParams()
    {
        return ['licence' => $this->getFromRoute('licence')];
    }

    /**
     * Get Form
     *
     * @return \Zend\Form\FieldsetInterface
     */
    protected function getConfiguredPermitForm()
    {
        $filters = $this->mapPermitFilters(['licence' => $this->getFromRoute('licence')]);
        return $this->getPermitForm($filters);
    }

    /**
     * Get view model for permit action
     *
     * @see \Olcs\Controller\Traits\PermitActionTrait
     * @return ViewModel
     */
    protected function getPermitView()
    {
        $filters = $this->mapPermitFilters(['licence' => $this->getFromRoute('licence')]);
        $table = $this->getPermitsTable($filters);

        return $this->getViewWithLicence(['table' => $table]);
    }
}
