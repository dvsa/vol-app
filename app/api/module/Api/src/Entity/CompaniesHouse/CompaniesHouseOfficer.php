<?php

namespace Dvsa\Olcs\Api\Entity\CompaniesHouse;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompaniesHouseOfficer Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="companies_house_officer",
 *    indexes={
 *        @ORM\Index(name="ix_companies_house_officer_companies_house_company_id",
     *     columns={"companies_house_company_id"})
 *    }
 * )
 */
class CompaniesHouseOfficer extends AbstractCompaniesHouseOfficer
{
    public function __construct(array $data)
    {
        $fields = [
            'name',
            'role',
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $method = 'set' . ucfirst($field);
                $this->$method($data[$field]);
            }
        }

        if (isset($data['dateOfBirth'])) {
            if (is_array($data['dateOfBirth'])) {
                $dob = new \DateTime();
                $dob->setDate(
                    $data['dateOfBirth']['year'],
                    $data['dateOfBirth']['month'],
                    // day element of D.o.B. is usually suppressed
                    $data['dateOfBirth']['day'] ?? 1
                );
                $dob->setTime(0, 0, 0);
                $this->setDateOfBirth($dob);
            } elseif ($data['dateOfBirth'] instanceof \DateTime) {
                $this->setDateOfBirth($data['dateOfBirth']);
            }
        }
    }

    public function toArray()
    {
        $arr = [
            'name' => $this->getName(),
            'role' => $this->getRole(),
            'dateOfBirth' => $this->getDateOfBirth(),
        ];

        return $arr;
    }
}
