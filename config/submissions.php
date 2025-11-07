<?php

return [

    'email' => [
        'success_recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SUCCESSFUL_EMAILS', ''))
        ))),
        'failure_recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('FAILED_EMAILS', ''))
        ))),
    ],

];
