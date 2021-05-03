<?php
include('function.php');
checkHTTPS();
//testCookiePHP();
session_start();
if (isset($_SESSION['uname'])) {
  redirect('', 'index_personal.php');
}
?>
<!DOCTYPE html>

<head>
  <link rel="stylesheet" type="text/css" href="pjt.css">
  <script type="text/javascript" src="function.js"></script>
</head>

<body id="body" onload="checkCookie()">
  <header>
    <div class="block_header">
      <h3 class="text_header">Air Company</h3>
    </div>
  </header>

  <nav class="navbar">
    <div class="navbar_button_container">
      <button class="navbar_button" type="button" onclick="window.location = 'index.php'">Home</button>
    </div>
  </nav>
  <form action="pjt_action.php" method="POST" class="box_registration">
    <h3 class="text_font" style="text-align: center;">Registrati ad Air Company</h3>
    <div>
      <h4 id="message" class="imput_message_error">
        <?php        
        if (isset($_SESSION['msg'])) {
          $msg = $_SESSION['msg'];
          $msg = sanitizeString($msg);
          echo  $msg;
          unset($_SESSION['msg']);
        }
        ?>
        <br></h4>
    </div>
    <div>
      <div class="input_container">
        <input class="input_box" type="email" id="txt_uname" name="username" placeholder="Indirizzo Email" required>
      </div>
      <div class="input_container">
        <input class="input_box" type="password" id="txt_pwd" name="password" placeholder="Password" pattern="^(?:(?=.*[a-z])(?=.*([A-Z]|\d)).*)$" title="la Password deve contenere almeno un carattere alfabetico minuscolo, ed almeno un altro carattere che sia alfabetico maiuscolo o un carattere numerico" required>
      </div>
      <div class="input_container">
        <!-- submit invia i dati del form-->
        <input class="submit_button" type="submit" name="action" value="Iscriviti" id="but_submit">
      </div>
    </div>
  </form>

  <noscript>
    Javascrit non è abilitato, il sito non funzionerà correttamente
  </noscript>
</body>

</html>