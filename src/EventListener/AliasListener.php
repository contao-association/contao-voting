<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;
use Contao\DataContainer;
use Contao\CoreBundle\Slug\Slug;

/**
 * @Callback(table="tl_voting", target="fields.alias.save")
 * @Callback(table="tl_voting_enquiry", target="fields.alias.save")
 */
class AliasListener
{
    private Connection $connection;
    private Slug $slug;

    public function __construct(Connection $connection, Slug $slug)
    {
        $this->connection = $connection;
        $this->slug = $slug;
    }

    public function __invoke($varValue, DataContainer $dc): string
    {
        if ($varValue) {
            return $varValue;
        }

        return $this->slug->generate
        (
            $dc->activeRecord->name,
            $dc->activeRecord->id,
            function (string $alias) use ($dc)
            {
                return $this->connection->fetchOne(
                    'SELECT COUNT(*) FROM '.$dc->table.' WHERE alias=?',
                    [$alias]
                ) > 0;
            }
        );
    }
}
