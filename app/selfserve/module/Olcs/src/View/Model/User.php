<?php

/**
 * Dashboard View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model;

use Common\View\AbstractViewModel;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Dashboard View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
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
     * Set the application data
     *
     * @param array $data Mandatory
     * @param array $params Optional
     */
    public function setUsers(array $data, array $params = [])
    {
        $this->setVariable('users', $this->getTable('users', $data, $params));
    }
}
