<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * PublicHoliday Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="public_holiday",
 *    indexes={
 *        @ORM\Index(name="ix_public_holiday_public_holiday_date", columns={"public_holiday_date"}),
 *        @ORM\Index(name="ix_public_holiday_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_public_holiday_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class PublicHoliday extends AbstractPublicHoliday
{
    /**
     * PublicHoliday constructor.
     */
    public function __construct(DateTime $holidayDate, $isEngland, $isWales, $isScotland, $isNorthernIreland)
    {
        $this->update($holidayDate, $isEngland, $isWales, $isScotland, $isNorthernIreland);
    }

    public function update(DateTime $holidayDate, $isEngland, $isWales, $isScotland, $isNorthernIreland)
    {
        $this->publicHolidayDate = $holidayDate;
        $this->isEngland = $isEngland;
        $this->isWales = $isWales;
        $this->isScotland = $isScotland;
        $this->isNi = $isNorthernIreland;
    }
}
