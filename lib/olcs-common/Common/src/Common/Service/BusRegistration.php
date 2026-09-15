<?php

namespace Common\Service;

/**
 * Class BusRegistration
 * @package Common\Service
 */
class BusRegistration
{
    public const STATUS_NEW = 'breg_s_new';

    public const STATUS_VAR = 'breg_s_var';

    public const STATUS_CANCEL = 'breg_s_cancellation';

    public const STATUS_ADMIN = 'breg_s_admin';

    public const STATUS_REGISTERED = 'breg_s_registered';

    public const STATUS_REFUSED = 'breg_s_refused';

    public const STATUS_WITHDRAWN = 'breg_s_withdrawn';

    public const STATUS_CNS = 'breg_s_cns';

    public const STATUS_CANCELLED = 'breg_s_cancelled';

    /**
     * @var array
     */
    protected $defaultAll = [
        // Reason for action text fields should all be empty
        'reasonSnRefused' => '',
        'reasonCancelled' => '',
        'reasonRefused' => '',
        // Withdrawn reason can be null; its here to override any value set in a variation/cancellation
        'withdrawnReason' => null,
        // At time of creation, we don't know if its short notice or not. Default to no.
        'isShortNotice' => 'N',
        // This is a new application/variation so hasn't been refused by short notice (yet)
        'shortNoticeRefused' => 'N',
        // Checks before granting should all default to no
        'copiedToLaPte' => 'N',
        'laShortNote' => 'N',
        'applicationSigned' => 'N',
        'opNotifiedLaPte' => 'N',
        // Trc conditions should also default to no/empty
        'trcConditionChecked' => 'N',
        'trcNotes' => '',
        // Timetable conditions should default to no
        'timetableAcceptable' => 'N',
        'mapSupplied' => 'N',
        // (Re)set dates to null
        'receivedDate' => null,
        'effectiveDate' => null,
        'endDate' => null,
        // These will be set to yes explicitly by the TXC processor, default it to no for the internal app
        'isTxcApp' => 'N',
        'ebsrRefresh' => 'N'
    ];

    /**
     * @var array
     */
    protected $defaultNew = [
        'subsidised' => 'bs_no', //might this need to be a constant?
        'busNoticePeriod' => 2,
        'variationNo' => 0,
        'needNewStop' => 'N', //should this be moved to all? and the details field wiped?
        'hasManoeuvre' => 'N',
        'hasNotFixedStop' => 'N',
        // Reg number is generated based upon the licence and route number. empty by default.
        'regNo' => '',
        'routeNo' => 0,
        'useAllStops' => 'N', //some discussion over what value of this should be John Spellman has now confirmed it
        'isQualityContract' => 'N',
        'isQualityPartnership' => 'N',
        'qualityPartnershipFacilitiesUsed' => 'N'
    ];

    /**
     * @var array
     */
    protected $defaultShortNotice = [
        'bankHolidayChange' => 'N',
        'connectionChange' => 'N',
        'connectionDetail' => null,
        'holidayChange' => 'N',
        'holidayDetail' => null,
        'notAvailableChange' => 'N',
        'notAvailableDetail' => null,
        'policeChange' => 'N',
        'policeDetail' => null,
        'replacementChange' => 'N',
        'replacementDetail' => null,
        'specialOccasionChange' => 'N',
        'specialOccasionDetail' => null,
        'timetableChange' => 'N',
        'timetableDetail' => null,
        'trcChange' => 'N',
        'trcDetail' => null,
        'unforseenChange' => 'N',
        'unforseenDetail' => null,
    ];

    /**
     * @param (int|string)[] $licence
     *
     * @return array
     *
     * @psalm-param array{id: 123, licNo: 'AB12563'} $licence
     */
    public function createNew($licence)
    {
        $data = array_merge($this->defaultAll, $this->defaultNew);
        $data['status'] = self::STATUS_NEW;
        $data['revertStatus'] = self::STATUS_NEW;

        $data['shortNotice'] = $this->defaultShortNotice;

        $data['licence']['id'] = $licence['id'];

        $data['_OPTIONS_'] = $this->getCascadeOptions();

        return $data;
    }

    /**
     * @param $previous
     * @param int[] $mostRecent
     *
     * @return mixed
     *
     * @psalm-param array{variationNo: 3} $mostRecent
     */
    public function createVariation($previous, $mostRecent)
    {
        $data = $previous;

        //unset database metadata
        $this->scrubEntity($data);
        if (isset($data['otherServices']) && is_array($data['otherServices'])) {
            foreach ($data['otherServices'] as &$otherService) {
                $this->scrubEntity($otherService);
            }
        }

        //new variation reasons will be required for a new variation
        unset($data['variationReasons']);

        $data['variationNo'] = $mostRecent['variationNo'] + 1;
        $data['status'] = self::STATUS_VAR;
        $data['statusChangeDate'] = date(\DateTime::ISO8601);
        $data['revertStatus'] = self::STATUS_VAR;

        //This is defined manyToOne in backend...
        $data['shortNotice'] = $this->defaultShortNotice;
        $data['parent']['id'] = $previous['id'];

        //optimise backend call
        $licence = $data['licence'];
        unset($data['licence']);
        $data['licence']['id'] = $licence['id'];

        //override columns which need different defaults for a variation
        $data = array_merge($data, $this->defaultAll);

        $data['_OPTIONS_'] = $this->getCascadeOptionsVariation();

        return $data;
    }

    /**
     * @param $parent
     *
     * @return mixed
     *
     * @psalm-param array{variationNo: mixed} $mostRecent
     */
    public function createCancellation($parent, $mostRecent)
    {
        $data = $this->createVariation($parent, $mostRecent);

        $data['status'] = self::STATUS_CANCEL;
        $data['revertStatus'] = self::STATUS_CANCEL;

        return $data;
    }

    /**
     * @param $entity
     */
    protected function scrubEntity(&$entity): void
    {
        //unset database metadata
        unset(
            $entity['id'],
            $entity['version'],
            $entity['createdBy'],
            $entity['lastModifiedBy'],
            $entity['createdOn'],
            $entity['lastModifiedOn'],
            $entity['busRegId']
        );
    }

    /**
     * @return array
     */
    public function getCascadeOptions()
    {
        return [
            'cascade' => [
                'single' => [
                    'shortNotice' => [
                        'entity' => 'BusShortNotice',
                        'parent' => 'busReg'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCascadeOptionsVariation()
    {
        return [
            'cascade' => [
                'single' => [
                    'shortNotice' => [
                        'entity' => 'BusShortNotice',
                        'parent' => 'busReg'
                    ]
                ],
                'list' => [
                    'otherServices' => [
                        'entity' => 'BusRegOtherService',
                        'parent' => 'busReg'
                    ]
                ]
            ]
        ];
    }
}
