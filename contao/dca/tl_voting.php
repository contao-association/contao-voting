<?php

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_voting'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_voting_enquiry'],
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias,published' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['start'],
            'flag' => DataContainer::SORT_MONTH_DESC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,alias,groups;{text_legend},description;{redirect_legend:collapsed},jumpTo;{publish_legend},published,start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
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
        'groups' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'basicEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'jumpTo' => [
            'inputType' => 'pageTree',
            'eval' => ['fieldType' => 'radio'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'published' => [
            'filter' => true,
            'toggle' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'datim',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'datim',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
