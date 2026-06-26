<?php

namespace CommonTest\Service\Table\Formatter;

/**
 * DataRetentionRule Link test
 */
class DataRetentionRuleIsEnabledTest extends \PHPUnit\Framework\TestCase
{
    public function testIsEnabledFalse(): void
    {
        $data = [
            "actionType" => [
                "description" => "DR action type",
                "displayOrder" => null,
                "id" => "Automate",
                "olbsKey" => null,
                "parent" => null,
                "refDataCategoryId" => "action_type",
                "version" => 1
            ],
            "createdBy" => null,
            "createdOn" => "2017-10-12T16:54:14+0000",
            "customProcedure" => null,
            "deletedDate" => null,
            "description" => "IRFO Operator expired",
            "id" => 1,
            "isCustomRule" => false,
            "isEnabled" => false,
            "lastModifiedBy" => null,
            "lastModifiedOn" => "2017-10-12T16:54:14+0000",
            "maxDataSet" => 1000,
            "populateProcedure" => "sp_populate_irfo_operator_expired",
            "retentionPeriod" => "60"
        ];

        $this->assertEquals('No', (new \Common\Service\Table\Formatter\DataRetentionRuleIsEnabled())->format($data));
    }

    public function testIsEnabledtrue(): void
    {
        $data = [
            "actionType" => [
                "description" => "DR action type",
                "displayOrder" => null,
                "id" => "Automate",
                "olbsKey" => null,
                "parent" => null,
                "refDataCategoryId" => "action_type",
                "version" => 1
            ],
            "createdBy" => null,
            "createdOn" => "2017-10-12T16:54:14+0000",
            "customProcedure" => null,
            "deletedDate" => null,
            "description" => "IRFO Operator expired",
            "id" => 1,
            "isCustomRule" => false,
            "isEnabled" => true,
            "lastModifiedBy" => null,
            "lastModifiedOn" => "2017-10-12T16:54:14+0000",
            "maxDataSet" => 1000,
            "populateProcedure" => "sp_populate_irfo_operator_expired",
            "retentionPeriod" => "60"
        ];

        $this->assertEquals('Yes', (new \Common\Service\Table\Formatter\DataRetentionRuleIsEnabled())->format($data));
    }
}
