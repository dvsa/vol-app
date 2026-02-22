<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Query\Licence;

use Dvsa\Olcs\Api\Domain\Repository\Query\Licence\InternationalGoodsReport as InternationalGoodsReportQry;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\OlcsTest\Api\Domain\Repository\Query\AbstractDbQueryTestCase;

/**
 * Expire bus reg test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class InternationalGoodsReportTest extends AbstractDbQueryTestCase
{
    protected $tableNameMap = [
        LicenceEntity::class => 'lic_table',
        LicenceVehicleEntity::class => 'lv_table',
        OrganisationEntity::class => 'org_table',
        RefDataEntity::class => 'rd_table',
        ContactDetailsEntity::class => 'cd_table',
        AddressEntity::class => 'address_table',
        CountryEntity::class => 'country_table'
    ];

    protected $columnNameMap = [
        CountryEntity::class => [
            'countryDesc' => [
                'column' => 'country_desc'
            ],
            'id' => [
                'column' => 'id'
            ],
        ],
        ContactDetailsEntity::class => [
            'address' => [
                'isAssociation' => true,
                'column' => 'address_id'
            ],
            'id' => [
                'column' => 'id'
            ],
        ],
        RefDataEntity::class => [
            'description' => [
                'column' => 'description'
            ],
            'id' => [
                'column' => 'id'
            ],
            'refDataCategoryId' => [
                'column' => 'ref_data_category_id'
            ]
        ],
        OrganisationEntity::class => [
            'name' => [
                'column' => 'name'
            ],
            'id' => [
                'column' => 'id'
            ],
        ],
        LicenceVehicleEntity::class => [
            'licence' => [
                'isAssociation' => true,
                'column' => 'licence_id'
            ],
            'specifiedDate' => [
                'column' => 'specified_date'
            ],
            'removalDate' => [
                'column' => 'removal_date'
            ],
        ],
        AddressEntity::class => [
            'addressLine1' => [
                'column' => 'saon_desc'
            ],
            'addressLine2' => [
                'column' => 'paon_desc'
            ],
            'addressLine3' => [
                'column' => 'street'
            ],
            'addressLine4' => [
                'column' => 'locality'
            ],
            'postcode' => [
                'column' => 'postcode'
            ],
            'town' => [
                'column' => 'town'
            ],
            'countryCode' => [
                'isAssociation' => true,
                'column' => 'country_code'
            ],
            'id' => [
                'column' => 'id'
            ],
        ],
        LicenceEntity::class => [
            'status' => [
                'isAssociation' => true,
                'column' => 'status'
            ],
            'organisation' => [
                'isAssociation' => true,
                'column' => 'organisation_id'
            ],
            'expiryDate' => [
                'column' => 'expiry_date'
            ],
            'totAuthVehicles' => [
                'column' => 'tot_auth_vehicles'
            ],
            'totAuthTrailers' => [
                'column' => 'tot_auth_trailers'
            ],
            'inForceDate' => [
                'column' => 'in_force_date'
            ],
            'cnsDate' => [
                'column' => 'cns_date'
            ],
            'revokedDate' => [
                'column' => 'in_force_date'
            ],
            'surrenderedDate' => [
                'column' => 'cns_date'
            ],
            'id' => [
                'column' => 'id'
            ],
            'licNo' => [
                'column' => 'lic_no'
            ],
            'correspondenceCd' => [
                'column' => 'correspondence_cd'
            ],
            'goodsOrPsv' => [
                'column' => 'goods_or_psv'
            ],
            'licenceType' => [
                'column' => 'licence_type'
            ],
        ]
    ];

    public static function paramProvider(): array
    {
        return [
            [
                [],
                [],
                [
                    'rdLicStatus' => RefDataEntity::LICENCE_STATUS,
                    'goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'licenceStatuses' => [
                        LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                        LicenceEntity::LICENCE_STATUS_CURTAILED,
                        LicenceEntity::LICENCE_STATUS_REVOKED,
                        LicenceEntity::LICENCE_STATUS_SURRENDERED,
                        LicenceEntity::LICENCE_STATUS_SUSPENDED,
                        LicenceEntity::LICENCE_STATUS_VALID,
                    ],
                    'licStatusCns' => LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
                    'licStatusRevoked' => LicenceEntity::LICENCE_STATUS_REVOKED,
                    'licStatusSurrendered' => LicenceEntity::LICENCE_STATUS_SURRENDERED,
                    'licStatusTerminated' => LicenceEntity::LICENCE_STATUS_TERMINATED
                ],
                [
                    'licenceStatuses' => DoctrineConnection::PARAM_STR_ARRAY,
                    'goodsOrPsv' => \PDO::PARAM_STR,
                    'licenceType' => \PDO::PARAM_STR,
                    'rdLicStatus' => \PDO::PARAM_STR,
                ]
            ]
        ];
    }

    protected function getSut(): InternationalGoodsReportQry
    {
        return new InternationalGoodsReportQry();
    }

    protected function getExpectedQuery(): string
    {
        return 'SELECT o.id AS organisationId, o.name AS organisationName, l.lic_no AS licenceNo, ' .
            'rsts.description AS licenceStatus, rd2.description AS licenceType, l.in_force_date AS licenceStart, ' .
            'l.expiry_date AS licenceContinuationDate, ' .
            'CASE l.status WHEN :licStatusCns THEN l.cns_date ' .
            'WHEN :licStatusRevoked THEN l.in_force_date ' .
            'WHEN :licStatusSurrendered THEN l.cns_date ' .
            'WHEN :licStatusTerminated THEN l.cns_date ELSE \'\' ' .
            'END AS licenceEnd, l.tot_auth_vehicles AS vehiclesAuthorised, l.tot_auth_trailers AS trailersAuthorised, ' .
            '(SELECT count(*) FROM lv_table lv WHERE lv.licence_id = l.id AND lv.specified_date IS NOT NULL ' .
            'AND lv.removal_date IS NULL) AS vehiclesSpecified, a.saon_desc AS addressLine1, ' .
            'a.paon_desc AS addressLine2, a.street AS addressLine3, a.locality AS addressLine4, a.town AS town, ' .
            'a.postcode AS postcode, c.country_desc AS country
FROM lic_table l JOIN org_table o ON l.organisation_id = o.id JOIN rd_table rsts ON rsts.id = l.status ' .
            'AND rsts.ref_data_category_id = :rdLicStatus JOIN ref_data rd2 ON rd2.id = l.licence_type ' .
            'LEFT JOIN cd_table cd ON cd.id = l.correspondence_cd LEFT JOIN address_table a ON a.id = cd.address_id ' .
            'LEFT JOIN country_table c ON c.id = a.country_code
WHERE l.status IN (:licenceStatuses) AND l.goods_or_psv = :goodsOrPsv AND l.licence_type = :licenceType
ORDER BY o.name;';
    }
}
