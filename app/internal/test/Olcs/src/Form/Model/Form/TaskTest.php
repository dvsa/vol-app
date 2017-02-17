<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class TaskTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class TaskTest extends AbstractFormTest
{
    protected $formName = \Olcs\Form\Model\Form\Task::class;

    protected function getDynamicSelectData()
    {
        return [
            [
                ['details', 'category'],
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
        $sm  = $this->getServiceManager();
        $dateHelper = $sm->get('Helper\Date');

        $todayStr     = $dateHelper->getDate('Y-m-d');
        $today        = array_combine(['Y', 'm', 'd'], explode('-', $todayStr));

        return [
            new F\Test(
                new F\Stack(['details', 'urgent']),
                new F\Value(F\Value::VALID, 'Y'),
                new F\Value(F\Value::VALID, 'N'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'category']),
                new F\Value(F\Value::VALID, 'c1'),
                new F\Value(F\Value::VALID, 'c2'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'subCategory']),
                new F\Value(F\Value::VALID, 'sc1'),
                new F\Value(F\Value::VALID, 'sc2'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['assignment', 'assignedToTeam']),
                new F\Value(F\Value::VALID, 't1'),
                new F\Value(F\Value::VALID, 't2'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['assignment', 'assignedToUser']),
                new F\Value(F\Value::VALID, 'u1'),
                new F\Value(F\Value::VALID, 'u2'),
                new F\Value(F\Value::VALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'actionDate']),
                new F\Value(
                    F\Value::VALID,
                    [
                        'day'   => $today['d'],
                        'month' => $today['m'],
                        'year'  => $today['Y'],
                    ]
                ),
                // probably shouldn't be allowed, but there's no validation set up
                // new F\Value(
                //     F\Value::INVALID,
                //     [
                //         'day'   => $yesterday['d'],
                //         'month' => $yesterday['m'],
                //         'year'  => $yesterday['y']
                //     ]
                // ),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'description']),
                new F\Value(F\Value::VALID, 'ok'),
                new F\Value(F\Value::VALID, 'this is a task description'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ''),
                new F\Value(F\Value::INVALID, 'x'), // too short
                new F\Value(F\Value::INVALID, str_pad('', 256, '+')) // too long
            )
        ];
    }
}
