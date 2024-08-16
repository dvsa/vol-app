<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * ApplicationTracking Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_tracking",
 *    indexes={
 *        @ORM\Index(name="fk_application_tracking_application1_idx", columns={"application_id"}),
 *        @ORM\Index(name="fk_application_tracking_user1_idx", columns={"created_by"}),
 *        @ORM\Index(name="fk_application_tracking_user2_idx", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="application_id_UNIQUE", columns={"application_id"})
 *    }
 * )
 */
class ApplicationTracking extends AbstractApplicationTracking
{
    public const STATUS_NOT_SET        = 0;
    public const STATUS_ACCEPTED       = 1;
    public const STATUS_NOT_ACCEPTED   = 2;
    public const STATUS_NOT_APPLICABLE = 3;

    protected $sections =  [
        'Addresses',
        'BusinessDetails',
        'BusinessType',
        'CommunityLicences',
        'ConditionsUndertakings',
        'ConvictionsPenalties',
        'Discs',
        'FinancialEvidence',
        'FinancialHistory',
        'LicenceHistory',
        'OperatingCentres',
        'People',
        'Safety',
        'TaxiPhv',
        'TransportManagers',
        'TypeOfLicence',
        'DeclarationsInternal',
        'VehiclesDeclarations',
        'VehiclesPsv',
        'Vehicles',
    ];

    /**
     * ApplicationTracking constructor.
     *
     * @param Application $application Application
     *
     * @return void
     */
    public function __construct(Application $application)
    {
        $this->setApplication($application);
    }

    /**
     * Get Calculated Values
     *
     * @return array
     * @deprecated
     */
    protected function getCalculatedValues()
    {
        return ['application' => null];
    }

    /**
     * Get Value options
     *
     * @return array
     */
    public static function getValueOptions()
    {
        return [
            (string) self::STATUS_NOT_SET        => '',
            (string) self::STATUS_ACCEPTED       => 'Accepted',
            (string) self::STATUS_NOT_ACCEPTED   => 'Not accepted',
            (string) self::STATUS_NOT_APPLICABLE => 'Not applicable',
        ];
    }

    /**
     * Apply section status from data
     *
     * @param array $data Data
     *
     * @return $this
     */
    public function exchangeStatusArray(array $data)
    {
        foreach ($this->sections as $section) {
            $key = lcfirst((string) $section) . 'Status';
            if (isset($data[$key])) {
                $method = 'set' . $section . 'Status';
                $this->$method($data[$key]);
            }
        }
        return $this;
    }

    /**
     * Is Valid
     *
     * @param array $sections Sections
     *
     * @return bool
     */
    public function isValid($sections)
    {
        $filter = new UnderscoreToCamelCase();

        $validStatuses = [self::STATUS_ACCEPTED, self::STATUS_NOT_APPLICABLE];

        foreach ($sections as $section) {
            $getter = 'get' . ucfirst((string) $filter->filter($section)) . 'Status';
            if (!in_array($this->$getter(), $validStatuses)) {
                return false;
            }
        }

        return true;
    }
}
