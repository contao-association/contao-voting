<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Migration;

use Contao\CoreBundle\Migration\Version500\AbstractBasicEntitiesMigration;

class BasicEntitiesMigration extends AbstractBasicEntitiesMigration
{
    protected function getDatabaseColumns(): array
    {
        return [
            ['tl_voting', 'name'],
            ['tl_voting', 'description'],
            ['tl_voting_enquiry', 'name'],
            ['tl_voting_enquiry', 'teaser'],
            ['tl_voting_enquiry', 'description'],
            ['tl_voting_enquiry', 'recommendation'],
        ];
    }
}
