<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoAssociation\VotingBundle\ContaoAssociationVotingBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(ContaoAssociationVotingBundle::class))->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
