# FormValidator (Beta)
Validate HTTP GET/POST and any raw value.



## What it does?

1. Sanitizes (make it clean and safe) the data
2. Checks whether a value is required or optional
3. Checks whether datatype is valid i.e. integer or date
4. Checks whether data format is valid i.e. yyyy-mm-dd or dd-mm-yyyy
5. Checks whether range is valid i.e. minimum or maximum value
6. Checks whether length is valid i.e. maximum 10 characters
7. If any validation fails, throws `FormValidationException`
8. If everything is fine, returns the value.



## Installation

- Download the repository

- Add *require_once("FormValidator.php")* in your script.

  

## Let's get started

Create a new instance of *FormValidator* class-

```php
$fv = new FormValidator();
```

The simplest way to start a validation is -

```php
$rawValue = "Hello World";
$validatedValue = $fv->value($rawValue)->validate();
```

But in the above example, there is no validation rule actually.

Let's start to validate step by step.

###### required() rule

If the *$value* is empty, this rule throws a *FormValidationException*.

```php
$value = "";

//FormValidationException
$afterValidate = $fv->value($beforeValidate)->required()->validate();
```

You should always use a try .. catch to trap the FormValidationException-

```php
try {
   $value = "";
   $fv->value($beforeValidate)->required()->validate(); 
} 
catch (FormValidationException $fvExp) {
   echo $exp->getMessage();
}
```



###### Data Sanitization

You can make your data clean and safe by using the **sanitize()** rule. This rule is a shorthand of *removeTags()*, *removeSlash()* and *convert()* rules.

**Syntax:**

```php
//All parameters are TRUE by default
sanitize(bool $removeTags, bool $removeBackslash, bool $convertHtmlSpecialChars)
```

The **sanitize()** rule does the following -

- It strips/removes HTML and PHP tags from the `$string`

  ```php
  $string = "<a href='test'>Test</a>";
  $afterValidate = $fv->value($string)->sanitize()->validate();
  echo $afterValidate; //Test
  ```

- It removes *backslashes* from `$beforeValidate`

  ```php
  $beforeValidate = "how\'s going on?";
  $afterValidate = $fv->value($beforeValidate)->sanitize()->validate();
  echo $afterValidate; //how's going on?
  ```

- It translates the characters that have special meaning in HTML from `$beforeValidate`

  ```php
  $beforeValidate = "<a href='test'>Test</a>";
  $afterValidate = $fv->value($beforeValidate)->sanitize()->validate();
  echo $afterValidate; //&lt;a href='test'&gt;Test&lt;/a&gt;
  ```

You can use **sanitize()** before, after or both of any other rule -

```php
->required()->sanitize()->
//OR
->sanitize()->required()->
//OR
required()->sanitize()->required()->
```

You can customize the default behavior of **sanitize()** as follows - 

- If `$removeTags = FALSE`, sanitize() will not remove any tags
- If `$removeSlash = FALSE`, sanitize() will not remove any backslashes
- If `$convertHtmlSpecialChars= FALSE`, sanitize() will not convert any HTML characters.





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

- **asAlphabetic()** - Allow alphabets (A-Z, a-z) only

  ```php
  //Syntax
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

  

- **asNumeric()** - Allow numbers (0-9) only

```php
//Syntax
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



### Rules for Length validation

##### equalLength() 

Checks whether the value has the specified length.

```
//Syntax
equalLength(integer $length)

$before = "Bangladesh";
$after = $fv->value($before)->equalLength(5)->validate(); //Exception : Length must be equal to 5 characters.
```

##### minLength() 

Checks whether the value has the specified minimum length.

```
//Syntax
minLength(integer $length)

$before = "Bangladesh";
$after = $fv->value($before)->minLength(20)->validate(); //Exception : Length must be equal to or greater than 20 characters.
```



##### maxLength() 

Checks whether the value has the specified maximum length.

```
//Syntax
maxLength(integer $length)

$before = "Bangladesh";
$after = $fv->value($before)->maxLength(2)->validate(); //Exception : Length must be equal to or less than 2 characters.
```



### Rules for value range validation

##### equalValue() 

Checks whether the value has the specified length.

```
//Syntax
equalValue(mixed $value)

$raw = "Bangladesh";
$validated = $fv->value($before)->equalValue("Bangla")->validate(); //Exception : Must be equal to Bangla.
```



##### minValue() 

Checks whether the value has the minimum specified value.

```
//Syntax
minValue(mixed $value)

$before = 10;
$after = $fv->value($before)->minValue(2)->validate(); //Exception : Must be equal to or greater than 10.
```



##### maxValue() 

Checks whether the value has the specified maximum length.

```
//Syntax
maxValue(mixed $value)

$before  = new DateTime("12-12-2021", new DateTimeZone("Asia/Dhaka"));
$maxDate = new DateTime("12-12-2020", new DateTimeZone("Asia/Dhaka"));
$after = $fv->value($before)->maxValue($maxDate)->validate(); //Exception : Must be equal to or less than 12-12-2020.
```



### Directly get value from HTTP GET/POST

You can take parameter directly from HTTP GET/POST request - 

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

   //Throws exception, because "age" field does not exist
   $age = $fv->httpPost("age")->validate();    //FormValidationException      
?>

```



### Label()

Label is the description of the parameter. Similar to HTML `<label></label>` tag. You should provide a label so that it can compose a meaningful message if validation fails.

```php
//FormValidationException 
//"Student's Age required"
$age = $fv->label("Student's Age")->httpPost("age")->validate();

//Now it becomes "Person's Age required"
$age = $fv->label("Person's Age")->httpPost("age")->validate();

```



### default()

If the user input is optional, this method is required to set data for database table.

In the example below, there is no "**age**" field in the form. Therefore 0 has been set as default value for **student** database table.

```php
$age = $fv->label("Student's Age")->httpPost("age", false)->default(0)->validate();
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

