<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\Security;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\FrontendUser;
use ContaoAssociation\VotingBundle\ContaoAssociationVotingPermissions;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VotingVoter extends Voter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return ContaoAssociationVotingPermissions::CAN_VOTE === $attribute
            && (is_numeric($subject) || (\is_array($subject) && isset($subject['id'], $subject['start'], $subject['stop'], $subject['groups'])));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        if (\is_array($subject)) {
            $voting = $subject;
        } else {
            $voting = $this->connection->fetchAssociative('SELECT * FROM tl_voting WHERE id=?', [$subject]);

            if (false === $voting) {
                return false;
            }
        }

        $time = time();

        if ($voting['start'] > $time || $voting['stop'] < $time) {
            return false;
        }

        if (!$this->accessDecisionManager->decide($token, [ContaoCorePermissions::MEMBER_IN_GROUPS], $voting['groups'])) {
            return false;
        }

        $votes = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) AS votes FROM tl_voting_registry WHERE voting=? AND member=?',
            [$voting['id'], $user->id],
        );

        return 0 === $votes;
    }
}
