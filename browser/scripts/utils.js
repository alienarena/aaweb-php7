function showHidePlayerGraph() {
    $("#playergraph").slideToggle("fast", function() {
        var el = $("#playergraph");
	    var visible = el.is(":visible");
	    $("#showplayergraph").text(visible ? "\u25BC" : "\u25B6");
    });    
}

function showHideServerGraph() {
    $("#servergraph").slideToggle("fast", function() {
        var el = $("#servergraph");
	    var visible = el.is(":visible");
        $("#showservergraph").text(visible ? "\u25BC" : "\u25B6");
    });    
}
