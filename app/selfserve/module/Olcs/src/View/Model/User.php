<?php

/**
 * User View Model
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\View\Model;

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
     * Set the user data
     *
     * @param array $data Mandatory
     * @param array $params Optional
     */
    public function setUsers(array $data, array $params = [])
    {
        $this->setVariable('users', $this->getTable('users', $data, $params));
    }
}
