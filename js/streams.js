function toggleMusicOn(url) {
    if ($(".m3uplayer").length > 0 && url == $("#theurl").data("url")) {
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
        url: "ajax.php",  
        data: "action=createPlaylistJs&dir=" + encodeURIComponent(url),
        success: function(html){
            var width = $("#content").width();
            $("#content-player").html(html);

            // Currently the player only works in iPhone with the Chrome browser.
            // We remove this playlist because it is not functional while playing.
            if (isMobile()) {
                $("#musicindex").remove();
                $("#playercontrols").remove();
                var newwidth = width - 16;
                //alert('newwidth = ' + newwidth);
                $("#mediaplayer_wrapper").css("width", newwidth + "px");
            }
        }
    });
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
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
    if ($("#playbutton").length > 0) {
        var p = $("#playbutton").parent();
        p.remove();
    }
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=search&q=" + encodeURIComponent(q),
        success: function(html){
            $("#musicindex").html(html);
        }
    });
}

function displayWorking() {
    $("#loading").css("display", "block").css("visibility", "visible");
}

function hideWorking() {
    $("#loading").css("display", "none").css("visibility", "hidden");
}

function addToPlaylist(e) {
    var event = e || window.event;
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=addToPlaylist&dir=" + encodeURIComponent($(event).data('url')),
        success: function(html){
            hideWorking();
        }
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

$(document).ready(function(){
    init();

    if ($("#content-player").length > 0 && $(".m3uplayer").length > 0) {
        $("#playbutton").html("Pause");
    }
    
    $(document).on("click", "#playbutton", function() {
        toggleMusicOn($(this).data('url'));
    });

    $(document).on("click", ".droplink", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".dirlink", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".dirlinkcover", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".addtoplaylist", function(e) {
        addToPlaylist(this);
    });

    prevtime = parseInt(new Date().getTime());
    // Waits 500 milliseconds before performing search.
    threshold = 500;
    curval = "";
    t = null;
    $(document).on("keyup", "#search", function() {
        curval = $(this).val();
        curtime = parseInt(new Date().getTime());
        next = prevtime + threshold;
        prevtime = curtime;
        if (curtime < next) {
            clearTimeout(t);
            t = setTimeout("search('" + curval + "')", threshold);
            return;
        }
    });
});
