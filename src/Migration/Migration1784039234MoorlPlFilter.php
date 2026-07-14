<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1784039234MoorlPlFilter extends MigrationStep
{
    public const OPERATION_HASH = 'c6c0209eb84907163cb05d5af1b129b2';
    public const PLUGIN_VERSION = '1.7.24';

    public function getCreationTimestamp(): int
    {
        return 1784039234;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_pl_filter ADD preview TINYINT DEFAULT 0;
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
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'moorl_pl_filter', 'preview')) {
            $sql = "ALTER TABLE moorl_pl_filter ADD preview TINYINT DEFAULT 0;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_filter');
        }
    }
}
