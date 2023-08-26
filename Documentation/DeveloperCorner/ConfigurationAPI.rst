..  include:: /Includes.rst.txt

..  _configuration-api:

=================
Configuration API
=================

In order to access the :ref:`extension configuration <extension-configuration>`,
a slim PHP API exists. Each configuration option is accessible by
an appropriate class method.

..  php:namespace:: EliasHaeussler\Typo3FormConsent\Configuration

..  php:class:: Configuration

    API to access all available extension configuration options.

    ..  php:method:: getExcludedElementsFromPersistence()

        Get all form element types that are excluded from persistence.

        :returntype: :php:`list<string>`

..  seealso::

    View the sources on GitHub:

    -   `Configuration <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Configuration/Configuration.php>`__
