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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_voting']['name']        = array('Name', 'Please enter the voting name.');
$GLOBALS['TL_LANG']['tl_voting']['alias']       = array('Voting alias', 'The voting alias is a unique reference to the voting which can be called instead of its numeric ID.');
$GLOBALS['TL_LANG']['tl_voting']['groups']      = array('Allowed member groups', 'Please choose the member groups allowed to vote.');
$GLOBALS['TL_LANG']['tl_voting']['published']   = array('Publish voting', 'Make the voting publicly visible on the website.');
$GLOBALS['TL_LANG']['tl_voting']['start']       = array('Voting from', 'Do not allow to vote before this day.');
$GLOBALS['TL_LANG']['tl_voting']['stop']        = array('Voting until', 'Do not allow to vote on and after this day.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_voting']['name_legend']    = 'Name and alias';
$GLOBALS['TL_LANG']['tl_voting']['publish_legend'] = 'Publish settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_voting']['new']        = array('New voting', 'Create a new voting');
$GLOBALS['TL_LANG']['tl_voting']['show']       = array('Voting details', 'Show the details of voting ID %s');
$GLOBALS['TL_LANG']['tl_voting']['copy']       = array('Duplicate voting', 'Duplicate voting ID %s');
$GLOBALS['TL_LANG']['tl_voting']['delete']     = array('Delete voting', 'Delete voting ID %s');
$GLOBALS['TL_LANG']['tl_voting']['edit']       = array('Edit voting enquiries', 'Edit voting enquiries ID %s');
$GLOBALS['TL_LANG']['tl_voting']['editheader'] = array('Edit voting settings', 'Edit the settings of voting ID %s');
