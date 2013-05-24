function toggleMusicOn(url) {
    if ($(".m3uplayer").size() > 0 && url == $("#theurl").data("url")) {
        var player = document.getElementById("mediaplayer");
        var playlist = player.getPlaylist();
        if (playlist.length > 0) {
            if ($("#playbutton").html() == "Play") {
                player.sendEvent('PLAY', 'true');
                $("#playbutton").html("Pause");
            } else {
                player.sendEvent('PLAY', 'false');
                $("#playbutton").html("Play");
            }
        }
    } else {
        //location.href = "index.php?action=createPlaylist&dir=" + encodeURIComponent(url);
        createPlaylistJs(url);
    }
}

function createPlaylistJs(url) {
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=createPlaylistJs&dir=" + encodeURIComponent(url),
        success: function(html){
            $("#content-player").html(html);
        }
    });
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    //location.href = url;
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=openDir&url=" + encodeURIComponent(url) + "&dir=" + encodeURIComponent(url),
        success: function(text){
            $("#content").html(text);
        }
    });
}

function init() {
    var hash = window.location.hash;
    hash = hash.replace(/^#/, "");
    var hashVars = hash.split("/");
    switch(hashVars[1]) {
        case "open":
            var dir = decodeURIComponent(hashVars[2]);
            openDir(dir);
            break;
        default:
            var doNothing = true;
    }
}

function search(q) {
    if (q.length < 3) {
        return false;
    }
    if ($("#playbutton").size() > 0) {
        var p = $("#playbutton").parent();
        p.remove();
    }
    $.ajax({
        type: "GET",  
        url: "index.php",  
        data: "action=search&q=" + encodeURIComponent(q),
        success: function(html){
            $("#musicindex").html(html);
        }
    });
}

$(document).ready(function(){
    init();

    if ($("#content-player").size() > 0 && $(".m3uplayer").size() > 0) {
        $("#playbutton").html("Pause");
    }
    
    $("#playbutton").live("click", function() {
        toggleMusicOn($(this).data('url'));
    });

    $(".droplink").live("click", function() {
        openDir($(this).data('url'));
    });

    $(".dirlink").live("click", function() {
        openDir($(this).data('url'));
    });

    $(".dirlinkcover").live("click", function() {
        openDir($(this).data('url'));
    });

    $("#search").live("keyup", function() {
        search($(this).val());
    });
});
