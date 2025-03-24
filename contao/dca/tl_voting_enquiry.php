<?php

use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_voting_enquiry'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_voting',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,published' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'headerFields' => ['name', 'published', 'start', 'stop'],
            'panelLayout' => 'filter;search,limit',
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,alias,teaser;{text_legend},description,recommendation,attachments;{publish_legend},published',
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
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'basicEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alnum', 'unique' => true, 'spaceToUnderscore' => true, 'doNotCopy' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varbinary(128) NOT NULL default ''",
        ],
        'teaser' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'rte' => 'tinyMCE', 'basicEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'basicEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'recommendation' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'basicEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'attachments' => [
            'inputType' => 'fileTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isSortable' => true, 'extensions' => \Contao\Config::get('allowedDownload'), 'tl_class' => 'clr m12'],
            'sql' => 'blob NULL',
        ],
        'published' => [
            'filter' => true,
            'toggle' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'ayes' => [
            'eval' => ['doNotCopy' => true],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'nays' => [
            'eval' => ['doNotCopy' => true],
            'sql' => 'smallint(5) unsigned NOT null default 0',
        ],
    ],
];
