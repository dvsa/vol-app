<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/cases/single/prohibition-note")
 * @Transfer\Method("PUT")
 */
class UpdateProhibitionNote extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":4000})
     * @Transfer\Optional
     */
    protected $prohibitionNote = null;

    /**
     * @return string
     */
    public function getProhibitionNote()
    {
        return $this->prohibitionNote;
    }
}
