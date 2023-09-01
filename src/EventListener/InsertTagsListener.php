<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

#[AsHook('replaceInsertTags')]
class InsertTagsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    public function __invoke(string $insertTag): string|false
    {
        $parts = StringUtil::trimsplit('::', $insertTag);

        if ('voting' !== $parts[0]) {
            return false;
        }

        $voting = $this->connection->fetchAssociative(
            'SELECT * FROM tl_voting WHERE alias=?'.(!$this->tokenChecker->isPreviewMode() ? ' AND published=1' : ''),
            [Input::get('auto_item')],
        );

        if (false === $voting || !isset($voting[$parts[1]])) {
            return '';
        }

        return (string) $voting[$parts[1]];
    }
}
