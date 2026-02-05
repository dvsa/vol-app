<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewServiceServices;
use Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section\Stub\AbstractReviewServiceStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * @covers Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewService
 */
class AbstractReviewServiceTest extends MockeryTestCase
{
    /** @var AbstractReviewServiceStub */
    private $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new AbstractReviewServiceStub($abstractReviewServiceServices);
    }

    public function testFormatPersonFullName(): void
    {
        $person = m::mock(Person::class);
        $person->shouldReceive('getTitle->getDescription')
            ->withNoArgs()
            ->andReturn('Mr');
        $person->shouldReceive('getForename')
            ->withNoArgs()
            ->andReturn('John');
        $person->shouldReceive('getFamilyName')
            ->withNoArgs()
            ->andReturn('Smith');

        $this->assertEquals(
            'Mr John Smith',
            $this->sut->formatPersonFullName($person)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormatDate')]
    public function testFormatDate(mixed $expected, mixed $date): void
    {
        $this->assertEquals($expected, $this->sut->formatDate($date));
    }

    public static function dpFormatDate(): array
    {
        return [
            ['15 Aug 2005', '2005-08-15T15:52:01+00:00'],
            ['01 Aug 2017', '2017-08-01T15:52:00+05:00'],
        ];
    }

    public function testFormatFullAddress(): void
    {
        $address = [
            'addressLine1' => 'DVSA',
            'addressLine2' => 'Hillcrest House',
            'addressLine3' => '386 Harehills Lane',
            'addressLine4' => 'Harehills',
            'town' => 'Leeds',
            'postcode' => 'LS9 6NF',
            'countryCode' => [
                'id' => 'UK'
            ]
        ];

        $expected = 'DVSA, Hillcrest House, 386 Harehills Lane, Harehills, Leeds, LS9 6NF, UK';

        $this->assertEquals(
            $expected,
            $this->sut->formatFullAddress($address)
        );
    }

    public function testFormatShortAddress(): void
    {
        $address = [
            'addressLine1' => 'DVSA',
            'town' => 'Leeds'
        ];

        $expected = 'DVSA, Leeds';

        $this->assertEquals(
            $expected,
            $this->sut->formatShortAddress($address)
        );
    }

    public function testFindFiles(): void
    {
        $document1 = m::mock(Document::class)->makePartial();
        $document1->setCategory(Category::CATEGORY_LICENSING);
        $document1->setSubCategory(Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL);

        $document2 = m::mock(Document::class)->makePartial();
        $document2->setCategory(Category::CATEGORY_LICENSING);
        $document2->setSubCategory(Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE);

        $document3 = m::mock(Document::class)->makePartial();
        $document3->setCategory(Category::CATEGORY_COMPLIANCE);
        $document3->setSubCategory(Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE);

        $document4 = m::mock(Document::class)->makePartial();
        $document4->setCategory(Category::CATEGORY_LICENSING);
        $document4->setSubCategory(Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL);

        $document5 = m::mock(Document::class)->makePartial();
        $document5->setCategory(Category::CATEGORY_PERMITS);
        $document5->setSubCategory(Category::BUS_SUB_CATEGORY_OTHER_DOCUMENTS);

        $files = new ArrayCollection(
            [$document1, $document2, $document3, $document4, $document5]
        );

        $foundFiles = $this->sut->findFiles(
            $files,
            Category::CATEGORY_LICENSING,
            Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL
        );

        $this->assertInstanceOf(ArrayCollection::class, $foundFiles);

        $values = $foundFiles->getValues();

        $this->assertCount(2, $values);
        $this->assertSame($document1, $values[0]);
        $this->assertSame($document4, $values[1]);
    }

    public function testTranslate(): void
    {
        $translationKey = 'translation.key';
        $translated = 'translated';

        $this->mockTranslator->shouldReceive('translate')
            ->with($translationKey)
            ->andReturn($translated);

        $this->assertEquals(
            $translated,
            $this->sut->translate($translationKey)
        );
    }

    public function testTranslateReplace(): void
    {
        $translationKey = 'translation.key';
        $translated = 'the %s jumped over the %s hare';
        $arguments = ['fox', 'brown'];

        $expected = 'the fox jumped over the brown hare';

        $this->mockTranslator->shouldReceive('translate')
            ->with($translationKey)
            ->andReturn($translated);

        $this->assertEquals(
            $expected,
            $this->sut->translateReplace($translationKey, $arguments)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormatYesNo')]
    public function testFormatYesNo(mixed $value, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->sut->formatYesNo($value)
        );
    }

    public static function dpFormatYesNo(): array
    {
        return [
            ['B','No'],
            ['Z', 'No'],
            ['N', 'No'],
            ['Y', 'Yes'],
        ];
    }
}
