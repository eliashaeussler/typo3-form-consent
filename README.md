<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `form_consent`

[![Coverage](https://codecov.io/gh/eliashaeussler/typo3-form-consent/branch/main/graph/badge.svg?token=PQ0101QE3S)](https://codecov.io/gh/eliashaeussler/typo3-form-consent)
[![Maintainability](https://api.codeclimate.com/v1/badges/c88c6c0bbc31c02153ef/maintainability)](https://codeclimate.com/github/eliashaeussler/typo3-form-consent/maintainability)
[![Tests](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/cgl.yaml)
[![Release](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/release.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/release.yaml)
[![License](http://poser.pugx.org/eliashaeussler/typo3-form-consent/license)](LICENSE.md)\
[![Version](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/form_consent/version/shields)](https://extensions.typo3.org/extension/form_consent)
[![Downloads](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/form_consent/downloads/shields)](https://extensions.typo3.org/extension/form_consent)
[![Extension stability](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/form_consent/stability/shields)](https://extensions.typo3.org/extension/form_consent)
[![TYPO3 badge](https://shields.io/endpoint?url=https://typo3-badges.dev/badge/typo3/shields)](https://typo3.org/)

:package:&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/typo3-form-consent) |
:hatched_chick:&nbsp;[TYPO3 extension repository](https://extensions.typo3.org/extension/form_consent) |
:floppy_disk:&nbsp;[Repository](https://github.com/eliashaeussler/typo3-form-consent) |
:bug:&nbsp;[Issue tracker](https://github.com/eliashaeussler/typo3-form-consent/issues)

</div>

---

> **Note**
>
> Installation with **TYPO3 11.5.13** is currently **not possible**.
>
> Read more at https://github.com/eliashaeussler/typo3-form-consent/pull/69
> and consider downgrading to TYPO3 11.5.12.

---

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
* Possibility to [invoke finishers on consent approval or dismissal](#invoke-finishers-on-consent-approval-or-dismissal)
* Several [events](#events) for better customization
* Scheduler garbage collection task for expired consents
* Dashboard widget for approved, non-approved and dismissed consents
* Compatible with TYPO3 10.4 and 11.5 LTS

## :fire: Installation

```bash
composer require eliashaeussler/typo3-form-consent
```

Alternatively, you can download the extension via the
[TYPO3 extension repository (TER)][4].

## :zap: Usage

### Finisher

A new finisher `Consent` is available in the backend form editor.
It saves all submitted form data in the database and sends a
corresponding mail to either approve or dismiss a given consent.

The last inserted consent is populated with the finisher variable
provider. It can be accessed as `{Consent.lastInsertedConsent}` in
the `.form.yaml` configuration.

Example:

```yaml
finishers:
  -
    options:
      table: tx_myextension_domain_model_mymodel
      mode: insert
      databaseColumnMappings:
        consent:
          value: '{Consent.lastInsertedConsent.uid}'
    identifier: SaveToDatabase
```

### Plugin

A plugin is required for approval or dismiss of the consent. The
associated page containing the plugin must then be specified in the
finisher settings.

## :open_file_folder: Configuration

The TypoScript setup under `EXT:form_consent/Configuration/TypoScript`
needs to be included and the required database changes need to be made.
Additionally, an extension configuration is provided.

### TypoScript

The following TypoScript constants are available:

| TypoScript constant | Description | Required | Default |
|---------------------|-------------|----------|---------|
| **`plugin.tx_formconsent.persistence.storagePid`** | Default storage PID for new consents | – | `0` |
| **`plugin.tx_formconsent.view.templateRootPath`** | Path to template root for consent mail and validation plugin | – | – |
| **`plugin.tx_formconsent.view.partialRootPath`** | Path to template partials for consent mail and validation plugin | – | – |
| **`plugin.tx_formconsent.view.layoutRootPath`** | Path to template layouts for consent mail and validation plugin | – | – |

### Finisher options

The following options are available to the `Consent` finisher:

| Finisher option | Description | Required | Default |
|-----------------|-------------|----------|---------|
| **`subject`**   | Mail subject | – | `Approve your consent` |
| **`recipientAddress`** | Recipient e-mail address | :white_check_mark: | – |
| **`recipientName`** | Recipient name | – | – |
| **`senderAddress`** | Sender e-mail address | – | _System default sender e-mail address_ |
| **`senderName`** | Sender name | – | _System default sender name_ |
| **`approvalPeriod`** | Approval period | :white_check_mark: | `86400` (1 day), `0` = unlimited |
| **`showDismissLink`** | Show dismiss link in consent mail | – | `false` |
| **`confirmationPid`** | Confirmation page (contains plugin) | :white_check_mark: | – |
| **`storagePid`** | Storage page | – | `plugin.tx_formconsent.persistence.storagePid` |
| **`templateRootPaths`** | Additional paths to template root | – | – |
| **`partialRootPaths`** | Additional paths to template partials | – | – |
| **`layoutRootPaths`** | Additional paths to template layouts | – | – |

:bulb: **Note:** Template paths that are configured via form finisher
options are only applied to the appropriate form. They are merged
with the default template paths configured via TypoScript.

### Extension configuration

The following extension configuration options are available:

| Configuration key | Description | Required | Default |
|-------------------|-------------|----------|---------|
| **`persistence.excludedElements`** | Form element types to be excluded from persistence (comma-separated list) | – | `Honeypot` |

## :writing_hand: Customization

The lifecycle of the entire consent process can be influenced in several
ways. This leads to high flexibility in customization while maintaining
high stability of the core components.

### Events

PSR-14 events can be used to modify different areas in the consent process.
The following events are available:

* [`ApproveConsentEvent`](Classes/Event/ApproveConsentEvent.php)
* [`DismissConsentEvent`](Classes/Event/DismissConsentEvent.php)
* [`GenerateHashEvent`](Classes/Event/GenerateHashEvent.php)
* [`ModifyConsentEvent`](Classes/Event/ModifyConsentEvent.php)
* [`ModifyConsentMailEvent`](Classes/Event/ModifyConsentMailEvent.php)

### Invoke finishers on consent approval or dismissal

After a user has given or revoked consent, it is often necessary to execute
certain form finishers. For example, to send an admin email or redirect to a
specific page.

To achieve this, after the user gives or revokes consent, the originally
completed form is resubmitted. During this resubmission of the form, the
selected finishers can now be overwritten using the `isConsentApproved()`
or `isConsentDismissed()` conditions in a form variant.

#### Requirements

The following requirements must be met for the form to be resubmitted:

1. Form variant at the root level of the form must exist
2. Form variant must redefine the finishers used
3. Conditions `isConsentApproved()` or `isConsentDismissed()` must exist
   in the variant

#### Example

The following form variant is stored directly on the root level of the
form definition (that is, your `.form.yaml` file). It specifies the form
finishers to be executed in case of successful approval by the user.

```yaml
variants:
  -
    identifier: post-consent-approval-variant-1
    condition: 'isConsentApproved()'
    finishers:
      -
        identifier: EmailToReceiver
        options:
          # ...
      -
        identifier: Redirect
        options:
          # ...
```

In this example, an admin email would be sent after the consent has been
given and a redirect to the configured confirmation page would take place.

The same behavior can be achieved in case the user revokes his consent. The
condition `isConsentDismissed()` must then be used instead.

## :construction: Migration

### 0.3.x → 0.4.0

#### Generic consent state

The current state of form consents is now represented in a more generic way.

* Database field `tx_formconsent_domain_model_consent.approved` was renamed to
  `tx_formconsent_domain_model_consent.state`.
  - Upgrade wizard [`formConsentMigrateConsentState`][5] needs to be executed.
* Database field `tx_formconsent_domain_model_consent.approval_date` was renamed to
  `tx_formconsent_domain_model_consent.update_date`.
    - Upgrade wizard [`formConsentMigrateConsentState`][5] needs to be executed.
    - Note: The database column is now nullable.
* [`$consent->setApproved()`][1] does no longer accept any parameters.
  - Use `$consent->setState()` instead.
* [`$consent->getApprovalDate()`][1] was removed.
  - Use `$consent->getUpdateDate()` instead.
* [`$consent->setApprovalDate()`][1] was removed.
  - Use `$consent->setUpdateDate()` instead.

#### Post-consent dismissal finishers

Custom finishers can now be executed after consent was dismissed.

* Event listener was renamed.
  - Change references to [`EliasHaeussler\Typo3FormConsent\Event\Listener\InvokeFinishersListener`][6].
  - Adapt your service configuration, if needed.
* Listener method was renamed.
  - Use `onConsentApprove($event)` instead of `__invoke($event)`.
* Event listener identifier `formConsentInvokeFinishersOnApproveListener` changed.
  - Change references to `formConsentInvokeFinishersOnConsentApproveListener`.

### 0.2.x → 0.3.0

#### Post-consent approval finishers

Custom finishers can now be executed after consent was approved.

* Database field `tx_formconsent_domain_model_consent.original_request_parameters` was added.
  - Manual migration required.
  - Database field should contain an JSON-encoded string of the parsed body sent with the original
    form submit request.
* Database field `tx_formconsent_domain_model_consent.original_content_element_uid` was added.
  - Manual migration required.
  - Database field should contain the content element UID of the original form plugin.
* Post-approval finishers can now be defined [as described above](#invoke-finishers-on-consent-approval).
  - Manual migration required.
  - Create form variants and configure the post-approval finishers.

#### [`Consent`][1] model

Form values are now represented as an instance of [`JsonType`][2].

* Method `getDataArray()` was removed.
  - Use `getData()->toArray()` instead.
* Return type of `getData()` was changed to `JsonType|null`.
  - If you need the JSON-encoded string, use `json_encode($consent->getData())` instead.
* Parameter `$data` of `setData()` was changed to `JsonType|null`.
  - If you need to pass a JSON-encoded string, use `$consent->setData(new JsonType($json))` instead.
  - If you need to pass a JSON-decoded array, use `$consent->setData(JsonType::fromArray($array))` instead.

#### Codebase

* Minimum PHP version was raised to PHP 7.4.
  - Upgrade your codebase to support at least PHP 7.4.
* Several classes were marked as `final`.
  - If you still need to extend or override them, consider refactoring
    your code or [submit an issue][3].

## :technologist: Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## :gem: Credits

Icons made by [Google](https://www.flaticon.com/authors/google) from
[www.flaticon.com](https://www.flaticon.com/).

## :star: License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Feliashaeussler%2Ftypo3-form-consent.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Feliashaeussler%2Ftypo3-form-consent?ref=badge_large)

[1]: Classes/Domain/Model/Consent.php
[2]: Classes/Type/JsonType.php
[3]: https://github.com/eliashaeussler/typo3-form-consent/issues
[4]: https://extensions.typo3.org/extension/form_consent
[5]: Classes/Updates/MigrateConsentStateUpgradeWizard.php
[6]: Classes/Event/Listener/InvokeFinishersListener.php
