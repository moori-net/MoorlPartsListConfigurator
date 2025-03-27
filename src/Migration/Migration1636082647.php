<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Migration;

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
CREATE TABLE IF NOT EXISTS `moorl_pl` (
    `id` BINARY(16) NOT NULL,
    `parts_list_configurator_media_id` BINARY(16),
    `cms_page_id` BINARY(16),
    `active` TINYINT NOT NULL,
    `calculator` varchar(255),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_pl_translation` (
    `moorl_pl_id` BINARY(16) NOT NULL,
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
    
    PRIMARY KEY (`moorl_pl_id`, `language_id`),
    
    CONSTRAINT `fk.moorl_pl_translation.language_id`
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_pl_translation.moorl_pl_id`
        FOREIGN KEY (`moorl_pl_id`)
        REFERENCES `moorl_pl` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_pl_filter` (
    `id` BINARY(16) NOT NULL,
    `moorl_pl_id` BINARY(16) NOT NULL,
    `position` INT(11) NOT NULL,
    `technical_name` varchar(255),
    `fixed` TINYINT NOT NULL,
    `logical` TINYINT NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.moorl_pl_filter.moorl_pl_id`
        FOREIGN KEY (`moorl_pl_id`)
        REFERENCES `moorl_pl` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_pl_filter_product_stream` (
    `moorl_pl_filter_id` BINARY(16) NOT NULL,
    `product_stream_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`moorl_pl_filter_id`, `product_stream_id`),
    
    CONSTRAINT `fk.moorl_pl_filter_product_stream.product_stream_id`
        FOREIGN KEY (`product_stream_id`)
        REFERENCES `product_stream` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_pl_filter_product_stream.moorl_pl_filter_id`
        FOREIGN KEY (`moorl_pl_filter_id`)
        REFERENCES `moorl_pl_filter` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_pl_filter_option` (
    `moorl_pl_filter_id` BINARY(16) NOT NULL,
    `property_group_option_id` BINARY(16) NOT NULL,
    
    PRIMARY KEY (`moorl_pl_filter_id`, `property_group_option_id`),
    
    CONSTRAINT `fk.moorl_pl_filter_option.property_group_option_id`
        FOREIGN KEY (`property_group_option_id`)
        REFERENCES `property_group_option` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_pl_filter_option.moorl_pl_filter_id`
        FOREIGN KEY (`moorl_pl_filter_id`)
        REFERENCES `moorl_pl_filter` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_pl_media` (
    `id` binary(16) NOT NULL,
    `moorl_pl_id` binary(16) NOT NULL,
    `position` int(11) NOT NULL DEFAULT '1',
    `media_id` binary(16) NOT NULL,
    `custom_fields` json DEFAULT NULL,
    `created_at` datetime(3) NOT NULL,
    `updated_at` datetime(3) DEFAULT NULL,
  
    PRIMARY KEY (`id`),
    KEY `fk.moorl_pl_media.media_id` (`media_id`),
    KEY `fk.moorl_pl_media.moorl_pl_id` (`moorl_pl_id`),
  
    CONSTRAINT `fk.moorl_pl_media.media_id` 
        FOREIGN KEY (`media_id`) 
        REFERENCES `media` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_pl_media.moorl_pl_id` 
        FOREIGN KEY (`moorl_pl_id`) 
        REFERENCES `moorl_pl` (`id`) 
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
