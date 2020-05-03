function toggleRankingDetails(rank) {
    var rankRow = $('#' + rank + '_row');    
    var detailsRow = $('#' + rank + '_details_row');
    var detailsDiv = $('#' + rank + '_details');
    var tdDetails = $(detailsRow.find('td.datarowdetails')[0]);    

    if (!tdDetails.is(':visible')) {
        tdDetails.html(detailsDiv.html());
        tdDetails.show();
        rankRow.attr('title', 'Click to hide details');
    } else {
        tdDetails.hide();
        rankRow.attr('title', 'Click for more details');
    }
}

function documentReady() {
    setPlayerColors();
}