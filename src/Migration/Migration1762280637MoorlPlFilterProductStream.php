<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1762280637MoorlPlFilterProductStream extends MigrationStep
{
    public const OPERATION_HASH = '8a5ee223fb623d4d48a686eba3c6a081';
    public const PLUGIN_VERSION = '1.7.14';

    public function getCreationTimestamp(): int
    {
        return 1762280637;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_filter_product_stream (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_filter_id, moorl_pl_filter_version_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;
SQL;

        // Try to execute all queries at once
        try {
            $connection->executeStatement($sql);
            return;
        } catch (\Exception) {
            if (!class_exists(EntityDefinitionQueryHelper::class)) {
                throw new MissingRequirementException('moorl/foundation', '1.6.50');
            }
        }

        // Try to execute all queries step by step
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_filter_product_stream', '')) {
            $sql = "CREATE TABLE moorl_pl_filter_product_stream (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, moorl_pl_filter_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, PRIMARY KEY (moorl_pl_filter_id, moorl_pl_filter_version_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_product_stream', 'fk.moorl_pl_filter_product_stream.moorl_pl_filter_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id, moorl_pl_filter_version_id) REFERENCES moorl_pl_filter (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_product_stream', 'fk.moorl_pl_filter_product_stream.product_stream_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
        }
    }
}
