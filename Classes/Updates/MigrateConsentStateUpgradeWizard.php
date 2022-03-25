<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Updates;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\DBAL\Statement;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Type\ConsentStateType;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * MigrateConsentStateUpgradeWizard
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class MigrateConsentStateUpgradeWizard implements UpgradeWizardInterface, ChattyInterface
{
    public const IDENTIFIER = 'formConsentMigrateConsentState';

    private const LEGACY_COLUMNS = [
        'approved',
        'approval_date',
    ];

    private Connection $connection;
    private OutputInterface $output;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getTitle(): string
    {
        return 'Migrate legacy form consent fields';
    }

    public function getDescription(): string
    {
        return 'Migrates legacy fields of form consent database records to the new consent state fields.';
    }

    public function executeUpdate(): bool
    {
        $result = true;
        $legacyColumns = $this->getOutdatedColumns();

        foreach ($this->selectAffectedRows($legacyColumns)->fetchAll(FetchMode::ASSOCIATIVE) as $record) {
            if ($this->migrateRecord($record)) {
                $this->output->writeln('<info>Done</info>');
            } else {
                $this->output->writeln('<error>Failed</error>');
                $result = false;
            }
        }

        return $result;
    }

    public function updateNecessary(): bool
    {
        return $this->countAffectedRows($this->getOutdatedColumns()) > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function migrateRecord(array $record): bool
    {
        $uid = $record['uid'];

        $this->output->writeln(
            sprintf('Starting migration of record <comment>"%s.%d"</comment>...', Consent::TABLE_NAME, $uid)
        );

        // Migrate consent state
        if ($this->migrateState($record)) {
            $this->output->writeln(
                sprintf(
                    '  - Migrate <comment>"approved:%s"</comment> to <comment>"state:%s"</comment>.',
                    $record['approved'],
                    $record['state']
                )
            );
        }

        // Migrate update date
        if ($dateSource = $this->migrateUpdateDate($record)) {
            $this->output->writeln(
                sprintf(
                    '  - Migrate <comment>"%s:%s"</comment> to <comment>"update_date:%s"</comment>.',
                    $dateSource,
                    $record[$dateSource],
                    $record['update_date']
                )
            );
        }

        return 1 === $this->connection->update(Consent::TABLE_NAME, $record, ['uid' => $uid]);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function migrateState(array &$record): bool
    {
        // Early return if state was already migrated
        if (ConsentStateType::NEW !== (int)$record['state']) {
            return false;
        }

        // Early return if approval state is no longer available
        if (!isset($record['approved'])) {
            return false;
        }

        // Approved consent
        if ($record['approved']) {
            $record['state'] = ConsentStateType::APPROVED;

            return true;
        }

        // Dismissed consent
        if ($record['deleted'] && null === $record['data']) {
            $record['state'] = ConsentStateType::DISMISSED;

            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function migrateUpdateDate(array &$record): ?string
    {
        // Early return if update date was already migrated
        if (\is_int($record['update_date']) && $record['update_date'] > 0) {
            return null;
        }

        // Early return if approval date is no longer available
        if (!isset($record['approval_date'])) {
            return null;
        }

        // Approved consent
        if (\is_int($record['approval_date']) && $record['approval_date'] > 0 && !$record['deleted']) {
            $record['update_date'] = $record['approval_date'];

            return 'approval_date';
        }

        // Dismissed consent
        if ($record['deleted']) {
            $record['update_date'] = (int)$record['tstamp'];

            return 'tstamp';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function getOutdatedColumns(): array
    {
        $schemaManager = $this->connection->getSchemaManager();
        $legacyColumns = [];

        foreach ($schemaManager->listTableColumns(Consent::TABLE_NAME) as $column) {
            $columnName = $column->getName();

            if (\in_array($columnName, self::LEGACY_COLUMNS, true)) {
                $legacyColumns[] = $columnName;
            }
        }

        return $legacyColumns;
    }

    /**
     * @param list<string> $legacyColumns
     * @return Result<string, mixed>|Statement
     */
    private function selectAffectedRows(array $legacyColumns)
    {
        $result = $this->getPreparedQueryBuilder($legacyColumns)
            ->select('*')
            ->execute();
        \assert($result instanceof Result || $result instanceof Statement);

        return $result;
    }

    /**
     * @param list<string> $legacyColumns
     */
    private function countAffectedRows(array $legacyColumns): int
    {
        $result = $this->getPreparedQueryBuilder($legacyColumns)
            ->count('uid')
            ->execute();
        \assert($result instanceof Result || $result instanceof Statement);

        return (int)$result->fetch(FetchMode::COLUMN);
    }

    /**
     * @param list<string> $legacyColumns
     */
    private function getPreparedQueryBuilder(array $legacyColumns): QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder()->from(Consent::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $expr = $queryBuilder->expr();

        foreach ($legacyColumns as $legacyColumn) {
            switch ($legacyColumn) {
                case 'approved':
                    $queryBuilder->orWhere(
                        $expr->orX(
                            /* @phpstan-ignore-next-line */
                            $expr->andX(
                                $expr->eq('approved', $queryBuilder->createNamedParameter(true, Connection::PARAM_BOOL)),
                                $expr->eq('state', $queryBuilder->createNamedParameter(ConsentStateType::NEW, Connection::PARAM_INT))
                            ),
                            /* @phpstan-ignore-next-line */
                            $expr->andX(
                                $expr->eq('approved', $queryBuilder->createNamedParameter(false, Connection::PARAM_BOOL)),
                                $expr->eq('state', $queryBuilder->createNamedParameter(ConsentStateType::NEW, Connection::PARAM_INT)),
                                $expr->eq('deleted', $queryBuilder->createNamedParameter(true, Connection::PARAM_BOOL)),
                                $expr->isNull('data')
                            )
                        )
                    );
                    break;

                case 'approval_date':
                    $queryBuilder->orWhere(
                        $expr->andX(
                            $expr->neq('approval_date', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                            $expr->isNull('update_date')
                        )
                    );
                    break;

                default:
                    $queryBuilder->andWhere('0=1');
                    break;
            }
        }

        return $queryBuilder;
    }
}
