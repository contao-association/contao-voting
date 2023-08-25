<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

#[AsCallback(table: 'tl_voting', target: 'fields.alias.save')]
#[AsCallback(table: 'tl_voting_enquiry', target: 'fields.alias.save')]
class AliasListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Slug $slug,
    ) {
    }

    public function __invoke(string $varValue, DataContainer $dc): string
    {
        if ($varValue) {
            return $varValue;
        }

        return $this->slug->generate(
            $dc->activeRecord->name,
            $dc->activeRecord->id,
            fn (string $alias) => $this->connection->fetchOne(
                'SELECT COUNT(*) FROM '.$dc->table.' WHERE alias=?',
                [$alias],
            ) > 0,
        );
    }
}
