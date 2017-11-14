<?php


namespace OlcsTest\Controller\Lva\Traits;


use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;

class VariationWizardPageWithSubsequentPageControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    private $sut;

    public function setUp()
    {
        $this->sut = $this->getMockForTrait(VariationWizardPageWithSubsequentPageControllerTrait::class);
    }


    public function testHasCompletedFalseIfNoSection()
    {
        $actual = $this->sut->hasCompleted([], null);
        $this->assertFalse($actual);
    }


    public function testHasCompletedTrueIfMatchingSectionsEqualsTwo()
    {
        $sectionsCompleted = [
            'addressesStatus' => 0,
            'application' => null,
            'businessDetailsStatus' => 2,
            'businessTypeStatus' => 0,
            'communityLicencesStatus' => 0
        ];
        $sections = ['businessDetailsStatus'];
        $actual = $this->sut->hasCompleted($sections, $sectionsCompleted);
        $this->assertTrue($actual);
    }

    public function testHasCompletedFalseIfAMatchingSectionNotEqualToTwo()
    {
        $sectionsCompleted = [
            'addressesStatus' => 0,
            'application' => null,
            'businessDetailsStatus' => 1,
            'businessTypeStatus' => 0,
            'communityLicencesStatus' => 0
        ];
        $sections = ['businessDetailsStatus'];
        $actual = $this->sut->hasCompleted($sections, $sectionsCompleted);
        $this->assertFalse($actual);
    }

    public function testHasCompletedTrueIfMatchingSectionsAllEqualToTwo()
    {
        $sectionsCompleted = [
            'addressesStatus' => 0,
            'application' => null,
            'businessDetailsStatus' => 2,
            'businessTypeStatus' => 2,
            'communityLicencesStatus' => 0
        ];
        $sections = ['businessDetailsStatus','businessTypeStatus'];
        $actual = $this->sut->hasCompleted($sections, $sectionsCompleted);
        $this->assertTrue($actual);
    }
}
