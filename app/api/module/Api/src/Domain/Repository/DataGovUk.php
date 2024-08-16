<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PDO;

/**
 * Contains methods to get data from DB to export for data.gov.uk
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class DataGovUk
{
    /**
     * DataGovUk constructor.
     *
     * @param Connection $conn Database connection
     */
    public function __construct(private readonly Connection $conn)
    {
    }

    /**
     * Close database connection
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->conn !== null) {
            $this->conn->close();
        }
    }

    public function fetchPsvOperatorList(): Result
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM data_gov_uk_psv_operator_list'
        );

        return $stmt->executeQuery();
    }

    /**
     * Fetch operator licences in specified areas
     */
    public function fetchOperatorLicences(array $areaNames): Result
    {
        $inStmt = implode(', ', array_fill(0, count($areaNames), '?'));

        //  query data
        $stmt = $this->conn->prepare(
            'SELECT * FROM data_gov_uk_operator_licence_view WHERE `GeographicRegion` IN (' . $inStmt . ')'
        );

        foreach ($areaNames as $idx => $name) {
            $stmt->bindValue($idx + 1, $name, PDO::PARAM_STR);
        }

        return $stmt->executeQuery();
    }

    /**
     * Fetch bus registered only
     */
    public function fetchBusRegisteredOnly(array $areaCodes): Result
    {
        return $this->fetchBusReg('data_gov_uk_bus_registered_only_view', $areaCodes);
    }

    /**
     * Fetch bus variations
     */
    public function fetchBusVariation(array $areaCodes): Result
    {
        return $this->fetchBusReg('data_gov_uk_bus_variation_view', $areaCodes);
    }

    /**
     * Fetch bus registrations
     */
    private function fetchBusReg(string $view, array $areaCodes): Result
    {
        $inStmt = implode(', ', array_fill(0, count($areaCodes), '?'));

        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . $view . ' WHERE `Current Traffic Area` IN (' . $inStmt . ')'
        );

        foreach ($areaCodes as $idx => $code) {
            $stmt->bindValue($idx + 1, $code, PDO::PARAM_STR);
        }

        return $stmt->executeQuery();
    }
}
