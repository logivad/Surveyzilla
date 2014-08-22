window.onload = function() {
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
};