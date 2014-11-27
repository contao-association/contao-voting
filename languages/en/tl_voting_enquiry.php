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
$GLOBALS['TL_LANG']['tl_voting_enquiry']['name']        = array('Name', 'Please enter the enquiry name.');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['alias']       = array('Enquiry alias', 'The enquiry alias is a unique reference to the enquiry which can be called instead of its numeric ID.');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['teaser']      = array('Teaser', 'Please enter the enquiry teaser.');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['description'] = array('Description', 'Please enter the enquiry description.');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['recommendation'] = array('Recommendation', 'Please enter the board\'s recommendation if available.');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['attachments'] = array('Attachments', 'Here you can select the attachments.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_voting_enquiry']['name_legend'] = 'Name and alias';
$GLOBALS['TL_LANG']['tl_voting_enquiry']['text_legend'] = 'Description and attachments';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_voting_enquiry']['new']        = array('New enquiry', 'Create a new enquiry');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['show']       = array('Enquiry details', 'Show the details of enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['edit']       = array('Edit enquiry', 'Edit enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['cut']        = array('Move enquiry', 'Move enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['copy']       = array('Duplicate enquiry', 'Duplicate enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['delete']     = array('Delete enquiry', 'Delete enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['pasteafter'] = array('Paste after', 'Paste after enquiry ID %s');
$GLOBALS['TL_LANG']['tl_voting_enquiry']['pasteinto']  = array('Paste into', 'Paste into enquiry ID %s');
