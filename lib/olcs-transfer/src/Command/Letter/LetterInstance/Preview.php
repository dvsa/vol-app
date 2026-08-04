<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterType;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTestDataOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-instance/preview")
 * @Transfer\Method("POST")
 */
final class Preview extends AbstractCommand
{
    use LetterType;
    use LicenceOptional;
    use ApplicationOptional;
    use CasesOptional;
    use LetterTestDataOptional;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $selectedIssues;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $selectedTodos;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $selectedAppendices;

    /**
     * @var array
     * @Transfer\Optional
     */
    protected $additionalData;

    /**
     * @return array
     */
    public function getSelectedIssues()
    {
        return $this->selectedIssues;
    }

    /**
     * @return array
     */
    public function getSelectedTodos()
    {
        return $this->selectedTodos;
    }

    /**
     * @return array
     */
    public function getSelectedAppendices()
    {
        return $this->selectedAppendices;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
