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

1.  Parameter Value & Metadata  : Takes value from the outside world along with few information.

      				2.  Sanitization                                 :  Makes the data clean and safe for further processing.
      				3.  Validation                                     : Performs various types of validation.

Let's see those one by one -



#### Parameter Value & Metadata

```php
require_once("FormValidator.php");
$fv = new FormValidator();
```

The simplest way to get started is -

```php
$beforeValidate = "Hello World";
$afterValidate = $fv->value($beforeValidate)->validate();
```

But in the above example, there is no validation rule actually.

Let's start to validate step by step.

In the following example, we'll check a **required** value by `required()` rule. If the variable is empty, this rule throws a FormValidationException.

```php
$beforeValidate = "";
$afterValidate = $fv->value($beforeValidate)->required()->validate(); //Exception
```



###### Make your data clean and safe- Data Sanitization

You can make your data clean and safe by using the **sanitize()** rule.

- It strips/removes HTML and PHP tags from the value

  ```php
  $beforeValidate = "<a href='test'>Test</a>";
  $afterValidate = $fv->value($beforeValidate)->sanitize()->validate();
  $afterValidate = "Test";
  ```

- It removes backslashes

  ```php
  $beforeValidate = "how\'s going on?";
  $afterValidate = $fv->value($beforeValidate)->sanitize()->validate();
  $afterValidate = "how's going on?";
  ```

- It translates the characters that have special meaning in HTML

  ```php
  $beforeValidate = "<a href='test'>Test</a>";
  $afterValidate = $fv->value($beforeValidate)->sanitize()->validate();
  $afterValidate = "&lt;a href='test'&gt;Test&lt;/a&gt;";
  ```



You can customize the default behavior of sanitize() as follows

```
sanitize($removeTags, $removeBackslash , $convertHtmlSpecialChars);
```

In the above example, 

But always remember to put a **validate()** at last.

You can also take parameter directly from HTTP GET/POST request - 

```php
$fv->httpGet($fieldName)->validate();  //HTTP GET
$fv->httpPost($fieldName)->validate(); //HTTP POST
```



You can mark the HTTP GET/POST field as required or optional - 

```php
$fv->httpPost($fieldName)->validate();         //default is required = true.
$fv->httpPost($fieldName, false)->validate();  //now it is optional.
```

if you mark the field as required, then that field name must be present in the **$_POST** or **$_GET** array.  Otherwise, you'll get a **FormValidationException**.



Let's see an example-

```php+HTML
<form>
  <input name="student_name" type="text">
</form>

<?php
$studentName = $fv->httpPost("student_name")->validate();  //OK.
$age         = $fv->httpPost("age")->validate();           //Throws Exception
?>

```



###### Label()

Label is the description of the parameter. Similar to HTML `<label></label>` tag. You should provide a label so that it can compose a meaningful message if validation fails.

```php
try {       
     $age = $fv->label("Student's Age")->httpPost("age")->validate(); //Exception         
} 
catch (FormValidationException $exp) {
      echo $exp->getMessage(); //"Student's Age required"
}
```



###### default()

If the user input is optional, this method is required to set data for database table.

 In the example below, there is no "**age**" field in the form. Therefore 0 has been set as default value for **student** database table.

```php
try {       
     $age = $fv->label("Student's Age")->httpPost("age", false)->default(0)->validate(); //Exception         
} 
catch (FormValidationException $exp) {
      echo $exp->getMessage(); //"Student's Age required"
}
```

If the user input is mandatory, no need to use default().

Please note that, there is no default order position for label(), httpPost()/httpGet() and default() method. You are free to interchange their position - 

```php
$fv->label("Age")->httpPost("age", false)->default(0)->validate();  //OK
$fv->httpPost("age", false)->default(0)->label("Age")->validate();  //OK
$fv->default(0)->label("Age")->httpPost("age", false)->validate();  //OK
```



###### required() and optional()

you can use **required()** before **sanitize()** or after or both.

```php
$form->value("abc")->required()->sanitize()->required()->validate();
```

