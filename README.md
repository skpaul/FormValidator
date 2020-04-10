# FormValidator
Validate HTTP Post in PHP backend.

## What it does?

1. It takes a value
2. Sanitizes (make it clean and safe) the data by removing html & JavaScript tags, backslashes and HTML special characters. Configurable.
3. Checks whether a value is required or optional
4. Checks whether datatype is valid i.e. integer or date
5. Checks whether data format is valid i.e. yyyy-mm-dd or dd-mm-yyyy
6. Checks whether range is valid i.e. minimum or maximum value
7. Checks whether length is valid i.e. maximum 10 characters
8. If any validation fails, throws `FormValidationException`
9. If everything is fine, returns the value.

