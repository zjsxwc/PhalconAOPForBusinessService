<?php

return [
    [
        '/.*SomeService/',
        '/create|update|delete/', //point
        'after',
        function ($m, $a, $r=null) { //advice
            //DO LOG
        },
    ],
];
