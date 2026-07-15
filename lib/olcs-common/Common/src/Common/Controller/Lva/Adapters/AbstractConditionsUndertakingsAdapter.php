<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\ConditionsUndertakingsAdapterInterface;
use Common\RefData;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\TableBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

abstract class AbstractConditionsUndertakingsAdapter extends AbstractAdapter implements
    ConditionsUndertakingsAdapterInterface
{
    protected $tableName = 'lva-conditions-undertakings';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Attach the relevant scripts to the main page
     */
    public function attachMainScripts(): void
    {
        $this->container->get(ScriptFactory::class)->loadFile('lva-crud');
    }

    /**
     * Check whether we can update the record
     *
     * @param int $id
     * @param int $parentId
     * @return bool
     */
    #[\Override]
    public function canEditRecord($data)
    {
        // prevent PMD errors
        unset($data);
        return true;
    }

    /**
     * Remove the restore button
     */
    public function alterTable(TableBuilder $table): void
    {
        $table->removeAction('restore');
    }

    /**
     * Process the data for saving
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    #[\Override]
    public function processDataForSave($data, $id)
    {
        // prevent PMD errors
        unset($id);
        if ($data['fields']['attachedTo'] == RefData::ATTACHED_TO_LICENCE) {
            $data['fields']['operatingCentre'] = null;
        } else {
            $data['fields']['operatingCentre'] = $data['fields']['attachedTo'];
            $data['fields']['attachedTo'] = RefData::ATTACHED_TO_OPERATING_CENTRE;
        }

        return $data;
    }

    /**
     * Set the attached to options for the form, based on the lva type and id
     *
     * @param array $data
     */
    #[\Override]
    public function alterForm(Form $form, $data): void
    {
        $licNo = 'Unknown';
        if (isset($data['licence']['licNo'])) {
            $licNo = $data['licence']['licNo'];
        }

        if (isset($data['licNo'])) {
            $licNo = $data['licNo'];
        }

        $options = [
            'Licence' => [
                'label' => 'Licence',
                'options' => [
                    RefData::ATTACHED_TO_LICENCE => 'Licence (' . $licNo . ')'
                ]
            ]
        ];

        $operatingCentres = $this->getOperatingCentresForList($data);
        $attachedToOperatingCentres = [];

        /** @var Address $addressFormatter */
        $addressFormatter = $this->container->get(FormatterPluginManager::class)->get(Address::class);

        foreach ($operatingCentres as $oc) {
            $attachedToOperatingCentres[$oc['id']] = $addressFormatter->format($oc['address']);
        }

        if ($attachedToOperatingCentres !== []) {
            $options['OC'] = [
                'label' => 'OC Address',
                'options' => $attachedToOperatingCentres
            ];
        }

        $form->get('fields')->get('attachedTo')->setValueOptions($options);
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get a list of Operating Centres
     *
     * @param array $data
     *
     * @return array
     */
    protected function getOperatingCentresForList($data)
    {
        $ocs = [];
        if (isset($data['licence'])) {
            foreach ($data['licence']['operatingCentres'] as $loc) {
                $ocs[$loc['operatingCentre']['id']] = $loc['operatingCentre'];
            }

            foreach ($data['operatingCentres'] as $aoc) {
                if ($aoc['action'] === 'D') {
                    unset($ocs[$aoc['operatingCentre']['id']]);
                    continue;
                }

                $ocs[$aoc['operatingCentre']['id']] = $aoc['operatingCentre'];
            }
        } else {
            foreach ($data['operatingCentres'] as $loc) {
                $ocs[$loc['operatingCentre']['id']] = $loc['operatingCentre'];
            }
        }

        return $ocs;
    }

    /**
     * Get the command to delete
     *
     * @param array  $ids List of ConditionUndertaking ID to delete
     *
     * @return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\DeleteList
     */
    public function getDeleteCommand($id, $ids)
    {
        // prevent PMD errors
        unset($id);
        return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\DeleteList::create(['ids' => $ids]);
    }

    /**
     * Get the command to update
     *
     * @param array $formData Form data
     * @param int   $id       Licence/Application ID
     *
     * @return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update
     */
    public function getUpdateCommand($formData, $id)
    {
        $data = $this->processDataForSave($formData, null);
        $params = [
            'id' => $data['fields']['id'],
            'version' => $data['fields']['version'],
            'type' => $data['fields']['type'],
            'notes' => $data['fields']['notes'],
            'fulfilled' => $data['fields']['fulfilled'],
            'attachedTo' => $data['fields']['attachedTo'],
            'operatingCentre' => $data['fields']['operatingCentre'],
            'conditionCategory' => $data['fields']['conditionCategory'],
        ];

        return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update::create($params);
    }

    /**
     * Get the command to create
     *
     * @param array  $formData Form data
     * @param string $lva      "licence", "application" or "variation"
     * @param int    $id       Licence or Application ID
     *
     * @return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create
     */
    public function getCreateCommand($formData, $lva, $id)
    {
        $data = $this->processDataForSave($formData, null);
        $params = [
            'type' => $data['fields']['type'],
            'notes' => $data['fields']['notes'],
            'fulfilled' => $data['fields']['fulfilled'],
            'attachedTo' => $data['fields']['attachedTo'],
            'operatingCentre' => $data['fields']['operatingCentre'],
            'conditionCategory' => $data['fields']['conditionCategory'],
        ];

        if ($lva === 'licence') {
            $params['licence'] = $id;
        } else {
            $params['application'] = $id;
        }

        return \Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create::create($params);
    }
}
