<?php

namespace Olcs\Controller\Licence;

use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenCases;
use Olcs\Controller\AbstractInternalController;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

class SurrenderController extends AbstractInternalController
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_surrender';
    protected $cases;
    protected $counts;


    /**
     * index Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {

        $this->setupCasesTable();

        $this->setupBusRegTable();

        if ($this->counts['opencases'] === 0) {
            $this->placeholder()->setPlaceholder('casesTable', '');
        }

        if ($this->counts['openbusregs'] === 0) {
            $this->placeholder()->setPlaceholder('busRegTable', '');
        }

        return $this->details(
            ByLicence::class,
            new GenericItem(['id' => 'licence']),
            'details',
            'sections/licence/pages/surrender',
            'Surrender details'
        );
    }

    /**
     * Setup Oppositions table
     *
     * @return void
     */
    private function setupCasesTable()
    {
        $this->index(
            OpenCases::class,
            new GenericList(['id' => 'licence'], 'id'),
            'casesTable',
            'open-cases',
            $this->tableViewTemplate
        );
    }

    public function alterTable($table, $data)
    {
        $tableName = $table->getAttributes()['name'];
        $this->counts[$tableName] = $data['count'];
        return $table;
    }

    /**
     * Setup Environment Complaints table
     *
     * @return void
     */
    private function setupBusRegTable()
    {
        $this->index(
            OpenBusReg::class,
            new GenericList([
                'id' => 'licence',
            ], 'licId'),
            'busRegTable',
            'licence-surrender-busreg',
            $this->tableViewTemplate
        );
    }
}