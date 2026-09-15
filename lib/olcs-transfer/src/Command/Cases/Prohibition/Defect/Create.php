<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/defect")
 * @Transfer\Method("POST")
 */
class Create extends AbstractCommand
{
    use FieldType\Traits\Prohibition;
    use FieldType\Traits\Notes;

    /**
     * @var string
     *
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $defectType = null;

    /**
     * @return string
     */
    public function getDefectType()
    {
        return $this->defectType;
    }
}
