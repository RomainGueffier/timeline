<?php

namespace App\Security;

use App\Entity\Timeline;
use App\Entity\Character;
use App\Entity\Event;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TimelineVoter extends Voter
{
    // these strings are just invented: you can use anything
    const READ = 'read';
    const EDIT = 'edit';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::READ, self::EDIT])) {
            return false;
        }

        // only vote on objects of Timeline and his data
        if (!$subject instanceof Timeline
            && !$subject instanceof Event
            && !$subject instanceof Character
            && !$subject instanceof Category) {
            return false;
        }
       
        return true;
    }

    protected function voteOnAttribute(string $attribute, $entity, TokenInterface $token)
    {
        $user = $token->getUser();
        
        // else if user is connected
        /** @var Timeline / Character / Event / Category $subject */
        switch ($attribute) {
            case self::READ:
                return $this->canRead($entity, $user);
            case self::EDIT:
                return $this->canEdit($entity, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canRead(object $entity, $user)
    {
        // if user is anomymous
        if (!$user instanceof User) {
            return $entity->isPublic();
        }

        // if user connected, also check visibility
        // check if the entity is public or private reading
        if ($entity->isPublic()) {
            return true;
        }

        // if not public check if the current user own the entity
        return $this->canEdit($entity, $user);
    }

    private function canEdit(object $entity, $user)
    {
        // if user is anomymous
        if (!$user instanceof User) {
            return false;
        }

        return $user === $entity->getUser();
    }
}