<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class PartnerTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\Partner';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['address', 'category'],
                ['c1' => 'Category 1', 'c2' => 'Category 2']
            ],
            [
                ['details', 'subCategory'],
                ['sc1' => 'Subcategory 1', 'sc2' => 'Subcategory 2']
            ],
            [
                ['assignment', 'assignedToTeam'],
                ['t1' => 'Team 1', 't2' => 'Team2']
            ],
            [
                ['assignment', 'assignedToUser'],
                ['u1' => 'User 1', 'u2' => 'User 2']
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['main', 'description']),
                new F\Value(F\Value::VALID, 'Some Partner Name'),
                new F\Value(F\Value::INVALID, str_repeat('E', 35)),
                new F\Value(F\Value::INVALID, '11'),
                new F\Value(F\Value::INVALID, null)
            ),
        ];
    }
}
