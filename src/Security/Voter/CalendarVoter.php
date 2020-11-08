<?php

namespace App\Security\Voter;

use App\Entity\Calendar;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CalendarVoter extends Voter
{
    public const EDIT = 'calendar_edit';

    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [static::EDIT])
            && $subject instanceof Calendar;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case static::EDIT:
                return $this->security->isGranted('ROLE_ADMIN')
                    || $subject->getCreatedBy() === $user;
        }

        return false;
    }
}
