<?php

namespace OlcsTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\Data\Object as F;

/**
 * Class FeePaymentTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class FeePaymentTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\FeePayment';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['details', 'paymentType'],
                [
                    'fpm_card_offline' => 'Card',
                    'fpm_cash'         => 'Cash',
                    'fpm_cheque'       => 'Cheque',
                    'fpm_po'           => 'PO',
                ]
            ],
        ];
    }

    protected function getFormData()
    {
        $sm  = \OlcsTest\Bootstrap::getServiceManager();
        $dateHelper = $sm->get('Helper\Date');

        $todayStr     = $dateHelper->getDate('Y-m-d');
        $today        = array_combine(['y', 'm', 'd'], explode('-', $todayStr));
        $yesterdayStr = $dateHelper->getDateObject('yesterday')->format('Y-m-d');
        $yesterday    = array_combine(['y', 'm', 'd'], explode('-', $yesterdayStr));
        $tomorrowStr  = $dateHelper->getDateObject('tomorrow')->format('Y-m-d');
        $tomorrow     = array_combine(['y', 'm', 'd'], explode('-', $tomorrowStr));

        return [
            new F\Test(
                new F\Stack(['details', 'paymentType']),
                new F\Value(F\Value::VALID, 'fpm_foo'),
                new F\Value(F\Value::VALID, 'fpm_bar'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['details', 'received']),
                new F\Value(F\Value::VALID, '1'),
                new F\Value(F\Value::VALID, '1.01'),
                new F\Value(F\Value::INVALID, '-1'),
                new F\Value(F\Value::INVALID, '0'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'not a number')
            ),
            new F\Test(
                new F\Stack(['details', 'receiptDate']),
                new F\Value(F\Value::VALID, ['day'=>'05', 'month'=>'01', 'year'=>'2015']),
                new F\Value(
                    F\Value::VALID,
                    [
                        'day'   => $today['d'],
                        'month' => $today['m'],
                        'year'  => $today['y'],
                    ]
                ),
                new F\Value(
                    F\Value::VALID,
                    [
                        'day'   => $yesterday['d'],
                        'month' => $yesterday['m'],
                        'year'  => $yesterday['y'],
                    ]
                ),
                new F\Value(
                    F\Value::INVALID,
                    [
                        'day'   => $tomorrow['d'],
                        'month' => $tomorrow['m'],
                        'year'  => $tomorrow['y'],
                    ]
                ),

                // null receiptDate is only valid for card payments
                new F\Value(F\Value::INVALID, null),
                new F\Value(
                    F\Value::VALID, null,
                    new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_card_offline')
                ),
                new F\Value(
                    F\Value::INVALID, null,
                    new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_cash')
                ),
                new F\Value(
                    F\Value::INVALID, null,
                    new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_cheque')
                ),
                new F\Value(
                    F\Value::INVALID, null,
                    new F\Context(new F\Stack(['details', 'paymentType']), 'fpm_po')
                )
            ),
        ];
    }
}
