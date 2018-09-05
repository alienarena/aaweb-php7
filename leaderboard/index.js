var showAccuracy = true;

function hideWeaponAccuracy() {
    $("div.weaponaccuracy").each(function(index, elem) {
        $(elem).hide();
    });
}

function showPopup(id) {
    showAccuracy = false;
    hideWeaponAccuracy();
    $("html, body").animate({ scrollTop: 0 }, "slow");
    $("#" + id).fadeIn();
    $("#overlay").fadeIn();
    window.setTimeout(function() { showAccuracy = true; }, 100);
}

function hidePopup() {
    showAccuracy = false;
    hideWeaponAccuracy();
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
    var yoffset = 50;
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

    $("div.weaponaccuracy").each(function(index, elem) {
        if (elem.id != id) {
            $(elem).hide();
        }
    });
    window.clearTimeout(timeout);
    timeout = window.setTimeout(function() {el.fadeOut(1000);}, 2000);
}

function documentReady() {
    var numberOfQualifiers = 9999;
    var enableWeaponSkillTable = true;

    $("#leaderboardtable").show();

    // Set player colors
    $("table.scoretable").each(function(tableindex, table) {
        var playerCounter = 0;
        $(table).find("td.playername").each(function(index, elem) {
            playerCounter += 1;
            if (playerCounter <= numberOfQualifiers) {
                colorize(elem);
            } else {
                colorize(elem, "grey");
            }            
        });        
    });

    if (enableWeaponSkillTable) {
        $(document).mousemove(function(e){
            xpos = e.pageX;
            ypos = e.pageY;
        });

        $("div.weaponaccuracy td.playername").each(function(index, elem) {
            colorize(elem);
        });    

        // Set mouse events to show weapon skill table tooltip
        $("table.details").each(function(tableindex, table) {                        
            var tableId = $(table).attr("id");

            $(table).find("tr td.playerid").each(function(rowindex, row) {
                var playerNumber = row.innerText;

                $(row).find("~td.datarow").each(function(index, cell) {
                    $(cell).mouseenter(function() { 
                        showWeaponAccuracy(tableId + "_weaponaccuracy_" + playerNumber); 
                    });
                    $(cell).mousemove(function() { 
                        showWeaponAccuracy(tableId + "_weaponaccuracy_" + playerNumber); 
                    });
                    
                    // Make sure the tooltip is not blocking the mouseover on the score table
                    $("#" + tableId + "_weaponaccuracy_" + playerNumber).mouseenter(function() {
                        $("#" + tableId + "_weaponaccuracy_" + playerNumber).hide();
                    });
                });
            });
        });            
    }
}
