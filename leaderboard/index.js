var showAccuracy = true;

function hideWeaponAccuracy() {
    $("div.infotooltip").each(function(index, elem) {
        $(elem).hide();
    });
}

function showPopup(id) {
    showAccuracy = false;
    hideWeaponAccuracy();
    
    // Disable scrolling    
    $("body").css("overflow", "hidden");

    $("#" + id).fadeIn();
    $("#overlay").fadeIn();
    window.setTimeout(function() { showAccuracy = true; }, 100);
}

function hidePopup() {
    showAccuracy = false;
    hideWeaponAccuracy();
    
    // Enable scrolling    
    $("body").css("overflow", "visible");

    $("#overlay").fadeOut()
    $("div.details").fadeOut()
}

var timeout;
function showWeaponAccuracy(id) {
    // Workaround for not showing the weapon accuracy tooltip immediately 
    // when showing the detailed table but only after moving the mouse.
    if (!showAccuracy) {
        return;
    }
    var xoffset = 280;
    var yoffset = -10;
    var el = $("#" + id);
    var top = ypos - yoffset;    
    var left;
   
    if (xpos + xoffset + 20 < $(window).width()) {
        left = xpos + 20;
    } else {
        left = xpos - xoffset;
    }
    
    if (top < 0) top = 0;

    el.css("top", top);
    el.css("left", left);
    el.show();

    $("div.infotooltip").each(function(index, elem) {
        if (elem.id != id) {
            $(elem).hide();
        }
    });
    window.clearTimeout(timeout);
    timeout = window.setTimeout(function() {el.fadeOut(1000);}, 10000);
}

function documentReady() {
    $("#leaderboardtable").show();

    setPlayerColors();

    $(document).mousemove(function(e) {
        xpos = e.pageX;
        ypos = e.pageY;
    });

    // Set mouse events to show weapon skill table tooltip
    $("table.details").each(function(tableindex, table) {                        
        var tableId = $(table).attr("id");

        $(table).find("tr").each(function(rowindex, row) {
            var datarowCell = $(row).find('td.datarow.playerid');
            if (datarowCell) {
                var playerNumber = $(datarowCell[0]).text();

                $(row).find("td").each(function(index, cell) {
                    $(cell).mouseenter(function() { 
                        showWeaponAccuracy(tableId + "_weaponaccuracy_" + playerNumber); 
                    });
                    $(cell).mousemove(function() { 
                        showWeaponAccuracy(tableId + "_weaponaccuracy_" + playerNumber); 
                    });
                    $(cell).mouseout(function() {
                        hideWeaponAccuracy();
                    });               
                
                    // Make sure the tooltip is not blocking the mouseover on the score table
                    $("#" + tableId + "_weaponaccuracy_" + playerNumber).mouseenter(function() {
                        $("#" + tableId + "_weaponaccuracy_" + playerNumber).hide();
                    });
                });
            }
        });
    });            
}
