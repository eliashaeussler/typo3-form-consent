..  include:: /Includes.rst.txt

..  _extension-configuration:

=======================
Extension configuration
=======================

The extension currently provides the following configuration options:

..  _extconf-limit:

..  confval:: excludedElements

    :Path: :typoscript:`persistence.excludedElements`
    :type: string (comma-separated list)
    :Default: `Honeypot`

    Contains all form element types that should be excluded from persistence.
    When configured, the :ref:`consent finisher <consent-finisher>` automatically
    excludes all form elements matching the configured type from being persisted.
