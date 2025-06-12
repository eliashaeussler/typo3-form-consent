<div align="center">

![Extension icon](Resources/Public/Icons/Extension.svg)

# TYPO3 extension `form_consent`

[![Coverage](https://img.shields.io/coverallsCoverage/github/eliashaeussler/typo3-form-consent?logo=coveralls)](https://coveralls.io/github/eliashaeussler/typo3-form-consent)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/eliashaeussler/typo3-form-consent?logo=codeclimate)](https://codeclimate.com/github/eliashaeussler/typo3-form-consent/maintainability)
[![CI](https://img.shields.io/github/actions/workflow/status/eliashaeussler/typo3-form-consent/ci.yaml?label=CI&logo=github)](https://github.com/eliashaeussler/typo3-form-consent/actions/workflows/ci.yaml)
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
* Possibility to invoke finishers on consent approval or dismissal
* Several events for better customization
* Scheduler garbage collection task for expired consents
* Dashboard widget for approved, non-approved and dismissed consents
* Compatible with TYPO3 12.4 LTS and 13.4 LTS

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
[TYPO3 extension repository (TER)](https://extensions.typo3.org/extension/form_consent).

## 📙 Documentation

Please have a look at the
[official extension documentation](https://docs.typo3.org/p/eliashaeussler/typo3-form-consent/main/en-us/).

## 💎 Credits

Icons made by [Google](https://www.flaticon.com/authors/google) from
[www.flaticon.com](https://www.flaticon.com/).

## 🔒 Security Policy

Please read our [security policy](SECURITY.md) if you discover a security
vulnerability in this extension.

## ⭐ License

This project is licensed under [GNU General Public License 2.0 (or later)](LICENSE.md).
