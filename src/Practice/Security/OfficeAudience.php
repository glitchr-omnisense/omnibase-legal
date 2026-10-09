<?php

namespace Base\Legal\Practice\Security;

use Base\Office\Entity\Share\Document;
use Base\Office\Share\AudienceResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Professional secrecy within the practice, as the tool applies it to a
 * client's documents, beyond the client and whoever sent the document (the
 * voter's own business):
 *
 * - those who work on the files (legal.roles.readers: a firm's lawyers, an
 *   office's notaries and clerks, who draw up the deeds) see and read it;
 * - the rest of the staff (the secretariat, the reception) sees that it
 *   exists and what it is called, and reads only what is not marked
 *   confidential;
 * - nobody else: neither another client, nor the site's administrators as
 *   such.
 *
 * Only its sender withdraws a document.
 */
final class OfficeAudience implements AudienceResolverInterface
{
    /** @param list<string> $readerRoles */
    public function __construct(
        private readonly RoleHierarchyInterface $roles,
        #[Autowire('%legal.roles.readers%')] private readonly array $readerRoles = [],
        #[Autowire('%legal.roles.staff%')] private readonly string $staffRole = 'ROLE_STAFF',
    ) {
    }

    public function decide(string $attribute, Document $document, UserInterface $user): ?bool
    {
        if (self::REVOKE === $attribute || null === $document->getRecipient()) {
            return null;
        }
        $held = $this->roles->getReachableRoleNames($user->getRoles());
        if (!\in_array($this->staffRole, $held, true)) {
            return null;
        }
        if ([] !== array_intersect($this->readerRoles, $held)) {
            return true;
        }

        return self::VIEW === $attribute || !$document->isConfidential() ? true : null;
    }
}
