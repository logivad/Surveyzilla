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
    $('#btn_update_stat').click(function(){
        alert('Updating data...');
    });
});