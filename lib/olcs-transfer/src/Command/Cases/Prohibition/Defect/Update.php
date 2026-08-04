<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/defect/single")
 * @Transfer\Method("PUT")
 */
class Update extends AbstractCommand implements
    FieldType\IdentityInterface,
    FieldType\VersionInterface
{
    // Identity & Locking
    use FieldType\Traits\Identity;
    use FieldType\Traits\Version;

    // Foreign Keys
    use FieldType\Traits\ProhibitionOptional;
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
