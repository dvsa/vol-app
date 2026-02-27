<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\DocumentSearchView;

/**
 * @covers Dvsa\Olcs\Api\Entity\View\DocumentSearchView
 */
class DocumentSearchViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var DocumentSearchView */
    protected $sut;

    /**  @var array */
    protected $testData;

    public function setUp(): void
    {
        $this->testData = [
            'id' => 'unit_Id',
            'issuedDate' => '2014-12-10',
            'category' => 1,
            'documentSubCategory' => 2,
            'description' => 'foo',
            'documentStoreIdentifier' => 'someid',
            'document' => 3,
            'categoryName' => 'cat',
            'documentSubCategoryName' => 'subcat',
            'filename' => 'file1',
            'isExternal' => 1,
            'identifier' => 'ident',
            'licenceNo' => 'LIC1',
            'licenceId' => 4,
            'familyName' => 'Smith',
            'caseId' => 5,
            'busRegId' => 6,
            'tmId' => 7,
            'ciId' => 8,
            'irfoOrganisationId' => 10,
            'irhpApplicationId' => 11,
            'applicationId' => 'unit_AppId',
            'deletedDate' => 'unit_deleteDate',
            'agreedDate' => 'unit_agreedDate',
            'targetDate' => 'unit_targetDate',
            'sentDate' => 'unit_sentDate',
            'extension' => 'unit_extension',
        ];
        $this->sut = new DocumentSearchView();
    }

    public function testSetGetters(): void
    {
        $ref = new \ReflectionObject($this->sut);

        // test all teh getters
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
