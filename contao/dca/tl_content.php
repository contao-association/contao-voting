<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['voting_list'] = '{title_legend},title,headline,type;{redirect_legend},jumpTo;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['voting_enquiry_list'] = '{title_legend},title,headline,type;{redirect_legend},jumpTo;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['voting_enquiry_reader'] = '{title_legend},title,headline,type;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID,space';

if (!isset($GLOBALS['TL_DCA']['tl_content']['fields']['jumpTo'])) {
    $GLOBALS['TL_DCA']['tl_content']['fields']['jumpTo'] = [
        'inputType' => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval' => ['fieldType' => 'radio'],
        'sql' => 'int(10) unsigned NOT NULL default 0',
        'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    ];
}
