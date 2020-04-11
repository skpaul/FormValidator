
<?php 

    class FormValidationException extends Exception
    {
    }

    class FormValidator{
        private $label = "";

        /**
         * Here we keep the value we going to be validated.
         *
         * @var mix $valueToValidate
         */
        private $valueToValidate;

        /**
         * Here we keep the default value of the data.
         *
         * @var mix $defaultValue
         */
        private $defaultValue;

        private $required = false;
        
        /**
         * @var string $character_or_digit
         * 
         * It's value can be either "digits" or "characters".
         * 
         * This variable is required to compose a meaningful message while throwing FormValidationException.
         */
        private $character_or_digit = "";

        #region construct and destruct
        public function __construct() {}

        public function __destruct(){}
        #endregion

        


        #region Receive value to validate

        /**
         * Sets the description of the value.
         * Similar to HTML <label></label> tag.
         * 
         * Example- 'Customer Name' or 'Date of Birth'
         *        
         * It is required to compose a meaningful message if validation fails.
         *
         * @param string $label
         *
         * @return this
         */
        public function label($label){
            $this->label = trim($label);
            return $this;
        }

        //Receive value manually
        public function value($value){
            $this->valueToValidate = trim($value);
            return $this;
        }

        //Receive value from HTTP POST
        public function httpPost($httpPostFieldName, $isRequiredField=true){
            if($isRequiredField){
                if(!isset($_POST[$httpPostFieldName])){
                    throw new FormValidationException("{$this->label} required.");
                }
               
                $this->valueToValidate = trim($_POST[$httpPostFieldName]);
            }
            else{
                //This field is not required. But if the value sets, return that.
                if(isset($_POST[$httpPostFieldName])){
                    $value = trim($_POST[$httpPostFieldName]);
                    $this->valueToValidate = $value;
                }
            }

            return $this;
        }
     
        //Receive value from HTTP GET
        public function httpGet($httpGetFieldName, $isRequiredField=true){
            if($isRequiredField){
                if(!isset($_GET[$httpGetFieldName])){
                    throw new FormValidationException("{$this->label} required.");
                }
               
                $this->valueToValidate = trim($_GET[$httpGetFieldName]);
            }
            else{
                //This field is not required. But if the value sets, return that.
                if(isset($_GET[$httpGetFieldName])){
                    $this->valueToValidate = trim($_GET[$httpGetFieldName]);
                }
            }

            return $this;
        }

        /**
         * default()
         * 
         * If the user input is optional, this method is required to set data for database table.
         * If the user input is mandatory, no need to use this method.
         * 
         * @param mix $defaultValue
         * 
         * @return this. 
         */
        public function default($defaultValue){
            $this->defaultValue = $defaultValue;
            return $this;
        }
        #endregion

        #region Sanitize

        /**
         * sanitize()
         * 
         * It removes HTML & JavaScript tags, backslashes(\) and HTML special characters
         * 
         * @param bool $removeTags - whether remove tags or not
         * @param bool $removeSlash - whether remove backslashes or not
         * @param bool $convert - whether convert HTML special characters
         * 
         * @return this $this
         */
        public function sanitize($removeTags = true, $removeSlash = true, $convertHtmlSpecialChars = true){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $valueToValidate = $this->valueToValidate;

                if($removeTags){
                    $valueToValidate = $this->_strip_tags($valueToValidate, null);
                }
    
                if($removeSlash){
                    $valueToValidate = $this->_removeSlash($valueToValidate);
                }
    
                if($convertHtmlSpecialChars){
                    $valueToValidate = $this->_convert($valueToValidate);
                }

                $this->valueToValidate = $valueToValidate ;
            }
            return $this;
        }

        /**
         * removeTags()
         * 
         * Remove HTML and PHP tags from a string.
         * 
         * You can use the optional parameter to specify tags which should not be removed. 
         * These are either given as string, or as of PHP 7.4.0, as array.
         * 
         * @param mixed $allowableTags
         * 
         * @return this $this
         */
        public function removeTags($allowableTags = null){
            $this->valueToValidate = $this->_strip_tags($this->valueToValidate, $allowableTags); 
            return $this;
        }

        //Called from removeTags() and sanitize()
        private function _strip_tags($valueToValidate, $allowableTags){
            //strip_tags() - Strip HTML and PHP tags from a string

            if(isset($allowableTags) && !empty($allowableTags)){
                $valueToValidate = strip_tags($valueToValidate, $allowableTags); 
            }
            else{
                $valueToValidate = strip_tags($valueToValidate); 
            }

            return $valueToValidate;
        }

        /**
         * removeSlash()
         * 
         * Remove the backslash (\) from a string.
         * Example: "how\'s going on?" = "how's going on?"
         * 
         */
        public function removeSlash(){
            //The following cascading variables used for making the debugging easy.
            $valueToValidate = $this->valueToValidate ;
            $valueToValidate = $this->_removeSlash($valueToValidate); 
            $this->valueToValidate = $valueToValidate;
            return $this;
        }

        private function _removeSlash($valueToValidate){
            /* 
                Example 
                $text="My dog don\\\\\\\\\\\\\\\\'t like the postman!";
                echo removeslashes($text);
                RESULT: My dog don't like the postman!
            */

            $temp = implode("", explode("\\", $valueToValidate));
            $valueToValidate = stripslashes(trim($temp));
            return $valueToValidate;
        }

        /**
         * convert()
         * 
         * Convert special characters to HTML entities
         * 
         * Example: htmlspecialchars("<br> Here") = &lt;br&gt; Here
         * 
         * @param bool $convertDoubleQuote - whether convert double quote
         * @param bool $convertSingleQuote - whether convert single quote
         */
        public function convert($convertDoubleQuote, $convertSingleQuote){

            $flag = ENT_QUOTES; //ENT_QUOTES	Will convert both double and single quotes.

            if($convertDoubleQuote && !$convertSingleQuote){
                $flag = ENT_COMPAT;
            }
            elseif(!$convertDoubleQuote && !$convertSingleQuote){
                $flag = ENT_NOQUOTES;
            }
            else{
                $flag = ENT_QUOTES;
            }

            /*
                ENT_COMPAT	Will convert double-quotes and leave single-quotes alone.
                ENT_QUOTES	Will convert both double and single quotes.
                ENT_NOQUOTES	Will leave both double and single quotes unconverted.
            */

            $valueToValidate = $this->valueToValidate;
            $valueToValidate = $this->_convert($valueToValidate, $flag);  // Converts both double and single quotes
            $this->valueToValidate = $valueToValidate ;
            
            return $this;
        }

        //If you use this method,
        //you should use 'htmlspecialchars_decode()' to show back the data.
        private function _convert($valueToValidate, $flag = ENT_QUOTES){

             /*
                htmlentities — Convert all applicable characters to HTML entities.
                htmlspecialchars — Convert special characters to HTML entities.
                Source- https://stackoverflow.com/questions/46483/htmlentities-vs-htmlspecialchars/3614344
            */

            //However, if you also have additional characters that are Unicode or uncommon symbols in your text then you should use htmlentities() to ensure they show up properly in your HTML page.

            /*
                ENT_COMPAT	Will convert double-quotes and leave single-quotes alone.
                ENT_QUOTES	Will convert both double and single quotes.
                ENT_NOQUOTES	Will leave both double and single quotes unconverted.
            */
            $valueToValidate = htmlspecialchars($valueToValidate, $flag); 

            //There is a bug, therefore use that function twice
            $valueToValidate = htmlspecialchars($valueToValidate, $flag); 

            return $valueToValidate;
        }
        #endregion

        #region Required and Optional

        /**
         * required()
         * 
         * Checks whether current value is required or optional.
         * 
         * @return $this
         * 
         * @throws FormValidationException
         */
        public function required(){
            $this->required = true;
            if(!isset($this->valueToValidate) || empty($this->valueToValidate)){
                throw new FormValidationException("{$this->label} required.");
            }
            return $this;
        }

        /**
         * optional()
         * 
         * The opposite of required()
         * This method is not required to call.
         * Because the value is optional by default.
         * 
         * @return this @this
         */
        public function optional(){
            $this->required = false;
            return $this;
        }


        #endregion

        #region Check for data type

        /**
         * asAlphabetic()
         * 
         * Check for alphabet character(s).
         * It allows only A-Z/a-z.
         * 
         * @param bool @allowSpace - sets whether allow space in the value.
         * 
         * @return this $this
         * 
         * @throws FormValidationException
         */
        public function asAlphabetic($allowSpace){
            $this->character_or_digit = "characters";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if($allowSpace){
                    //if allow space, then remove spaces before applying ctype_alpha.
                    $temp = str_replace(" ", "", $this->valueToValidate);
                }
                else{
                    if($this->_hasWhitespace($this->valueToValidate)){
                        throw new FormValidationException("{$this->label} can not have blank space.");
                    }
                    $temp = $this->valueToValidate;
                }

                if(!ctype_alpha($temp)){
                    throw new FormValidationException("{$this->label} must be alphabetic.");
                }
            }
            return $this;
        }


        /**
         * Checks string for whitespace characters.
         *
         * @param string $text
         *   The string to test.
         * @return bool
         *   TRUE if any character creates some sort of whitespace; otherwise, FALSE.
         */
        private function _hasWhitespace( $text )
        {
            for ( $idx = 0; $idx < strlen( $text ); $idx += 1 )
                if ( ctype_space( $text[ $idx ] ) )
                    return TRUE;

            return FALSE;
        }

        /**
         * asAlphaNumeric()
         * 
         * Check for alpha-numeric character(s).
         * It allows only A-Z, a-z and 0-9.
         * 
         * @param boolean $allowSpace
         * @return this $this
         * @throws FormValidationException
         */
        public function asAlphaNumeric($allowSpace){
            $this->character_or_digit = "characters";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if($allowSpace){
                    //if allow space, then remove spaces before applying ctype_alpha.
                    $temp = str_replace(" ", "", $this->valueToValidate);
                }
                else{
                    $temp = $this->valueToValidate;
                }

                if(!ctype_alnum($temp)){
                    throw new FormValidationException("{$this->label} must be a-z/A-Z and/or 0-9.");
                }
            }
            
            return $this;
        }
        
        /**
         * asNumeric()
         * 
         * @return this $this
         * 
         * @throws FormValidationException
         */
        public function asNumeric(){
            $this->character_or_digit = "digits";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if(!is_numeric($this->valueToValidate)){
                    throw new FormValidationException("{$this->label} must be numeric.");
                }
            }
            
            return $this;
        }

        /**
         * value can be "1001" or 1001
         * 
         * @return this $this
         * 
         * @throws FormValidationException
         */
        public function asInteger(){
            $this->character_or_digit = "digits";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if(!is_numeric($this->valueToValidate)){
                    throw new FormValidationException("{$this->label} must be numeric.");
                }

                if($this->valueToValidate > PHP_INT_MAX ){
                    throw new FormValidationException("{$this->label} must be less than or equal to " . PHP_INT_MAX . ".");
                }

                if(!is_int(intval($this->valueToValidate))){
                    throw new FormValidationException("{$this->label} invalid.");
                }

                $this->valueToValidate = intval($this->valueToValidate);
            }
            
            return $this;
        }

       
        /**
         * value can be "1.001" or 1.001
         * @return $this
         * @throws FormValidationException
         */
        public function asFloat(){
             //check whether has a decimal point.
            //if has a decimal point, then check it with is_float().
            //if no decimal point, then check it with is_int().
            //finally return with floatval.

            $this->character_or_digit = "digits";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if($this->_has_decimal($this->valueToValidate)){

                    /**
                        function is_decimal( $val )
                        {
                            return is_numeric( $val ) && floor( $val ) != $val;
                        }
                    */
                    if(!is_numeric($this->valueToValidate)){
                        throw new FormValidationException("{$this->label} must be numeric.");
                    }

                    // if(!is_float(floatval($this->valueToValidate))){
                    //     throw new FormValidationException("{$this->label} must be numeric.");
                    // }
                }
                else{
                    if(!is_numeric($this->valueToValidate)){
                        throw new FormValidationException("{$this->label} must be numeric.");
                    }
                    // if(!is_int(intval($this->valueToValidate))){
                    //     throw new FormValidationException("{$this->label} must be numeric.");
                    // }
                }

                $this->valueToValidate = floatval($this->valueToValidate);
            }
            return $this;
        }
        
                //It counts the digits after a decimal point.
        //i.e. 
        private function _count_decimal_value($required_digits){
            $arr = explode('.', strval($this->valueToValidate));
            if(strlen($arr[1]) == $required_digits){
                return true;
            }
            else{
                return false;
            }
        }
    
        private function _has_decimal($number){
           $count = substr_count(strval($number), '.');
           if($count == 1){
               return true;
           }
           else{
               return false;
           }
        }

        public function asEmail(){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $label = $this->label;
                if (!filter_var($this->valueToValidate, FILTER_VALIDATE_EMAIL)) {
                    throw new FormValidationException("{$this->label} invalid.");
                }
            }
            return $this;
        }

        /**
         * asMobile()
         * 
         * Checks whether a mobile number is valid.
         * It produces a valid mobile mobile with "880" prefix.
         * 
         * @return this $this
         * @throws FormValidationException.
         */
        public function asMobile(){
            $MobileNumber = $this->valueToValidate;
           
            if(empty($MobileNumber)){
                throw new FormValidationException("{$this->label} invalid.");
            }
        
            if(!is_numeric($MobileNumber)){
                throw new FormValidationException("{$this->label} invalid.");
            }
        
            if(strlen($MobileNumber)<10){
                throw new FormValidationException("{$this->label} invalid.");
            }
        
            $OperatorCodes = array( "013", "014", "015", "016", "017", "018", "019" );
            
            if($this->_starts_with($MobileNumber,"1")){
                //if the number is 1711781878, it's length must be 10 digits        
                if(strlen($MobileNumber) != 10){
                    throw new FormValidationException("{$this->label} invalid.");
                }
        
                $firstTwoDigits = substr($MobileNumber, 0, 2); //returns 17, 18 etc,
                $operatorCode = "0" . $firstTwoDigits; //Making first two digits a valid operator code with adding 0.
        
                if (!in_array($operatorCode, $OperatorCodes)) {
                    throw new FormValidationException("{$this->label} invalid.");
                }
        
                $finalNumberString = "880" . $MobileNumber;
               
                $this->valueToValidate = $finalNumberString;
                return $this;
            }
            
            if($this->_starts_with($MobileNumber,"01")){
                //if the number is 01711781878, it's length must be 11 digits        
                if(strlen($MobileNumber) != 11){
                    throw new FormValidationException("{$this->label} invalid.");
                }
        
                $operatorCode = substr($MobileNumber, 0, 3); //returns 017, 018 etc,
                
                if (!in_array($operatorCode, $OperatorCodes)) {
                    throw new FormValidationException("{$this->label} invalid.");
                }
        
                $finalNumberString = "88" . $MobileNumber;
                $this->valueToValidate = $finalNumberString;
                return $this;
            }
        
            if($this->_starts_with($MobileNumber,"8801")){
                //if the number is 8801711781878, it's length must be 13 digits    
                if(strlen($MobileNumber) != 13){
                    throw new FormValidationException("{$this->label} invalid.");
                }
        
                $operatorCode = substr($MobileNumber, 2, 3); //returns 017, 018 etc,
                
                if (!in_array($operatorCode, $OperatorCodes)) {
                    $this->is_valid = false;
                    return false;
                }        
        
               
                $this->valueToValidate = $MobileNumber;
                return $this;
            }
           
            throw new FormValidationException("{$this->label} invalid.");
        }

        /**
         * asDate()
         * 
         * Checks whether the value is a valid date/datetime
         * Convert the value as datetime object.
         * 
         * @param string $datetimeZone Default is "Asia/Dhaka".
         * @throws FormatValidationException if the value is invalid.
         * 
         * @return this $this
         */
        public function asDate($datetimeZone = "Asia/Dhaka"){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                try {
                    $valueToValidate = $this->valueToValidate; //make it debug-friendly with xdebug.
                    $valueToValidate = new Datetime($valueToValidate, new DatetimeZone($datetimeZone));
                    $this->valueToValidate = $valueToValidate;
                } catch (Exception $exp) {
                    throw new FormValidationException("Invalid date.");
                }
                // if(!$this->_is_date_valid($this->valueToValidate)){
                //     $msg = "{$this->label} is invalid. It must be a valid date in dd-mm-yyyy format.";
                //     throw new FormValidationException($msg);
                // }
                // $this->valueToValidate =$this->_convert_string_to_date($this->valueToValidate);
            }
            return $this;
        }
      
        private function _is_date_valid($date_string){
            //$date_string = '23-11-2010';

            $matches = array();
            $pattern = '/^([0-9]{1,2})\\-([0-9]{1,2})\\-([0-9]{4})$/';
            if (!preg_match($pattern, $date_string, $matches)) return false;
            if (!checkdate($matches[2], $matches[1], $matches[3])) return false;
            return true;

            // $test_arr  = explode('-', $date_string);
            // if (count($test_arr) == 3) {
            //     //checkdate ( int $month , int $day , int $year ) : bool
            //     if (checkdate($test_arr[1], $test_arr[0], $test_arr[2])) {
            //         return true;
            //     } else {
            //         false;
            //     }
            // }
            // else{
            //     return false;
            // }
        }

        private function _convert_string_to_date($DateString){
            $date =  date("Y-m-d", strtotime($DateString));
            return $date;
        }


        #endregion
        
        #region Length checking

        /**
         * equalLength()
         * 
         * Checks whether the value has the specified length.
         * 
         * @param int $length
         * @return this $this
         * @throws FormValidationException
         */
        public function equalLength($length){
            if(!empty($this->valueToValidate)){
                $_length = strlen($this->valueToValidate);
                $label = $this->label;
                if($_length != $length){
                    $msg = "$label invalid. $length  $this->character_or_digit required. Found $_length  $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
        
            return $this;
        }

        /**
         * minLength()
         * 
         * Checks whether the value has the minimum specified length.
         * 
         * @param int $length
         * @return this $this
         * @throws FormValidationException
         */
        public function minLength($length){
            if(!empty($this->valueToValidate)){
                $_length = strlen($this->valueToValidate);
                $label = $this->label;
                if($_length < $length){
                    $msg = "{$label} Invalid. Minimum {$length} {$this->character_or_digit} required. Found $_length $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }

        /**
         * maxLength()
         * 
         * Checks whether the value has the maximum specified length.
         * 
         * @param int $length
         * @return this $this
         * @throws FormValidationException
         */
        public function maxLength($length){
            if(!empty($this->valueToValidate)){
                $_length = strlen($this->valueToValidate);
                $label = $this->label;
                if($_length > $length){
                    $msg = "Invalid {$label}. Maximum {$length} $this->character_or_digit allowed. Found $_length $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }


       #endregion
       
        #region Range checking
    
        
        /**
         * minValue()
         * 
         * Checks whether the value has the minimum specified value.
         * If datatype is date, then convert into date before passing as arguement.
         * 
         * @param int $minimumValue
         * @return this $this
         * @throws FormValidationException
         */
        public function minValue($minimumValue){   
            if(!empty($this->valueToValidate)){
                $label = $this->label;
                if($this->valueToValidate < $minimumValue){
                    $msg = "$label must be equal to or greater than $minimumValue.";
                    throw new FormValidationException($msg);
                }
            }
           
            return $this;
        }
     
        /**
         * maxValue()
         * 
         * Checks whether the value has the minimum specified value.
         * If datatype is date, then convert into date before passing as arguement.
         * 
         * @param int $minimumValue
         * @return this $this
         * @throws FormValidationException
         */
        public function maxValue($maximumValue){ 
            if(!empty($this->valueToValidate)){
                $label = $this->label;
                if($this->valueToValidate > $maximumValue){
                    $msg = "$label must be equal to or less than $maximumValue.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }
        #endregion

        public function startsWith($startString){ 
            $string = $this->valueToValidate;
            $label = $this->label;
            if(!$this->_starts_with($string,$startString)){
                $msg = "$label must starts with $startString.";
                throw new FormValidationException($msg);
            }
            return $this;
        } 

        private function _starts_with($string, $startString){ 
            $len = strlen($startString); 
            if(strlen($string) === 0){
                return false;
            }
            return (substr($string, 0, $len) === $startString); 
        } 
        
        function endsWith($endString){ 
            $string = $this->valueToValidate;
            if(!$this->_ends_with($string, $endString)){
                $msg = "$this->label must ends with $endString.";
                throw new FormValidationException($msg);
            }
            return $this;
        } 

        private function _ends_with($string, $endString){ 
            $len = strlen($endString); 
            if(strlen($string) === 0){
                return false;
            }
            return (substr($string, -$len) === $endString); 
        } 

        /**
         * validate()
         * 
         * This must be the final call.
         * 
         * @return mix $valueToValidate Value or default value.
         */
        public function validate(){
            if(!isset($this->valueToValidate) || empty($this->valueToValidate)){
                $this->valueToValidate = $this->defaultValue;
            }
          
            $temp = $this->valueToValidate;
            $this->_reset_private_variables();
            return $temp;
        }

                
        private function _reset_private_variables(){
            $this->label = "";
            unset($this->defaultValue);
            $this->required = false;
            unset($this->valueToValidate);
            $this->character_or_digit = "";
        }




    } //<--class

?>