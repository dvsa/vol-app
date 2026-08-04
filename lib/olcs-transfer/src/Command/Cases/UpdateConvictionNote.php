<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/cases/single/conviction-note")
 * @Transfer\Method("PUT")
 */
class UpdateConvictionNote extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":4000})
     * @Transfer\Optional
     */
    protected $convictionNote = null;

    /**
     * @return string
     */
    public function getConvictionNote()
    {
        return $this->convictionNote;
    }
}
