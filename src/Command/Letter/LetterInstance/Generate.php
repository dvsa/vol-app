<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterType;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTestDataOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-instance/generate")
 * @Transfer\Method("POST")
 */
final class Generate extends AbstractCommand
{
    use LetterType;
    use LicenceOptional;
    use ApplicationOptional;
    use CasesOptional;
    use BusRegOptional;
    use TransportManagerOptional;
    use IrhpApplicationOptional;
    use IrfoOrganisationOptional;
    use LetterTestDataOptional;

    // selectedSections removed temporarily - will be added back in future tickets
    // /**
    //  * @var array
    //  * @Transfer\Optional
    //  * @Transfer\ArrayInput
    //  */
    // protected $selectedSections;

    /**
     * @var array
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $selectedIssues;

    // selectedTodos removed temporarily - will be added back in future tickets
    // /**
    //  * @var array
    //  * @Transfer\Optional
    //  * @Transfer\ArrayInput
    //  */
    // protected $selectedTodos;

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
    protected $selectedChoices;

    /**
     * @var array
     * @Transfer\Optional
     */
    protected $additionalData;

    // Getter removed temporarily - will be added back in future tickets
    // /**
    //  * @return array
    //  */
    // public function getSelectedSections()
    // {
    //     return $this->selectedSections;
    // }

    /**
     * @return array
     */
    public function getSelectedIssues()
    {
        return $this->selectedIssues;
    }

    // Getter removed temporarily - will be added back in future tickets
    // /**
    //  * @return array
    //  */
    // public function getSelectedTodos()
    // {
    //     return $this->selectedTodos;
    // }

    /**
     * @return array
     */
    public function getSelectedChoices()
    {
        return $this->selectedChoices;
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
