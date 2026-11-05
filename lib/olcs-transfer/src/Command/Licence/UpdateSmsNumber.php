<?php

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

#[Transfer\RouteName('backend/licence/single/sms-number')]
#[Transfer\Method('PUT')]
final class UpdateSmsNumber extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $smsNumber;

    public function getSmsNumber()
    {
        return $this->smsNumber;
    }
}
