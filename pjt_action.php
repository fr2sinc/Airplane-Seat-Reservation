<?php
include ('function.php');
checkHTTPS();
if(!isset($_POST['action']) || empty($_POST['action']) )
    redirect('', 'index.php');
//------------------------//
//DA QUI TUTTE LE FUNZIONI RECUPERANO LA SESSIONE
//inoltre ho eliminaato la session_start(); anche da checkInattivita()
session_start();
//------------------------//
if(isset($_POST['action']) && !empty($_POST['action'])) {  
    $action = $_POST['action'];
    if(isset($_SESSION['uname']) && !empty($_SESSION['uname'])){
        switch($action) {
            case 'clickSeat': 
                if(checkInattivita() == 0){
                    clickSeat();
                } 
                else{//allora è scaduto il timeout e deve riloggarsi
                    $return['result'] = -2;                
                    $return['msg'] = "Sei stato per diverso tempo inattivo, rieffettua il login";                
                    echo json_encode($return);
                }            
                break;

            case 'buySeat': 
                if(checkInattivita() == 1){
                    redirect('', 'login.php');
                }
                else
                    buySeat(); 
                break;
            case 'logout': logout(); break;
            default : redirect('', 'index_personal.php'); break;
        }
    }
    else{
        switch($action) {
            case 'Accedi' : login(); break;
            case 'Iscriviti': signin(); break;
            case 'logout': logout(); break;
            default : redirect('', 'index.php'); break;
        }
    }

}

