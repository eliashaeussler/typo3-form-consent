..  include:: /Includes.rst.txt

..  _form-finisher-configuration:

===========================
Form finisher configuration
===========================

The :ref:`consent finisher <consent-finisher>` can be configured with the
following options:

..  _finisher-config-subject:

..  confval:: subject

    :Required: true
    :type: string or LLL reference
    :Default: :yaml:`LLL:EXT:warming/Resources/Private/Language/locallang.xlf:consentMail.subject`

    Mail subject of the consent mail sent to the form submitter.

..  _finisher-config-recipientAddress:

..  confval:: recipientAddress

    :Required: true
    :type: string

    Email address of the consent mail recipient. Can be a placeholder to an
    existing form element.

..  _finisher-config-recipientName:

..  confval:: recipientName

    :Required: false
    :type: string

    Name of the consent mail recipient. May contain placeholders to one or more
    existing form elements.

..  _finisher-config-senderAddress:

..  confval:: senderAddress

    :Required: false
    :type: string
    :Default: :ref:`System default <t3coreapi:mail-sender>` sender e-mail address

    Email address of the consent mail sender. Can be a placeholder to an
    existing form element.

..  _finisher-config-senderName:

..  confval:: senderName

    :Required: false
    :type: string
    :Default: :ref:`System default <t3coreapi:mail-sender>` sender name

    Name of the consent mail sender. May contain placeholders to one or more
    existing form elements.

..  _finisher-config-replyToAddress:

..  confval:: replyToAddress

    :Required: false
    :type: string

    ..  versionadded:: 2.2.0

        `Feature #221 – Allow configuration of Reply-To recipients in consent mail <https://github.com/eliashaeussler/typo3-form-consent/pull/221>`__

    Email address of the `Reply-To` mail recipient. Can be a placeholder to an
    existing form element.

..  _finisher-config-replyToName:

..  confval:: replyToName

    :Required: false
    :type: string

    ..  versionadded:: 2.2.0

        `Feature #221 – Allow configuration of Reply-To recipients in consent mail <https://github.com/eliashaeussler/typo3-form-consent/pull/221>`__

    Name of the `Reply-To` mail recipient. May contain placeholders to one or more
    existing form elements.

..  _finisher-config-approvalPeriod:

..  confval:: approvalPeriod

    :Required: false
    :type: integer
    :Default: :yaml:`86400` *(= 1 day)*

    Duration in seconds where consent mails can be approved. In fact, this
    results in a concrete datetime value for the `valid_until` database field
    of the current consent.

    ..  note::

        Set to :yaml:`0` to disable the approval period. A consent will then be
        approvable forever.

..  _finisher-config-showDismissLink:

..  confval:: showDismissLink

    :Required: false
    :type: boolean
    :Default: :yaml:`false`

    Enable or disable a link in the consent mail that allows to dismiss a
    consent. If this link is clicked, the consent is deleted.

..  _finisher-config-confirmationPid:

..  confval:: confirmationPid

    :Required: true
    :type: integer

    ID of the page where consent confirmation is validated. This page should
    contain the :ref:`validation plugin <validation-plugin>` that handles consent
    approval and dismissal. The appropriate links in the consent mail are
    generated for this page.

..  _finisher-config-storagePid:

..  confval:: storagePid

    :Required: false
    :type: integer
    :Default: :yaml:`0` (falls back to :typoscript:`plugin.tx_formconsent.persistence.storagePid`)

    Page ID where to store new consents. This is typically a folder in the
    page tree that exclusively contains form consents.

    ..  note::

        Set to :yaml:`0` to use the default storage PID configured in
        site set setting :yaml:`plugin.tx_formconsent.persistence.storagePid`
        or TypoScript configuration :typoscript:`plugin.tx_formconsent.persistence.storagePid`.
        See :ref:`site-set-configuration` and :ref:`typoscript-configuration`
        for more information.

..  _finisher-config-templateRootPaths:

..  confval:: templateRootPaths

    :Required: false
    :type: array

    Additional paths to template roots used to render Fluid templates
    for the consent mail.

    ..  note::

        Template root paths configured in form finishers take precedence over
        the ones configured within :ref:`site settings <site-set-configuration>`
        and :ref:`TypoScript <typoscript-configuration>`.

..  _finisher-config-partialRootPaths:

..  confval:: partialRootPaths

    :Required: false
    :type: array

    Additional paths to template partials used to render Fluid partials
    for the consent mail.

    ..  note::

        Partial root paths configured in form finishers take precedence over
        the ones configured within :ref:`site settings <site-set-configuration>`
        and :ref:`TypoScript <typoscript-configuration>`.

..  _finisher-config-layoutRootPaths:

..  confval:: layoutRootPaths

    :Required: false
    :type: array

    Additional paths to template layouts used to render Fluid layouts
    for the consent mail.

    ..  note::

        Layout root paths configured in form finishers take precedence over
        the ones configured within :ref:`site settings <site-set-configuration>`
        and :ref:`TypoScript <typoscript-configuration>`.
