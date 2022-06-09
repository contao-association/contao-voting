<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Controller;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Date;
use Contao\FrontendUser;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;

abstract class AbstractVotingController extends AbstractFrontendModuleController
{
    protected Security $security;
    protected Connection $connection;
    protected TokenChecker $tokenChecker;

    public function __construct(Security $security, Connection $connection, TokenChecker $tokenChecker)
    {
        $this->security = $security;
        $this->connection = $connection;
        $this->tokenChecker = $tokenChecker;
    }

    protected function canUserVote(array $voting): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        if (!$this->isActive($voting)) {
            return false;
        }

        // User is not in an allowed member group
        if (\count(array_intersect($user->groups, StringUtil::deserialize($voting['groups'], true))) < 1) {
            return false;
        }

        // User already voted before
        if ($this->hasUserVoted($voting)) {
            return false;
        }

        return true;
    }

    protected function hasUserVoted(array $voting): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $votes = $this->connection->fetchOne(
            'SELECT COUNT(*) AS votes FROM tl_voting_registry WHERE voting=? AND member=?',
            [$voting['id'], $user->id]
        );

        return $votes > 0;
    }

    protected function isActive(array $voting): bool
    {
        $time = time();

        return $voting['start'] <= $time && $voting['stop'] >= $time;
    }

    protected function getPeriod(array $voting): string
    {
        $dateFormat = isset($GLOBALS['objPage']) ? $GLOBALS['objPage']->dateFormat : $GLOBALS['TL_CONFIG']['dateFormat'];

        return sprintf(
            '%s – %s',
            Date::parse($dateFormat, $voting['start']),
            Date::parse($dateFormat, $voting['stop'])
        );
    }
}
