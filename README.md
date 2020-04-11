# FormValidator (Beta)
Validate manually-provided data or  HTTP GET in PHP backend.



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



You can customize the default behavior of **sanitize()** as follows. All parameters are boolean and default is TRUE.

```php
sanitize($removeTags, $removeBackslash , $convertHtmlSpecialChars)
```

You can set FALSE to all/any of the parameters- 

```php
sanitize(true, false , true)
OR//
sanitize(false, false , true)
```



You can use the following rules instead of sanitize() -

- **removeTags()**

  ```php
  removeTags(mixed $allowableTags = null)
  ```

  You can use the optional parameter to specify tags which should not be removed. These are either given as string, or as of PHP 7.4.0, as array.

- **removeSlash()**

  ```php
  removeSlash()
  ```

- **convert()** 

  Convert special characters to HTML entities. 

  If you set `$convertDoubleQuote = false,` it'll not convert double quote symbol. 

  If you set `$convertSingleQuote= false,` it'll not convert single quote symbol. 

  ```php
  convert(bool $convertDoubleQuote = true, bool $convertSingleQuote = true)
  
  $beforeValidate = "<br> This is a break";
  $afterValidate = $fv->value($beforeValidate)->convert()->validate();
  $afterValidate = "&lt;br&gt; This is a break";
  
  $beforeValidate = "\"Double 'Single";
  $afterValidate = $fv->value($beforeValidate)->convert(true, false)->validate();
  $afterValidate = "&quot;Double 'Single";
  
  $beforeValidate = "\"Double 'Single";
  $afterValidate = $fv->value($beforeValidate)->convert(true, true)->validate();
  $afterValidate = "&quot;Double &#039;Single";    
  
  $beforeValidate = "\"Double 'Single";
  $afterValidate = $fv->value($beforeValidate)->convert(false, false)->validate();
  $afterValidate = ""Double 'Single";    
  ```



### Datatype Rules

Allow alphabets (A-Z, a-z) only

```php
asAlphabetic(bool $allowSpace)

$beforeValidate = "This is a sentence";
$afterValidate = $fv->value($beforeValidate)->asAlphabetic(false)->validate(); //Exception : White space not allowed.

$beforeValidate = "This_is_a_sentence";
$afterValidate = $fv->value($beforeValidate)->asAlphabetic(false)->validate(); //OK
$afterValidate = "This_is_a_sentence"; 

$beforeValidate = "This is a sentence";
$afterValidate = $fv->value($beforeValidate)->asAlphabetic(true)->validate();
$afterValidate = "This is a sentence"; 

$before = "This is sentence 1";
$after = $fv->value($before)->asAlphabetic(true)->validate(); //Exception : number not allowed.
```



Allow numbers (0-9) only

```php
asNumeric()

$before = "12345A";
$after = $fv->value($before)->asNumeric()->validate(); //Exception : Invalid number.
```



Allow alphabets (A-Z, a-z) and numbers (0-9), but not any special characters-

```
asAlphaNumeric(bool $allowSpace)
```



Allow integer only

```
asInteger()
```



```
asFloat()
```



```
asEmail()
```



```
asMobile()
```



```
asDate
```



### MISC

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

