<?php

namespace Base\Legal\Notary\Guard;

/**
 * "L'adresse de messagerie professionnelle sortante du notaire se termine
 * par « .notaires.fr », afin d'éviter toute confusion dans l'esprit du
 * public" (règlement professionnel du notariat, art. 16.3, approved by the
 * order of 29 January 2024).
 *
 * The host of the address is notaires.fr itself (prenom.nom@notaires.fr) or
 * one of its subdomains (@paris.notaires.fr); a look-alike
 * (@notaires.fr.example, @mes-notaires.fr) is not.
 */
final class EmailDomain
{
    public static function isNotarial(?string $email, string $suffix = 'notaires.fr'): bool
    {
        $email = mb_strtolower(trim((string) $email));
        $suffix = ltrim(mb_strtolower(trim($suffix)), '.@');
        $at = strrpos($email, '@');
        if (false === $at || 0 === $at || '' === $suffix || false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $host = substr($email, $at + 1);

        return $host === $suffix || str_ends_with($host, '.'.$suffix);
    }
}
