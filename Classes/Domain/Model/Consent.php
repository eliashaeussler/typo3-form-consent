<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Domain\Model;

use EliasHaeussler\Typo3FormConsent\Type;
use TYPO3\CMS\Extbase;

/**
 * Consent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @final
 */
class Consent extends Extbase\DomainObject\AbstractEntity
{
    final public const TABLE_NAME = 'tx_formconsent_domain_model_consent';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var Type\JsonType<string, mixed>|null
     */
    protected $data;

    /**
     * @var string
     */
    protected $formPersistenceIdentifier = '';

    /**
     * @var Type\JsonType<string, array<string, array<string, mixed>>>|null
     */
    protected $originalRequestParameters;

    /**
     * @var int
     */
    protected $originalContentElementUid = 0;

    /**
     * @var Type\ConsentStateType|null
     */
    protected $state;

    /**
     * @var \DateTime|null
     */
    protected $updateDate;

    /**
     * @var \DateTime|null
     */
    protected $validUntil;

    /**
     * @var string
     */
    protected $validationHash = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Type\JsonType<string, mixed>|null
     */
    public function getData(): ?Type\JsonType
    {
        return $this->data;
    }

    /**
     * @param Type\JsonType<string, mixed>|null $data
     */
    public function setData(?Type\JsonType $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getFormPersistenceIdentifier(): string
    {
        return $this->formPersistenceIdentifier;
    }

    public function setFormPersistenceIdentifier(string $formPersistenceIdentifier): self
    {
        $this->formPersistenceIdentifier = $formPersistenceIdentifier;
        return $this;
    }

    /**
     * @return Type\JsonType<string, array<string, array<string, mixed>>>|null
     */
    public function getOriginalRequestParameters(): ?Type\JsonType
    {
        return $this->originalRequestParameters;
    }

    /**
     * @param Type\JsonType<string, array<string, array<string, mixed>>>|null $originalRequestParameters
     */
    public function setOriginalRequestParameters(?Type\JsonType $originalRequestParameters): self
    {
        $this->originalRequestParameters = $originalRequestParameters;
        return $this;
    }

    public function getOriginalContentElementUid(): int
    {
        return $this->originalContentElementUid;
    }

    public function setOriginalContentElementUid(int $originalContentElementUid): self
    {
        $this->originalContentElementUid = $originalContentElementUid;
        return $this;
    }

    public function getState(): ?Type\ConsentStateType
    {
        return $this->state;
    }

    public function isApproved(): bool
    {
        return $this->state !== null && $this->state->isApproved();
    }

    public function setApproved(): self
    {
        $this->setState(Type\ConsentStateType::createApproved());
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function isDismissed(): bool
    {
        return $this->state !== null && $this->state->isDismissed();
    }

    public function setDismissed(): self
    {
        $this->setState(Type\ConsentStateType::createDismissed());
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function setState(?Type\ConsentStateType $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->updateDate = $updateDate;
        return $this;
    }

    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTime $validUntil): self
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function getValidationHash(): string
    {
        return $this->validationHash;
    }

    public function setValidationHash(string $validationHash): self
    {
        $this->validationHash = $validationHash;
        return $this;
    }
}
