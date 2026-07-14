<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1784032889MoorlPlPreview extends MigrationStep
{
    public const OPERATION_HASH = '582528e63c8a4304126f9d00fd25b186';
    public const PLUGIN_VERSION = '1.7.23';

    public function getCreationTimestamp(): int
    {
        return 1784032889;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_preview (id BINARY(16) NOT NULL, media_id BINARY(16) DEFAULT NULL, moorl_pl_id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id, version_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_preview ADD CONSTRAINT `fk.moorl_pl_preview.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE moorl_pl_preview ADD CONSTRAINT `fk.moorl_pl_preview.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_preview', '')) {
            $sql = "CREATE TABLE moorl_pl_preview (id BINARY(16) NOT NULL, media_id BINARY(16) DEFAULT NULL, moorl_pl_id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id, version_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_preview', 'fk.moorl_pl_preview.media_id')) {
            $sql = "ALTER TABLE moorl_pl_preview ADD CONSTRAINT `fk.moorl_pl_preview.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview');
        }
        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_preview', 'fk.moorl_pl_preview.moorl_pl_id')) {
            $sql = "ALTER TABLE moorl_pl_preview ADD CONSTRAINT `fk.moorl_pl_preview.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_preview');
        }
    }
}
