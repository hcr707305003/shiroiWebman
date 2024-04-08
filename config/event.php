<?php

use app\event\UserEvent;

return [
    'user.register' => [
        [UserEvent::class, 'register']
    ],
    'user.login' => [
        [UserEvent::class, 'login']
    ],
    'user.enable' => [
        [UserEvent::class, 'enable']
    ],
    'user.disable' => [
        [UserEvent::class, 'disable']
    ],
    'user.delete' => [
        [UserEvent::class, 'delete']
    ],
    'user.update' => [
        [UserEvent::class, 'update']
    ],
];