function logout(){
    
    if(ini_get("session.use_cookies")){
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    redirect('',"index.php");    
}
function login() {
    
    $conn = dbConnect();
    $no_mysqli_real_escape_uname = $_POST['username'];
    $password = $_POST['password'];
    
    if ( filter_var($no_mysqli_real_escape_uname, FILTER_VALIDATE_EMAIL) && preg_match("/^(?:(?=.*[a-z])(?=.*([A-Z]|\d)).*)$/", $password ) ){        
        //sanifico l'username
        $no_mysqli_real_escape_uname = sanitizeString($no_mysqli_real_escape_uname);
        $uname = mysqli_real_escape_string($conn, $no_mysqli_real_escape_uname);        
        //aggiungo il sale e faccio l'hash
        $uname_split = explode('.', $uname);
        $hash_password = md5($password.$uname_split[0].'xXG24yK91'.$uname_split[1]);

        $sql_query = 'SELECT count(*) as cntUser FROM accounts WHERE Username="'.$uname.'" and Password="'.$hash_password.'";';

        if($result = mysqli_query($conn,$sql_query) ){//se la query è andata a buon fine
            $row = mysqli_fetch_array($result);        

            $count = $row['cntUser'];
            mysqli_close($conn);
            
            if($count > 0){ 
                $_SESSION['time'] = time();               
                $_SESSION['uname'] = $no_mysqli_real_escape_uname;               
                redirect('', 'index_personal.php');
            }else{
                $msg = "Email o Password non valida";               
                redirect($msg, 'login.php');   
            }
        } else{//se la query non è andata buon fine            
            mysqli_close($conn);
            $msg = "Si è verificato un problema, riprova più tardi";             
            redirect($msg, 'login.php');
        }                            
    }
    else{//non vado a fare richiesta al db
        $msg = "Email o Password non valida";     
        redirect($msg, 'login.php'); 
    }
}

function signin(){
    
    $conn = dbConnect();  
    $no_mysqli_real_escape_uname = $_POST['username'];
    $password = $_POST['password'];
    
    if (filter_var($no_mysqli_real_escape_uname, FILTER_VALIDATE_EMAIL) && preg_match("/^(?:(?=.*[a-z])(?=.*([A-Z]|\d)).*)$/", $password ) ){       
        //sanifico l'username
        $no_mysqli_real_escape_uname = sanitizeString($no_mysqli_real_escape_uname);
        $uname = mysqli_real_escape_string($conn, $no_mysqli_real_escape_uname);
        //aggiungo il sale e faccio l'hash
        $uname_split = explode('.', $uname);
        $hash_password = md5($password.$uname_split[0].'xXG24yK91'.$uname_split[1]);

        try {
            mysqli_autocommit($conn, false);       
            //locko interamente la tabella
            if(!$result = mysqli_query($conn,'SELECT * from accounts limit 1000 FOR UPDATE;'))
                throw new Exception('Si è verificato un problema, riprova più tardi');

            if( !$result = mysqli_query($conn, 'SELECT count(*) as cntUser FROM accounts WHERE Username="'.$uname.'";'))
                throw new Exception('');
            
            $row = mysqli_fetch_array($result);        
            $count = $row['cntUser'];       
            
                if($count == 0){ //allora non c'è nessun utente con quello username e puoi inserirlo

                    if(!$result = mysqli_query($conn, 'INSERT INTO accounts VALUES ("'. $uname .'", "'. $hash_password .'");') )
                        throw new Exception('');
                    
                    mysqli_autocommit($conn, true);              
                    mysqli_close($conn);              //se la query va a buon fine 
                    $_SESSION['time'] = time();       //setto tempo e username
                    $_SESSION['uname'] = $no_mysqli_real_escape_uname;                   
                    redirect('', 'index_personal.php');        

                }else{                
                    mysqli_autocommit($conn, true);            
                    mysqli_close($conn);              
                    redirect("Username già presente", 'registration.php');
                }  
            
        }catch (Exception $e) {
            //altrimenti dici di ritentare dopo        
            mysqli_rollback($conn);
            mysqli_autocommit($conn, true);
            mysqli_close($conn);                     
            redirect("Si è verificato un problema, riprova più tardi", 'registration.php');
        }     
                  
    }else{    
        mysqli_close($conn);       
        redirect("Email o Password non valida", 'registration.php'); 
    }
        
}

function postoValido($id){
    $match='';
    if(preg_match("/^([1-9][0-9]*)([A-Z])$/", $id, $match)){
        $array_letters = range('A', 'Z');        
        $max_lettera = $array_letters[ $GLOBALS['larghezza'] -1 ];     
        //ord() trasforma la lettera in numero, per effettuare i controlli di range
        if( ($match[1] >= 1 && $match[1] <= $GLOBALS['lunghezza'] ) && ord($match[2]) >= ord('A')  && ord($match[2]) <= ord($max_lettera) ){
            return true;
        }else
            return false;       
    }else
        return false;        
}

function clickSeat() {
    
    $conn = dbConnect();
    $id_seat = sanitizeString($_POST['id_seat']);
    $id_seat = mysqli_real_escape_string($conn, $id_seat);
    
    //testo l'id_seat se valido con una regular expression
    if (postoValido($id_seat) && isset($_SESSION['uname'])){        
        
        try{   
            mysqli_autocommit($conn, false); 
            //locko interamente la tabella
            if(!$result = mysqli_query($conn,'SELECT * from seats limit 1000 FOR UPDATE;'))
                throw new Exception('Si è verificato un problema, riprova più tardi');

            if(!$result = mysqli_query($conn,'SELECT count(*) as cntId FROM seats WHERE Id="'.$id_seat.'";'))
                throw new Exception('');
            //sleep(10);//debug per testare la concorrenza
            $row = mysqli_fetch_array($result);
            $count = $row['cntId'];            
                
                if($count == 0 ){//significa che il posto è libero e può essere quindi creato nel db                   
                    if(!$result = mysqli_query($conn,'INSERT into seats VALUES ("'.$id_seat.'","'.$_SESSION['uname'].'", "seat_prenotato");') )
                        throw new Exception('');
                    clickReturn(1, 'seat_temp_prenotato', 'Posto '.$id_seat.' prenotato correttamente', $id_seat, $conn);
                    
                }else{//significa che il posto è prenotato o venduto  
                    
                    if(!$result = mysqli_query($conn,'SELECT count(*) as cntId FROM seats WHERE Id="'.$id_seat.'" and Status="seat_venduto";'))
                        throw new Exception('');
                
                    $row = mysqli_fetch_array($result);
                    $count = $row['cntId'];        
                
                    if($count > 0){//significa che il posto è venduto                                             
                        clickReturn(1, 'seat_venduto', 'Posto '.$id_seat.' non disponibile', $id_seat, $conn);
                        
                    }else{//significa che il posto è prenotato

                        if(!$result = mysqli_query($conn,'SELECT count(*) as cntId FROM seats WHERE Id="'.$id_seat.'" and Status="seat_prenotato" and Account="'.$_SESSION['uname'].'";'))
                            throw new Exception('');
                
                        $row = mysqli_fetch_array($result);
                        $count = $row['cntId'];        
                    
                        if($count > 0){//significa che il posto è prenotato da lui
                            if(!$result = mysqli_query($conn,'DELETE  FROM seats WHERE Id="'.$id_seat.'";'))
                                throw new Exception('');                                         
                            clickReturn(1, 'seat_libero', 'Posto '.$id_seat.' liberato correttamente', $id_seat, $conn);
                            
                        }else{//significa che il posto è prenotato da altri, allora lo prenota per lui                
                            if(!$result = mysqli_query($conn,'UPDATE seats SET Account="'.$_SESSION['uname'].'" WHERE Id="'.$id_seat.'";'))
                                throw new Exception('');                                         
                            clickReturn(1, 'seat_temp_prenotato', 'Posto '.$id_seat.' prenotato correttamente', $id_seat, $conn);                            
                        }                       
                    }                  
                }              
             
            }catch (Exception $e) {            
            mysqli_rollback($conn);
            mysqli_autocommit($conn, true);
            //altrimenti dici di ritentare dopo                
            clickReturn(0, 'error', 'Si è verificato un problema, riprova più tardi', $id_seat, $conn);        
        }          
    }
    else{
        clickReturn(0, 'error', 'utente malevolo', $id_seat, $conn);  
    }
}

function clickReturn($value, $status, $msg, $id_seat, $conn){    
    mysqli_autocommit($conn, true);      
    mysqli_close($conn);
    $return = array();
    $return['result'] = $value;
    $return['status'] = $status;     
    $return['msg'] = $msg;
    $return['id_seat'] = $id_seat;
    echo json_encode($return);
}

function buySeat(){  
    
    if(!isset($_SESSION['uname']))   
        redirect('', 'index.php');

    $id_seats = json_decode($_POST['data']);
    $prenotati_da_altri = array(); 
    $prenotati_da_lui = array(); 
    $liberi = array(); 

    $conn = dbConnect();    
    
    try{
        mysqli_autocommit($conn, false);

        $fail = false; 
        //controllo se arrivano input strani dall'esterno
        if(sizeof($id_seats)<= 0)
            throw new Exception('Utente malevolo');

        if(!$result = mysqli_query($conn,'SELECT * from seats limit 1000 FOR UPDATE;'))
            throw new Exception('Si è verificato un problema, riprova più tardi');
        //sleep(10);//debug per mostrare la concorrenza tra prenotazione e acquisto 14.5.2019 ora funziona come un orologio svizzero
        $i_tabella=0;
        while ($riga = mysqli_fetch_array($result)){//Mi fetcho tutta la tabella   
            $tabella[$i_tabella] = $riga;    
            $i_tabella++;
        }
        $lenght = $i_tabella;   

        for($i = 0; $i<sizeof($id_seats); $i++){
            //sanifico ogni id ricevuto e controllo se realmente contiene un posto(esempio: "1A") con la regular expression
            $id_seats[$i] = sanitizeString($id_seats[$i]);
            $id_seats[$i] = mysqli_real_escape_string($conn, $id_seats[$i]);                        
            if ( !postoValido($id_seats[$i]) )
                throw new Exception('Utente malevolo');       
            
            $find = false;
            $error = false;   
            for($i_tabella=0; $i_tabella<$lenght; $i_tabella++){            
                if( $tabella[$i_tabella]['Id'] == $id_seats[$i] && $tabella[$i_tabella]['Account'] == $_SESSION['uname'] && $tabella[$i_tabella]['Status'] =='seat_prenotato'){              
                    $find = true;
                    $error = false;//trova il posto, è prenotato da lui, quindi lo può acquistare
                    array_push($prenotati_da_lui, $id_seats[$i]);
                    break;                
                }
                else if ($tabella[$i_tabella]['Id'] == $id_seats[$i] && ($tabella[$i_tabella]['Account'] != $_SESSION['uname'] || $tabella[$i_tabella]['Status'] =='seat_venduto') ){
                    $find = true;
                    $error = true;//trova il posto ma non è prenotato da lui o è già venduto a qualcuno, allora non deve andare a buon fine
                    break;  
                }            
            }            
            if($find == false){//allora il posto è libero e può essere venduto correttamente                
                array_push($liberi, $id_seats[$i]);//mi salvo tutti i posti richiesti dall'utente che sono liberi
            }
            else if($find == true && $error == true){
                $fail = true;
                array_push($prenotati_da_altri, $id_seats[$i]);//mi salvo tutti i posti richiesti dall'utente che sono o venduti o non prenotati da lui
            }
        }
        if($fail==false){        
            for($i=0; $i<sizeof($prenotati_da_lui); $i++){
                if(!$result = mysqli_query($conn,'UPDATE seats SET Status="seat_venduto" WHERE Id="'.$prenotati_da_lui[$i].'" and ACCOUNT="'.$_SESSION['uname'].'" and Status="seat_prenotato";'))
                    throw new Exception('Si è verificato un problema, riprova più tardi');
            }   
            for($i=0; $i<sizeof($liberi); $i++){
                if(!$result = mysqli_query($conn,'INSERT into seats VALUES ("'.$liberi[$i].'", "'.$_SESSION['uname'].'", "seat_venduto");' ))
                    throw new Exception('Si è verificato un problema, riprova più tardi');
            }   
            mysqli_autocommit($conn, true);
            mysqli_close($conn);
            $msg = 'Posti: [ '.implode(" ", $id_seats).' ] comprati correttamente';                             
            redirect($msg, 'index_personal.php');
        }
        else{
            if(!$result = mysqli_query($conn,'DELETE FROM seats WHERE Account="'.$_SESSION['uname'].'" and Status="seat_prenotato";'))
                throw new Exception('Si è verificato un problema, riprova più tardi');
            mysqli_autocommit($conn, true);
            mysqli_close($conn);
            $msg = 'Operazione fallita poichè i posti: [ '.implode(" ", $prenotati_da_altri) .' ] sono stati prenotati/acquistati da altri utenti';              
            redirect($msg, 'index_personal.php');
        }
    } catch (Exception $e) {        
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);
        mysqli_close($conn);        
        $msg =$e->getMessage(); //debug                
        redirect($msg, 'index_personal.php'); 
    }   
}

