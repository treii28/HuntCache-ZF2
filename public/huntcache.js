/**
 * Created with JetBrains PhpStorm.
 * User: scottw
 * Date: 1/7/13
 * Time: 1:27 PM
 * To change this template use File | Settings | File Templates.
 */

function setIcon(val) {
    $('#icon').val(val);
    //$( "#iconlist" ).dialog("close");
    return true;
}
function drawIconList(data) {
    $(function() { $( "#iconlist" ).dialog({ width: 200, height: 400, position: [700,25] }); });
    console.log(data);
    var newTable = document.createElement('table');
    var tblBody = document.createElement('tbody');
    for(var i in data) {
        var icon = data[i];
        var newRow = document.createElement('tr');
        newRow.setAttribute('onClick',sprintf("setIcon('%s');",icon.filename))
        var newCell = document.createElement('td');
        newCell.setAttribute('align',"center");
        newCell.appendChild(getImg(icon.filename,icon.urlpath));
        newRow.appendChild(newCell);
        var nameCell = document.createElement('td');
        nameCell.setAttribute('align',"left");
        nameCell.innerHTML = icon.filename;
        newRow.appendChild(nameCell);
        tblBody.appendChild(newRow);
    }
    newTable.appendChild(tblBody);
    $('#iconlist').html('');
    $('#iconlist').append(newTable);

    return true;
}

function getImg(filename,urlpath) {
    var newImg = document.createElement('img');
    newImg.setAttribute('border',0);
    newImg.setAttribute('width',25);
    newImg.setAttribute('height',25);
    newImg.setAttribute('alt',filename);
    newImg.setAttribute('title',filename);
    newImg.setAttribute('src',urlpath+filename);
    return newImg;
}
function getIconList() {
    var ajaxUrl = sprintf("http://%s%s/json/geticonlist?icondir=attributes", location.host, baseUrl);
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: ajaxUrl
    }).done( function(data) { drawIconList(data); } );
    return true;
}

var map;
function initGMap() {
    var mapOptions = {
        zoom: 8,
        center: new google.maps.LatLng(-34.397, 150.644),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById('map_canvas'),
        mapOptions);
}
google.maps.event.addDomListener(window, 'load', initGMap);
