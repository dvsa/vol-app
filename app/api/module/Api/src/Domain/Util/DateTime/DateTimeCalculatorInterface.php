<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

interface DateTimeCalculatorInterface
{
    public function calculateDate(\DateTime $date, int $days): \DateTime;
}
