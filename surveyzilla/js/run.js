$(document).ready(function(){
    $('.btn-menu').hover(
        function(){
            $('.btn-menu div').css('background-color','orange');
        },
        function(){
            $('.btn-menu div').css('background-color','white');
        }
    );
    $('.btn-menu').click(function(){
        $('.settings').toggle();
    });
    function refreshStatData(){
        // Get poll statistics object from cache and update the graph
        var pollId = $('div.stat').attr('data-sz-poll');
        $.post('cache/stat_' + pollId + '.json', function(stat){
            for (var q in stat) {
                for (var option in stat[q]) {
                    // Bar div
                    //Example: var bar = $("table[data-sz-q='Будьте добры, укажите свой пол'] div[data-sz-option='Мужчина']");
                    var bar = $("table[data-sz-q='" + q + "'] div[data-sz-option='" + option + "']");
                    // Setting bar width
                    bar.css('width',stat[q][option]['percent'] + '%');
                    // Setting bar title. Will change just number of votes
                    bar.parent().attr('title', bar.parent().attr('title').split(':')[0] + ': ' + stat[q][option]['total']);
                    // Setting value in column with numbers
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