<?php

namespace Dvsa\Olcs\Transfer\Command\Messaging\Subject;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Category;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubCategory;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/messaging/subjects')]
#[Transfer\Method('POST')]
final class Create extends AbstractCommand
{
    use Category;
    use SubCategory;

    // the conversation subject is "<description> query" and has to fit in 255
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1, 'max' => 249])]
    protected $description;

    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    protected $isActive = true;

    public function getDescription()
    {
        return $this->description;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }
}
