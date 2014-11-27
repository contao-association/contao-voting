<?php

/**
 * voting extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/contao-association/contao-voting
 */

abstract class ModuleVoting extends \Module
{
    /**
     * Return true if the user can vote
     *
     * @param \Database_Result $objVoting
     *
     * @return bool
     */
    protected function canUserVote($objVoting)
    {
        if (!FE_USER_LOGGED_IN) {
            return false;
        }

        if (!$this->isActive($objVoting)) {
            return false;
        }

        // User is not in an allowed member group
        if (count(array_intersect($this->User->groups, deserialize($objVoting->groups, true))) < 1) {
            return false;
        }

        // User already voted before
        if ($this->hasUserVoted($objVoting)) {
            return false;
        }

        return true;
    }

    protected function hasUserVoted($objVoting)
    {
        if (!FE_USER_LOGGED_IN) {
            return false;
        }

        $objVoted = $this->Database->prepare("SELECT COUNT(*) AS votes FROM tl_voting_registry WHERE voting=? AND member=?")
                                   ->executeUncached($objVoting->id, $this->User->id);

        // User already voted before
        if ($objVoted->votes > 0) {
            return true;
        }

        return false;
    }

    protected function isActive($objVoting)
    {
        $time = time();

        if ($objVoting->start <= $time && $objVoting->stop >= $time) {
            return true;
        }

        return false;
    }
}