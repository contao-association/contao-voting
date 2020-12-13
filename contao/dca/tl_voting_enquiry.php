<?php

$GLOBALS['TL_DCA']['tl_voting_enquiry'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_voting',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' =>
            [
                'mode' => 4,
                'fields' => ['sorting'],
                'headerFields' => ['name', 'published', 'start', 'stop'],
                'panelLayout' => 'filter;search,limit',
            ],
        'global_operations' => [
            'all' =>
                [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                    'href' => 'act=select',
                    'class' => 'header_edit_all',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
                ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,alias,teaser;{text_legend},description,recommendation,attachments',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'sorting' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['name'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'alnum',
                'unique' => true,
                'spaceToUnderscore' => true,
                'doNotCopy' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
            ],
            'sql' => "varbinary(128) NOT NULL default ''",
        ],
        'teaser' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['teaser'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['description'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'recommendation' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['recommendation'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'attachments' => [
            'label' => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['attachments'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['files' => true, 'filesOnly' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'ayes' => [
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'nays' => [
            'sql' => 'smallint(5) unsigned NOT null default 0',
        ],
    ],
];
