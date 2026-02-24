<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\DocTemplateSearchView;

/**
 * @covers Dvsa\Olcs\Api\Entity\View\DocTemplateSearchView
 */
class DocTemplateSearchViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var DocTemplateSearchView */
    protected $sut;

    /**  @var array */
    protected $testData;

    public function setUp(): void
    {
        $this->testData = [
            'id' => 'unit_Id',
            'lastModifiedOn' => '2014-12-10',
            'category' => 1,
            'subCategory' => 2,
            'description' => 'foo',
            'filename' => 'someid',
            'document' => 3,
            'categoryName' => 'cat',
            'subCategoryName' => 'subcat',
            'deletedDate' => '2015-11-18 16:37:11'
        ];
        $this->sut = new DocTemplateSearchView();
    }

    public function testSetGetters(): void
    {
        $ref = new \ReflectionObject($this->sut);

        // test all the getters
        foreach ($this->testData as $property => $value) {
            $methodName = ucfirst((string) $property);

            if (!method_exists($this->sut, 'set' . $methodName)) {
                $refProperty = $ref->getProperty($property);
                $refProperty->setValue($this->sut, $value);
            } else {
                $this->sut->{'set' . $methodName}($value);
            }
            static::assertEquals($value, $this->sut->{'get' . $methodName}());
        }
    }

    public function testIsDelete(): void
    {
        static::assertFalse($this->sut->isDeleted());
        $this->sut->setDeletedDate(new \DateTime());
        static::assertTrue($this->sut->isDeleted());
    }
}
