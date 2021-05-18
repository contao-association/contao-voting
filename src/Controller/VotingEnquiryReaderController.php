<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Image;
use Contao\PageModel;
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
            'SELECT *, (SELECT published FROM tl_voting WHERE tl_voting.id=tl_voting_enquiry.pid) AS voting_published FROM tl_voting_enquiry WHERE alias=?',
            [Input::get('auto_item')]
        );

        if (false === $enquiry || (!$this->tokenChecker->isPreviewMode() && (!$enquiry['published'] || !$enquiry['voting_published']))) {
            throw new PageNotFoundException();
        }

        foreach ($enquiry as $k => $v) {
            $template->{$k} = $v;
        }

        $template->attachments = $this->generateAttachements($enquiry);

        return $template->getResponse();
    }

    private function generateAttachements(array $enquiry)
    {
        $attachments = StringUtil::deserialize($enquiry['attachments']);

        // Return if there are no files
        if (empty($attachments) && !\is_array($attachments)) {
            return [];
        }

        // Get the file entries from the database
        $objFiles = FilesModel::findMultipleByUuids($attachments);

        if (null === $objFiles) {
            return '';
        }

        $file = Input::get('file', true);

        // Send the file to the browser (see #4632 and #8375)
        if ($file) {
            while ($objFiles->next()) {
                if ($file == $objFiles->path || \dirname($file) == $objFiles->path) {
                    Controller::sendFileToBrowser($file, true);
                }
            }

            throw new PageNotFoundException('Invalid file name');
        }

        /** @var PageModel $objPage */
        global $objPage;

        $allowedDownload = StringUtil::trimsplit(',', strtolower(Config::get('allowedDownload')));

        // Get all files
        while ($objFiles->next()) {
            // Continue if the files has been processed or does not exist
            if (isset($files[$objFiles->path]) || !file_exists(System::getContainer()
                        ->getParameter('kernel.project_dir').'/'.$objFiles->path)) {
                continue;
            }

            // Single files
            if ($objFiles->type !== 'file') {
                continue;
            }

            $objFile = new File($objFiles->path);

            if (!\in_array($objFile->extension, $allowedDownload)) {
                continue;
            }

            $arrMeta = Frontend::getMetaData($objFiles->meta, $objPage->language);

            if (empty($arrMeta)) {
                if ($objPage->rootFallbackLanguage !== null) {
                    $arrMeta = Frontend::getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
                }
            }

            // Use the file name as title if none is given
            if (!$arrMeta['title']) {
                $arrMeta['title'] = StringUtil::specialchars($objFile->basename);
            }

            $strHref = Environment::get('request');

            // Remove an existing file parameter (see #5683)
            if (isset($_GET['file'])) {
                $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
            }

            $strHref .= (strpos($strHref, '?') !== false ? '&amp;' : '?').'file='.System::urlEncode($objFiles->path);

            // Add the image
            $files[$objFiles->path] = [
                'id' => $objFiles->id,
                'uuid' => $objFiles->uuid,
                'name' => $objFile->basename,
                'title' => StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename)),
                'link' => $arrMeta['title'],
                'caption' => $arrMeta['caption'],
                'href' => $strHref,
                'filesize' => System::getReadableSize($objFile->filesize),
                'icon' => Image::getPath($objFile->icon),
                'mime' => $objFile->mime,
                'meta' => $arrMeta,
                'extension' => $objFile->extension,
                'path' => $objFile->dirname,
            ];
        }

        $tmp = StringUtil::deserialize($enquiry['attachmentsOrder']);

        if (!empty($tmp) && \is_array($tmp)) {
            // Remove all values
            $arrOrder = array_map(static function () {}, array_flip($tmp));

            // Move the matching elements to their position in $arrOrder
            foreach ($files as $k => $v) {
                if (\array_key_exists($v['uuid'], $arrOrder)) {
                    $arrOrder[$v['uuid']] = $v;
                    unset($files[$k]);
                }
            }

            // Append the left-over files at the end
            if (!empty($files)) {
                $arrOrder = array_merge($arrOrder, array_values($files));
            }

            // Remove empty (unreplaced) entries
            $files = array_values(array_filter($arrOrder));
            unset($arrOrder);
        }

        return $files;
    }
}
