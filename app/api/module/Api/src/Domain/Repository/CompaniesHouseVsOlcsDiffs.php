<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

/**
 * Contains methods to get difference between company house and olcs data from DB
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class CompaniesHouseVsOlcsDiffs
{
    public function __construct(private readonly Connection $conn)
    {
    }

    public function __destruct()
    {
        $this->conn->close();
    }

    /**
     * Fetch organisation officer (people) differences from db
     */
    public function fetchOfficerDiffs(): Result
    {
        return $this->conn->executeQuery('CALL sp_ch_vs_olcs_diff_organisation_officer');
    }

    /**
     * Fetch organisation address differences from db
     */
    public function fetchAddressDiffs(): Result
    {
        return $this->conn->executeQuery('CALL sp_ch_vs_olcs_diff_organisation_address');
    }

    /**
     * Fetch organisation name differences from db
     */
    public function fetchNameDiffs(): Result
    {
        return $this->conn->executeQuery('CALL sp_ch_vs_olcs_diff_organisation_name');
    }

    /**
     * Fetch organisation with not active status in company house
     */
    public function fetchWithNotActiveStatus(): Result
    {
        return $this->conn->executeQuery('CALL sp_ch_vs_olcs_diff_organisation_not_active');
    }
}