/*
Questa è una funzione per acquistare i posti che non consente l'acquisto di un posto se prima non è prenotato nel DB per quell'utente
*/
/*function buySeat(){  
    
    if(!isset($_SESSION['uname']))   
        redirect('', 'index.php');

    $id_seats = json_decode($_POST['data']);
    $prenotati_da_altri = array();    
    $conn = dbConnect();    
    
    try{
        mysqli_autocommit($conn, false);

        $fail = false; 
        //controllo se arrivano input strani dall'esterno
        if(sizeof($id_seats)<= 0)
            throw new Exception('Utente malevolo');

        if(!$result = mysqli_query($conn,'SELECT * from seats WHERE Account="'.$_SESSION['uname'].'" and Status="seat_prenotato" limit 1000 FOR UPDATE;'))
            throw new Exception('Si è verificato un problema, riprova più tardi');
        //sleep(10);//debug per mostrare la concorrenza tra prenotazione e acquisto
        $i_tabella=0;
        while ($riga = mysqli_fetch_array($result)){//Mi fetcho tutta la tabella   
            $tabella[$i_tabella] = $riga;    
            $i_tabella++;
        }
        $lenght = $i_tabella;

        for($i = 0; $i<sizeof($id_seats); $i++){
            //sanifico ogni id ricevuto e controllo se realmente contiene un posto(esempio: "1A") con la regular expression
            $id_seats[$i] = sanitizeString($id_seats[$i]);
            $id_seats[$i] = mysqli_real_escape_string($conn, $id_seats[$i]);                        
            if ( !postoValido($id_seats[$i]) )
                throw new Exception('Utente malevolo');           
            $find = false;   
            for($i_tabella=0; $i_tabella<$lenght; $i_tabella++){            
                if( $tabella[$i_tabella]['Id'] == $id_seats[$i] ){              
                    $find = true;
                    break;                
                }            
            }            
            if($find == false){
                $fail = true;
                array_push($prenotati_da_altri, $id_seats[$i]);//mi salvo tutti i posti che non trova
            }
        }
        if($fail==false){
            if(!$result = mysqli_query($conn,'UPDATE seats SET Status="seat_venduto" WHERE ACCOUNT="'.$_SESSION['uname'].'";'))
                throw new Exception('Si è verificato un problema, riprova più tardi');
            mysqli_autocommit($conn, true);
            mysqli_close($conn);
            $msg = 'Posti: [ '.implode(" ", $id_seats).' ] comprati correttamente';                              
            redirect($msg, 'index_personal.php');
        }
        else{
            if(!$result = mysqli_query($conn,'DELETE FROM seats WHERE Account="'.$_SESSION['uname'].'" and Status="seat_prenotato";'))
                throw new Exception('Si è verificato un problema, riprova più tardi');
            mysqli_autocommit($conn, true);
            mysqli_close($conn);
            $msg = 'Operazione fallita poichè i posti: ['.implode(" ", $prenotati_da_altri) .' ] sono stati prenotati/acquistati/liberati da altri utenti';              
            redirect($msg, 'index_personal.php');
        }
    } catch (Exception $e) {        
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);
        mysqli_close($conn);        
        $msg =$e->getMessage(); //debug                
        redirect($msg, 'index_personal.php'); 
    }   
}*/

?>