## File Abstraction Layer

ExtBase does not provide user upload support with their new FAL setup. Instead we've rolled our own:
* Works with latest FAL
* Works with extbase property mapper for single or collection properties. Errors are applied to the appropriate property.
* Does real validation of size and mime type.
* Saves uploaded files if there are other errors on the form, so users don't have to re-upload if the form fails.

Refer to the class comments on the  [FileReferenceConverter](../Classes/Property/TypeConverter/FileReferenceConverter.php) for specific details.

[back to docs](.)