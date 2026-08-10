<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1786379656MoorlPlFilterAvailabilityProductStream extends MigrationStep
{
    public const OPERATION_HASH = 'd6ad28a8f838a0fd02a0a2a72659731b';
    public const PLUGIN_VERSION = '1.7.25';

    public function getCreationTimestamp(): int
    {
        return 1786379656;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_filter_availability_ps (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_filter_id, moorl_pl_filter_version_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_filter_availability_ps ADD CONSTRAINT `fk.moorl_pl_filter_availability_ps.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_filter_availability_ps ADD CONSTRAINT `fk.moorl_pl_filter_availability_ps.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_filter_availability_ps', '')) {
            $sql = "CREATE TABLE moorl_pl_filter_availability_ps (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_filter_id, moorl_pl_filter_version_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_availability_ps');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_availability_ps', 'fk.moorl_pl_filter_availability_ps.moorl_pl_filter_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_availability_ps ADD CONSTRAINT `fk.moorl_pl_filter_availability_ps.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_availability_ps');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_availability_ps', 'fk.moorl_pl_filter_availability_ps.product_stream_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_availability_ps ADD CONSTRAINT `fk.moorl_pl_filter_availability_ps.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_availability_ps');
        }
    }
}
