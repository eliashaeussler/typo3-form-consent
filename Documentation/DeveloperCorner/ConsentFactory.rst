..  include:: /Includes.rst.txt

..  _consent-factory:

===============
Consent factory
===============

New consents are normally built by the :ref:`consent finisher <consent-finisher>`,
based on the current :php:`FormRuntime`. In order to better separate
the actual consent building process from the form submission, a
dedicated consent factory is provided.

..  php:namespace:: EliasHaeussler\Typo3FormConsent\Domain\Factory

..  php:class:: ConsentFactory

    Factory class to create new consents from a given form.

    ..  php:method:: createFromForm($finisherOptions, $finisherContext)

        Create a new form consent from the given form, derived from the
        given finisher options.

        ..  tip::

            This method's result can be modified by listening on the
            :ref:`modify-consent-event`.

        :param EliasHaeussler\Typo3FormConsent\Domain\Finishers\FinisherOptions $finisherOptions: Consent finisher options of the current form
        :param TYPO3\CMS\Form\Domain\Finishers\FinisherContext $finisherContext: The current finisher context
        :returntype: EliasHaeussler\\Typo3FormConsent\\Domain\\Model\\Consent

..  seealso::
    View the sources on GitHub:

    -   `ConsentFactory <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Domain/Factory/ConsentFactory.php>`__
