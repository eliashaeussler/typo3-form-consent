..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

..  _requirements:

Requirements
============

-   PHP 8.1 - 8.3
-   TYPO3 11.5 LTS - 12.4 LTS

..  _steps:

Installation
============

Require the extension via Composer (recommended):

..  code-block:: bash

    composer require eliashaeussler/typo3-form-consent

Or download it from the
`TYPO3 extension repository <https://extensions.typo3.org/extension/form_consent>`__.

..  _typoscript-setup:

TypoScript setup
================

Once installed, make sure to include the TypoScript setup at
:file:`EXT:form_consent/Configuration/TypoScript` in your root template.

..  _version-matrix:

Version matrix
==============

+--------------------+-------------------------+---------------+
| Extension versions | TYPO3 versions          | PHP versions  |
+====================+=========================+===============+
| **since 1.1.0**    | **11.5 LTS - 12.4 LTS** | **8.1 - 8.3** |
+--------------------+-------------------------+---------------+
| 1.0.0              | 11.5 LTS - 12.4 LTS     | 8.1 - 8.2     |
+--------------------+-------------------------+---------------+
| 0.3.0 - 0.7.1      | 10.4 LTS - 11.5 LTS     | 7.4 - 8.1     |
+--------------------+-------------------------+---------------+
| 0.2.x              | 10.4 LTS - 11.5 LTS     | 7.2 - 8.1     |
+--------------------+-------------------------+---------------+
| 0.1.x              | 10.4 LTS - 11.5 LTS     | 7.2 - 8.0     |
+--------------------+-------------------------+---------------+
