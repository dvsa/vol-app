<?php

/**
 * User View Model
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\View\Model;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Common\View\AbstractViewModel;

/**
 * User View Model
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class User extends AbstractViewModel
{
    /**
     * Set the template for the dashboard
     *
     * @var string
     */
    protected $template = 'user';

    /**
     * @param array $data
     * @param UrlHelperService $urlHelper
     * @param TableFactory $tableService
     * @param array $params
     */
    public function setUsers(array $data, UrlHelperService $urlHelper, TableFactory $tableService, array $params = [])
    {
        $this->setVariable('users', $this->getTable('users', $data, $urlHelper, $tableService, $params));
    }
}
