<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Custom\VehicleVrm;
use Common\Form\Elements\Types\PlainText;
use Common\Form\Elements\Types\VehicleTableSearch;
use PHPUnit\Framework\TestCase;
use Laminas\Form\Element\Button;

class VehicleTableSearchTest extends TestCase
{
    public function testVehicleTableSearchCreate(): void
    {
        $sut = new VehicleTableSearch();

        $this->assertInstanceOf(PlainText::class, $sut->get(VehicleTableSearch::ELEMENT_HINT_NAME));
        $this->assertInstanceOf(VehicleVrm::class, $sut->get(VehicleTableSearch::ELEMENT_INPUT_NAME));
        $this->assertFalse($sut->get(VehicleTableSearch::ELEMENT_INPUT_NAME)->getOption('validateVrm'));
        $this->assertInstanceOf(Button::class, $sut->get(VehicleTableSearch::ELEMENT_SUBMIT_NAME));
    }
}
