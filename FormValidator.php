
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
        
        private $character_or_digit = "";

        #region construct and destruct
        public function __construct() {}

        public function __destruct(){}
        #endregion

        
        
        private function _reset_private_variables(){
            $this->label = "";
            unset($this->defaultValue);
            $this->required = false;
            unset($this->valueToValidate);
            $this->character_or_digit = "";
        }





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
         * @param bool $removeBackslash - whether remove backslashes or not
         * @param bool $convert - whether convert HTML special characters
         * 
         * @return this $this
         */
        public function sanitize($removeTags = true, $removeBackslash = true, $convertHtmlSpecialChars = true){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $valueToValidate = $this->valueToValidate;

                if($removeTags){
                    $valueToValidate = $this->_strip_tags($valueToValidate, null);
                }
    
                if($removeBackslash){
                    $valueToValidate = $this->_removeBackslash($valueToValidate);
                }
    
                if($convertHtmlSpecialChars){
                    $valueToValidate = $this->_htmlspecialchars($valueToValidate);
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
         * removeBackslash()
         * 
         * Remove the backslash (\) from a string.
         * Example: "how\'s going on?" = "how's going on?"
         * 
         */
        public function removeBackslash(){
            //The following cascading variables used for making the debugging easy.
            $valueToValidate = $this->valueToValidate ;
            $valueToValidate = $this->_removeBackslash($valueToValidate); 
            $this->valueToValidate = $valueToValidate;
            return $this;
        }

        private function _removeBackslash($valueToValidate){
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

        /*
            htmlentities — Convert all applicable characters to HTML entities.
            htmlspecialchars — Convert special characters to HTML entities.
            Source- https://stackoverflow.com/questions/46483/htmlentities-vs-htmlspecialchars/3614344
        */
        
        /*
            Convert special characters to HTML entities
            --------------------------example ----------------------------
            $new = htmlspecialchars("<a href='test'>Test</a>", ENT_QUOTES);
            echo $new; // &lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;
            ---------------------------------------------------------------
        */
        /**
         * convert()
         * 
         * Convert special characters to HTML entities
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
            $valueToValidate = $this->_htmlspecialchars($valueToValidate, $flag);  // Converts both double and single quotes
            $this->valueToValidate = $valueToValidate ;
            
            return $this;
        }

        //If you use this method,
        //you should use 'htmlspecialchars_decode()' to show back the data.
        private function _htmlspecialchars($valueToValidate, $flag = ENT_QUOTES){

            //htmlentities() vs. htmlspecialchars()
            //https://stackoverflow.com/questions/46483/htmlentities-vs-htmlspecialchars

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

        #region Required or Optional



        /**
         * @return $this
         * @throws FormValidationException
         */
        public function required(){
            $this->required = true;
            if(!isset($this->valueToValidate) || empty($this->valueToValidate)){
                throw new FormValidationException("{$this->label} required.");
            }
            return $this;
        }

        public function optional(){
            $this->required = false;
            return $this;
        }


        #endregion

        #region Check for data type

        /**
         * Check for alphabet character(s)
         * @param true/false
         * @return this
         * @throws FormValidationException
         */
        public function asAlphabetic($allow_space){
            $this->character_or_digit = "characters";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if($allow_space){
                    //if allow space, then remove spaces before applying ctype_alpha.
                    $temp = str_replace(" ", "", $this->valueToValidate);
                }
                else{
                    $temp = $this->valueToValidate;
                }

                if(!ctype_alpha($temp)){
                    throw new FormValidationException("{$this->label} must be alphabetic.");
                }
            }
            return $this;
        }

        /**
         * Check for alphanumeric character(s)
         * @param true/false
         * @return this
         * * @throws FormValidationException
         */
        public function asAlphaNumeric($allow_space){
            $this->character_or_digit = "characters";
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if($allow_space){
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
         * @return this
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
         * value can be "1.001" or 1.001
         * @return $this
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
         * Checks whether a mobile number is in valid format.
         * @return formatted mobile numbers with "880" prefix.
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

        public function asDate(){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if(!$this->_is_date_valid($this->valueToValidate)){
                    $msg = "{$this->label} is invalid. It must be a valid date in dd-mm-yyyy format.";
                    throw new FormValidationException($msg);
                }
                $this->valueToValidate =$this->_convert_string_to_date($this->valueToValidate);
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

        public function equalLength($equal_length){
            if(!empty($this->valueToValidate)){
                $length = strlen($this->valueToValidate);
                $label = $this->label;
                if($length != $equal_length){
                    $msg = "$label invalid. $equal_length  $this->character_or_digit required. Found $length  $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
        
            return $this;
        }

        public function minLength($minimum_length){
            if(!empty($this->valueToValidate)){
                $length = strlen($this->valueToValidate);
                $label = $this->label;
                if($length < $minimum_length){
                    $msg = "{$label} Invalid. Minimum {$minimum_length} {$this->character_or_digit} required. Found $length $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }

        public function maxLength($maximum_length){
            if(!empty($this->valueToValidate)){
                $length = strlen($this->valueToValidate);
                $label = $this->label;
                if($length > $maximum_length){
                    $msg = "Invalid {$label}. Maximum {$maximum_length} $this->character_or_digit allowed. Found $length $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }


       #endregion
       
        #region Range checking
        //If datatype is date, then convert into date using ConvertStringToDate() before passing as arguement.
        public function minValue($minimum_value){   
            if(!empty($this->valueToValidate)){
                $label = $this->label;
                if($this->valueToValidate < $minimum_value){
                    $msg = "$label must be equal to or greater than $minimum_value.";
                    throw new FormValidationException($msg);
                }
            }
           
            return $this;
        }
     
        //If datatype is date, then convert into date using ConvertStringToDate() before passing as arguement.
        public function maxValue($maximum_value){ 
            if(!empty($this->valueToValidate)){
                $label = $this->label;
                if($this->valueToValidate > $maximum_value){
                    $msg = "$label must be equal to or less than $maximum_value.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }
        #endregion



        private function _starts_with($string, $startString){ 
            $len = strlen($startString); 
            if(strlen($string) === 0){
                return false;
            }
            return (substr($string, 0, $len) === $startString); 
        } 
        
        public function startsWith($startString){ 
            $string = $this->valueToValidate;
            $label = $this->label;
            if(!$this->_starts_with($string,$startString)){
                $msg = "$label must starts with $startString.";
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

        function endsWith($endString){ 
            $string = $this->valueToValidate;
            if(!$this->_ends_with($string, $endString)){
                $msg = "$this->label must ends with $endString.";
                throw new FormValidationException($msg);
            }
            return $this;
        } 


        /**
         * @return validated value or default value.
         */
        public function validate(){
            if(!isset($this->valueToValidate) || empty($this->valueToValidate)){
                $this->valueToValidate = $this->defaultValue;
            }
          
            $temp = $this->valueToValidate;
            $this->_reset_private_variables();
            return $temp;
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


    } //<--class

?>