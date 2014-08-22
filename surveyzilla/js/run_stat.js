// Вариант на чистом JavaScript
window.onload = function() {
    
    /****************************************************
     * Код для страницы статистики и прохождения опроса *
     ****************************************************/
    
    // Сияние кнопки при наведении курсора
    document.getElementsByClassName('btn-menu')[0].onmouseover = function() {
        var btnLines = document.querySelectorAll('.btn-menu div');
        for (var line=0; line<3; line++) {
            btnLines[line].style.boxShadow = '0 0 10px 1px white';
        }
    };
    // Прекращение сияния кнопки при уходе курсора
    document.getElementsByClassName('btn-menu')[0].onmouseout = function() {
        var btnLines = document.querySelectorAll('.btn-menu div');
        for (var line=0; line<3; line++) {
            btnLines[line].style.boxShadow = 'none';
        }
    };
    // Показ/скрытие вспомогательной панели
    document.getElementsByClassName('btn-menu')[0].onclick = function() {
        var settingsBox = document.getElementsByClassName('settings')[0];
        if (settingsBox.style.display === 'block') {
            settingsBox.style.display = 'none';
        } else {
            settingsBox.style.display = 'block';
        }
    };
    
    /*******************************
     * Код для страницы статистики *
     *******************************/

    // Функция для получения статистических данных и обновления графика
    function refreshStatData(){
        var statContainer = document.getElementsByClassName('stat')[0];
        var pollId = statContainer.getAttribute('data-sz-poll');
        var xmlhttp;
        if (window.XMLHttpRequest) {
            xmlhttp=new XMLHttpRequest();
        }
        else {
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var stat = JSON.parse(xmlhttp.responseText);
                for (var q in stat) {
                    for (var option in stat[q]) {
                        //Пример: var bar = $("table[data-sz-q='Будьте добры, укажите свой пол'] div[data-sz-option='Мужчина']");
                        var bar = document.querySelector("table[data-sz-q='" + q + "'] div[data-sz-option='" + option + "']");
                        // Установим ширину полоски (проценты)
                        bar.style = 'width: ' + stat[q][option]['percent'] + '%';
                        // Title для полоски (количество голосов)
                        barBg = bar.parentNode;
                        barBg.setAttribute('title', barBg.getAttribute('title').split(':')[0] + ': ' + stat[q][option]['total']);
                        // Пишем число (проценты) в правый столбец
                        document.querySelector("td[data-sz-option='" + option + "']").innerHTML = stat[q][option]['percent'] + '%';
                    }
                }
            }
        }
        xmlhttp.open("POST",'cache/stat_' + pollId + '.json',true);
        xmlhttp.send();
    }
    document.getElementById('refresh').checked = false;
    var autoUpdate;
    document.getElementById('refresh').onchange = function(){
        if (document.getElementById('refresh').checked) {
            autoUpdate = window.setInterval(refreshStatData, 5000);
        } else {
            window.clearInterval(autoUpdate);
        }
    };
};


/*

// Вариант на jQuery
$(document).ready(function(){
    $('.btn-menu').hover(
        function(){
            $('.btn-menu div').addClass('glow');
        },
        function(){
            $('.btn-menu div').removeClass('glow');
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

*/