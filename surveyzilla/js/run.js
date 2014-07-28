$(document).ready(function(){
    $('.menu').hover(
        function(){
            $('.menu-line').css('background-color','#fff');
        },
        function(){
            $('.menu-line').css('background-color','#e3e3e3');
        }
    );
    $('.menu').click(function(){
        $('.settings').toggle();
    });
    function refreshStatData(){
        /* Get poll statistics object from cache and update the graph */
        var pollId = $('div.stat').attr('data-sz-poll');
        $.post('cache/stat_' + pollId + '.json', function(stat){
            //console.log(stat["Будьте добры, укажите свой пол"]["Мужчина"]["percent"]);
            for (var q in stat) {
                for (var option in stat[q]) {
                    //console.log(stat[q]);
                    $("table[data-sz-q='" + q + "'] div[data-sz-option='" + option + "']").css('width',stat[q][option]['percent'] + '%');
                    $("td[data-sz-option='" + option + "']").text(stat[q][option]['percent'] + '%');
                }
            }
        });
    }
    document.getElementById('refresh').checked = false;
    var autoUpdate;
    $('#refresh').change(function(){
        if (document.getElementById('refresh').checked) {
            autoUpdate = window.setInterval(refreshStatData, 5000);
        } else {
            window.clearInterval(autoUpdate);
        }
    });
});