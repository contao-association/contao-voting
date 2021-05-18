<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Contao\ModuleModel;
use Contao\Template;
use Contao\PageModel;

/**
 * @FrontendModule(category="voting")
 */
class VotingListController extends AbstractVotingController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $results = $this->connection->fetchAllAssociative('
            SELECT *,
                (SELECT COUNT(*) FROM tl_voting_enquiry WHERE pid=tl_voting.id'.(!$this->tokenChecker->isPreviewMode() ? " AND published='1'" : '').') AS total_enquiries
            FROM tl_voting
            '. (!$this->tokenChecker->isPreviewMode() ? " WHERE published=1" : "") . "
            ORDER BY start DESC
        ");

        if (false === $results) {
            return new Response();
        }

        $strUrl = '';

        if ($model->jumpTo > 0) {
            $jumpTo = PageModel::findByPk($model->jumpTo);

            if (null !== $jumpTo) {
                $strUrl = $jumpTo->getFrontendUrl('/%s');
            }
        }

        $votings = [];

        foreach ($results as $row) {
            $currentUrl = $strUrl;

            if ($row['jumpTo'] > 0) {
                $jumpTo = PageModel::findByPk($row['jumpTo']);

                if (null !== $jumpTo) {
                    $currentUrl = $jumpTo->getFrontendUrl('/%s');
                }
            }

            $votings[$row['id']] = $row;
            $votings[$row['id']]['href'] = sprintf($currentUrl, $row['alias']);
            $votings[$row['id']]['period'] = $this->getPeriod($row);
        }

        $template->votings = $votings;

        return $template->getResponse();
    }
}
