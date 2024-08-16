<?php

/**
 * Create Fee for an Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Create Fee for an Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateFee extends AbstractCommand
{
    use Identity;

    /**
     * FeeType.FeeType ref data id
     * @var string
     */
    protected $feeTypeFeeType;

    protected $task;

    protected $optional;

    public function getFeeTypeFeeType()
    {
        return $this->feeTypeFeeType;
    }

    /**
     * @return mixed
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return mixed
     */
    public function getOptional()
    {
        return $this->optional;
    }
}
