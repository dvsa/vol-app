<?php

namespace Dvsa\Olcs\Transfer\Command\TaskAlphaSplit;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/task-alpha-split/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use \Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
    use \Dvsa\Olcs\Transfer\FieldType\Traits\Version;
    use \Dvsa\Olcs\Transfer\FieldType\Traits\User;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":50})
     */
    protected $letters;

    public function getLetters()
    {
        return $this->letters;
    }
}
