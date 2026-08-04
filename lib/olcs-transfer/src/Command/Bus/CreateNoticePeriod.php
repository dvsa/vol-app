<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/bus/notice-period-create")
 * @Transfer\Method("POST")
 */
final class CreateNoticePeriod extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":70})
     */
    public $noticeArea;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min":0, "max":999})
     */
    public $standardPeriod;

    public function getNoticeArea(): string
    {
        return $this->noticeArea;
    }

    public function getStandardPeriod(): int
    {
        return (int)$this->standardPeriod;
    }
}
