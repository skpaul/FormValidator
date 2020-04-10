# FormValidator (Beta)
Validate HTTP Post in PHP backend.



## What it does?

1. It takes a value
2. Sanitizes (make it clean and safe) the data i.e. removes HTML & JavaScript tags, backslashes and HTML special characters.
3. Checks whether a value is required or optional
4. Checks whether datatype is valid i.e. integer or date
5. Checks whether data format is valid i.e. yyyy-mm-dd or dd-mm-yyyy
6. Checks whether range is valid i.e. minimum or maximum value
7. Checks whether length is valid i.e. maximum 10 characters
8. If any validation fails, throws `FormValidationException`
9. If everything is fine, returns the value.



## First thing is first

It has mainly three parts

				1.  Input Value & Metadata: Takes input from the outside world and few information about the value.
   				2.  Sanitizing                         :  Make the data clean and safe for further processing.
   				3.  Validation                         : Performs various types of validation.

Let's see those one by one -



#### Validating Value & Metadata

```php
require_once("FormValidator.php");
$fv = new FormValidator();
```

It's pretty simple-

```php
$fv->value($value);
```

You can also take parameter directly from HTTP GET/POST - 

```php
$fv->httpGet($fieldName);  //HTTP GET
$fv->httpPost($fieldName); //HTTP POST
```

You can mark the HTTP GET/POST field as required or optional - 

```php
$fv->httpPost("student_name"); //default is required = true.

//make it optional
$fv->httpPost("student_name", false); //now it is optional.
```

if you mark the field as required, then that field must be present in the **$_POST** or **$_GET** array.  Otherwise, you'll get a **FormValidationException**.

```html
<form>
  <input name="student_name" type="text">
</form>
```





###### required() and optional()

you can use **required()** before **sanitize()** or after or both.

```php
$form->value("abc")->required()->sanitize()->required()->validate();
```

