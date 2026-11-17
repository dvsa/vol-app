<?php

declare(strict_types=1);

namespace CommonTest\Common\Form\Elements\Types;

use Common\Form\Elements\Types\Address;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
final class AddressTest extends TestCase
{
    public function testUseNamesAsIdsReplacesFixedIds(): void
    {
        $address = new Address('workAddress');
        $address->add($this->text('addressLine1', 'addressLine1'));
        $address->add($this->text('addressLine2', ''));
        $address->add($this->text('postcode', 'postcode'));

        $search = new Fieldset('searchPostcode');
        $search->add($this->text('postcode', 'postcodeInput1'));
        $address->add($search);

        $address->useNamesAsIds();

        $this->assertSame('workAddress[addressLine1]', $address->get('addressLine1')->getAttribute('id'));
        $this->assertSame('workAddress[postcode]', $address->get('postcode')->getAttribute('id'));
        // fields without a fixed id already render with their names as ids
        $this->assertSame('', $address->get('addressLine2')->getAttribute('id'));
        // the postcode search has its own unique ids
        $this->assertSame('postcodeInput1', $search->get('postcode')->getAttribute('id'));
    }

    private function text(string $name, string $id): Text
    {
        $text = new Text($name);
        $text->setAttribute('id', $id);

        return $text;
    }
}
