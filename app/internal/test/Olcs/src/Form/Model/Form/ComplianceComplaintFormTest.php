<?php

namespace OlcsTest\Form\Model\Form;

use Dvsa\OlcsTest\FormTester\Data\Object as F;

/**
 * Class ComplianceComplaintFormTest
 * @package OlcsTest\FormTest
 * @group ComponentTests
 * @group FormTests
 */
class ComplianceComplaintFormTest extends AbstractFormTest
{
    protected $formName = '\Olcs\Form\Model\Form\Complaint';

    protected function getDynamicSelectData()
    {
        return [
            [
                ['fields', 'status'],
                ['ecst_open' => 'Open', 'ecst_closed' => 'Closed']
            ],
            [
                ['fields', 'status'],
                [
                    'cs_ack' => 'Acknowledged',
                    'cs_pin' => 'PI notified',
                    'cs_rfs' => 'Review form sent',
                    'cs_vfr' => 'Valid for review',
                    'cs_yst' => 'Are you still there?'
                ]
            ],
            [
                ['fields', 'complaintType'],
                [
                    'ct_cor' => 'foo',
                    'ct_cov' => 'foo',
                    'ct_dgm' => 'foo',
                    'ct_dsk' => 'foo',
                    'ct_fls' => 'foo',
                    'ct_lvu' => 'foo',
                    'ct_ndl' => 'foo',
                    'ct_nol' => 'foo',
                    'ct_olr' => 'foo',
                    'ct_ovb' => 'foo',
                    'ct_pvo' => 'foo',
                    'ct_rds' => 'foo',
                    'ct_rta' => 'foo',
                    'ct_sln' => 'foo',
                    'ct_spe' => 'foo',
                    'ct_tgo' => 'foo',
                    'ct_ufl' => 'foo',
                    'ct_ump' => 'foo',
                    'ct_urd' => 'foo',
                    'ct_vpo' => 'foo'
                ]
            ]
        ];
    }

    protected function getFormData()
    {
        return [
            new F\Test(
                new F\Stack(['fields', 'complainantForename']),
                new F\Value(F\Value::VALID, 'John'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'a'),
                new F\Value(F\Value::INVALID, 'This is longer than the max123456789')
            ),
            new F\Test(
                new F\Stack(['fields', 'complainantFamilyName']),
                new F\Value(F\Value::VALID, 'Smith'),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, 'a'),
                new F\Value(F\Value::INVALID, 'This is longer than the max123456789')
            ),
            new F\Test(
                new F\Stack(['fields', 'complaintDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, null),
                new F\Value(F\Value::INVALID, ['day'=>'26', 'month'=>'09', 'year'=>'2500']),
                new F\Value(F\Value::INVALID, ['day'=>'31', 'month'=>'02', 'year'=>'2015'])
            ),
            new F\Test(
                new F\Stack(['fields', 'complaintType']),
                new F\Value(F\Value::VALID, 'ct_cor'),
                new F\Value(F\Value::VALID, 'ct_cov'),
                new F\Value(F\Value::VALID, 'ct_dgm'),
                new F\Value(F\Value::VALID, 'ct_fls'),
                new F\Value(F\Value::VALID, 'ct_lvu'),
                new F\Value(F\Value::VALID, 'ct_ndl'),
                new F\Value(F\Value::VALID, 'ct_nol'),
                new F\Value(F\Value::VALID, 'ct_olr'),
                new F\Value(F\Value::VALID, 'ct_ovb'),
                new F\Value(F\Value::VALID, 'ct_pvo'),
                new F\Value(F\Value::VALID, 'ct_rds'),
                new F\Value(F\Value::VALID, 'ct_rta'),
                new F\Value(F\Value::VALID, 'ct_sln'),
                new F\Value(F\Value::VALID, 'ct_spe'),
                new F\Value(F\Value::VALID, 'ct_tgo'),
                new F\Value(F\Value::VALID, 'ct_ufl'),
                new F\Value(F\Value::VALID, 'ct_ump'),
                new F\Value(F\Value::VALID, 'ct_urd'),
                new F\Value(F\Value::VALID, 'ct_vpo'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'status']),
                new F\Value(F\Value::VALID, 'cs_ack'),
                new F\Value(F\Value::VALID, 'cs_pin'),
                new F\Value(F\Value::VALID, 'cs_rfs'),
                new F\Value(F\Value::VALID, 'cs_vfr'),
                new F\Value(F\Value::VALID, 'cs_yst'),
                new F\Value(F\Value::INVALID, null)
            ),
            new F\Test(
                new F\Stack(['fields', 'description']),
                new F\Value(F\Value::VALID, 'A description between 5-4000 chars'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, str_pad('', 4000, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 4001, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'vrm']),
                new F\Value(F\Value::VALID, 'AB12CDE'),
                new F\Value(F\Value::VALID, 'AB 12 CDE'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, ''),
                // ToDo: removed as part of VOL-2922 - reinstate or expand test as requirements for VRM validation fully elaborated
                //new F\Value(F\Value::INVALID, 'AAAAAAAA')
            ),
            new F\Test(
                new F\Stack(['fields', 'driverForename']),
                new F\Value(F\Value::VALID, 'firstname'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, str_pad('', 35, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 36, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'driverFamilyName']),
                new F\Value(F\Value::VALID, 'surname'),
                new F\Value(F\Value::VALID, null),
                new F\Value(F\Value::VALID, ''),
                new F\Value(F\Value::VALID, str_pad('', 35, '+')),
                new F\Value(F\Value::INVALID, str_pad('', 36, '+'))
            ),
            new F\Test(
                new F\Stack(['fields', 'closedDate']),
                new F\Value(F\Value::VALID, ['day'=>'26', 'month'=>'09', 'year'=>'2013']),
                new F\Value(F\Value::INVALID, ['day'=>'31', 'month'=>'02', 'year'=>'2015'])
            )

        ];
    }
}
