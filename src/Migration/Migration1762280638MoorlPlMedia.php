<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1762280638MoorlPlMedia extends MigrationStep
{
    public const OPERATION_HASH = '96f1aabda81fbbce0793811bfb923c64';
    public const PLUGIN_VERSION = '1.7.14';

    public function getCreationTimestamp(): int
    {
        return 1762280638;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_media (id BINARY(16) NOT NULL, media_id BINARY(16) NOT NULL, moorl_pl_id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, position INT DEFAULT NULL, custom_fields JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id, version_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_pl_media ADD CONSTRAINT `fk.moorl_pl_media.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_media ADD CONSTRAINT `fk.moorl_pl_media.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_media', '')) {
            $sql = "CREATE TABLE moorl_pl_media (id BINARY(16) NOT NULL, media_id BINARY(16) NOT NULL, moorl_pl_id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, position INT DEFAULT NULL, custom_fields JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id, version_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_media', 'fk.moorl_pl_media.moorl_pl_id')) {
            $sql = "ALTER TABLE moorl_pl_media ADD CONSTRAINT `fk.moorl_pl_media.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_media', 'fk.moorl_pl_media.media_id')) {
            $sql = "ALTER TABLE moorl_pl_media ADD CONSTRAINT `fk.moorl_pl_media.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_media');
        }
    }
}
