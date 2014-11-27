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
 * Class ModuleVotingEnquiryList
 *
 * Front end module "voting enquiry list".
 */
class ModuleVotingEnquiryList extends ModuleVoting
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_voting_enquiry_list';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['voting_enquiry_list'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
            $this->Input->setGet('items', $this->Input->get('auto_item'));
        }

        // Throw a 404 page if no item has been specified
        if (!$this->Input->get('items')) {

            /** @type \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($GLOBALS['objPage']->id);
        }

        $this->import('FrontendUser', 'User');

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $objVoting = $this->Database->prepare("
            SELECT *
            FROM tl_voting
            WHERE alias=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : "")
        )->limit(1)->executeUncached($this->Input->get('items'));

        if (!$objVoting->numRows) {

            /** @type \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($GLOBALS['objPage']->id);
        }

        $objEnquiries = $this->Database->prepare("SELECT * FROM tl_voting_enquiry WHERE pid=? ORDER BY sorting")
                                       ->execute($objVoting->id);

        $strUrl = '';

        // Get the jumpTo page
        if ($this->jumpTo > 0) {
            $objJump = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                                      ->execute($this->jumpTo);

            if ($objJump->numRows) {
                $strUrl = ampersand($this->generateFrontendUrl($objJump->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/items/%s')));
            }
        }

        $blnCanVote = $this->canUserVote($objVoting);

        // Setup form variables
        $doNotSubmit = false;
        $strFormId = 'voting_' . $this->id;
        $arrWidgets = array();

        $limit = $objEnquiries->numRows;
        $count = 0;
        $arrEnquiries = array();
        $objEnquiries->reset();

        // Generate enquiries
        while ($objEnquiries->next()) {
            $arrEnquiries[$objEnquiries->id] = $objEnquiries->row();
            $arrEnquiries[$objEnquiries->id]['class'] = ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even');
            $arrEnquiries[$objEnquiries->id]['href'] = sprintf($strUrl, $objEnquiries->alias);
            $arrEnquiries[$objEnquiries->id]['linkTitle'] = specialchars($objEnquiries->name);
            $arrEnquiries[$objEnquiries->id]['more'] = $GLOBALS['TL_LANG']['MSC']['more'];
            $arrEnquiries[$objEnquiries->id]['moreTitle'] = specialchars($GLOBALS['TL_LANG']['MSC']['more']);

            // Setup form widgets
            if ($blnCanVote) {
                $strWidget = 'enquiry_' . $objEnquiries->id;

                /** @type FormRadioButton $objWidget */
                $objWidget = new $GLOBALS['TL_FFL']['radio']($this->prepareForWidget(array
                (
                    'name'      => $strWidget,
                    'inputType' => 'radio',
                    'options'   => array('yes', 'no', 'abstention'),
                    'reference' => $GLOBALS['TL_LANG']['MSC']['voting_options'],
                    'eval'      => array('mandatory'=>true)
                ), $strWidget));

                // Validate the widget
                if ($this->Input->post('FORM_SUBMIT') == $strFormId) {
                    $objWidget->validate();

                    if ($objWidget->hasErrors()) {
                        $doNotSubmit = true;
                    }
                }

                $arrWidgets[$objEnquiries->id] = $objWidget;
                $arrEnquiries[$objEnquiries->id]['widget'] = $objWidget;
            }
        }

        // Process the voting
        if ($blnCanVote && !$doNotSubmit && $this->Input->post('FORM_SUBMIT') == $strFormId) {

            $this->Database->lockTables(
                array(
                    'tl_voting_enquiry'  => 'WRITE',
                    'tl_voting_registry' => 'WRITE'
                )
            );

            // Check voting status again after tables are locked
            if ($this->canUserVote($objVoting)) {
                foreach ($arrWidgets as $intEnquiry => $objWidget) {

                    // Do not insert vote record if use chose abstention
                    if ($objWidget->value != 'yes' && $objWidget->value != 'no') {
                        continue;
                    }

                    $strField = ($objWidget->value == 'yes') ? 'ayes' : 'nays';

                    $this->Database->prepare("UPDATE tl_voting_enquiry SET $strField=($strField+1) WHERE id=?")
                                   ->execute($intEnquiry);
                }

                // Store the voting in registry
                $this->Database->prepare("INSERT INTO tl_voting_registry %s")
                               ->set(array('tstamp' => time(), 'voting' => $objVoting->id, 'member' => $this->User->id))
                               ->execute();
            }

            $this->Database->unlockTables();
            $this->reload();
        }

        $this->Template->voting = $objVoting->row();
        $this->Template->totalEnquiries = $objEnquiries->numRows;
        $this->Template->duration = $this->getDuration($objVoting);

        $this->Template->enquiries = $arrEnquiries;
        $this->Template->canVote = $blnCanVote;
        $this->Template->hasVoted = $this->hasUserVoted($objVoting) && $this->isActive($objVoting);
        $this->Template->formId = $strFormId;
        $this->Template->action = ampersand($this->Environment->request);
        $this->Template->submit = specialchars($GLOBALS['TL_LANG']['MSC']['voting_vote']);
    }
}
