<?php


namespace OlcsTest\Controller\Lva\Traits;


class VariationWizardPageWithSubsequentPageControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    private $sut;

    public function setUp()
    {
        $this->sut = $this->getMockForTrait(VariationWizardPageWithSubsequentPageControllerTraitTest::class);
    }

    public function testRedirectsIfNotComplete()
    {

    }
}