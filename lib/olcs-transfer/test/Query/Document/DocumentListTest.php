<?php

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Document\DocumentList
 */
class DocumentListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'isExternal' => 'unit_isExternal',
            'category' => 'unit_category',
            'documentSubCategory' => 'unit_documentSubCategory',
            'showDocs' => 'unit_showDocs',
            'irfoOrganisation' => 'unit_irfoOrganisation',
            'transportManager' => 'unit_transportManager',
            'busReg' => 'unit_busReg',
            'case' => 'unit_case',
            'licence' => 'unit_licence',
            'application' => 'unit_application',
            'irhpApplication' => 'unit_irhpApplication',
            'page' => 'unit_page',
            'limit' => 'unit_limit',
            'sort' => 'unit_sort',
            'order' => 'unit_order',
            'format' => 'foo',
            'onlyUnlinked' => 'Y',
        ];

        $sut = DocumentList::create($data);

        static::assertEquals('unit_isExternal', $sut->getIsExternal());
        static::assertEquals('unit_category', $sut->getCategory());
        static::assertEquals('unit_documentSubCategory', $sut->getDocumentSubCategory());
        static::assertEquals('unit_showDocs', $sut->getShowDocs());
        static::assertEquals('unit_irfoOrganisation', $sut->getIrfoOrganisation());
        static::assertEquals('unit_transportManager', $sut->getTransportManager());
        static::assertEquals('unit_busReg', $sut->getBusReg());
        static::assertEquals('unit_case', $sut->getCase());
        static::assertEquals('unit_licence', $sut->getLicence());
        static::assertEquals('unit_application', $sut->getApplication());
        static::assertEquals('unit_irhpApplication', $sut->getIrhpApplication());
        static::assertEquals('unit_page', $sut->getPage());
        static::assertEquals('unit_limit', $sut->getLimit());
        static::assertEquals('unit_sort', $sut->getSort());
        static::assertEquals('unit_order', $sut->getOrder());
        static::assertEquals('foo', $sut->getFormat());
        static::assertEquals('Y', $sut->getOnlyUnlinked());
    }
}
