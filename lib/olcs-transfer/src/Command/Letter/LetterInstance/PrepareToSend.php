<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/letter/letter-instance/prepare-to-send")
 * @Transfer\Method("POST")
 */
final class PrepareToSend extends AbstractCommand
{
    use Identity;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $docTemplate;

    /**
     * @return int
     */
    public function getDocTemplate()
    {
        return $this->docTemplate;
    }
}
