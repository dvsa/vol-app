<?php

declare(strict_types=1);

namespace OlcsTest\Form\Model\Form;

use Admin\Form\Model\Form\BusNoticePeriod;
use Laminas\Validator\Between;
use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * @see   BusNoticePeriod
 * @group FormTests
 */
class BusNoticePeriodTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = BusNoticePeriod::class;

    public function testNoticeArea(): void
    {
        $this->assertFormElementIsRequired(['busNoticePeriod', 'noticeArea']);
        $this->assertFormElementText(['busNoticePeriod', 'noticeArea'], 1, 70);
    }

    public function testStandardPeriod(): void
    {
        $this->assertFormElementIsRequired(['busNoticePeriod', 'standardPeriod']);
        $this->assertFormElementNumber(
            ['busNoticePeriod', 'standardPeriod'],
            0,
            999,
            [Between::NOT_BETWEEN]
        );
    }

    public function testSubmit(): void
    {
        $this->assertFormElementActionButton(['form-actions', 'submit']);
    }

    public function testCancel(): void
    {
        $this->assertFormElementActionButton(['form-actions', 'cancel']);
    }
}
