<?php

/**
 * Create Transport Manager Application for a User
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/transport-manager-application")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $user;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"A","U"}})
     */
    protected $action;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     */
    protected $dob;

    /**
     * Get Application ID
     *
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get User ID
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get Action (eg Update(U), Add(A))
     *
     * @return string A or U
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get date of birth
     *
     * @return string
     */
    public function getDob()
    {
        return $this->dob;
    }
}
