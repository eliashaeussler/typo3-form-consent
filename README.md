<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `form_consent`

[![Coverage](https://img.shields.io/codecov/c/github/eliashaeussler/typo3-form-consent?logo=codecov&token=PQ0101QE3S)](https://codecov.io/gh/eliashaeussler/typo3-form-consent)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/eliashaeussler/typo3-form-consent?logo=codeclimate)](https://codeclimate.com/github/eliashaeussler/typo3-form-consent/maintainability)
[![CGL](https://img.shields.io/github/actions/workflow/status/eliashaeussler/typo3-form-consent/cgl.yaml?label=cgl&logo=github)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/eliashaeussler/typo3-form-consent/tests.yaml?label=tests&logo=github)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/tests.yaml)
[![Supported TYPO3 versions](https://typo3-badges.dev/badge/form_consent/typo3/shields.svg)](https://extensions.typo3.org/extension/form_consent)
[![Slack](https://img.shields.io/badge/slack-%23ext--form__consent-4a154b?logo=slack)](https://typo3.slack.com/archives/C03719PJJJD)

</div>

An extension for TYPO3 CMS that adds double opt-in functionality to
EXT:form. It allows the dynamic adaptation of the entire double opt-in
process using various events. In addition, the extension integrates
seamlessly into TYPO3, for example to delete expired consents in
compliance with the GDPR.

## 🚀 Features

* Custom `Consent` form finisher for EXT:form
* Stores all submitted form data as JSON in database
* System-dependent hash-based validation system (using TYPO3's HMAC functionality)
* Plugin to approve or dismiss a consent
* Possibility to [invoke finishers on consent approval or dismissal](#invoke-finishers-on-consent-approval-or-dismissal)
* Several [events](#events) for better customization
* Scheduler garbage collection task for expired consents
* Dashboard widget for approved, non-approved and dismissed consents
* Compatible with TYPO3 11.5 LTS and 12.4 LTS

## 🔥 Installation

### Composer

[![Packagist](https://img.shields.io/packagist/v/eliashaeussler/typo3-form-consent?label=version&logo=packagist)](https://packagist.org/packages/eliashaeussler/typo3-form-consent)
[![Packagist Downloads](https://img.shields.io/packagist/dt/eliashaeussler/typo3-form-consent?color=brightgreen)](https://packagist.org/packages/eliashaeussler/typo3-form-consent)

```bash
composer require eliashaeussler/typo3-form-consent
```

### TER

[![TER version](https://typo3-badges.dev/badge/form_consent/version/shields.svg)](https://extensions.typo3.org/extension/form_consent)
[![TER downloads](https://typo3-badges.dev/badge/form_consent/downloads/shields.svg)](https://extensions.typo3.org/extension/form_consent)

Download the zip file from
[TYPO3 extension repository (TER)][4].

## ⚡ Usage

Once installed, make sure to include the TypoScript setup at
`EXT:form_consent/Configuration/TypoScript` in your root template.

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

## 📂 Configuration

### TypoScript

The following TypoScript constants are available:

| TypoScript constant                                | Description                                                      | Required | Default |
|----------------------------------------------------|------------------------------------------------------------------|----------|---------|
| **`plugin.tx_formconsent.persistence.storagePid`** | Default storage PID for new consents                             | –        | `0`     |
| **`plugin.tx_formconsent.view.templateRootPath`**  | Path to template root for consent mail and validation plugin     | –        | –       |
| **`plugin.tx_formconsent.view.partialRootPath`**   | Path to template partials for consent mail and validation plugin | –        | –       |
| **`plugin.tx_formconsent.view.layoutRootPath`**    | Path to template layouts for consent mail and validation plugin  | –        | –       |

### Finisher options

The following options are available to the `Consent` finisher:

| Finisher option         | Description                           | Required | Default                                        |
|-------------------------|---------------------------------------|----------|------------------------------------------------|
| **`subject`**           | Mail subject                          | –        | `Approve your consent`                         |
| **`recipientAddress`**  | Recipient e-mail address              | ✅        | –                                              |
| **`recipientName`**     | Recipient name                        | –        | –                                              |
| **`senderAddress`**     | Sender e-mail address                 | –        | _System default sender e-mail address_         |
| **`senderName`**        | Sender name                           | –        | _System default sender name_                   |
| **`approvalPeriod`**    | Approval period                       | ✅        | `86400` (1 day), `0` = unlimited               |
| **`showDismissLink`**   | Show dismiss link in consent mail     | –        | `false`                                        |
| **`confirmationPid`**   | Confirmation page (contains plugin)   | ✅        | –                                              |
| **`storagePid`**        | Storage page                          | –        | `plugin.tx_formconsent.persistence.storagePid` |
| **`templateRootPaths`** | Additional paths to template root     | –        | –                                              |
| **`partialRootPaths`**  | Additional paths to template partials | –        | –                                              |
| **`layoutRootPaths`**   | Additional paths to template layouts  | –        | –                                              |

💡 **Note:** Template paths that are configured via form finisher
options are only applied to the appropriate form. They are merged
with the default template paths configured via TypoScript.

### Extension configuration

The following extension configuration options are available:

| Configuration key                  | Description                                                               | Required | Default    |
|------------------------------------|---------------------------------------------------------------------------|----------|------------|
| **`persistence.excludedElements`** | Form element types to be excluded from persistence (comma-separated list) | –        | `Honeypot` |

## ✍️ Customization

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

## 🚧 Migration

### 0.7.x → 1.0.0

#### Consent state enum

Different consent states moved from [`ConsentStateType`][7] to a new
[`ConsentState`][8] enum.

* Use enum cases instead of the old class constants.

### 0.6.x → 0.7.0

#### Global form settings

Form settings for Frontend requests (`plugin.tx_form`) are no longer included
globally.

* Make sure to add the static TypoScript setup at `EXT:form_consent/Configuration/TypoScript`
  to your root template.

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

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## 💎 Credits

Icons made by [Google](https://www.flaticon.com/authors/google) from
[www.flaticon.com](https://www.flaticon.com/).

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).

[1]: Classes/Domain/Model/Consent.php
[2]: Classes/Type/JsonType.php
[3]: https://github.com/eliashaeussler/typo3-form-consent/issues
[4]: https://extensions.typo3.org/extension/form_consent
[5]: Classes/Updates/MigrateConsentStateUpgradeWizard.php
[6]: Classes/Event/Listener/InvokeFinishersListener.php
[7]: Classes/Type/ConsentStateType.php
[8]: Classes/Enums/ConsentState.php
