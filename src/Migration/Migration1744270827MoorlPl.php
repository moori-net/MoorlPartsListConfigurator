<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744270827MoorlPl extends MigrationStep
{
    public const OPERATION_HASH = '2ddced65d08fb35e7dedd231aad5ae8a';
    public const PLUGIN_VERSION = '0.0.1';

    public function getCreationTimestamp(): int
    {
        return 1744270827;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_pl (id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, cms_page_id BINARY(16) DEFAULT NULL, cms_page_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425, type VARCHAR(255) DEFAULT 'calculator' NOT NULL, calculator VARCHAR(255) DEFAULT 'demo-fence', active TINYINT(1) DEFAULT 0, moorl_pl_media_id BINARY(16) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id, version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE moorl_pl ADD CONSTRAINT `fk.moorl_pl.cms_page_id` FOREIGN KEY (cms_page_id, cms_page_version_id) REFERENCES cms_page (id, version_id) ON UPDATE CASCADE ON DELETE SET NULL;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_pl', '')) {
            $sql = "CREATE TABLE moorl_pl (id BINARY(16) NOT NULL, version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425 NOT NULL, cms_page_id BINARY(16) DEFAULT NULL, cms_page_version_id BINARY(16) DEFAULT 0x0FA91CE3E96A4BC2BE4BD9CE752C3425, type VARCHAR(255) DEFAULT 'calculator' NOT NULL, calculator VARCHAR(255) DEFAULT 'demo-fence', active TINYINT(1) DEFAULT 0, moorl_pl_media_id BINARY(16) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id, version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_pl', 'fk.moorl_pl.cms_page_id')) {
            $sql = "ALTER TABLE moorl_pl ADD CONSTRAINT `fk.moorl_pl.cms_page_id` FOREIGN KEY (cms_page_id, cms_page_version_id) REFERENCES cms_page (id, version_id) ON UPDATE CASCADE ON DELETE SET NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_pl');
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
