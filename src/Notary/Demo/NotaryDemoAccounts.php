<?php

namespace Base\Legal\Notary\Demo;

use Base\Demo\DemoAccount;
use Base\Demo\DemoAccountProviderInterface;

/**
 * The demonstration accounts of a notary's office (glitchr/omnibase's `demo`
 * environment): one for each role the profile and omnibase/office know. The
 * roles of the office come from three groups, as in a real one - "Notaires"
 * (ROLE_NOTARY), "Clercs" (ROLE_CLERK) and "Accueil" (ROLE_STAFF), the first
 * two ROLE_STAFF through the application's role hierarchy; "etude" is the
 * office's administrator.
 *
 * An application's fixtures take them from Base\Demo\DemoAccountFactory
 * ($accounts->account('notaire', $manager)) and attach what makes them worth
 * signing in as: a member of the team and an agenda to the notary, a request
 * and documents in the vault to the client. An office without one of these
 * roles leaves it out: base.demo.exclude.
 *
 * Registered when the installed glitchr/omnibase has the demo environment
 * (config/services.php).
 */
final class NotaryDemoAccounts implements DemoAccountProviderInterface
{
    public const NOTARIES = 'Notaires';
    public const CLERKS = 'Clercs';
    public const RECEPTION = 'Accueil';
    public const CLIENTS = 'Clients';

    public function getDemoAccounts(): iterable
    {
        yield new DemoAccount('notaire', '@legal.notary.demo.notaire.label', '@legal.notary.demo.notaire.description', group: self::NOTARIES, groupRoles: ['ROLE_NOTARY'], position: 10);
        yield new DemoAccount('clerc', '@legal.notary.demo.clerc.label', '@legal.notary.demo.clerc.description', group: self::CLERKS, groupRoles: ['ROLE_CLERK'], position: 20);
        yield new DemoAccount('accueil', '@legal.notary.demo.accueil.label', '@legal.notary.demo.accueil.description', group: self::RECEPTION, groupRoles: ['ROLE_STAFF'], position: 30);
        yield new DemoAccount('etude', '@legal.notary.demo.etude.label', '@legal.notary.demo.etude.description', roles: ['ROLE_ADMIN'], position: 40);
        yield new DemoAccount('client', '@legal.notary.demo.client.label', '@legal.notary.demo.client.description', group: self::CLIENTS, position: 50);
    }
}
