function getQueryString(field, url) {
    var href = url ? url : window.location.href;
    var reg = new RegExp( '[?&]' + field + '=([^&#]*)', 'i' );
    var string = reg.exec(href);
    return string ? string[1] : null;
};

function colorize(element, color) {
    var currentHtml = element.innerHTML;
    var newHtml = '';

    if (currentHtml.indexOf("^") == -1) {
        newHtml = "<font color='" + (color ? color : colorCodeToFontColor(2)) + "'>" + currentHtml + "</font>";
    } else {
        var i = 0;    
        while (i < currentHtml.length) {
            if (currentHtml[i] == '^') {
                var nextColorCodePos = currentHtml.indexOf("^", i + 1);
                var namePart;
                if (nextColorCodePos > -1) {
                    namePart = currentHtml.substring(i + 2, currentHtml.indexOf("^", i + 1));
                } else {
                    namePart = currentHtml.substring(i + 2);
                }             
                if (color) {
                    newHtml += namePart;
                } else {
                    newHtml += "<font color='" + colorCodeToFontColor(currentHtml[i + 1]) + "'>" + namePart + "</font>";                
                }
                i += (namePart.length + 2);
            }
            else {
                if (color) {
                    newHtml += currentHtml[i];                
                } else {
                    newHtml += "<font color='" + colorCodeToFontColor(2) + "'>" + currentHtml[i] + "</font>";                
                }
                i++;
            }
        }
        if (color) {
            newHtml = "<font color='" + color + "'>" + newHtml + "</font>";
        }
    }
    element.innerHTML = newHtml;
}

function colorCodeToFontColor(color) {
    switch (parseInt(color) % 8) {
        case 0:
            return "black";
        case 1:
            return "red";
        case 2:
            return "lime";
        case 3:
            return "yellow";
        case 4:
            return "blue";
        case 5:
            return "cyan";
        case 6:
            return "purple";
        case 7:
            return "white";
    }
    return "lime";
}
