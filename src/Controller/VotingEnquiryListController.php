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
use Contao\PageModel;
use Contao\StringUtil;
use Contao\FormRadioButton;
use Contao\Widget;
use Contao\Controller;
use Contao\FrontendUser;

/**
 * @FrontendModule(category="voting")
 */
class VotingEnquiryListController extends AbstractVotingController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $voting = $this->connection->fetchAssociative('
            SELECT * FROM tl_voting WHERE alias=?'. (!$this->tokenChecker->isPreviewMode() ? ' AND published=1' : ''),
            [Input::get('auto_item')]
        );

        if (false === $voting) {
            throw new PageNotFoundException();
        }

        $enquiries = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_voting_enquiry WHERE pid=? ORDER BY sorting',
            [$voting['id']]
        );

        $strUrl = '';

        if ($model->jumpTo > 0) {
            $jumpTo = PageModel::findByPk($model->jumpTo);

            if (null !== $jumpTo) {
                $strUrl = $jumpTo->getFrontendUrl('/%s');
            }
        }

        $blnCanVote = $this->canUserVote($voting);

        // Setup form variables
        $doNotSubmit = false;
        $strFormId = 'voting_' . $model->id;
        $arrWidgets = [];

        $arrEnquiries = array();

        foreach ($enquiries as $enquiry) {
            $arrEnquiries[$enquiry['id']] = $enquiry;
            $arrEnquiries[$enquiry['id']]['href'] = sprintf($strUrl, $enquiry['alias']);

            // Setup form widgets
            if ($blnCanVote) {
                $strWidget = 'enquiry_' . $enquiry['id'];

                /** @type FormRadioButton $objWidget */
                $objWidget = new $GLOBALS['TL_FFL']['radio'](Widget::getAttributesFromDca([
                    'name'      => $strWidget,
                    'inputType' => 'radio',
                    'options'   => ['yes', 'no', 'abstention'],
                    'reference' => $GLOBALS['TL_LANG']['MSC']['voting_options'],
                    'eval'      => ['mandatory'=>true]
                ], $strWidget));

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
        if ($blnCanVote && !$doNotSubmit && Input::post('FORM_SUBMIT') === $strFormId) {

            $this->connection->executeStatement(
                'LOCK TABLES tl_voting_enquiry WRITE, tl_voting_registry WRITE'
            );

            // Check voting status again after tables are locked
            if ($this->canUserVote($voting)) {
                /** @var FrontendUser $user */
                $user = $this->security->getUser();

                foreach ($arrWidgets as $intEnquiry => $objWidget) {

                    // Do not insert vote record if user chose abstention
                    if ('yes' !== $objWidget->value && 'no' !== $objWidget->value) {
                        continue;
                    }

                    $strField = ('yes' === $objWidget->value) ? 'ayes' : 'nays';

                    $this->connection->executeStatement(
                        "UPDATE tl_voting_enquiry SET $strField=($strField+1) WHERE id=?",
                        [$intEnquiry]
                    );
                }

                // Store the voting in registry
                $this->connection->insert(
                    'tl_voting_registry',
                    [
                        'tstamp' => time(),
                        'voting' => $voting['id'],
                        'member' => $user->id
                    ]
                );
            }

            $this->connection->executeStatement('UNLOCK TABLES');

            Controller::reload();
        }

        $template->voting = $voting;
        $template->totalEnquiries = count($enquiries);
        $template->period = $this->getPeriod($voting);

        $template->enquiries = $arrEnquiries;
        $template->canVote = $blnCanVote;
        $template->hasVoted = $this->hasUserVoted($voting) && $this->isActive($voting);
        $template->formId = $strFormId;
        $template->submit = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['voting_vote']);

        return $template->getResponse();
    }
}
