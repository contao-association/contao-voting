<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Codefog\HasteBundle\Formatter;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FormRadio;
use Contao\FrontendUser;
use Contao\Input;
use Contao\PageModel;
use Contao\Widget;
use ContaoAssociation\VotingBundle\ContaoAssociationVotingPermissions;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'voting')]
class VotingEnquiryListController extends AbstractContentElementController
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
            $template->wildcard = '## ENQUIRY LIST ##';

            return $template->getResponse();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $voting = $this->connection->fetchAssociative(
            'SELECT * FROM tl_voting WHERE alias=?'.(!$this->tokenChecker->isPreviewMode() ? " AND published='1'" : ''),
            [Input::get('auto_item')],
        );

        if (false === $voting) {
            throw new PageNotFoundException();
        }

        $enquiries = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_voting_enquiry WHERE pid=?'.(!$this->tokenChecker->isPreviewMode() ? ' AND published=1' : '').' ORDER BY sorting',
            [$voting['id']],
        );

        $jumpTo = PageModel::findById($model->jumpTo);

        if (!$jumpTo) {
            throw new \RuntimeException('Missing jumpTo for tl_content.'.$model->id.' ('.self::class.')');
        }

        $canVote = $this->isGranted(ContaoAssociationVotingPermissions::CAN_VOTE, $voting);

        // Setup form variables
        $doNotSubmit = false;
        $strFormId = 'voting_'.$model->id;
        $arrWidgets = [];

        $arrEnquiries = [];

        foreach ($enquiries as $enquiry) {
            $arrEnquiries[$enquiry['id']] = $enquiry;
            $arrEnquiries[$enquiry['id']]['href'] = $this->generateContentUrl($jumpTo, ['parameters' => '/'.$enquiry['alias']]);

            // Setup form widgets
            if ($canVote) {
                $strWidget = 'enquiry_'.$enquiry['id'];

                $objWidget = new FormRadio(Widget::getAttributesFromDca(
                    [
                        'name' => $strWidget,
                        'inputType' => 'radio',
                        'options' => ['yes', 'no', 'abstention'],
                        'reference' => $GLOBALS['TL_LANG']['MSC']['voting_options'],
                        'eval' => ['mandatory' => true],
                    ],
                    $strWidget,
                ));

                // Validate the widget
                if (Input::post('FORM_SUBMIT') === $strFormId) {
                    $objWidget->validate();

                    if ($objWidget->hasErrors()) {
                        $doNotSubmit = true;
                    }
                }

                $arrWidgets[$enquiry['id']] = $objWidget;
                $arrEnquiries[$enquiry['id']]['widget'] = $objWidget;
            }
        }

        // Process the voting
        if ($canVote && !$doNotSubmit && Input::post('FORM_SUBMIT') === $strFormId) {
            $this->connection->executeStatement(
                'LOCK TABLES tl_voting_enquiry WRITE, tl_voting_registry WRITE',
            );

            // Check voting status again after tables are locked
            if ($this->isGranted(ContaoAssociationVotingPermissions::CAN_VOTE, $voting)) {
                /** @var FrontendUser $user */
                $user = $this->getUser();

                foreach ($arrWidgets as $intEnquiry => $objWidget) {
                    // Do not insert vote record if user chose abstention
                    if ('yes' !== $objWidget->value && 'no' !== $objWidget->value) {
                        continue;
                    }

                    $strField = 'yes' === $objWidget->value ? 'ayes' : 'nays';

                    $this->connection->executeStatement(
                        "UPDATE tl_voting_enquiry SET $strField=($strField+1) WHERE id=?",
                        [$intEnquiry],
                    );
                }

                // Store the voting in registry
                $this->connection->insert('tl_voting_registry', [
                    'tstamp' => time(),
                    'voting' => $voting['id'],
                    'member' => $user->id,
                ]);
            }

            $this->connection->executeStatement('UNLOCK TABLES');

            throw new RedirectResponseException($request->getUri());
        }

        $template->voting = $voting;
        $template->totalEnquiries = \count($enquiries);
        $template->period = \sprintf('%s – %s', $this->formatter->date((int) $voting['start']), $this->formatter->date((int) $voting['stop']));

        $template->enquiries = $arrEnquiries;
        $template->canVote = $canVote;
        $template->hasVoted = $this->hasVoted($voting);
        $template->formId = $strFormId;

        return $template->getResponse();
    }

    private function hasVoted(array $voting): bool
    {
        $user = $this->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $votes = $this->connection->fetchOne(
            'SELECT COUNT(*) AS votes FROM tl_voting_registry WHERE voting=? AND member=?',
            [$voting['id'], $user->id],
        );

        return $votes > 0;
    }
}
