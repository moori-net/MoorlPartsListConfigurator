<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1636082647 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1_636_082_647;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16),
    `cms_page_id` BINARY(16),
    `product_line_property_id` BINARY(16) NOT NULL,
    `active` TINYINT,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc_translation` (
    `moorl_fc_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` varchar(255),
    `description` longtext,
    `teaser` longtext,
    `keywords` longtext,
    `meta_title` longtext,
    `meta_description` longtext,
    `slot_config` json DEFAULT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`moorl_fc_id`, `language_id`),
    
    CONSTRAINT `fk.moorl_fc_translation.language_id`
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_fc_translation.moorl_fc_id`
        FOREIGN KEY (`moorl_fc_id`)
        REFERENCES `moorl_fc` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc_option` (
    `moorl_fc_id` BINARY(16) NOT NULL,
    `property_group_option_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`moorl_fc_id`, `property_group_option_id`),
    
    CONSTRAINT `fk.moorl_fc_option.property_group_option_id`
        FOREIGN KEY (`property_group_option_id`)
        REFERENCES `property_group_option` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_fc_option.moorl_fc_id`
        FOREIGN KEY (`moorl_fc_id`)
        REFERENCES `moorl_fc` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc_post_option` (
    `moorl_fc_id` BINARY(16) NOT NULL,
    `property_group_option_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`moorl_fc_id`, `property_group_option_id`),
    
    CONSTRAINT `fk.moorl_fc_post_option.property_group_option_id`
        FOREIGN KEY (`property_group_option_id`)
        REFERENCES `property_group_option` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_fc_post_option.moorl_fc_id`
        FOREIGN KEY (`moorl_fc_id`)
        REFERENCES `moorl_fc` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc_product` (
    `id` binary(16) NOT NULL,
    `moorl_fc_id` binary(16) NOT NULL,
    `product_id` binary(16) NOT NULL,
    `product_version_id` binary(16) NOT NULL,
    `priority` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` datetime(3) DEFAULT NULL,
    
    PRIMARY KEY (`id`),
    
    UNIQUE KEY `uniq.moorl_fc_product.id` (`product_id`, `product_version_id`, `moorl_fc_id`),
    
    KEY `fk.moorl_fc_product.product_id` (`product_id`),
    
    CONSTRAINT `fk.moorl_fc_product.product_id` 
        FOREIGN KEY (`product_id`) 
        REFERENCES `product` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_fc_product.moorl_fc_id` 
        FOREIGN KEY (`moorl_fc_id`) 
        REFERENCES `moorl_fc` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_fc_media` (
    `id` binary(16) NOT NULL,
    `moorl_fc_id` binary(16) NOT NULL,
    `position` int(11) NOT NULL DEFAULT '1',
    `media_id` binary(16) NOT NULL,
    `custom_fields` json DEFAULT NULL,
    `created_at` datetime(3) NOT NULL,
    `updated_at` datetime(3) DEFAULT NULL,
  
    PRIMARY KEY (`id`),
    KEY `fk.moorl_fc_media.media_id` (`media_id`),
    KEY `fk.moorl_fc_media.moorl_fc_id` (`moorl_fc_id`),
  
    CONSTRAINT `fk.moorl_fc_media.media_id` 
        FOREIGN KEY (`media_id`) 
        REFERENCES `media` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_fc_media.moorl_fc_id` 
        FOREIGN KEY (`moorl_fc_id`) 
        REFERENCES `moorl_fc` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
