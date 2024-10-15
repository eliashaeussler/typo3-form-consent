..  include:: /Includes.rst.txt

..  _site-set-configuration:

=====================
SiteSet configuration
=====================

..  versionadded:: 2.3.0

    `Feature #259 – Add support for site sets (TYPO3 >= v13.1) <https://github.com/eliashaeussler/typo3-form-consent/pull/259>`__

The extension ships with a :ref:`site set <t3coreapi:site-sets>` called
:yaml:`eliashaeussler/typo3-form-consent`. It can be used as drop-in replacement
for :ref:`TypoScript <typoscript-configuration>` in TYPO3 v13.1 or later.

The following settings are available for the site set:

..  _site-set-settings:

Settings
========

..  _site-set-settings-view.templateRootPath:

..  confval:: view.templateRootPath
    :name: siteset-view-templateRootPath
    :type: string
    :Path: :yaml:`plugin.tx_formconsent`

    Additional path to template root used in Frontend context. Within this
    path, Fluid templates of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-templateRootPaths>`.

..  _site-set-settings-view.partialRootPath:

..  confval:: view.partialRootPath
    :name: siteset-view-partialRootPath
    :type: string
    :Path: :yaml:`plugin.tx_formconsent`

    Additional path to template partials used in Frontend context. Within this
    path, Fluid partials of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-partialRootPaths>`.

..  _site-set-settings-view.layoutRootPath:

..  confval:: view.layoutRootPath
    :name: siteset-view-layoutRootPath
    :type: string
    :Path: :yaml:`plugin.tx_formconsent`

    Additional path to template layouts used in Frontend context. Within this
    path, Fluid layouts of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-layoutRootPaths>`.

..  _site-set-settings-persistence.storagePid:

..  confval:: persistence.storagePid
    :name: siteset-persistence-storagePid
    :type: integer
    :Path: :yaml:`plugin.tx_formconsent`

    Page ID where to store new consents created by the
    :ref:`consent finisher <consent-finisher>`.

    ..  note::

        This configuration option can be overridden in each form configuration.
        See :ref:`form-finisher-configuration` for more information.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-persistence-storagePid>`.
