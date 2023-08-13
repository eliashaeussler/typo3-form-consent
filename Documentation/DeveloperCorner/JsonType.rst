..  include:: /Includes.rst.txt

..  _json-type:

=========
JSON type
=========

The extension provides a custom type to persist arbitrary form
values as JSON data to the database. This allows to dynamically
persist values in a relatively easy and maintainable way, since
the provided type implements TYPO3 core API. The resulting values
are actually JSON-encoded strings.

..  php:namespace:: EliasHaeussler\Typo3FormConsent\Type

..  php:class:: JsonType

    Type implementing TYPO3's :php:interface:`TYPO3\\CMS\\Core\\Type\\TypeInterface`
    that accepts and stores JSON-encoded strings.

    ..  php:staticmethod:: fromArray($data)

        Create a new JSON type from the given array.

        :param array $data: Array to persist, will be JSON-encoded
        :returntype: EliasHaeussler\\Typo3FormConsent\\Type\\JsonType

    ..  php:method:: toArray()

        Return current JSON-encoded string as parsed array.

        :returntype: array

..  _type-transformers:

Type transformers
=================

Type transformers describe a way how specific data can be modified
so that persistence of its value into the database is assured. All
returned types are JSON types.

..  php:namespace:: EliasHaeussler\Typo3FormConsent\Type\Transformer

..  php:interface:: TypeTransformer

    Interface used to transform a given value to a persistable JSON type.

    ..  php:method:: transform($formRuntime)

        Transform a specific form value from the given form runtime to a
        persistable JSON type.

        :param TYPO3\\CMS\\Form\\Domain\\Runtime\\FormRuntime $formRuntime: Form runtime from which to extract values
        :returntype: EliasHaeussler\\Typo3FormConsent\\Type\\JsonType

The extension ships with two type transformers:

-   :php:class:`EliasHaeussler\\Typo3FormConsent\\Type\\Transformer\\FormRequestTypeTransformer`:
    Used to transform the current form request parameters to JSON
    type in order to allow resubmission of the given form
-   :php:class:`EliasHaeussler\\Typo3FormConsent\\Type\\Transformer\\FormValuesTypeTransformer`:
    Used to transform all submitted form values to JSON type in
    order to store them next to the generated form consent

..  seealso::
    View the sources on GitHub:

    -   `JsonType <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Type/JsonType.php>`__
    -   `TypeTransformer <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Type/Transformer/TypeTransformer.php>`__
    -   `FormRequestTypeTransformer <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Type/Transformer/FormRequestTypeTransformer.php>`__
    -   `FormValuesTypeTransformer <https://github.com/eliashaeussler/typo3-form-consent/blob/main/Classes/Type/Transformer/FormValuesTypeTransformer.php>`__
