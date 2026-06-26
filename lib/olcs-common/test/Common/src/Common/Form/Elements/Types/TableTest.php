<?php

/**
 * Test Table Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\Table;
use Common\Service\Table\TableBuilder;

/**
 * Test Table Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test setTable
     */
    public function testSetTable(): void
    {
        $fieldset = 'table';

        $mockTable = $this->createPartialMock(TableBuilder::class, ['setFieldset', 'setDisabled']);

        $mockTable->expects($this->once())
            ->method('setFieldset')
            ->with($fieldset);

        $table = new Table($fieldset);

        $table->setTable($mockTable);
    }

    /**
     * Test render
     */
    public function testRenderDefersToSuppliedTableRenderMethod(): void
    {
        $fieldset = 'table';

        $mockTable = $this->createPartialMock(TableBuilder::class, ['setFieldset', 'setDisabled', 'render']);

        $mockTable->expects($this->once())
            ->method('setFieldset')
            ->with($fieldset);

        $mockTable->expects($this->once())
            ->method('render')
            ->will($this->returnValue('<table></table>'));

        $table = new Table($fieldset);

        $table->setTable($mockTable);

        $this->assertEquals('<table></table>', $table->render());
    }

    public function testSetTablePassesDisabledAttributeToBuilder(): void
    {
        $fieldset = 'table';

        $mockTable = $this->createPartialMock(TableBuilder::class, ['setFieldset', 'setDisabled', 'render']);

        $mockTable->expects($this->once())
            ->method('setDisabled')
            ->with(true);

        $table = new Table($fieldset);

        $table->setAttributes(['disabled' => true]);

        $table->setTable($mockTable);
    }
}
