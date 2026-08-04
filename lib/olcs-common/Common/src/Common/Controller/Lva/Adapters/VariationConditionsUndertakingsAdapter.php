<?php

namespace Common\Controller\Lva\Adapters;

use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Psr\Container\ContainerInterface;

class VariationConditionsUndertakingsAdapter extends AbstractConditionsUndertakingsAdapter
{
    protected $tableName = 'lva-variation-conditions-undertakings';

    public const ACTION_ADDED = 'A';
     // Record added to the application
    public const ACTION_EXISTING = 'E';
     // Unchanged record against the licence
    public const ACTION_CURRENT = 'C';
     // Current version of record updated on the application
    public const ACTION_UPDATED = 'U';
     // Record updated on the application
    public const ACTION_DELETED = 'D';
     // Record deleted on the application
    public const ACTION_REMOVED = 'R'; // The corresponding licence record, to the delta delete record

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Attach the relevant scripts to the main page
     */
    #[\Override]
    public function attachMainScripts(): void
    {
        $this->container->get(ScriptFactory::class)->loadFile('lva-crud-delta');
    }

    /**
     * Check whether we can update the record
     *
     * @param int $id
     * @return bool
     */
    #[\Override]
    public function canEditRecord($data)
    {
        if (!isset($data['action'])) {
            return true;
        }

        return in_array($data['action'], ['', self::ACTION_ADDED, self::ACTION_EXISTING, self::ACTION_UPDATED]);
    }

    /**
     * Remove the restore button
     */
    #[\Override]
    public function alterTable(TableBuilder $table): void
    {
        // prevent PMD error
        unset($table);
    }

    /**
     * Get the command to delete
     *
     * @param array  $ids List of ConditionUndertaking ID to delete
     */
    #[\Override]
    public function getDeleteCommand($id, $ids): \Dvsa\Olcs\Transfer\Command\Variation\DeleteListConditionUndertaking
    {
        return \Dvsa\Olcs\Transfer\Command\Variation\DeleteListConditionUndertaking::create(
            ['id' => $id, 'ids' => $ids]
        );
    }

    /**
     * Get the command to update
     *
     * @param array $formData Form data
     * @param int   $id Application ID
     */
    #[\Override]
    public function getUpdateCommand($formData, $id): \Dvsa\Olcs\Transfer\Command\Variation\UpdateConditionUndertaking
    {
        $data = $this->processDataForSave($formData, null);
        $params = [
            'id' => $id,
            'conditionUndertaking' => $data['fields']['id'],
            'version' => $data['fields']['version'],
            'type' => $data['fields']['type'],
            'notes' => $data['fields']['notes'],
            'fulfilled' => $data['fields']['fulfilled'],
            'attachedTo' => $data['fields']['attachedTo'],
            'operatingCentre' => $data['fields']['operatingCentre'],
            'conditionCategory' => $data['fields']['conditionCategory'],
        ];

        return \Dvsa\Olcs\Transfer\Command\Variation\UpdateConditionUndertaking::create($params);
    }
}
