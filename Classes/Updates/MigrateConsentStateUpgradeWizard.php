<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Updates;

use Doctrine\DBAL;
use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Enums;
use Symfony\Component\Console;
use TYPO3\CMS\Core;
use TYPO3\CMS\Install;

/**
 * MigrateConsentStateUpgradeWizard
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class MigrateConsentStateUpgradeWizard implements Install\Updates\UpgradeWizardInterface, Install\Updates\ChattyInterface
{
    public const IDENTIFIER = 'formConsentMigrateConsentState';

    private const LEGACY_COLUMNS = [
        'approved',
        'approval_date',
    ];
    private Console\Output\OutputInterface $output;

    public function __construct(
        private readonly Core\Database\Connection $connection,
    ) {
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

        foreach ($this->selectAffectedRows($legacyColumns)->fetchAllAssociative() as $record) {
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
            Install\Updates\DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function setOutput(Console\Output\OutputInterface $output): void
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
            sprintf('Starting migration of record <comment>"%s.%d"</comment>...', Domain\Model\Consent::TABLE_NAME, $uid)
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
        if (\is_string($dateSource = $this->migrateUpdateDate($record))) {
            $this->output->writeln(
                sprintf(
                    '  - Migrate <comment>"%s:%s"</comment> to <comment>"update_date:%s"</comment>.',
                    $dateSource,
                    $record[$dateSource],
                    $record['update_date']
                )
            );
        }

        return $this->connection->update(Domain\Model\Consent::TABLE_NAME, $record, ['uid' => $uid]) === 1;
    }

    /**
     * @param array{
     *     approved?: int,
     *     data: string|null,
     *     deleted: int,
     *     state: int|numeric-string,
     * } $record
     */
    private function migrateState(array &$record): bool
    {
        // Early return if state was already migrated
        if ((int)$record['state'] !== Enums\ConsentState::New->value) {
            return false;
        }

        // Early return if approval state is no longer available
        if (!isset($record['approved'])) {
            return false;
        }

        // Approved consent
        if ((bool)$record['approved'] === true) {
            $record['state'] = Enums\ConsentState::Approved->value;

            return true;
        }

        // Dismissed consent
        if ((bool)$record['deleted'] === true && $record['data'] === null) {
            $record['state'] = Enums\ConsentState::Dismissed->value;

            return true;
        }

        return false;
    }

    /**
     * @param array{
     *     approval_date?: int,
     *     deleted: int,
     *     tstamp: int,
     *     update_date: int|null,
     * } $record
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
        if ($record['approval_date'] > 0 && (bool)$record['deleted'] === false) {
            $record['update_date'] = $record['approval_date'];

            return 'approval_date';
        }

        // Dismissed consent
        if ((bool)$record['deleted'] === true) {
            $record['update_date'] = $record['tstamp'];

            return 'tstamp';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function getOutdatedColumns(): array
    {
        $legacyColumns = [];

        if (method_exists($this->connection, 'createSchemaManager')) {
            $schemaManager = $this->connection->createSchemaManager();
        } else {
            // @todo Remove once support for TYPO3 v11 is dropped
            $schemaManager = $this->connection->getSchemaManager();
        }

        foreach ($schemaManager->listTableColumns(Domain\Model\Consent::TABLE_NAME) as $column) {
            $columnName = $column->getName();

            if (\in_array($columnName, self::LEGACY_COLUMNS, true)) {
                $legacyColumns[] = $columnName;
            }
        }

        return $legacyColumns;
    }

    /**
     * @param list<string> $legacyColumns
     */
    private function selectAffectedRows(array $legacyColumns): DBAL\Result
    {
        return $this->getPreparedQueryBuilder($legacyColumns)
            ->select('*')
            ->executeQuery()
        ;
    }

    /**
     * @param list<string> $legacyColumns
     */
    private function countAffectedRows(array $legacyColumns): int
    {
        $result = $this->getPreparedQueryBuilder($legacyColumns)
            ->count('uid')
            ->executeQuery();

        return (int)$result->fetchOne();
    }

    /**
     * @param list<string> $legacyColumns
     */
    private function getPreparedQueryBuilder(array $legacyColumns): Core\Database\Query\QueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder()->from(Domain\Model\Consent::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        // Early return if no legacy columns are given
        if ($legacyColumns === []) {
            return $queryBuilder->andWhere('0=1');
        }

        foreach ($legacyColumns as $legacyColumn) {
            match ($legacyColumn) {
                'approved' => $queryBuilder->orWhere($this->getConstraintsForLegacyApprovedColumn($queryBuilder)),
                'approval_date' => $queryBuilder->orWhere($this->getConstraintsForLegacyApprovalDateColumn($queryBuilder)),
                default => $queryBuilder->andWhere('0=1'),
            };
        }

        return $queryBuilder;
    }

    private function getConstraintsForLegacyApprovedColumn(Core\Database\Query\QueryBuilder $queryBuilder): Core\Database\Query\Expression\CompositeExpression
    {
        $expr = $queryBuilder->expr();

        return $expr->or(
            $expr->and(
                $expr->eq('approved', $queryBuilder->createNamedParameter(true, Core\Database\Connection::PARAM_BOOL)),
                $expr->eq('state', $queryBuilder->createNamedParameter(Enums\ConsentState::New->value, Core\Database\Connection::PARAM_INT))
            ),
            $expr->and(
                $expr->eq('approved', $queryBuilder->createNamedParameter(false, Core\Database\Connection::PARAM_BOOL)),
                $expr->eq('state', $queryBuilder->createNamedParameter(Enums\ConsentState::New->value, Core\Database\Connection::PARAM_INT)),
                $expr->eq('deleted', $queryBuilder->createNamedParameter(true, Core\Database\Connection::PARAM_BOOL)),
                $expr->isNull('data')
            )
        );
    }

    private function getConstraintsForLegacyApprovalDateColumn(Core\Database\Query\QueryBuilder $queryBuilder): Core\Database\Query\Expression\CompositeExpression
    {
        $expr = $queryBuilder->expr();

        return $expr->and(
            $expr->neq('approval_date', $queryBuilder->createNamedParameter(0, Core\Database\Connection::PARAM_INT)),
            $expr->isNull('update_date')
        );
    }
}
