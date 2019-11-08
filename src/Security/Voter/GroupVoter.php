<?php
/**
 *  This file is a part of SebkSmallUserBundle
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallUserBundle\Security;

use App\SmallSchedulerModelBundle\Model\User;
use App\SmallSchedulerModelBundle\Model\Group;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    const CONTROL = "control";

    /**
     * Check if vote supported
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if(!in_array($attribute,  [static::CONTROL])) {
            return false;
        }

        if(!$subject instanceof Group) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $loggedUser */
        $loggedUser = $token->getUser();

        switch($attribute) {
            case static::CONTROL:
                return $this->canControl($subject, $loggedUser);

            default:
                throw new \LogicException("Security failure");
        }

        return false;
    }

    /**
     * Can user control group
     * @param $subject
     * @param $user
     * @return bool
     */
    protected function canControl(Group $subject, User $user)
    {
        if ($user->hasRole("ROLE_ADMIN")) {
            return true;
        }

        $subject->loadToMany("groupUsers");
        foreach ($subject->getGroupUsers() as $userGroup) {
            if ($user->getId() == $userGroup->getUserId()) {
                return true;
            }
        }

        return false;
    }
}