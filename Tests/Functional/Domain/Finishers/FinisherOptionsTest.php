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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Form;
use TYPO3\TestingFramework;

/**
 * FinisherOptionsTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Finishers\FinisherOptions::class)]
final class FinisherOptionsTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    /**
     * @var Framework\MockObject\Stub&Extbase\Configuration\ConfigurationManagerInterface $configurationManagerStub
     */
    protected Framework\MockObject\Stub $configurationManagerStub;
    protected Src\Domain\Finishers\FinisherOptions $subject;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationManagerStub = self::createStub(Extbase\Configuration\ConfigurationManagerInterface::class);
        $this->subject = new Src\Domain\Finishers\FinisherOptions(
            $this->fetchOption(...),
            new Core\Http\ServerRequest(),
        );

        Core\Utility\GeneralUtility::setSingletonInstance(
            Extbase\Configuration\ConfigurationManagerInterface::class,
            $this->configurationManagerStub,
        );

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/pages.csv');

        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    #[Framework\Attributes\Test]
    public function getSubjectReturnsAlreadyParsedSubject(): void
    {
        $this->options['subject'] = 'foo';

        self::assertSame('foo', $this->subject->getSubject());

        $this->options = [];

        self::assertSame('foo', $this->subject->getSubject());
    }

    #[Framework\Attributes\Test]
    public function getSubjectReturnsLocalizedTranslatableSubject(): void
    {
        $this->options['subject'] = 'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:consentMail.subject';

        self::assertSame('Approve your consent', $this->subject->getSubject());
    }

    #[Framework\Attributes\Test]
    public function getSubjectReturnsDefaultSubjectIfFetchedSubjectIsEmpty(): void
    {
        $this->options['subject'] = '';

        self::assertSame('Approve your consent', $this->subject->getSubject());
    }

    #[Framework\Attributes\Test]
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

        $expected = new Fluid\View\TemplatePaths();
        $expected->setTemplateRootPaths([
            0 => 'foo',
            10 => 'baz',
        ]);
        $expected->setPartialRootPaths([
            0 => 'foo',
            10 => 'baz',
        ]);
        $expected->setLayoutRootPaths([
            0 => 'foo',
            10 => 'baz',
        ]);
        $expected->setFormat('both');

        self::assertEquals($expected, $this->subject->getTemplatePaths());

        $this->options = [];

        self::assertEquals($expected, $this->subject->getTemplatePaths());
    }

    #[Framework\Attributes\Test]
    public function getRecipientAddressReturnsAlreadyParsedRecipientAddress(): void
    {
        $this->options['recipientAddress'] = 'foo@baz.de';

        self::assertSame('foo@baz.de', $this->subject->getRecipientAddress());

        $this->options = [];

        self::assertSame('foo@baz.de', $this->subject->getRecipientAddress());
    }

    #[Framework\Attributes\Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsNotAString(): void
    {
        $this->options['recipientAddress'] = null;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1640186663);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must contain a valid e-mail address.');

        $this->subject->getRecipientAddress();
    }

    #[Framework\Attributes\Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsEmpty(): void
    {
        $this->options['recipientAddress'] = '';

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576947638);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must be set.');

        $this->subject->getRecipientAddress();
    }

    #[Framework\Attributes\Test]
    public function getRecipientAddressThrowsExceptionIfFetchedRecipientAddressIsInvalid(): void
    {
        $this->options['recipientAddress'] = 'foo';

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576947682);
        $this->expectExceptionMessage('The finisher option "recipientAddress" must contain a valid e-mail address.');

        $this->subject->getRecipientAddress();
    }

    #[Framework\Attributes\Test]
    public function getRecipientNameReturnsRecipientNameAndStoresTheParsedResult(): void
    {
        $this->options['recipientName'] = 'foo';

        self::assertSame('foo', $this->subject->getRecipientName());

        $this->options = [];

        self::assertSame('foo', $this->subject->getRecipientName());
    }

    #[Framework\Attributes\Test]
    public function getSenderAddressReturnsAlreadyParsedSenderAddress(): void
    {
        $this->options['senderAddress'] = 'foo@baz.de';

        self::assertSame('foo@baz.de', $this->subject->getSenderAddress());

        $this->options = [];

        self::assertSame('foo@baz.de', $this->subject->getSenderAddress());
    }

    #[Framework\Attributes\Test]
    public function getSenderAddressThrowsExceptionIfFetchedSenderAddressIsNotAString(): void
    {
        $this->options['senderAddress'] = null;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1640186811);
        $this->expectExceptionMessage('The finisher option "senderAddress" must contain a valid e-mail address.');

        $this->subject->getSenderAddress();
    }

    #[Framework\Attributes\Test]
    public function getSenderAddressThrowsExceptionIfFetchedSenderAddressIsNotEmptyAndInvalid(): void
    {
        $this->options['senderAddress'] = 'foo';

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1587842752);
        $this->expectExceptionMessage('The finisher option "senderAddress" must contain a valid e-mail address.');

        $this->subject->getSenderAddress();
    }

    #[Framework\Attributes\Test]
    public function getSenderNameReturnsSenderNameAndStoresTheParsedResult(): void
    {
        $this->options['senderName'] = 'foo';

        self::assertSame('foo', $this->subject->getSenderName());

        $this->options = [];

        self::assertSame('foo', $this->subject->getSenderName());
    }

    #[Framework\Attributes\Test]
    public function getReplyToAddressReturnsAlreadyParsedReplyToAddress(): void
    {
        $this->options['replyToAddress'] = 'foo@baz.de';

        self::assertSame('foo@baz.de', $this->subject->getReplyToAddress());

        $this->options = [];

        self::assertSame('foo@baz.de', $this->subject->getReplyToAddress());
    }

    #[Framework\Attributes\Test]
    public function getReplyToAddressThrowsExceptionIfFetchedReplyToAddressIsNotAString(): void
    {
        $this->options['replyToAddress'] = null;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1716797809);
        $this->expectExceptionMessage('The finisher option "replyToAddress" must contain a valid e-mail address.');

        $this->subject->getReplyToAddress();
    }

    #[Framework\Attributes\Test]
    public function getReplyToAddressThrowsExceptionIfFetchedReplyToAddressIsNotEmptyAndInvalid(): void
    {
        $this->options['replyToAddress'] = 'foo';

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1716797811);
        $this->expectExceptionMessage('The finisher option "replyToAddress" must contain a valid e-mail address.');

        $this->subject->getReplyToAddress();
    }

    #[Framework\Attributes\Test]
    public function getReplyToNameReturnsReplyToNameAndStoresTheParsedResult(): void
    {
        $this->options['replyToName'] = 'foo';

        self::assertSame('foo', $this->subject->getReplyToName());

        $this->options = [];

        self::assertSame('foo', $this->subject->getReplyToName());
    }

    #[Framework\Attributes\Test]
    public function getApprovalPeriodReturnsAlreadyParsedApprovalPeriod(): void
    {
        $this->options['approvalPeriod'] = 86400;

        self::assertSame(86400, $this->subject->getApprovalPeriod());

        $this->options = [];

        self::assertSame(86400, $this->subject->getApprovalPeriod());
    }

    #[Framework\Attributes\Test]
    public function getApprovalPeriodThrowsExceptionIfFetchedApprovalPeriodIsInvalid(): void
    {
        $this->options['approvalPeriod'] = -1;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576948900);
        $this->expectExceptionMessage('The finisher option "approvalPeriod" must be zero or more seconds.');

        $this->subject->getApprovalPeriod();
    }

    #[Framework\Attributes\Test]
    public function getConfirmationPidThrowsExceptionIfFetchedConfirmationPidIsLowerThanZero(): void
    {
        $this->options['confirmationPid'] = -1;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576948961);
        $this->expectExceptionMessage('The finisher option "confirmationPid" must be set.');

        $this->subject->getConfirmationPid();
    }

    #[Framework\Attributes\Test]
    public function getConfirmationPidThrowsExceptionIfFetchedConfirmationPidIsInvalid(): void
    {
        $this->options['confirmationPid'] = 123;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576949163);
        $this->expectExceptionMessage('The finisher option "confirmationPid" must be set to a valid page.');

        $this->subject->getConfirmationPid();
    }

    #[Framework\Attributes\Test]
    public function getConfirmationPidReturnsConfirmationPidAndStoresTheParsedResult(): void
    {
        $this->options['confirmationPid'] = 1;

        self::assertSame(1, $this->subject->getConfirmationPid());

        $this->options['confirmationPid'] = 123;

        self::assertSame(1, $this->subject->getConfirmationPid());
    }

    #[Framework\Attributes\Test]
    public function getStoragePidReturnsDefaultStoragePidIfFetchedStoragePidIsZero(): void
    {
        $this->options['storagePid'] = 0;

        $this->configurationManagerStub->method('getConfiguration')->willReturn([
            'persistence' => [
                'storagePid' => '1',
            ],
        ]);

        self::assertSame(1, $this->subject->getStoragePid());
    }

    #[Framework\Attributes\Test]
    public function getStoragePidReturnsZeroIfFetchedStoragePidIsZero(): void
    {
        $this->options['storagePid'] = 0;

        $this->configurationManagerStub->method('getConfiguration')->willReturn([
            'persistence' => [
                'storagePid' => '0',
            ],
        ]);

        self::assertSame(0, $this->subject->getStoragePid());
    }

    #[Framework\Attributes\Test]
    public function getStoragePidThrowsExceptionIfFetchedStoragePidIsLowerThanZero(): void
    {
        $this->options['storagePid'] = -1;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576951495);
        $this->expectExceptionMessage('The finisher option "storagePid" must be set.');

        $this->subject->getStoragePid();
    }

    #[Framework\Attributes\Test]
    public function getStoragePidThrowsExceptionIfFetchedStoragePidIsInvalid(): void
    {
        $this->options['storagePid'] = 123;

        $this->expectException(Form\Domain\Finishers\Exception\FinisherException::class);
        $this->expectExceptionCode(1576951499);
        $this->expectExceptionMessage('The finisher option "storagePid" must be set to a valid page.');

        $this->subject->getStoragePid();
    }

    #[Framework\Attributes\Test]
    public function getStoragePidReturnsStoragePidAndStoresTheParsedResult(): void
    {
        $this->options['storagePid'] = 1;

        self::assertSame(1, $this->subject->getStoragePid());

        $this->options['storagePid'] = 123;

        self::assertSame(1, $this->subject->getStoragePid());
    }

    #[Framework\Attributes\Test]
    public function getShowDismissLinkReturnsShowDismissLinkAndStoresTheParsedResult(): void
    {
        $this->options['showDismissLink'] = false;

        self::assertFalse($this->subject->getShowDismissLink());

        $this->options['showDismissLink'] = true;

        self::assertFalse($this->subject->getShowDismissLink());
    }

    #[Framework\Attributes\Test]
    public function requiresVerificationForApprovalLinkReturnsRequireApproveVerificationAndStoresTheParsedResult(): void
    {
        $this->options['requireApproveVerification'] = false;

        self::assertFalse($this->subject->requiresVerificationForApproval());

        $this->options['requireApproveVerification'] = true;

        self::assertFalse($this->subject->requiresVerificationForApproval());
    }

    #[Framework\Attributes\Test]
    public function requiresVerificationForDismissalLinkReturnsRequireDismissVerificationAndStoresTheParsedResult(): void
    {
        $this->options['requireDismissVerification'] = false;

        self::assertFalse($this->subject->requiresVerificationForDismissal());

        $this->options['requireDismissVerification'] = true;

        self::assertFalse($this->subject->requiresVerificationForDismissal());
    }

    public function fetchOption(string $optionName): mixed
    {
        return $this->options[$optionName] ?? null;
    }
}
