<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1761674183MoorlPlFilterOption extends MigrationStep
{
    public const OPERATION_HASH = 'f2c6c6b79deaf3bc7aa0c1dbe0e36efd';
    public const PLUGIN_VERSION = '1.7.11';

    public function getCreationTimestamp(): int
    {
        return 1761674183;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_pl_filter_option DROP FOREIGN KEY `fk.moorl_pl_filter_option.moorl_pl_filter_id`;
ALTER TABLE moorl_pl_filter_option ADD moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL;
ALTER TABLE moorl_pl_filter_option ADD CONSTRAINT `fk.moorl_pl_filter_option.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_option', 'fk`.`moorl_pl_filter_option`.`moorl_pl_filter_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_option DROP FOREIGN KEY `fk.moorl_pl_filter_option.moorl_pl_filter_id`;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_option');
        }

        if (!EntityDefinitionQueryHelper::columnExists($connection, 'moorl_pl_filter_option', 'moorl_pl_filter_version_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_option ADD moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_option');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_option', 'fk.moorl_pl_filter_option.moorl_pl_filter_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_option ADD CONSTRAINT `fk.moorl_pl_filter_option.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_option');
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
