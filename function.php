<?php
$GLOBALS['larghezza']=6;//da A a Z quindi RANGE[1-26]
$GLOBALS['lunghezza']=10;
$GLOBALS['svuotaDb'] = false;

function svuotaDb(){
    $conn = dbConnect();
    mysqli_autocommit($conn, true);
    try {
        //locko la tabella dei posti così nessun utente può visionare l'aereo
        if(!$result = mysqli_query($conn,'SELECT * from seats limit 1000 FOR UPDATE;'))
                throw new Exception('lock select');

        if(!$result = mysqli_query($conn, 'Select * from airplane where id=1 limit 1 for update;') )
            throw new Exception('select');
        $riga = mysqli_fetch_array($result);
        if($riga['Larghezza']!=$GLOBALS['larghezza'] || $riga['Lunghezza']!=$GLOBALS['lunghezza']){
            if(!$result = mysqli_query($conn, 'DELETE FROM seats;') )
                throw new Exception('truncate');
            if(!$result = mysqli_query($conn, 'UPDATE airplane SET Larghezza='.$GLOBALS['larghezza'].', Lunghezza='.$GLOBALS['lunghezza'].' WHERE Id=1;') )
                throw new Exception('update');
        } 
        mysqli_autocommit($conn, true);   
        mysqli_close($conn); 
    }catch (Exception $e) {
        //altrimenti dici di ritentare dopo        
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);
        mysqli_close($conn);                     
        echo "Si è verificato un problema, riprova più tardi";
        //echo $e->getMessage();//debug
        exit();
    }  
}

// sanitize input string
function sanitizeString($var) {
	$var = strip_tags($var);
	$var = htmlentities($var);
	$var = stripcslashes($var);    
    return $var;
}

function dbConnect(){
    if(!$conn = mysqli_connect('localhost', 's259760', 'inessavy', 's259760')){//per il DB al Labinf
    //if(!$conn = mysqli_connect('localhost', 'root', '', 'seatreservation')){//per il DB in locale     
        if(mysqli_connect_error()){
            echo 'ERRORE SITO AIR COMPANY: '.mysqli_connect_error(); 
            echo '<br> Il sito è momentaneamente non disponibile, riprova più tardi'; 
            exit();
        }
    }
    return $conn;
}

function redirect($msg='', $new_location){
    if(!empty($msg)){
        $_SESSION['msg'] = $msg;
    }    
    header("HTTP/1.1 303 See Other");
    header('Location: '.$new_location);
    exit;
}

function checkHTTPS(){
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'){
      //allora è già abilitato HTTPS  
    }
    else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='off'){
        echo "Abilita HTTPS nel BROWSER";
    }
    else{
        $redirect = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$redirect);
        exit();
    }
}

function checkInattivita(){
    //session_start(); la sessione la prendo da pjt_action.php
    $t=time();
    $diff=0;
    $new=false;
    if(isset($_SESSION['time'])){
        $t0=$_SESSION['time'];
        $diff=($t-$t0);        
    }
    else{
        $new=true;
    }
    if($new || $diff > 120){//timeout di 120 secondi
        $_SESSION=array();
        if(ini_get("session.use_cookies")){
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time()-3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        return 1;
    }
    else{
        $_SESSION['time']=time();
        return 0;        
    }
}

function createTable(){
    $seat_libero=0;
    $seat_venduto=0;
    $seat_prenotato=0;

    $larghezza = $GLOBALS['larghezza'];
    $lunghezza = $GLOBALS['lunghezza'];
    $array_letters = range('A', 'Z');
    //-------------------------------//
    //per svuotare il DB(posti riservati) se cambia larghezza o lunghezza
    if($GLOBALS['svuotaDb'] == true)
        svuotaDb();
    //-------------------------------//
    $conn = dbConnect();

    if($result = mysqli_query($conn, 'SELECT * FROM seats LIMIT 1000;') ){
        $i_tabella=0;
        while ($riga = mysqli_fetch_array($result)){//Mi fetcho tutta la tabella   
        $tabella[$i_tabella] = $riga;    
        $i_tabella++;
        }
        $lenght = $i_tabella;
    } else {
        die("Query SELECT non riuscita");        
    }
    mysqli_close($conn);   
    echo '<table class="plane_table" id="myTable" >';

    for ($i = 1; $i <= $lunghezza; $i++) {
        echo "<tr>";
        for ($j = 0; $j < $larghezza; $j++) {
        //---------------------------------------------//
        $find = false;      
            for($i_tabella=0; $i_tabella<$lenght; $i_tabella++){
                //echo $i.$array_letters[$j];            
                if( $tabella[$i_tabella]['Id'] == $i.$array_letters[$j] ){              
                    $find = true;
                    break;                
                }            
            }
            if($find){      
            if($tabella[$i_tabella]["Status"]=='seat_venduto')
                $seat_venduto++;
            if($tabella[$i_tabella]["Status"]=='seat_prenotato')
                $seat_prenotato++;                       
            //Faccio il controllo se ho prenotato in passato dei posti
            if ( isset($_SESSION['uname']) && $tabella[$i_tabella]["Status"]=='seat_prenotato' && $tabella[$i_tabella]["Account"] == $_SESSION["uname"] )
                $tabella[$i_tabella]['Status'] = 'seat_temp_prenotato';         
            }
            else{
                $seat_libero++;      
                $tabella[$i_tabella]['Status'] = 'seat_libero'; 
            }       
            //---------------------------------------------//
            echo '<td id="'. $i . $array_letters[$j] .'" class="'. $tabella[$i_tabella]['Status'] .'">'
                    . $i . $array_letters[$j]

                .'</td>';
            unset($riga);
        }
        echo "</tr>";
    }
    echo "</table>"; 
    //per mostrare il numero dei posti attuali a video
    if(!isset($_SESSION['uname']) ){
        echo "Posti liberi : ".$seat_libero.'<br>';
        echo "Posti prenotati : ".$seat_prenotato.'<br>';
        echo "Posti acquistati : ".$seat_venduto.'<br>';
    }

}

function testCookiePHP(){    
    //-----------------------------------------//
    //verifico se sono abilitati i cookies
    //-----------------------------------------//
    if (isset($_COOKIE['testcookie'])) {
        //se è stato settato allora elimino il cookie
        //setcookie('testcookie', '1', time()-3600*24);
        //echo "cookies enabled";//allora il sito può funzionare correttamente
    } else {
        if (isset($_REQUEST['test_cookies'])) {
        echo "I cookies sono disabilitati, senza di essi il sito non è accessibile";
        exit();
        } else {
        setcookie("testcookie", "1", time() + (86400 * 30), "/"); //questo cookie è settato per 30 giorni
        header('Location: '.$_SERVER['PHP_SELF'].'?test_cookies=1');
        }
    }
    //-----------------------------------------//
}

?>