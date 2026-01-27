<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class PartsListConfiguratorTranslationEntity extends TranslationEntity
{
    protected string $name = "";
    protected ?string $role = null;
    protected ?string $teaser = null;
    protected ?string $description = null;
    protected ?string $errorMessage = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }
}
