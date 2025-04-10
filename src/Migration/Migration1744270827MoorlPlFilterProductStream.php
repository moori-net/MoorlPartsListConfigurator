<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744270827MoorlPlFilterProductStream extends MigrationStep
{
    public const OPERATION_HASH = 'dae1b380a2a42095256851ac1efe9b0b';
    public const PLUGIN_VERSION = '0.0.1';

    public function getCreationTimestamp(): int
    {
        return 1744270827;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_filter_product_stream (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, PRIMARY KEY(moorl_pl_filter_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id) REFERENCES moorl_pl_filter (id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_filter_product_stream', '')) {
            $sql = "CREATE TABLE moorl_pl_filter_product_stream (moorl_pl_filter_id BINARY(16) NOT NULL, product_stream_id BINARY(16) NOT NULL, PRIMARY KEY(moorl_pl_filter_id, product_stream_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_product_stream', 'fk.moorl_pl_filter_product_stream.moorl_pl_filter_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.moorl_pl_filter_id` FOREIGN KEY (moorl_pl_filter_id) REFERENCES moorl_pl_filter (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_filter_product_stream', 'fk.moorl_pl_filter_product_stream.product_stream_id')) {
            $sql = "ALTER TABLE moorl_pl_filter_product_stream ADD CONSTRAINT `fk.moorl_pl_filter_product_stream.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter_product_stream');
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
