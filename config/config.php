<?php

/**
 * voting extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/contao-association/contao-voting
 */

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['voting'] = array
(
    'tables' => array('tl_voting', 'tl_voting_enquiry'),
    'icon'   => 'system/modules/voting/assets/icon.png'
);

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['voting'] = array
(
    'voting_list'           => 'ModuleVotingList',
    'voting_enquiry_list'   => 'ModuleVotingEnquiryList',
    'voting_enquiry_reader' => 'ModuleVotingEnquiry', // ModuleVotingEnquiryReader seems to be too long for Cache class?
);
