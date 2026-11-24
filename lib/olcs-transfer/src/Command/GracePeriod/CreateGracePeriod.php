<?php

/**
 * CreateGracePeriod.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\GracePeriod;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

#[Transfer\RouteName('backend/grace-periods')]
#[Transfer\Method('POST')]
final class CreateGracePeriod extends AbstractCommand
{
    /**
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $licence;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 32])]
    protected $gracePeriodType;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $startDate;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $endDate;

    /**
     * @var string
     */
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $description;

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return string
     */
    public function getGracePeriodType()
    {
        return $this->gracePeriodType;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
}
