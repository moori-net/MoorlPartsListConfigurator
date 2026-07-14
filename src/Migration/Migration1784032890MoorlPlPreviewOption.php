<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1784032890MoorlPlPreviewOption extends MigrationStep
{
    public const OPERATION_HASH = '5337b85edc16e67c1ecde4853640a03d';
    public const PLUGIN_VERSION = '1.7.23';

    public function getCreationTimestamp(): int
    {
        return 1784032890;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_preview_option (moorl_pl_preview_id BINARY(16) NOT NULL, property_group_option_id BINARY(16) NOT NULL, moorl_pl_preview_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_preview_id, moorl_pl_preview_version_id, property_group_option_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_preview_option ADD CONSTRAINT `fk.moorl_pl_preview_option.moorl_pl_preview_id` FOREIGN KEY (moorl_pl_preview_id, moorl_pl_preview_version_id) REFERENCES moorl_pl_preview (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_preview_option ADD CONSTRAINT `fk.moorl_pl_preview_option.property_group_option_id` FOREIGN KEY (property_group_option_id) REFERENCES property_group_option (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_preview_option', '')) {
            $sql = "CREATE TABLE moorl_pl_preview_option (moorl_pl_preview_id BINARY(16) NOT NULL, property_group_option_id BINARY(16) NOT NULL, moorl_pl_preview_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_preview_id, moorl_pl_preview_version_id, property_group_option_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview_option');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_preview_option', 'fk.moorl_pl_preview_option.moorl_pl_preview_id')) {
            $sql = "ALTER TABLE moorl_pl_preview_option ADD CONSTRAINT `fk.moorl_pl_preview_option.moorl_pl_preview_id` FOREIGN KEY (moorl_pl_preview_id, moorl_pl_preview_version_id) REFERENCES moorl_pl_preview (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview_option');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_preview_option', 'fk.moorl_pl_preview_option.property_group_option_id')) {
            $sql = "ALTER TABLE moorl_pl_preview_option ADD CONSTRAINT `fk.moorl_pl_preview_option.property_group_option_id` FOREIGN KEY (property_group_option_id) REFERENCES property_group_option (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview_option');
        }
    }
}
