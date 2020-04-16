<?php
    require_once("FormValidator.php");
    
      $form = new FormValidator();
     try {
        if(!ctype_alnum("df$")){
            echo "no ok";
        }
        else{
            echo "ok";
        }
      
     } catch (FormValidationException $exp) {
         echo $exp->getMessage();
     }
    //$data = strip_tags("");

    // $new = htmlspecialchars("<a href='test'>'Test</a>",ENT_QUOTES );
    // $new = htmlspecialchars("<br> Here",ENT_QUOTES );

    // echo $new; // &lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;
    // echo "<br>";
    // $new = htmlspecialchars($new, ENT_QUOTES);
    // echo $new;
    // //echo htmlentities("&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;" ) ;

    // echo htmlspecialchars_decode($new);
    // //var_dump($data);

    
?>