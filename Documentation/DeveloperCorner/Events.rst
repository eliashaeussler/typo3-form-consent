..  include:: /Includes.rst.txt

..  _events:

======
Events
======

Some :ref:`PSR-14 events <t3coreapi:EventDispatcher>` are dispatched while
consents are generated or modified. This allows to step in the consent
lifecycle and perform several actions, based on the actual event.

The following events are currently dispatched:

..  _approve-consent-event:

ApproveConsentEvent
===================

This event is dispatched if a consent was approved by the user. This is
especially useful when running additional actions that provide a concrete
response to be rendered. The response can be attached to the dispatched
event and will be handled by the dispatching controller.

..  _dismiss-consent-event:

DismissConsentEvent
===================

This event is dispatched if a consent was dismissed by the user. This is
especially useful when running additional actions that provide a concrete
response to be rendered. The response can be attached to the dispatched
event and will be handled by the dispatching controller.

..  _generate-hash-event:

GenerateHashEvent
=================

When the :php:`HashService` generates a new consent validation hash, it
dispatches this event. It can be used to either attach a ready-to-use
validation hash or modify the hash components used to internally generate
the final hash.

..  _modify-consent-event:

ModifyConsentEvent
==================

This event is dispatched after a new consent was generated, which is
not yet persisted. It allows to modify the consent while having access
to the current :php:`FormRuntime`.

..  _modify-consent-mail-event:

ModifyConsentMailEvent
======================

Once the consent is persisted, the consent mail is sent to the user. It
can be modified by listening to this event, which provides the mail as
an instance of :php:`FluidEmail` along with the current :php:`FormRuntime`.

..  seealso::

    View the sources on GitHub:

    -   `ApproveConsentEvent <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Event/ApproveConsentEvent.php>`__
    -   `DismissConsentEvent <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Event/DismissConsentEvent.php>`__
    -   `GenerateHashEvent <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Event/GenerateHashEvent.php>`__
    -   `ModifyConsentEvent <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Event/ModifyConsentEvent.php>`__
    -   `ModifyConsentMailEvent <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Event/ModifyConsentMailEvent.php>`__
