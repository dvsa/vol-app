<?php

declare(strict_types=1);

namespace CommonTest\Common\Form\Elements\Types;

use Common\Form\Elements\Types\Address;
use Common\Form\Elements\Types\PostcodeSearch;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
final class AddressTest extends TestCase
{
    public function testPreparingAddressesConfiguresTheirPostcodeSearches(): void
    {
        $form = new Form('test');
        foreach ([Address::TYPE_HOME, Address::TYPE_WORK] as $name) {
            $address = new Address($name, ['label' => ucfirst($name) . ' address', 'address_type' => $name]);
            $address->add(new PostcodeSearch('searchPostcode'));
            $form->add($address);
        }

        $form->prepare();

        foreach ([Address::TYPE_HOME, Address::TYPE_WORK] as $name) {
            $search = $form->get($name)->get('searchPostcode');
            self::assertSame(ucfirst($name) . ' address postcode', $search->get('postcode')->getLabel());
            self::assertSame('Find ' . $name . ' address', $search->get('search')->getLabel());
            self::assertSame(
                ['postcode.error.not-available', 'Enter ' . $name . ' address manually'],
                $search->get('manual-link')->getTokens()
            );
        }
        self::assertNotSame(
            $form->get('home')->get('searchPostcode')->get('postcode')->getAttribute('id'),
            $form->get('work')->get('searchPostcode')->get('postcode')->getAttribute('id')
        );
    }

    public function testUnsupportedAddressTypeIsRejected(): void
    {
        $form = new Form('test');
        $address = new Address('address', ['address_type' => 'other']);
        $address->add(new PostcodeSearch('searchPostcode'));
        $form->add($address);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported address type: other');
        $form->prepare();
    }

    public function testUnlabelledAddressGetsGenericLegendAndSearchLabels(): void
    {
        $form = new Form('test');
        $address = new Address('address');
        $address->add(new PostcodeSearch('searchPostcode'));
        $form->add($address);
        $form->prepare();

        self::assertSame('Address', $address->getLabel());
        self::assertSame('Postcode search', $address->get('searchPostcode')->get('postcode')->getLabel());
        self::assertSame('Find address', $address->get('searchPostcode')->get('search')->getLabel());
        self::assertSame(
            ['postcode.error.not-available', 'postcode.address.manual_entry'],
            $address->get('searchPostcode')->get('manual-link')->getTokens()
        );
    }

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
