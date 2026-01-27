<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1769527286MoorlPlTranslation extends MigrationStep
{
    public const OPERATION_HASH = '14e3ba643a8e1ff2eda3ed0ec29da430';
    public const PLUGIN_VERSION = '1.7.21';

    public function getCreationTimestamp(): int
    {
        return 1769527286;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_pl_translation ADD error_message LONGTEXT DEFAULT NULL;
SQL;

        // Try to execute all queries at once
        try {
            $connection->executeStatement($sql);
            $this->additionalCustomUpdate($connection);
            return;
        } catch (\Exception) {
            if (!class_exists(EntityDefinitionQueryHelper::class)) {
                throw new MissingRequirementException('moorl/foundation', '1.6.50');
            }
        }

        // Try to execute all queries step by step
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'moorl_pl_translation', 'error_message')) {
            $sql = "ALTER TABLE moorl_pl_translation ADD error_message LONGTEXT DEFAULT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_translation');
        }

        $this->additionalCustomUpdate($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }

    private function additionalCustomUpdate(Connection $connection): void
    {
        // Add custom update if necessary
    }
}
