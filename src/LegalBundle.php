<?php

namespace Base\Legal;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The legal professions, one bundle and a profile for each
 * (legal.profession: lawyer | notary | jurist - one site, one profession).
 *
 * What a lawyer's firm and a notary's office share is written once, on
 * omnibase/office (src/Practice): the fields of practice, a first
 * appointment asked without an account, the client's space on the encrypted
 * vault, who of the practice reads a document, the settings, and the
 * compliance checks every regulated practice answers - it can be
 * identified, it names its consumer mediator, its texts carry no
 * comparison.
 *
 * What is a profession's own is its profile (src/Lawyer, src/Notary,
 * src/Jurist), loaded when it is the one chosen and then alone - its rules
 * as compliance checks, the words it must not use, its entities and their
 * tables, its pages, its demonstration accounts:
 *
 *   lawyer   article 10 of the RIN - the domain name, certified
 *            specialisations and dominant fields, the fees and their
 *            agreement, the declaration to the conseil de l'Ordre, fees
 *            paid online and nothing else;
 *   notary   the règlement professionnel - addresses in notaires.fr, the
 *            regulated tariff and the discounts, the declaration to the
 *            chamber, the profession's charter; no online payment;
 *   jurist   the law of 31 December 1971 - nothing a regulated profession
 *            alone may do is offered.
 *
 * Compliance built into the code does not replace the control of the
 * Ordre or of the chamber.
 */
class LegalBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /** Modern layout: the class lives in src/, the bundle root is the package root. */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // The App\ names an application may extend, for what the professions share. No entity sits in src/Entity:
        // Doctrine would map it for every site, whatever its profession - LegalExtension::prepend() maps the
        // practice's, and the chosen profile's (whose repositories are services of its folder, loaded with it).
        $this->setMapping($this->getPath().'/src/Practice/Entity', 'Base\Legal\Practice\Entity', 'App\Entity\Legal');
        $this->setMapping($this->getPath().'/src/Practice/Repository', 'Base\Legal\Practice\Repository', 'App\Repository\Legal');
    }
}
