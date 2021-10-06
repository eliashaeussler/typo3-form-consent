<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `form_consent`

[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=eliashaeussler_typo3-form-consent&metric=coverage)](https://sonarcloud.io/dashboard?id=eliashaeussler_typo3-form-consent)
[![Tests](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/cgl.yaml)
[![Latest Stable Version](http://poser.pugx.org/eliashaeussler/typo3-form-consent/v)](https://packagist.org/packages/eliashaeussler/typo3-form-consent)
[![License](http://poser.pugx.org/eliashaeussler/typo3-form-consent/license)](LICENSE.md)

:package:&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/typo3-form-consent) |
:hatched_chick:&nbsp;[TYPO3 extension repository](https://extensions.typo3.org/extension/form_consent) |
:floppy_disk:&nbsp;[Repository](https://github.com/eliashaeussler/typo3-form-consent) |
:bug:&nbsp;[Issue tracker](https://github.com/eliashaeussler/typo3-form-consent/issues)

</div>

An extension for TYPO3 CMS that adds double opt-in functionality to
EXT:form. It allows the dynamic adaptation of the entire double opt-in
process using various events. In addition, the extension integrates
seamlessly into TYPO3, for example to delete expired consents in
compliance with the GDPR.

## :rocket: Features

* Custom `Consent` form finisher for EXT:form
* Stores all submitted form data as JSON in database
* System-dependent hash-based validation system (using TYPO3's HMAC functionality)
* Plugin to approve or dismiss a consent
* Several events for better customization
* Scheduler garbage collection task for expired consents
* Dashboard widget for approved, non-approved and dismissed consents
* Compatible with TYPO3 10.4 LTS and 11.5 LTS

## :fire: Installation

```bash
composer require eliashaeussler/typo3-form-consent
```

## :zap: Usage

A new finisher `Consent` is available in the backend form editor.
It saves all submitted form data in the database and sends a
corresponding mail to either approve or dismiss a given consent.

A plugin is required for approval or dismiss of the consent. The
associated page containing the plugin must then be specified in the
finisher settings.

## :open_file_folder: Configuration

Only the TypoScript setup under `EXT:form_consent/Configuration/TypoScript`
needs to be included and the required database changes need to be made.

### TypoScript

The following TypoScript constants are available:

| TypoScript constant | Description | Required | Default |
|---------------------|-------------|----------|---------|
| **`plugin.tx_formconsent.persistence.storagePid`** | Default storage PID for new consents | :x: | `0` |
| **`plugin.tx_formconsent.view.templateRootPath`** | Path to template root for consent mail and validation plugin | :x: | – |
| **`plugin.tx_formconsent.view.partialRootPath`** | Path to template partials for consent mail and validation plugin | :x: | – |
| **`plugin.tx_formconsent.view.layoutRootPath`** | Path to template layouts for consent mail and validation plugin | :x: | – |

### Finisher options

The following options are available to the `Consent` finisher:

| Finisher option | Description | Required | Default |
|-----------------|-------------|----------|---------|
| **`subject`**   | Mail subject | :x: | `Approve your consent` |
| **`recipientAddress`** | Recipient e-mail address | :white_check_mark: | – |
| **`recipientName`** | Recipient name | :x: | – |
| **`senderAddress`** | Sender e-mail address | :x: | _System default sender e-mail address_ |
| **`senderName`** | Sender name | :x: | _System default sender name_ |
| **`approvalPeriod`** | Approval period | :white_check_mark: | `86400` (1 day), `0` = unlimited |
| **`showDismissLink`** | Show dismiss link in consent mail | :x: | `false` |
| **`confirmationPid`** | Confirmation page (contains plugin) | :white_check_mark: | – |
| **`storagePid`** | Storage page | :x: | `plugin.tx_formconsent.persistence.storagePid` |

## :gem: Credits

Icons made by [Google](https://www.flaticon.com/authors/google) from
[www.flaticon.com](https://www.flaticon.com/).

## :star: License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
