<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * IrhpPermitRange Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_range",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_stock_ranges_irhp_permit_stocks1_idx",
     *     columns={"irhp_permit_stock_id"}),
 *        @ORM\Index(name="fk_irhp_permit_range_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_range_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpPermitRange extends AbstractIrhpPermitRange implements DeletableInterface
{
    public const BILATERAL_TYPE_STANDARD_SINGLE = 'standard.single';
    public const BILATERAL_TYPE_STANDARD_MULTIPLE = 'standard.multiple';
    public const BILATERAL_TYPE_CABOTAGE_SINGLE = 'cabotage.single';
    public const BILATERAL_TYPE_CABOTAGE_MULTIPLE = 'cabotage.multiple';

    public const BILATERAL_TYPES = [
        RefData::JOURNEY_SINGLE => [
            0 => self::BILATERAL_TYPE_STANDARD_SINGLE,
            1 => self::BILATERAL_TYPE_CABOTAGE_SINGLE,
        ],
        RefData::JOURNEY_MULTIPLE => [
            0 => self::BILATERAL_TYPE_STANDARD_MULTIPLE,
            1 => self::BILATERAL_TYPE_CABOTAGE_MULTIPLE,
        ],
    ];

    public const BILATERAL_TYPES_CRITERIA = [
        self::BILATERAL_TYPE_STANDARD_SINGLE => [
            'journey' => RefData::JOURNEY_SINGLE,
            'cabotage' => false,
        ],
        self::BILATERAL_TYPE_STANDARD_MULTIPLE => [
            'journey' => RefData::JOURNEY_MULTIPLE,
            'cabotage' => false,
        ],
        self::BILATERAL_TYPE_CABOTAGE_SINGLE => [
            'journey' => RefData::JOURNEY_SINGLE,
            'cabotage' => true,
        ],
        self::BILATERAL_TYPE_CABOTAGE_MULTIPLE => [
            'journey' => RefData::JOURNEY_MULTIPLE,
            'cabotage' => true,
        ],
    ];

    /**
     * Create
     *
     * @param IrhpPermitStock $permitStock
     * @param RefData $emissionsCategory
     * @param string $prefix
     * @param int $rangeFrom
     * @param int $rangeTo
     * @param int $reserve
     * @param int $replacement
     * @param array $countries
     * @param RefData $journey
     * @param int $cabotage
     *
     * @return IrhpPermitRange
     */
    public static function create(
        $permitStock,
        $emissionsCategory,
        $prefix,
        $rangeFrom,
        $rangeTo,
        $reserve,
        $replacement,
        $countries,
        $journey,
        $cabotage
    ) {
        $instance = new self();

        $instance->irhpPermitStock = $permitStock;
        $instance->emissionsCategory = $emissionsCategory;
        $instance->prefix = $prefix;
        $instance->fromNo = $rangeFrom;
        $instance->toNo = $rangeTo;
        $instance->ssReserve = $reserve;
        $instance->lostReplacement = $replacement;
        $instance->countrys = $countries;
        $instance->journey = $journey;
        $instance->cabotage = $cabotage;

        return $instance;
    }

    /**
     * Update
     *
     * @param IrhpPermitStock $permitStock
     * @param RefData $emissionsCategory
     * @param string $prefix
     * @param int $rangeFrom
     * @param int $rangeTo
     * @param int $reserve
     * @param int $replacement
     * @param array $countries
     * @param RefData $journey
     * @param int $cabotage
     *
     * @return IrhpPermitRange
     */
    public function update(
        $permitStock,
        $emissionsCategory,
        $prefix,
        $rangeFrom,
        $rangeTo,
        $reserve,
        $replacement,
        $countries,
        $journey,
        $cabotage
    ) {
        $this->irhpPermitStock = $permitStock;
        $this->emissionsCategory = $emissionsCategory;
        $this->prefix = $prefix;
        $this->fromNo = $rangeFrom;
        $this->toNo = $rangeTo;
        $this->ssReserve = $reserve;
        $this->lostReplacement = $replacement;
        $this->countrys = $countries;
        $this->journey = $journey;
        $this->cabotage = $cabotage;

        return $this;
    }

    /**
     * Checks whether there are dependencies on the Permit Range and returns whether the Permit Range can be deleted.
     *
     * @return boolean
     */
    public function canDelete()
    {
        return
            count($this->irhpCandidatePermits) === 0 &&
            count($this->irhpPermits) === 0 &&
            count($this->countrys) === 0;
    }

    /**
     * Get the number of possible permits in this range
     *
     * @return int
     */
    public function getSize()
    {
        return(($this->toNo - $this->fromNo) + 1);
    }

    /**
     * Whether this range has one or more restricted countries
     *
     * @return bool
     */
    public function hasCountries()
    {
        return count($this->countrys) > 0;
    }

    /**
     * Whether this range is a cabotage one
     *
     * @return bool
     */
    public function isCabotage()
    {
        return $this->cabotage;
    }

    /**
     * Whether this range is a standard one
     *
     * @return bool
     */
    public function isStandard()
    {
        return !$this->cabotage;
    }
}
