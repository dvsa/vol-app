<?php

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompaniesHouseCompany Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="companies_house_company")
 */
class CompaniesHouseCompany extends AbstractCompaniesHouseCompany
{
    public function __construct(array $data)
    {
        parent::__construct();

        $fields = [
            'addressLine1',
            'addressLine2',
            'companyName',
            'companyNumber',
            'companyStatus',
            'country',
            'locality',
            'poBox',
            'postalCode',
            'premises',
            'region',
            'insolvencyProcessed'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $method = 'set' . ucfirst($field);
                $this->$method($data[$field]);
            }
        }

        if (!empty($data['officers'])) {
            foreach ($data['officers'] as $officerData) {
                $officer = new CompaniesHouseOfficer($officerData);
                $officer->setCompaniesHouseCompany($this);
                $this->getOfficers()->add($officer);
            }
        }

        if (!empty($data['insolvencyPractitioners'])) {
            foreach ($data['insolvencyPractitioners'] as $insolvencyPractitionerData) {
                $insolvencyPractitioner = new CompaniesHouseInsolvencyPractitioner($insolvencyPractitionerData);
                $insolvencyPractitioner->setCompaniesHouseCompany($this);
                $this->getInsolvencyPractitioners()->add($insolvencyPractitioner);
            }
        }
    }

    public function toArray()
    {
        $arr = [
            'addressLine1' => $this->getAddressLine1(),
            'addressLine2' => $this->getAddressLine2(),
            'companyName' => $this->getCompanyName(),
            'companyNumber' => $this->getCompanyNumber(),
            'companyStatus' => $this->getCompanyStatus(),
            'country' => $this->getCountry(),
            'locality' => $this->getLocality(),
            'poBox' => $this->getPoBox(),
            'postalCode' => $this->getPostalCode(),
            'premises' => $this->getPremises(),
            'region' => $this->getRegion(),
            'insolvencyProcessed' => $this->getInsolvencyProcessed(),
            'officers' => [],
        ];

        if (!empty($this->getOfficers())) {
            foreach ($this->getOfficers() as $officer) {
                $arr['officers'][] = $officer->toArray();
            }
        }

        if (!empty($this->getInsolvencyPractitioners())) {
            foreach ($this->getInsolvencyPractitioners() as $insolvencyPractitioner) {
                $arr['insolvencyPractitioners'][] = $insolvencyPractitioner->toArray();
            }
        }

        return $arr;
    }
}
