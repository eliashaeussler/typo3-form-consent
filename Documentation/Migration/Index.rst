..  include:: /Includes.rst.txt

..  _migration:

=========
Migration
=========

This page lists all notable changes and required migrations when
upgrading to a new major version of this extension.

..  _version-2.0.0:

Version 2.0.0
=============

Finisher context in event
-------------------------

-   :php:class:`EliasHaeussler\\Typo3FormConsent\\Event\\ModifyConsentEvent` no longer
    explicitly provides the current :php:class:`TYPO3\\CMS\\Form\\Domain\\Runtime\\FormRuntime`
    instance via the :php:`getFormRuntime()` method. Use :php:`getFinisherContext()->getFormRuntime()`
    instead.
-   :php:meth:`EliasHaeussler\\Typo3FormConsent\\Domain\\Factory\\ConsentFactory::createFromForm`
    no longer expects a :php:class:`TYPO3\\CMS\\Form\\Domain\\Runtime\\FormRuntime`
    as second parameter. Pass the current :php:class:`TYPO3\\CMS\\Form\\Domain\\Finishers\\FinisherContext`
    instead.

..  _version-1.0.0:

Version 1.0.0
=============

Consent state enum
------------------

-   Different consent states moved from :php:class:`EliasHaeussler\\Typo3FormConsent\\Type\\ConsentStateType`
    to a new :php:class:`EliasHaeussler\\Typo3FormConsent\\Enums\\ConsentState` enum.
-   Use enum cases instead of the old class constants.

..  _version-0.7.0:

Version 0.7.0
=============

Global form settings
--------------------

-   Form settings for Frontend requests (:typoscript:`plugin.tx_form`) are
    no longer included globally.
-   Make sure to add the static TypoScript setup at
    :file:`EXT:form_consent/Configuration/TypoScript` to your root template.

..  _version-0.4.0:

Version 0.4.0
=============

Generic consent state
---------------------

-   The current state of form consents is now represented in a more generic way.
-   Database field `tx_formconsent_domain_model_consent.approved` was renamed to
    `tx_formconsent_domain_model_consent.state`. Upgrade wizard
    :php:`formConsentMigrateConsentState` needs to be executed.
-   Database field `tx_formconsent_domain_model_consent.approval_date` was renamed
    to `tx_formconsent_domain_model_consent.update_date`. Upgrade wizard
    :php:`formConsentMigrateConsentState` needs to be executed. Note: The database
    column is now nullable.
-   :php:`$consent->setApproved()` does no longer accept any parameters. Use
    :php:`$consent->setState()` instead.
-   :php:`$consent->getApprovalDate()` was removed. Use
    :php:`$consent->getUpdateDate()` instead.
-   :php:`$consent->setApprovalDate()` was removed. Use
    :php:`$consent->setUpdateDate()` instead.

Post-consent dismissal finishers
--------------------------------

-   Custom finishers can now be executed after consent was dismissed.
-   Event listener was renamed. Change references to
    :php:class:`EliasHaeussler\\Typo3FormConsent\\Event\\Listener\\InvokeFinishersListener`.
    Adapt your service configuration, if needed.
-   Listener method was renamed. Use :php:`onConsentApprove($event)` instead of
    :php:`__invoke($event)`.
-   Event listener identifier :php:`formConsentInvokeFinishersOnApproveListener`
    changed. Change references to :php:`formConsentInvokeFinishersOnConsentApproveListener`.


..  _version-0.3.0:

Version 0.3.0
=============

Post-consent approval finishers
-------------------------------

-   Custom finishers can now be executed after consent was approved.
-   Database field `tx_formconsent_domain_model_consent.original_request_parameters`
    was added. A manual migration is required. Database field should contain an
    JSON-encoded string of the parsed body sent with the original form submit request.
-   Database field `tx_formconsent_domain_model_consent.original_content_element_uid`
    was added. A manual migration is required. Database field should contain the content
    element UID of the original form plugin.
-   Post-approval finishers can now be defined as described here:
    :ref:`post-consent-finisher-invocation`. A manual migration is required. Create
    form variants and configure the post-approval finishers.

Consent model
-------------

-   Form values are now represented as an instance of
    :php:class:`EliasHaeussler\\Typo3FormConsent\\Type\\JsonType`.
-   Method :php:`getDataArray()` was removed. Use :php:`getData()->toArray()` instead.
-   Return type of :php:`getData()` was changed to :php:`JsonType|null`. If you need
    the JSON-encoded string, use :php:`json_encode($consent->getData())` instead.
-   Parameter :php:`$data` of :php:`setData()` was changed to :php:`JsonType|null`.
    If you need to pass a JSON-encoded string, use :php:`$consent->setData(new JsonType($json))`
    instead. If you need to pass a JSON-decoded array, use
    :php:`$consent->setData(JsonType::fromArray($array))` instead.

Codebase
--------

-   Minimum PHP version was raised to PHP 7.4. Upgrade your codebase to support at least
    PHP 7.4.
-   Several classes were marked as :php:`final`. If you still need to extend or override
    them, consider refactoring your code or submit an issue.
