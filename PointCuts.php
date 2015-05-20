<?php

return [
    [
        '/.*SomeService/',
        '/create|update|delete/', //point
        'after',
        function ($m, $a) { //advice
            //DO LOG
        },
    ],
];
