<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Contao\ModuleModel;
use Contao\Template;
use Contao\Input;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\StringUtil;
use Contao\Controller;
use Contao\File;
use Contao\Environment;
use Contao\System;

/**
 * @FrontendModule(category="voting")
 */
class VotingEnquiryReaderController extends AbstractVotingController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $enquiry = $this->connection->fetchAssociative(
            'SELECT *, (SELECT published FROM tl_voting WHERE tl_voting.id=tl_voting_enquiry.pid) AS published FROM tl_voting_enquiry WHERE alias=?',
            [Input::get('auto_item')]
        );

        if (false === $enquiry || (!$this->tokenChecker->isPreviewMode() && !$enquiry['published'])) {
            throw new PageNotFoundException();
        }

        foreach ($enquiry as $k => $v) {
            $template->{$k} = $v;
        }

        $arrAttachments = array();
        $attachments = StringUtil::deserialize($enquiry['attachments'], true);

        // Generate attachments
        if (!empty($attachments)) {

            $file = Input::get('file', true);

            // Send the file to the browser
            if ($file != '' && (in_array($file, $attachments) || in_array(dirname($file), $attachments)) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file))) {
                Controller::sendFileToBrowser($file);
            }

            $allowedDownload = StringUtil::trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

            foreach ($attachments as $attachment) {
                $objFile = new File($attachment);

                // Skip the file if the extension is not available to downloads
                if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($attachment))) {
                    continue;
                }

                $arrMeta = $this->arrMeta[$objFile->basename];

                if ($arrMeta[0] == '') {
                    $arrMeta[0] = StringUtil::specialchars($objFile->basename);
                }

                $strHref = Environment::get('request');

                // Remove an existing file parameter (see #5683)
                if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
                    $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
                }

                $strHref .= '?file=' . System::urlEncode($attachment);

                $arrAttachments[] = [
                    'link' => $arrMeta[0],
                    'title' => $arrMeta[0],
                    'href' => $strHref,
                    'caption' => $arrMeta[2],
                    'filesize' => System::getReadableSize($objFile->filesize, 1),
                    'icon' => 'system/themes/flexible/images/' . $objFile->icon,
                    'mime' => $objFile->mime,
                    'meta' => $arrMeta,
                    'extension' => $objFile->extension,
                    'path' => $objFile->dirname
                ];
            }
        }

        $template->attachments = $arrAttachments;

        return $template->getResponse();
    }
}
