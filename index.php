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
      <button class="navbar_button" type="button" onclick="window.location = 'registration.php'">Registrati</button>
    </div>
    <div class="navbar_button_container">
      <button class="navbar_button" type="button" onclick="window.location = 'login.php'">Accedi</button>
    </div>
  </nav>
  <h4>
    <?php
    if (isset($_SESSION['msg'])) {
      $msg = $_SESSION['msg'];
      $msg = sanitizeString($msg);
      echo  $msg;
      unset($_SESSION['msg']);
    }
    ?>
  </h4>
  <div class="plane">
    <div class="plane">
      <h1 class="text_font">Mappa dei posti</h1>
    </div>

    <?php
    createTable();
    ?>

  </div>
  <noscript>
    Javascrit non è abilitato, il sito non funzionerà correttamente
  </noscript>
</body>

</html>