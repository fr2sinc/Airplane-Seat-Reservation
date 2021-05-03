<?php
include('function.php');
checkHTTPS();
//testCookiePHP();
session_start();
if (!isset($_SESSION['uname'])) {
  redirect('', 'index.php');
}
?>
<!DOCTYPE html>

<head>
  <link rel="stylesheet" type="text/css" href="pjt.css">
  <script type="text/javascript" src="jquery-3.4.1.min.js"></script>
  <script type="text/javascript" src="function.js"></script>
</head>

<body id="body" onload=<?php
              if (isset($_SESSION['uname']) && !empty($_SESSION['uname'])) {
                echo '"myLoad()"';
              }
              ?>>
  <header>
    <div class="block_header">
      <h3 class="text_header">Air Company</h3>
    </div>
  </header>

  <nav class="navbar">
    <div>
      <h4 class="text_font_black">
        <?php
        if (isset($_SESSION['uname']) && !empty($_SESSION['uname'])) {
          echo $_SESSION['uname'];
        }
        ?>
      </h4>
    </div>
    <div class="navbar_button_container">
      <!-- submit invia i dati del form-->
      <form action="pjt_action.php" method="post">
        <button class="navbar_button" type="submit" name="action" value="logout">Esci</button>
      </form>
    </div>
  </nav>


  <div class="plane">
    <h1 class="text_font">Seleziona i posti che preferisci</h1>
    <?php
    createTable();
    ?>

    <!-- submit invia i dati del form-->
    <div class="acquista_aggiorna_container">
      <div class="acquista_aggiorna_button_container">
        <form method="post" id="buySeats">
          <button class="acquista_aggiorna_button" type="button" onclick="send_seat_temp_prenotato()">Acquista posti prenotati</button>
        </form>
      </div>
      <div class="acquista_aggiorna_button_container">
        <button class="acquista_aggiorna_button" type="button" onclick="window.location.href=window.location.href">Aggiorna</button>
      </div>
      <h4 id="message" class="imput_message_error">
        <?php
        if (isset($_SESSION['msg'])) {
          $msg = $_SESSION['msg'];
          $msg = sanitizeString($msg);
          echo  $msg;
          unset($_SESSION['msg']);
        }
        ?>
      </h4>

    </div>
  </div>
  <noscript>
    Javascrit non è abilitato, il sito non funzionerà correttamente
  </noscript>
</body>

</html>