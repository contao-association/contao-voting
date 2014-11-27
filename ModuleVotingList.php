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
 * Class ModuleVotingList
 *
 * Front end module "voting list".
 */
class ModuleVotingList extends ModuleVoting
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_voting_list';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['voting_list'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $objVotings = $this->Database->execute("
            SELECT *,
                (SELECT COUNT(*) FROM tl_voting_enquiry WHERE pid=tl_voting.id) AS total_enquiries
            FROM tl_voting
            " . (!BE_USER_LOGGED_IN ? " WHERE published=1" : "") . "
            GROUP BY id
        ");

        if (!$objVotings->numRows) {
            return;
        }

        $strUrl = '';

        // Get the jumpTo page
        if ($this->jumpTo > 0) {
            $objJump = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                                      ->limit(1)
                                      ->execute($this->jumpTo);

            if ($objJump->numRows) {
                $strUrl = ampersand($this->generateFrontendUrl($objJump->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/items/%s')));
            }
        }

        $limit = $objVotings->numRows;
        $count = 0;
        $arrVotings = array();

        // Generate votings
        while ($objVotings->next()) {
            $arrVotings[$objVotings->id] = $objVotings->row();
            $arrVotings[$objVotings->id]['class'] = ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even') . ($this->isActive($objVotings) ? ' active' : '');
            $arrVotings[$objVotings->id]['href'] = sprintf($strUrl, $objVotings->alias);
            $arrVotings[$objVotings->id]['linkTitle'] = specialchars($objVotings->name);
            $arrVotings[$objVotings->id]['duration'] = sprintf(
                '%s – %s',
                $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objVotings->start),
                $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objVotings->stop)
            );
        }

        $this->Template->votings = $arrVotings;
    }
}
