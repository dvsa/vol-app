<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Licence;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;

/**
 * Query to retrieve the international goods report
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class InternationalGoodsReport extends AbstractRawQuery
{
    protected $templateMap = [
        'l' => LicenceEntity::class,
        'lv' => LicenceVehicleEntity::class,
        'o' => OrganisationEntity::class,
        'rsts' => RefDataEntity::class,
        'cd' => ContactDetailsEntity::class,
        'a' => AddressEntity::class,
        'c' => CountryEntity::class
    ];

    protected $queryTemplate = 'SELECT
	{o.id} AS organisationId,
    {o.name} AS organisationName,
    {l.licNo} AS licenceNo,
    {rsts.description} AS licenceStatus,
    rd2.description AS licenceType,
	{l.inForceDate} AS licenceStart,
    {l.expiryDate} AS licenceContinuationDate,
    CASE {l.status}
		WHEN :licStatusCns THEN {l.cnsDate}
		WHEN :licStatusRevoked THEN {l.revokedDate}
        WHEN :licStatusSurrendered THEN {l.surrenderedDate}
        WHEN :licStatusTerminated THEN {l.surrenderedDate}
        ELSE \'\'  
	END AS licenceEnd,
    {l.totAuthVehicles} AS vehiclesAuthorised,
    {l.totAuthTrailers} AS trailersAuthorised,
    (SELECT count(*) 
      FROM {lv} 
      WHERE {lv.licence} = {l.id} 
      AND {lv.specifiedDate} IS NOT NULL
      AND {lv.removalDate} IS NULL) AS vehiclesSpecified,
	{a.addressLine1} AS addressLine1,
	{a.addressLine2} AS addressLine2,	
    {a.addressLine3} AS addressLine3,
	{a.addressLine4} AS addressLine4,
	{a.town} AS town,
	{a.postcode} AS postcode,
	{c.countryDesc} AS country
FROM
	{l}
    JOIN {o} ON {l.organisation} = {o.id}
    JOIN {rsts} ON {rsts.id} = {l.status} AND {rsts.refDataCategoryId} = :rdLicStatus
    JOIN ref_data rd2 ON rd2.id = {l.licenceType}
    LEFT JOIN {cd} ON {cd.id} = {l.correspondenceCd}
    LEFT JOIN {a} ON {a.id} = {cd.address}
    LEFT JOIN {c} ON {c.id} = {a.countryCode}
WHERE 
    {l.status} IN (:licenceStatuses)
    AND {l.goodsOrPsv} = :goodsOrPsv
    AND {l.licenceType} = :licenceType
ORDER BY
	{o.name};';

    /**
     * get params
     *
     * @return array
     */
    #[\Override]
    protected function getParams()
    {
        return [
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
        ];
    }

    /**
     * get param types
     *
     * @return array
     */
    #[\Override]
    protected function getParamTypes()
    {
        return [
            'licenceStatuses' => DoctrineConnection::PARAM_STR_ARRAY,
            'goodsOrPsv' => \PDO::PARAM_STR,
            'licenceType' => \PDO::PARAM_STR,
            'rdLicStatus' => \PDO::PARAM_STR,
        ];
    }
}
