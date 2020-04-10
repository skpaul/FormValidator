
<?php 

    class FormValidationException extends Exception
    {
    }

    class FormValidator{
        private $title = "";
        private $default_value;
        private $required = false;
        private $data_value = null;
        private $character_or_digit = "";

        #region construct and destruct
        public function __construct() {}

        public function __destruct(){}
        #endregion

        private function _reset_private_variables(){
            $this->title = "";
            unset($this->default_value);
            $this->required = false;
            unset($this->valueToValidate);
            $this->character_or_digit = "";
        }
       
        //This must be the first method call.
        public function config($title, $defalt_value = null){
            $this->title = trim($title);
            $this->default_value = $defalt_value;
            return $this;
        }

        public function title($title){
            $this->title = trim($title);
            return $this;
        }

        /**
         * setDefault()
         * 
         * If the user input is optional, this method is required to set data for database table.
         * If the user input is mandatory, no need to use this method.
         * 
         * @return this. 
         */
        public function setDefault($defalt_value){
            $this->default_value = $defalt_value;
            return $this;
        }

        #region Receive value to validate

        //Receive value manually
        public function value($value){
            $this->valueToValidate = trim($value);
            return $this;
        }

        //Receive value from HTTP POST
        public function httpPost($httpPostFieldName, $isRequiredField=true){
            if($isRequiredField){
                if(!isset($_POST[$httpPostFieldName])){
                    throw new FormValidationException("{$this->title} required.");
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
                    throw new FormValidationException("{$this->title} required.");
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
        #endregion

        #region Normalize & Sanitize



        //Strip HTML and PHP tags from a string. (PHP 4, PHP 5, PHP 7)
        public function removeTags($allowable_tags = null){
            $this->_strip_tags($allowable_tags); 
            return $this;
        }

        private function _strip_tags($allowable_tags){
            if(isset($allowable_tags) && !empty($allowable_tags)){
                $this->valueToValidate = strip_tags($this->valueToValidate, $allowable_tags); 
            }
            else{
                $this->valueToValidate = strip_tags($this->valueToValidate); 
            }
        }


        /*
            Remove the backslash.
            The function stripslashes() will unescape characters that are escaped with a backslash, \.
            This function removes backslashes in a string.
            stripslashes("how\'s going on?") = how's going on?
        */
        public function removeBackslashes(){
            $this->_remove_slashes(); 
            return $this;
        }

        private function _remove_slashes(){
            /* 
                Example 

                $text="My dog don\\\\\\\\\\\\\\\\'t like the postman!";
                echo removeslashes($text);

                RESULT: My dog don't like the postman!
            */

            $temp = implode("", explode("\\", $this->valueToValidate));
            $this->valueToValidate = stripslashes(trim($temp));
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
        public function convert(){
            /*
                ENT_COMPAT	Will convert double-quotes and leave single-quotes alone.
                ENT_QUOTES	Will convert both double and single quotes.
                ENT_NOQUOTES	Will leave both double and single quotes unconverted.
            */
            $this->_htmlspecialchars($this->value, ENT_QUOTES);  // Converts both double and single quotes
            return $this;
        }

        
        private function _htmlspecialchars(){
            /*
                ENT_COMPAT	Will convert double-quotes and leave single-quotes alone.
                ENT_QUOTES	Will convert both double and single quotes.
                ENT_NOQUOTES	Will leave both double and single quotes unconverted.
            */
            $this->valueToValidate = htmlspecialchars($this->valueToValidate, ENT_QUOTES);  // Converts both double and single quotes
        }

        private function _normalize($RemoveTags, $RemoveBackSlashes, $Convert){
            if($RemoveTags){
                $this->_strip_tags(null);
            }

            if($RemoveBackSlashes){
                $this->_remove_slashes();
            }

            if($Convert){
                $this->_remove_slashes();
            }
        }

        public function normalize($RemoveTags = true, $RemoveBackSlashes = true, $Convert = true){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $this->_normalize($RemoveTags, $RemoveBackSlashes, $Convert);
            }
            return $this;
        }

        public function normalizeAll(){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $this->_normalize(true, true, true);
            }
            return $this;
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
                throw new FormValidationException("{$this->title} required.");
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
                    throw new FormValidationException("{$this->title} must be alphabetic.");
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
                    throw new FormValidationException("{$this->title} must be a-z/A-Z and/or 0-9.");
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
                    throw new FormValidationException("{$this->title} must be numeric.");
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
                    throw new FormValidationException("{$this->title} must be numeric.");
                }

                if($this->valueToValidate > PHP_INT_MAX ){
                    throw new FormValidationException("{$this->title} must be less than or equal to " . PHP_INT_MAX . ".");
                }

                if(!is_int(intval($this->valueToValidate))){
                    throw new FormValidationException("{$this->title} invalid.");
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
                        throw new FormValidationException("{$this->title} must be numeric.");
                    }

                    // if(!is_float(floatval($this->valueToValidate))){
                    //     throw new FormValidationException("{$this->title} must be numeric.");
                    // }
                }
                else{
                    if(!is_numeric($this->valueToValidate)){
                        throw new FormValidationException("{$this->title} must be numeric.");
                    }
                    // if(!is_int(intval($this->valueToValidate))){
                    //     throw new FormValidationException("{$this->title} must be numeric.");
                    // }
                }

                $this->valueToValidate = floatval($this->valueToValidate);
            }
            return $this;
        }
        
        public function asEmail(){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                $title = $this->title;
                if (!filter_var($this->valueToValidate, FILTER_VALIDATE_EMAIL)) {
                    throw new FormValidationException("{$this->title} invalid.");
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
                throw new FormValidationException("{$this->title} invalid.");
            }
        
            if(!is_numeric($MobileNumber)){
                throw new FormValidationException("{$this->title} invalid.");
            }
        
            if(strlen($MobileNumber)<10){
                throw new FormValidationException("{$this->title} invalid.");
            }
        
            $OperatorCodes = array( "013", "014", "015", "016", "017", "018", "019" );
            
            if($this->_starts_with($MobileNumber,"1")){
                //if the number is 1711781878, it's length must be 10 digits        
                if(strlen($MobileNumber) != 10){
                    throw new FormValidationException("{$this->title} invalid.");
                }
        
                $firstTwoDigits = substr($MobileNumber, 0, 2); //returns 17, 18 etc,
                $operatorCode = "0" . $firstTwoDigits; //Making first two digits a valid operator code with adding 0.
        
                if (!in_array($operatorCode, $OperatorCodes)) {
                    throw new FormValidationException("{$this->title} invalid.");
                }
        
                $finalNumberString = "880" . $MobileNumber;
               
                $this->valueToValidate = $finalNumberString;
                return $this;
            }
            
            if($this->_starts_with($MobileNumber,"01")){
                //if the number is 01711781878, it's length must be 11 digits        
                if(strlen($MobileNumber) != 11){
                    throw new FormValidationException("{$this->title} invalid.");
                }
        
                $operatorCode = substr($MobileNumber, 0, 3); //returns 017, 018 etc,
                
                if (!in_array($operatorCode, $OperatorCodes)) {
                    throw new FormValidationException("{$this->title} invalid.");
                }
        
                $finalNumberString = "88" . $MobileNumber;
                $this->valueToValidate = $finalNumberString;
                return $this;
            }
        
            if($this->_starts_with($MobileNumber,"8801")){
                //if the number is 8801711781878, it's length must be 13 digits    
                if(strlen($MobileNumber) != 13){
                    throw new FormValidationException("{$this->title} invalid.");
                }
        
                $operatorCode = substr($MobileNumber, 2, 3); //returns 017, 018 etc,
                
                if (!in_array($operatorCode, $OperatorCodes)) {
                    $this->is_valid = false;
                    return false;
                }        
        
               
                $this->valueToValidate = $MobileNumber;
                return $this;
            }
           
            throw new FormValidationException("{$this->title} invalid.");
        }

        public function asDate(){
            if(isset($this->valueToValidate) && !empty($this->valueToValidate)){
                if(!$this->_is_date_valid($this->valueToValidate)){
                    $msg = "{$this->title} is invalid. It must be a valid date in dd-mm-yyyy format.";
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
                $title = $this->title;
                if($length != $equal_length){
                    $msg = "$title invalid. $equal_length  $this->character_or_digit required. Found $length  $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
        
            return $this;
        }

        public function minLength($minimum_length){
            if(!empty($this->valueToValidate)){
                $length = strlen($this->valueToValidate);
                $title = $this->title;
                if($length < $minimum_length){
                    $msg = "{$title} Invalid. Minimum {$minimum_length} {$this->character_or_digit} required. Found $length $this->character_or_digit.";
                    throw new FormValidationException($msg);
                }
            }
            return $this;
        }

        public function maxLength($maximum_length){
            if(!empty($this->valueToValidate)){
                $length = strlen($this->valueToValidate);
                $title = $this->title;
                if($length > $maximum_length){
                    $msg = "Invalid {$title}. Maximum {$maximum_length} $this->character_or_digit allowed. Found $length $this->character_or_digit.";
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
                $title = $this->title;
                if($this->valueToValidate < $minimum_value){
                    $msg = "$title must be equal to or greater than $minimum_value.";
                    throw new FormValidationException($msg);
                }
            }
           
            return $this;
        }
     
        //If datatype is date, then convert into date using ConvertStringToDate() before passing as arguement.
        public function maxValue($maximum_value){ 
            if(!empty($this->valueToValidate)){
                $title = $this->title;
                if($this->valueToValidate > $maximum_value){
                    $msg = "$title must be equal to or less than $maximum_value.";
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
            $title = $this->title;
            if(!$this->_starts_with($string,$startString)){
                $msg = "$title must starts with $startString.";
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
                $msg = "$this->title must ends with $endString.";
                throw new FormValidationException($msg);
            }
            return $this;
        } 


        /**
         * @return validated value or default value.
         */
        public function validate(){
            if(!isset($this->valueToValidate) || empty($this->valueToValidate)){
                $this->valueToValidate = $this->default_value;
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