..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

The extension provides a `Consent` finisher for forms created with
EXT:form. With this finisher, a complete double opt-in process can
be triggered. Several parts of this process can be customized by a
various set of events. Additionally, it provides a dashboard widget
and a garbage collection task for expired consents.

..  _features:

Features
========

-   Custom `Consent` :ref:`form finisher <consent-finisher>` for EXT:form
-   Stores all submitted form data as :ref:`JSON in database <json-type>`
-   Hash-based validation system (using TYPO3's HMAC functionality)
-   :ref:`Plugin <validation-plugin>` to approve or dismiss a consent
-   Possibility to :ref:`invoke finishers <post-consent-finisher-invocation>`
    on consent approval or dismissal
-   Several :ref:`events <events>` for better customization
-   :ref:`Scheduler task <scheduler-task>` for expired consents
-   :ref:`Dashboard widget <dashboard-widget>` for approved, non-approved
    and dismissed consents
-   Compatible with TYPO3 11.5 LTS, 12.4 LTS and 13.2

..  _support:

Support
=======

There are several ways to get support for this extension:

* Slack: https://typo3.slack.com/archives/C03719PJJJD
* GitHub: https://github.com/eliashaeussler/typo3-form-consent/issues

..  _license:

License
=======

This extension is licensed under
`GNU General Public License 2.0 (or later) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>`_.
