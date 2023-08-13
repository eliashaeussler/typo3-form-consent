..  include:: /Includes.rst.txt

..  _typoscript-configuration:

========================
TypoScript configuration
========================

The following global TypoScript configuration is available:

..  _typoscript-constants:

Constants
=========

..  _typoscript-constants-view.templateRootPath:

..  confval:: view.templateRootPath

    :type: string
    :Path: :typoscript:`plugin.tx_formconsent`

    Additional path to template root used in Frontend context. Within this
    path, Fluid templates of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-templateRootPaths>`.

..  _typoscript-constants-view.partialRootPath:

..  confval:: view.partialRootPath

    :type: string
    :Path: :typoscript:`plugin.tx_formconsent`

    Additional path to template partials used in Frontend context. Within this
    path, Fluid partials of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-partialRootPaths>`.

..  _typoscript-constants-view.layoutRootPath:

..  confval:: view.layoutRootPath

    :type: string
    :Path: :typoscript:`plugin.tx_formconsent`

    Additional path to template layouts used in Frontend context. Within this
    path, Fluid layouts of the consent mail sent by the
    :ref:`consent finisher <consent-finisher>` as well as templates of the
    :ref:`validation plugin <validation-plugin>` can be overwritten.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-view-layoutRootPaths>`.

..  _typoscript-constants-persistence.storagePid:

..  confval:: persistence.storagePid

    :type: integer
    :Path: :typoscript:`plugin.tx_formconsent`

    Page ID where to store new consents created by the
    :ref:`consent finisher <consent-finisher>`.

    ..  note::

        This configuration option can be overridden in each form configuration.
        See :ref:`form-finisher-configuration` for more information.

    ..  seealso::

        Read more in the :ref:`official documentation <t3tsref:setup-plugin-persistence-storagePid>`.
