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
 * Class ModuleVotingEnquiry
 *
 * Front end module "voting enquiry reader".
 */
class ModuleVotingEnquiry extends ModuleVoting
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_voting_enquiry_reader';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['voting_enquiry_reader'][0]) . ' ###';
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
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($GLOBALS['objPage']->id);
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $objEnquiry = $this->Database->prepare("SELECT *, (SELECT published FROM tl_voting WHERE tl_voting.id=tl_voting_enquiry.pid) AS published FROM tl_voting_enquiry WHERE alias=?")
                                     ->limit(1)
                                     ->execute($this->Input->get('items'));

        if (!$objEnquiry->numRows) {
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($GLOBALS['objPage']->id);
        }

        $this->Template->setData($objEnquiry->row());
        $arrAttachments = array();
        $attachments = deserialize($objEnquiry->attachments, true);

        // Generate attachments
        if (!empty($attachments)) {

            $file = $this->Input->get('file', true);

            // Send the file to the browser
            if ($file != '' && (in_array($file, $attachments) || in_array(dirname($file), $attachments)) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file))) {
                $this->sendFileToBrowser($file);
            }

            $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

            foreach ($attachments as $attachment) {
                $objFile = new File($attachment);

                // Skip the file if the extension is not available to downloads
                if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($attachment))) {
                    continue;
                }

                $arrMeta = $this->arrMeta[$objFile->basename];

                if ($arrMeta[0] == '') {
                    $arrMeta[0] = specialchars($objFile->basename);
                }

                $strHref = $this->Environment->request;

                // Remove an existing file parameter (see #5683)
                if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
                    $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
                }

                $strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($attachment);

                $arrAttachments[] = array
                (
                    'link' => $arrMeta[0],
                    'title' => $arrMeta[0],
                    'href' => $strHref,
                    'caption' => $arrMeta[2],
                    'filesize' => $this->getReadableSize($objFile->filesize, 1),
                    'icon' => 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon,
                    'mime' => $objFile->mime,
                    'meta' => $arrMeta,
                    'extension' => $objFile->extension,
                    'path' => $objFile->dirname
                );
            }
        }

        $this->Template->attachments = $arrAttachments;
    }
}
