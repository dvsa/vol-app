<?php

namespace OlcsTest\Form\Model\Form\Lva;

use Dvsa\OlcsTest\FormTester\AbstractFormValidationTestCase;

/**
 * Class AddGoodsVehicleLicenceTest
 *
 * @group FormTests
 */
class AddGoodsVehicleLicenceTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\AddGoodsVehicleLicence::class;

    public function testId()
    {
        $this->assertFormElementHidden(['data', 'id']);
    }

    public function testVersion()
    {
        $this->assertFormElementHidden(['data', 'version']);
    }

    public function testVrm()
    {
        $this->assertFormElementHidden(['data', 'vrm']);
    }

    public function testUnvalidatedVrm()
    {
        $this->assertFormElementText(['data', 'unvalidatedVrm']);
    }

    public function testPlatedWeight()
    {
        $this->assertFormElementVehiclePlatedWeight(['data', 'platedWeight']);
    }

    public function testLicenceVehicleId()
    {
        $element = ['licence-vehicle', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testLicenceVehicleVersion()
    {
        $element = ['licence-vehicle', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testLicenceVehicleReceivedDate()
    {
        $element = ['licence-vehicle', 'receivedDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testLicenceVehicleSpecifiedDateTime()
    {
        $element = ['licence-vehicle', 'specifiedDate'];

        $tomorrow = new \DateTimeImmutable('+1 day');

        $this->assertFormElementDateTimeValidCheck(
            $element,
            [
                'year'   => $tomorrow->format('Y'),
                'month'  => $tomorrow->format('m'),
                'day'    => $tomorrow->format('j'),
                'hour'   => 12,
                'minute' => 12,
                'second' => 12,
            ]
        );
    }

    public function testLicenceVehicleRemovalDate()
    {
        $element = ['licence-vehicle', 'removalDate'];
        $this->assertFormElementDate($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testDiscNumber()
    {
        $element = ['licence-vehicle', 'discNo'];
        $this->assertFormElementText($element);
        $this->assertFormElementRequired($element, false);
    }

    public function testWarningLetterSeedDate()
    {
        $this->assertFormElementDate(
            ['licence-vehicle', 'warningLetterSeedDate']
        );
    }

    public function testWarningLetterSentDate()
    {
        $this->assertFormElementDate(
            ['licence-vehicle', 'warningLetterSentDate']
        );
    }

    public function testAddAnother()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'addAnother']
        );
    }

    public function testSubmit()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'submit']
        );
    }

    public function testCancel()
    {
        $this->assertFormElementActionButton(
            ['form-actions', 'cancel']
        );
    }
}
