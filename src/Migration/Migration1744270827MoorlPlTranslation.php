<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744270827MoorlPlTranslation extends MigrationStep
{
    public const OPERATION_HASH = 'c5c0c625c9e58e93c33d130e9c245ae1';
    public const PLUGIN_VERSION = '0.0.1';

    public function getCreationTimestamp(): int
    {
        return 1744270827;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl_translation (moorl_pl_id BINARY(16) NOT NULL, language_id BINARY(16) NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, teaser LONGTEXT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, slot_config JSON DEFAULT NULL, meta_keywords LONGTEXT DEFAULT NULL, meta_title LONGTEXT DEFAULT NULL, meta_description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(moorl_pl_id, language_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE moorl_pl_translation ADD CONSTRAINT `fk.moorl_pl_translation.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_pl_translation ADD CONSTRAINT `fk.moorl_pl_translation.language_id` FOREIGN KEY (language_id) REFERENCES language (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl_translation', '')) {
            $sql = "CREATE TABLE moorl_pl_translation (moorl_pl_id BINARY(16) NOT NULL, language_id BINARY(16) NOT NULL, moorl_pl_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, teaser LONGTEXT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, slot_config JSON DEFAULT NULL, meta_keywords LONGTEXT DEFAULT NULL, meta_title LONGTEXT DEFAULT NULL, meta_description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(moorl_pl_id, language_id, moorl_pl_version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_translation', 'fk.moorl_pl_translation.moorl_pl_id')) {
            $sql = "ALTER TABLE moorl_pl_translation ADD CONSTRAINT `fk.moorl_pl_translation.moorl_pl_id` FOREIGN KEY (moorl_pl_id, moorl_pl_version_id) REFERENCES moorl_pl (id, version_id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl_translation', 'fk.moorl_pl_translation.language_id')) {
            $sql = "ALTER TABLE moorl_pl_translation ADD CONSTRAINT `fk.moorl_pl_translation.language_id` FOREIGN KEY (language_id) REFERENCES language (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl_translation');
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
