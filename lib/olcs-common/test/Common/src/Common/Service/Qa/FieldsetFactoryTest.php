<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa;

use Common\Form\Elements\Types\RadioVertical;
use Common\Service\Qa\FieldsetFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * FieldsetFactoryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class FieldsetFactoryTest extends MockeryTestCase
{
    private $fieldsetFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->fieldsetFactory = new FieldsetFactory();
    }

    public function testCreateForEcmtShortTermRestrictedCountries(): void
    {
        $name = 'fieldset45';
        $fieldset = $this->fieldsetFactory->create($name, 'ecmt_st_restricted_countries');
        $this->assertInstanceOf(RadioVertical::class, $fieldset);
        $this->assertSame($name, $fieldset->getName());
    }

    public function testCreateForOther(): void
    {
        $name = 'fieldset62';
        $fieldset = $this->fieldsetFactory->create($name, 'radio');
        $this->assertInstanceOf(Fieldset::class, $fieldset);
        $this->assertSame($name, $fieldset->getName());
    }
}
