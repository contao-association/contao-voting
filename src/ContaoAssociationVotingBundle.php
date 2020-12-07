<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoAssociationVotingBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
