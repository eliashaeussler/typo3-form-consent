..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

..  _requirements:

Requirements
============

-   PHP 8.1 - 8.3
-   TYPO3 11.5 LTS - 13.3

..  _steps:

Installation
============

Require the extension via Composer (recommended):

..  code-block:: bash

    composer require eliashaeussler/typo3-form-consent

Or download it from the
`TYPO3 extension repository <https://extensions.typo3.org/extension/form_consent>`__.

..  _initial-setup:

Initial setup
=============

Once installed, make sure to add the site set :yaml:`eliashaeussler/typo3-form-consent`
as dependency to your site (TYPO3 >= 13.1) or include the TypoScript setup
at :file:`EXT:form_consent/Configuration/TypoScript` in your root template.

..  _version-matrix:

Version matrix
==============

+--------------------+---------------------+---------------+
| Extension versions | TYPO3 versions      | PHP versions  |
+====================+=====================+===============+
| **since 2.4.0**    | **11.5 LTS - 13.3** | **8.1 - 8.3** |
+--------------------+---------------------+---------------+
| 2.2.0 - 2.3.1      | 11.5 LTS - 13.1     | 8.1 - 8.3     |
+--------------------+---------------------+---------------+
| 2.1.0              | 11.5 LTS - 13.0     | 8.1 - 8.3     |
+--------------------+---------------------+---------------+
| 1.1.0 - 2.0.0      | 11.5 LTS - 12.4 LTS | 8.1 - 8.3     |
+--------------------+---------------------+---------------+
| 1.0.0              | 11.5 LTS - 12.4 LTS | 8.1 - 8.2     |
+--------------------+---------------------+---------------+
| 0.3.0 - 0.7.1      | 10.4 LTS - 11.5 LTS | 7.4 - 8.1     |
+--------------------+---------------------+---------------+
| 0.2.x              | 10.4 LTS - 11.5 LTS | 7.2 - 8.1     |
+--------------------+---------------------+---------------+
| 0.1.x              | 10.4 LTS - 11.5 LTS | 7.2 - 8.0     |
+--------------------+---------------------+---------------+
