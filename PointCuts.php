<?php

return [
    [
        '/.*SomeService/',
        '/create|update|delete/', //point
        'after',
        function ($m) { //advice
            //DO LOG
        },
    ],
];
