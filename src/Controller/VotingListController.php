<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Codefog\HasteBundle\Formatter;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'voting')]
class VotingListController extends AbstractContentElementController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
        private readonly Formatter $formatter,
    ) {
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array|null $classes = null): Response
    {
        if ($this->isBackendScope($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '## VOTING LIST ##';

            return $template->getResponse();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $results = $this->connection->fetchAllAssociative('
            SELECT *,
                (SELECT COUNT(*) FROM tl_voting_enquiry WHERE pid=tl_voting.id'.(!$this->tokenChecker->isPreviewMode() ? " AND published='1'" : '').') AS total_enquiries
            FROM tl_voting
            '.(!$this->tokenChecker->isPreviewMode() ? ' WHERE published=1' : '').'
            ORDER BY start DESC
        ');

        $votings = [];

        foreach ($results as $voting) {
            $jumpTo = $voting['jumpTo'] > 0 ? PageModel::findById($voting['jumpTo']) : PageModel::findById($model->jumpTo);

            if (!$jumpTo) {
                throw new \RuntimeException('Missing jumpTo for tl_voting.'.$voting['id'].' / tl_content.'.$model->id.' ('.self::class.')');
            }

            $votings[$voting['id']] = $voting;
            $votings[$voting['id']]['href'] = $this->generateContentUrl($jumpTo, ['parameters' => '/'.$voting['alias']]);
            $votings[$voting['id']]['period'] = \sprintf('%s – %s', $this->formatter->date((int) $voting['start']), $this->formatter->date((int) $voting['stop']));
        }

        $template->votings = $votings;

        return $template->getResponse();
    }
}
