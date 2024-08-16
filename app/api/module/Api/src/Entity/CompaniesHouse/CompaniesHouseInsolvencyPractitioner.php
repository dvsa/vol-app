<?php

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompaniesHouseInsolvencyPractitioner Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="companies_house_insolvency_practitioner")
 */
class CompaniesHouseInsolvencyPractitioner extends AbstractCompaniesHouseInsolvencyPractitioner
{
    public function __construct(array $data)
    {
        $fields = [
            'name',
            'addressLine1',
            'addressLine2',
            'country',
            'locality',
            'postalCode',
            'region',
            'appointedOn',
            'companiesHouseCompany'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $method = 'set' . ucfirst($field);
                $this->$method($data[$field]);
            }
        }
    }

    public function toArray()
    {
        $arr = [
            'name' => $this->getName(),
            'addressLine1' => $this->getAddressLine1(),
            'addressLine2' => $this->getAddressLine2(),
            'country' => $this->getCountry(),
            'locality' => $this->getLocality(),
            'postalCode' => $this->getPostalCode(),
            'region' => $this->getRegion(),
            'appointedOn' => $this->getAppointedOn()
        ];

        return $arr;
    }
}
