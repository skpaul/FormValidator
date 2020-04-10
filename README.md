# FormValidator
Validate HTTP Post in PHP backend.

## What it does?

1. It takes a value
2. Then normalize and neutralize the value. Remove html tags, Remove back slashes and 
3. Checks whether a value is required or optional
4. check whether datatype is valid (i.e. integer or date)
5. checks whether data format is valid (i.e. yyyy-mm-dd or dd-mm-yyyy)
6. checks whether range is valid (i.e. minimum or maximum value)
7. Checks whether length is valid (i.e. maximum 10 characters)
8. If any validation fails, throws `FormValidationException`
9. If everything is fine, returns the value.

