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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent\Domain\Finishers\FinisherOptions;
use EliasHaeussler\Typo3FormConsent\Exception\NotAllowedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * FinisherOptionsTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[CoversClass(FinisherOptions::class)]
final class FinisherOptionsTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected FinisherOptions $subject;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FinisherOptions($this->fetchOption(...));

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/pages.csv');

        Bootstrap::initializeLanguageObject();
    }

    #[Test]
    public function getSubjectReturnsAlreadyParsedSubject(): void
    {
        $this->options['subject'] = 'foo';

        self::assertSame('foo', $this->subject->getSubject());

        $this->options = [];

        self::assertSame('foo', $this->subject->getSubject());
    }

    #[Test]
    public function getSubjectReturnsLocalizedTranslatableSubject(): void
    {
        $this->options['subject'] = 'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:consentMail.subject';

        self::assertSame('Approve your consent', $this->subject->getSubject());
    }

    #[Test]
    public function getSubjectReturnsDefaultSubjectIfFetchedSubjectIsEmpty(): void
    {
        $this->options['subject'] = '';

        self::assertSame('Approve your consent', $this->subject->getSubject());
    }

    #[Test]
    public function getTemplatePathsReturnsTemplatePathsAndStoresTheParsedResult(): void
    {
        $this->options = [
            'templateRootPaths' => [
                0 => 'foo',
                10 => 'baz',
            ],
            'partialRootPaths' => [
                0 => 'foo',
                10 => 'baz',
            ],
            'layoutRootPaths' => [
                0 => 'foo',
                10 => 'baz',
            ],
        ];

        $templatePathsArray = $this->options;
        $templatePathsArray['format'] = 'both';

        $expected = new TemplatePaths($templatePathsArray);

        self::assertEquals($expected, $this->subject->getTemplatePaths());

        $this->options = [];

        self::assertEquals($expected, $this->subject->getTemplatePaths());
    }

    #[Test]
    public function getRecipientAddressReturnsAlreadyParsedRecipientAddress(): void
    {
        $this->options['recipientAddress'] = 'foo@baz.de';

        self::assertSame('foo@baz.de', $this->subject->getRecipientAddress());

        $this->options = [];

        self::assertSame('foo@baz.de', $this->subject->getRecipientAddress());
    }

    #[Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsNotAString(): void
    {
        $this->options['recipientAddress'] = null;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1640186663);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must contain a valid e-mail address.');

        $this->subject->getRecipientAddress();
    }

    #[Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsEmpty(): void
    {
        $this->options['recipientAddress'] = '';

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576947638);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must be set.');

        $this->subject->getRecipientAddress();
    }

    #[Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsInvalid(): void
    {
        $this->options['recipientAddress'] = 'foo';

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576947682);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must contain a valid e-mail address.');

        $this->subject->getRecipientAddress();
    }

    #[Test]
    public function getRecipientNameReturnsRecipientNameAndStoresTheParsedResult(): void
    {
        $this->options['recipientName'] = 'foo';

        self::assertSame('foo', $this->subject->getRecipientName());

        $this->options = [];

        self::assertSame('foo', $this->subject->getRecipientName());
    }

    #[Test]
    public function getSenderAddressReturnsAlreadyParsedSenderAddress(): void
    {
        $this->options['senderAddress'] = 'foo@baz.de';

        self::assertSame('foo@baz.de', $this->subject->getSenderAddress());

        $this->options = [];

        self::assertSame('foo@baz.de', $this->subject->getSenderAddress());
    }

    #[Test]
    public function getSenderAddressThrowsExceptionIfFetchedSenderAddressIsNotAString(): void
    {
        $this->options['senderAddress'] = null;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1640186811);
        $this->expectExceptionMessage('The finisher option "senderAddress" must contain a valid e-mail address.');

        $this->subject->getSenderAddress();
    }

    #[Test]
    public function getSenderAddressThrowsExceptionIfFetchedSenderAddressIsNotEmptyAndInvalid(): void
    {
        $this->options['senderAddress'] = 'foo';

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1587842752);
        $this->expectExceptionMessage('The finisher option "senderAddress" must contain a valid e-mail address.');

        $this->subject->getSenderAddress();
    }

    #[Test]
    public function getSenderNameReturnsSenderNameAndStoresTheParsedResult(): void
    {
        $this->options['senderName'] = 'foo';

        self::assertSame('foo', $this->subject->getSenderName());

        $this->options = [];

        self::assertSame('foo', $this->subject->getSenderName());
    }

    #[Test]
    public function getApprovalPeriodReturnsAlreadyParsedApprovalPeriod(): void
    {
        $this->options['approvalPeriod'] = 86400;

        self::assertSame(86400, $this->subject->getApprovalPeriod());

        $this->options = [];

        self::assertSame(86400, $this->subject->getApprovalPeriod());
    }

    #[Test]
    public function getApprovalPeriodThrowsExceptionIfFetchedApprovalPeriodIsInvalid(): void
    {
        $this->options['approvalPeriod'] = -1;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576948900);
        $this->expectExceptionMessage('The finisher option "approvalPeriod" must be zero or more seconds.');

        $this->subject->getApprovalPeriod();
    }

    #[Test]
    public function getConfirmationPidThrowsExceptionIfFetchedConfirmationPidIsLowerThanZero(): void
    {
        $this->options['confirmationPid'] = -1;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576948961);
        $this->expectExceptionMessage('The finisher option "confirmationPid" must be set.');

        $this->subject->getConfirmationPid();
    }

    #[Test]
    public function getConfirmationPidThrowsExceptionIfFetchedConfirmationPidIsInvalid(): void
    {
        $this->options['confirmationPid'] = 123;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576949163);
        $this->expectExceptionMessage('The finisher option "confirmationPid" must be set to a valid page.');

        $this->subject->getConfirmationPid();
    }

    #[Test]
    public function getConfirmationPidReturnsConfirmationPidAndStoresTheParsedResult(): void
    {
        $this->options['confirmationPid'] = 1;

        self::assertSame(1, $this->subject->getConfirmationPid());

        $this->options['confirmationPid'] = 123;

        self::assertSame(1, $this->subject->getConfirmationPid());
    }

    #[Test]
    public function getStoragePidReturnsZeroIfFetchedStoragePidIsZero(): void
    {
        $this->options['storagePid'] = 0;

        self::assertSame(0, $this->subject->getStoragePid());
    }

    #[Test]
    public function getStoragePidThrowsExceptionIfFetchedStoragePidIsLowerThanZero(): void
    {
        $this->options['storagePid'] = -1;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576951495);
        $this->expectExceptionMessage('The finisher option "storagePid" must be set.');

        $this->subject->getStoragePid();
    }

    #[Test]
    public function getStoragePidThrowsExceptionIfFetchedStoragePidIsInvalid(): void
    {
        $this->options['storagePid'] = 123;

        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1576951499);
        $this->expectExceptionMessage('The finisher option "storagePid" must be set to a valid page.');

        $this->subject->getStoragePid();
    }

    #[Test]
    public function getStoragePidReturnsStoragePidAndStoresTheParsedResult(): void
    {
        $this->options['storagePid'] = 1;

        self::assertSame(1, $this->subject->getStoragePid());

        $this->options['storagePid'] = 123;

        self::assertSame(1, $this->subject->getStoragePid());
    }

    #[Test]
    public function getShowDismissLinkReturnsShowDismissLinkAndStoresTheParsedResult(): void
    {
        $this->options['showDismissLink'] = false;

        self::assertFalse($this->subject->getShowDismissLink());

        $this->options['showDismissLink'] = true;

        self::assertFalse($this->subject->getShowDismissLink());
    }

    #[Test]
    public function objectCanBeAccessedAsArrayInReadMode(): void
    {
        // offsetExists()
        self::assertArrayHasKey('approvalPeriod', $this->subject);
        self::assertArrayNotHasKey('foo', $this->subject);

        // offsetGet()
        self::assertSame(0, $this->subject['approvalPeriod']);
        self::assertNull($this->subject['foo']);
        self::assertNull($this->subject[null]);
    }

    #[Test]
    public function objectCannotBeAccessedAsArrayInWriteModeViaOffsetSet(): void
    {
        $this->expectException(NotAllowedException::class);
        $this->expectExceptionCode(1645781267);
        $this->expectExceptionMessage(
            sprintf('Calling the method "%s" is not allowed.', FinisherOptions::class . '::offsetSet')
        );

        $this->subject['approvalPeriod'] = 0;
    }

    #[Test]
    public function objectCannotBeAccessedAsArrayInWriteModeViaOffsetUnset(): void
    {
        $this->expectException(NotAllowedException::class);
        $this->expectExceptionCode(1645781267);
        $this->expectExceptionMessage(
            sprintf('Calling the method "%s" is not allowed.', FinisherOptions::class . '::offsetUnset')
        );

        unset($this->subject['approvalPeriod']);
    }

    /**
     * @return mixed|null
     */
    public function fetchOption(string $optionName)
    {
        return $this->options[$optionName] ?? null;
    }
}
