<?php
namespace surveyzilla\application\model;
abstract class UI
{
    // Пока что просто запишем встречающиеся тексты сайта в массив прямо здесь
    public static $text = array(
        'acces_denied' => 'Доступ запрещен',
        'error' => 'Ошибка!',
        'success' => 'Операция выполнена успешно',
        'log-in' => 'Войти',
        'logged-off' => 'Вы вышли из системы',
        'bad_login' => 'Неверный логин или пароль',
        'limit_poll' => 'Исчерпана возможность создавать опросы',
        'view_poll' => 'Просмотреть опрос',
        'enterCustOp' => 'другое...',
        'poll_end' => 'Спасибо за участие!',
        'poll_answered' => 'Вы уже отвечали на этот опрос'
    );
}