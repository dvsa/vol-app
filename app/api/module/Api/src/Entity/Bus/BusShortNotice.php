<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;

/**
 * BusShortNotice Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="bus_short_notice",
 *    indexes={
 *        @ORM\Index(name="ix_bus_short_notice_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_short_notice_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_bus_short_notice_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\UniqueConstraint(name="uk_bus_short_notice_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class BusShortNotice extends AbstractBusShortNotice
{
    public function getCalculatedBundleValues()
    {
        return [
            'isLatestVariation' => $this->getBusReg()->isLatestVariation()
        ];
    }

    /**
     * Updates a short notice record
     *
     * @param string $bankHolidayChange
     * @param string $unforseenChange
     * @param string $unforseenDetail
     * @param string $timetableChange
     * @param string $timetableDetail
     * @param string $replacementChange
     * @param string $replacementDetail
     * @param string $notAvailableChange
     * @param string $notAvailableDetail
     * @param string $specialOccasionChange
     * @param string $specialOccasionDetail
     * @param string $connectionChange
     * @param string $connectionDetail
     * @param string $holidayChange
     * @param string $holidayDetail
     * @param string $trcChange
     * @param string $trcDetail
     * @param string $policeChange
     * @param string $policeDetail
     */
    public function update(
        $bankHolidayChange,
        $unforseenChange,
        $unforseenDetail,
        $timetableChange,
        $timetableDetail,
        $replacementChange,
        $replacementDetail,
        $notAvailableChange,
        $notAvailableDetail,
        $specialOccasionChange,
        $specialOccasionDetail,
        $connectionChange,
        $connectionDetail,
        $holidayChange,
        $holidayDetail,
        $trcChange,
        $trcDetail,
        $policeChange,
        $policeDetail
    ) {
        $this->getBusReg()->canEdit();

        $this->bankHolidayChange = $bankHolidayChange;
        $this->unforseenChange = $unforseenChange;
        $this->unforseenDetail = $unforseenDetail;
        $this->timetableChange = $timetableChange;
        $this->timetableDetail = $timetableDetail;
        $this->replacementChange = $replacementChange;
        $this->replacementDetail = $replacementDetail;
        $this->notAvailableChange = $notAvailableChange;
        $this->notAvailableDetail = $notAvailableDetail;
        $this->specialOccasionChange = $specialOccasionChange;
        $this->specialOccasionDetail = $specialOccasionDetail;
        $this->connectionChange = $connectionChange;
        $this->connectionDetail = $connectionDetail;
        $this->holidayChange = $holidayChange;
        $this->holidayDetail = $holidayDetail;
        $this->trcChange = $trcChange;
        $this->trcDetail = $trcDetail;
        $this->policeChange = $policeChange;
        $this->policeDetail = $policeDetail;
    }

    public function reset()
    {
        $this->bankHolidayChange = 'N';
        $this->unforseenChange = 'N';
        $this->unforseenDetail = null;
        $this->timetableChange = 'N';
        $this->timetableDetail = null;
        $this->replacementChange = 'N';
        $this->replacementDetail = null;
        $this->notAvailableChange = 'N';
        $this->notAvailableDetail = null;
        $this->specialOccasionChange = 'N';
        $this->specialOccasionDetail = null;
        $this->connectionChange = 'N';
        $this->connectionDetail = null;
        $this->holidayChange = 'N';
        $this->holidayDetail = null;
        $this->trcChange = 'N';
        $this->trcDetail = null;
        $this->policeChange = 'N';
        $this->policeDetail = null;
        $this->createdBy = null;
        $this->lastModifiedOn = null;
        $this->lastModifiedBy = null;
    }

    /**
     * Returns whether a bus reg has short notice details which makes it grantable
     *
     * @return bool
     */
    public function hasGrantableDetails()
    {
        $shortNoticQuestionFields = [
            ['change' => 'bankHolidayChange'],
            ['change' => 'connectionChange', 'detail' => 'connectionDetail'],
            ['change' => 'holidayChange', 'detail' => 'holidayDetail'],
            ['change' => 'notAvailableChange', 'detail' => 'notAvailableDetail'],
            ['change' => 'policeChange', 'detail' => 'policeDetail'],
            ['change' => 'replacementChange', 'detail' => 'replacementDetail'],
            ['change' => 'specialOccasionChange', 'detail' => 'specialOccasionDetail'],
            ['change' => 'timetableChange', 'detail' => 'timetableDetail'],
            ['change' => 'trcChange', 'detail' => 'trcDetail'],
            ['change' => 'unforseenChange', 'detail' => 'unforseenDetail'],
        ];

        $hasShortNoticeDetails = false;

        // for short notice at least one question should be Yes
        // and corresponding textarea (if there is one) should not be empty
        foreach ($shortNoticQuestionFields as $questionField) {
            $changeValue = $this->{$questionField['change']};

            if (!empty($changeValue) && ($changeValue === 'Y')) {
                // marked as Yes
                if (!empty($questionField['detail'])) {
                    // detail field exists for the question
                    $detailValue = $this->{$questionField['detail']};

                    if (!empty($detailValue)) {
                        // value of the detail field not empty
                        $hasShortNoticeDetails = true;
                        break;
                    }
                } else {
                    // no detail field for the question
                    $hasShortNoticeDetails = true;
                    break;
                }
            }
        }

        return $hasShortNoticeDetails;
    }

    /**
     * Populate properties from data
     *
     * @param array $data data
     *
     * @return void
     */
    public function fromData($data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucwords((string) $key);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }
}
