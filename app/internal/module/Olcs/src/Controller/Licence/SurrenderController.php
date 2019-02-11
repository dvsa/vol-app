<?php

namespace Olcs\Controller\Licence;

use Common\Form\Form;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenCases;
use Olcs\Controller\AbstractInternalController;
use Olcs\Form\Model\Form\Licence\Surrender\Surrender;
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
        /**
         * @var $form Form
         */
        $form = $this->getForm(Surrender::class);


        $this->placeholder()->setPlaceholder('form', $form);

        $this->alterLayout($form);

        return $this->details(
            ByLicence::class,
            new GenericItem(['id' => 'licence']),
            'details',
            'sections/licence/pages/surrender',
            'Surrender details'
        );
    }

    public function alterLayout($form)
    {
        foreach ($this->counts as $key => $value) {
            if ($value === 0) {
                $this->placeholder()->setPlaceholder($key, '');
            } else {
                $form->get('checks')->remove($key);
            }
        }
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
            'openCases',
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
            'busRegistrations',
            'licence-surrender-busreg',
            $this->tableViewTemplate
        );
    }
}
