<?php

/**
 * Grant Goods Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Grant Goods Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantGoods extends AbstractCommand
{
    use Identity;

    protected $shouldCreateInspectionRequest;

    protected $dueDate;

    protected $notes;

    /**
     * @return mixed
     */
    public function getShouldCreateInspectionRequest()
    {
        return $this->shouldCreateInspectionRequest;
    }

    /**
     * @return mixed
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
