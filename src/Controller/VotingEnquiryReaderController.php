<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Filesystem\FileDownloadHelper;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemItemIterator;
use Contao\CoreBundle\Filesystem\FilesystemUtil;
use Contao\CoreBundle\Filesystem\PublicUri\ContentDispositionOption;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[AsContentElement(category: 'voting')]
class VotingEnquiryReaderController extends AbstractContentElementController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
        #[Autowire('@contao.filesystem.virtual.files')] private readonly VirtualFilesystem $filesStorage,
        #[Autowire('@contao.filesystem.file_download_helper')] private readonly FileDownloadHelper $downloadHelper,
    ) {
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array|null $classes = null): Response
    {
        if ($this->isBackendScope($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '## ENQUIRY READER ##';

            return $template->getResponse();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $enquiry = $this->connection->fetchAssociative(
            'SELECT *, (SELECT published FROM tl_voting WHERE tl_voting.id=tl_voting_enquiry.pid) AS voting_published FROM tl_voting_enquiry WHERE alias=?',
            [Input::get('auto_item')],
        );

        if (false === $enquiry || (!$this->tokenChecker->isPreviewMode() && (!$enquiry['published'] || !$enquiry['voting_published']))) {
            throw new PageNotFoundException();
        }

        foreach ($enquiry as $k => $v) {
            $template->{$k} = $v;
        }

        $template->attachments = $this->generateAttachments($enquiry, $request);

        return $template->getResponse();
    }

    private function generateAttachments(array $enquiry, Request $request): array
    {
        $attachments = $this->getAttachments($enquiry['attachments']);

        $response = $this->downloadHelper->handle(
            $request,
            $this->filesStorage,
            static function (FilesystemItem $item, array $context) use ($enquiry, $attachments): Response|null {
                if ($enquiry['id'] !== ($context['id'] ?? null)) {
                    return new Response('', Response::HTTP_NO_CONTENT);
                }

                if (!$attachments->any(static fn (FilesystemItem $listItem) => $listItem->getPath() === $item->getPath())) {
                    return new Response('The resource can not be accessed anymore.', Response::HTTP_GONE);
                }

                return null;
            },
        );

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            throw new ResponseException($response);
        }

        return array_map(
            fn (FilesystemItem $filesystemItem): array => [
                'href' => $this->generateDownloadUrl($filesystemItem, $request, (int) $enquiry['id']),
                'file' => $filesystemItem,
            ],
            iterator_to_array($attachments),
        );
    }

    private function getAttachments(string|null $sources): FilesystemItemIterator
    {
        $filesystemItems = FilesystemUtil::listContentsFromSerialized($this->filesStorage, $sources);
        $allowedDownload = StringUtil::trimsplit(',', Config::get('allowedDownload'));

        return $filesystemItems->filter(
            static fn (FilesystemItem $item): bool => \in_array(
                Path::getExtension($item->getPath(), true),
                array_map(strtolower(...), $allowedDownload),
                true,
            ),
        );
    }

    private function generateDownloadUrl(FilesystemItem $filesystemItem, Request $request, int $enquiry): string
    {
        $path = $filesystemItem->getPath();

        if ($publicUri = $this->filesStorage->generatePublicUri($path, new ContentDispositionOption(true))) {
            return (string) $publicUri;
        }

        $context = ['id' => $enquiry];

        return $this->downloadHelper->generateInlineUrl($request->getUri(), $path, $context);
    }
}
