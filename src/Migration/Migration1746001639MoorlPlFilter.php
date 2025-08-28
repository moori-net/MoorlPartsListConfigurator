<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746001639MoorlPlFilter extends MigrationStep
{
    public const OPERATION_HASH = '260c15348fbee1349762dddec3fc423a';
    public const PLUGIN_VERSION = '0.0.3';

    public function getCreationTimestamp(): int
    {
        return 1746001639;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_filter (id BINARY(16) NOT NULL, fixed TINYINT(1) DEFAULT 0, logical TINYINT(1) DEFAULT 0, position INT DEFAULT 0, technical_name VARCHAR(255) DEFAULT NULL, moorl_pl_id BINARY(16) NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_filter ADD CONSTRAINT `fk.moorl_pl_filter.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_filter', '')) {
            $sql = "CREATE TABLE moorl_pl_filter (id BINARY(16) NOT NULL, fixed TINYINT(1) DEFAULT 0, logical TINYINT(1) DEFAULT 0, position INT DEFAULT 0, technical_name VARCHAR(255) DEFAULT NULL, moorl_pl_id BINARY(16) NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter', 'fk.moorl_pl_filter.moorl_pl_id')) {
            $sql = "ALTER TABLE moorl_pl_filter ADD CONSTRAINT `fk.moorl_pl_filter.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter');
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
