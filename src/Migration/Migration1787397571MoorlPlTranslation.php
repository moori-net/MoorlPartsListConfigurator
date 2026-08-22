<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1787397571MoorlPlTranslation extends MigrationStep
{
    public const OPERATION_HASH = '079c4f336ebb1ce9ef89d91a457b241b';
    public const PLUGIN_VERSION = '1.7.29';

    public function getCreationTimestamp(): int
    {
        return 1787397571;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_pl_translation ADD info_message LONGTEXT DEFAULT NULL;
SQL;

        // Try to execute all queries at once
        try {
            $connection->executeStatement($sql);
            return;
        } catch (\Exception) {
            if (!class_exists(EntityDefinitionQueryHelper::class)) {
                throw new MissingRequirementException('moorl/foundation', '*');
            }
        }

        // Try to execute all queries step by step
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'moorl_pl_translation', 'info_message')) {
            $sql = "ALTER TABLE moorl_pl_translation ADD info_message LONGTEXT DEFAULT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_translation');
        }
    }
}
