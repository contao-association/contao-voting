<?php

$GLOBALS['TL_DCA']['tl_voting_registry'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'voting,member' => 'index',
            ]
        ]
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'voting' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'member' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
    ]
];
