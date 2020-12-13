<?php

$GLOBALS['TL_DCA']['tl_voting'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_voting_enquiry'],
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
                'published' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['start'],
            'flag' => 8,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_voting_enquiry',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'href' => 'act=edit',
                'icon' => 'header.gif',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,alias,groups;{redirect_legend:hide},jumpTo;{publish_legend},published,start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'name' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
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
        'groups' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'jumpTo' => [
            'exclude' => true,
            'inputType' => 'pageTree',
            'eval' => ['fieldType' => 'radio'],
            'sql' => 'int(10) unsigned NOT NULL default 0'
        ],
        'published' => [
            'exclude' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''"
        ],
        'start' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'datim',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'stop' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'datim',
                'datepicker' => true,
                'tl_class' => 'w50 wizard',
            ],
            'sql' => "varchar(10) NOT NULL default ''"
        ],
    ],
];
