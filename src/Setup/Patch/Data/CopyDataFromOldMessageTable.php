<?php

declare(strict_types=1);

namespace RunAsRoot\MessageQueueRetry\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * The reason for this class is to handle the migration from the old message table to the new one.
 * This is necessary because errors are being generated during integration testes while using
 * the onCreate="migrateDataFromAnotherTable('run_as_root_message')" in the db_schema.xml
 */
class CopyDataFromOldMessageTable implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
    ) {
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $oldMessageTable = $this->moduleDataSetup->getTable('run_as_root_message');
        $newMessageTable = $this->moduleDataSetup->getTable('run_as_root_queue_error_message');

        if (!$connection->isTableExists($oldMessageTable) || !$connection->isTableExists($newMessageTable)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        $select = $connection->select()->from($oldMessageTable);
        $connection->query($connection->insertFromSelect($select, $newMessageTable));

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
