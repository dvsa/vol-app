<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Custom\VehicleVrm;
use Common\Form\Elements\Types\PlainText;
use Common\Form\Elements\Types\DvlaVrmSearch;
use PHPUnit\Framework\TestCase;
use Laminas\Form\Element\Button;

class DvlaVrmSearchTest extends TestCase
{
    public function testVrmSearchCreate(): void
    {
        $sut = new DvlaVrmSearch();

        $this->assertInstanceOf(PlainText::class, $sut->get(DvlaVrmSearch::ELEMENT_HINT_NAME));
        $this->assertInstanceOf(VehicleVrm::class, $sut->get(DvlaVrmSearch::ELEMENT_INPUT_NAME));
        $this->assertInstanceOf(Button::class, $sut->get(DvlaVrmSearch::ELEMENT_SUBMIT_NAME));
    }
}
