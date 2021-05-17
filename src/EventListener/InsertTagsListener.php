<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\EventListener;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * @Hook("replaceInsertTags")
 */
class InsertTagsListener
{
    private Connection $connection;
    private TokenChecker $tokenChecker;

    public function __construct(Connection $connection, TokenChecker $tokenChecker)
    {
        $this->connection = $connection;
        $this->tokenChecker = $tokenChecker;
    }

    public function __invoke(string $insertTag)
    {
        $parts = StringUtil::trimsplit('::', $insertTag);

        if ($parts[0] !== 'voting') {
            return false;
        }

        $voting = $this->connection->fetchAssociative('
            SELECT * FROM tl_voting WHERE alias=?'. (!$this->tokenChecker->isPreviewMode() ? ' AND published=1' : ''),
            [Input::get('auto_item')]
        );

        if (false === $voting) {
            return '';
        }

        return $voting[$parts[1]];
    }
}
