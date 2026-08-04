<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Document\DocumentList::class)]
final class DocumentListTest extends MockeryTestCase
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

        $this->assertEquals('unit_isExternal', $sut->getIsExternal());
        $this->assertEquals('unit_category', $sut->getCategory());
        $this->assertEquals('unit_documentSubCategory', $sut->getDocumentSubCategory());
        $this->assertEquals('unit_showDocs', $sut->getShowDocs());
        $this->assertEquals('unit_irfoOrganisation', $sut->getIrfoOrganisation());
        $this->assertEquals('unit_transportManager', $sut->getTransportManager());
        $this->assertEquals('unit_busReg', $sut->getBusReg());
        $this->assertEquals('unit_case', $sut->getCase());
        $this->assertEquals('unit_licence', $sut->getLicence());
        $this->assertEquals('unit_application', $sut->getApplication());
        $this->assertEquals('unit_irhpApplication', $sut->getIrhpApplication());
        $this->assertEquals('unit_page', $sut->getPage());
        $this->assertEquals('unit_limit', $sut->getLimit());
        $this->assertEquals('unit_sort', $sut->getSort());
        $this->assertEquals('unit_order', $sut->getOrder());
        $this->assertEquals('foo', $sut->getFormat());
        $this->assertEquals('Y', $sut->getOnlyUnlinked());
    }
}
