function myLoad() {
    //querySelectorAll non va con internet explorer, peccato era molto più bello
    /*if (checkCookie() == true) {
        document.querySelectorAll('#myTable td')
            .forEach(e => e.setAttribute("onclick", "clickHandler(this)"));
    }*/
    if (checkCookie() == true) {
        var className = document.getElementsByClassName('seat_libero');
        var classnameCount = className.length;
        for (var j = 0; j < classnameCount; j++) {
            className[j].setAttribute("onclick", "clickHandler(this)");
        }
        className = document.getElementsByClassName('seat_temp');
        classnameCount = className.length;
        for (var j = 0; j < classnameCount; j++) {
            className[j].setAttribute("onclick", "clickHandler(this)");
        }
        className = document.getElementsByClassName('seat_prenotato');
        classnameCount = className.length;
        for (var j = 0; j < classnameCount; j++) {
            className[j].setAttribute("onclick", "clickHandler(this)");
        }
        className = document.getElementsByClassName('seat_temp_prenotato');
        classnameCount = className.length;
        for (var j = 0; j < classnameCount; j++) {
            className[j].setAttribute("onclick", "clickHandler(this)");
        }
        className = document.getElementsByClassName('seat_venduto');
        classnameCount = className.length;
        for (var j = 0; j < classnameCount; j++) {
            className[j].setAttribute("onclick", "clickHandler(this)");
        }
    }
}

function checkCookie() {
    var cookieEnabled = true;
    cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled) {
        document.getElementById("body").style.display = "none";
        document.write("I cookies sono disabilitati, senza di essi il sito non è accessibile");
    }
    return cookieEnabled;
}

function clickHandler(cell) {
    //alert(cell.getAttribute("id"));
    var id_seat = cell.getAttribute("id");
    var type = cell.getAttribute("class");

    if (type != "seat_venduto") {
        //var startTime = new Date().getTime();//debug velocità invio-risposta
        $.ajax({
            url: 'pjt_action.php',
            type: 'post',
            dataType: "json",
            data: { id_seat: id_seat, action: 'clickSeat' },
            success: function(response) {

                if (response.result == 1) {
                    //var time = new Date().getTime() - startTime;
                    $("#" + response.id_seat).attr("class", response.status);
                    alert(response.msg);
                    //alert(time);
                } else if (response.result == 0) {
                    alert(response.msg);
                } else if (response.result == -2) { //time oltrepassato
                    alert(response.msg);
                    window.location = "login.php";
                }
            }
        });
    } else {
        alert("Il posto è già stato venduto");
    }
}

function send_seat_temp_prenotato() {

    var className = document.getElementsByClassName('seat_temp_prenotato');
    var classnameCount = className.length;
    var IdStore = new Array();
    for (var j = 0; j < classnameCount; j++) {
        IdStore.push(className[j].id);
    }
    if (classnameCount > 0) {
        //alert(IdStore.toString());//debug
        var jsonArray = JSON.stringify(IdStore);

        $('#buySeats').attr('action', 'pjt_action.php');
        $('<input type="hidden" name="data"/>').val(jsonArray).appendTo('#buySeats');
        $('<input type="hidden" name="action"/>').val('buySeat').appendTo('#buySeats');
        $("#buySeats").submit();
    } else if (classnameCount == 0 || classnameCount == undefined) {
        alert("Seleziona prima un posto");
    }

}