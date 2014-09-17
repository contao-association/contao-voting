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
 * Table tl_voting_enquiry
 */
$GLOBALS['TL_DCA']['tl_voting_enquiry'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_voting',
        'enableVersioning'            => true,
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('sorting'),
            'headerFields'            => array('name', 'published', 'start', 'stop'),
            'panelLayout'             => 'filter;search,limit',
            'child_record_callback'   => array('tl_voting_enquiry', 'listEnquiries')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif'
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{name_legend},name,alias,teaser;{text_legend},description,attachments'
    ),

    // Fields
    'fields' => array
    (
        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['name'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
        ),
        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'alnum', 'unique'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array('tl_voting_enquiry', 'generateAlias')
            )
        ),
        'teaser' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['teaser'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>true, 'rte'=>'tinyMCE', 'tl_class'=>'clr'),
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['description'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>true, 'rte'=>'tinyMCE', 'tl_class'=>'clr'),
        ),
        'attachments' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_voting_enquiry']['attachments'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'clr'),
        ),
    )
);


/**
 * Class tl_voting_enquiry
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_voting_enquiry extends Backend
{

    /**
     * Auto-generate the enquiry alias if it has not been set yet
     * @param mixed
     * @param DataContainer
     * @return string
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue = standardize($this->restoreBasicEntities($dc->activeRecord->name));
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_voting_enquiry WHERE alias=?")
                                   ->execute($varValue);

        // Check whether the enquiry alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * List the enquiries
     * @param array
     * @return string
     */
    public function listEnquiries($arrRow)
    {
        $objVotes = $this->Database->prepare("SELECT
                COUNT(*) AS total,
                (SELECT COUNT(*) FROM tl_voting_vote WHERE enquiry=v1.enquiry AND vote=0) AS no,
                (SELECT COUNT(*) FROM tl_voting_vote WHERE enquiry=v1.enquiry AND vote=1) AS yes
            FROM tl_voting_vote v1 WHERE enquiry=?")
        ->execute($arrRow['id']);

        return '<div>
<h4>' . $arrRow['name'] . '</h4>
' . $arrRow['teaser'] . '
<table class="tl_listing" style="width:50%;">
    <tbody>
        <tr>
            <td class="tl_folder_tlist" colspan="2">' . $GLOBALS['TL_LANG']['MSC']['voting_summary'] . '</td>
        </tr>
        <tr>
            <td class="tl_file_list tl_red">' . $GLOBALS['TL_LANG']['MSC']['voting_options']['no'] . '</td>
            <td class="tl_file_list tl_red">' . $objVotes->no . '</td>
        </tr>
        <tr>
            <td class="tl_file_list tl_green">' . $GLOBALS['TL_LANG']['MSC']['voting_options']['yes'] . '</td>
            <td class="tl_file_list tl_green">' . $objVotes->yes . '</td>
        </tr>
        <tr>
            <td class="tl_file_list"><strong>' . $GLOBALS['TL_LANG']['MSC']['voting_total'] . '</td>
            <td class="tl_file_list"><strong>' . $objVotes->total . '</td>
        </tr>
    </tbody>
</table>
</div>' . "\n";
    }
}
