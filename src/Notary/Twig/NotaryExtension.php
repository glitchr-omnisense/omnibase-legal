<?php

namespace Base\Legal\Notary\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The notaries' own in templates, beside the practice's (legal_record(),
 * legal_areas(), legal_mediator()...): legal_tariff() - the official text,
 * the order in force and its last day.
 */
class NotaryExtension extends AbstractExtension
{
    public function __construct(#[Autowire('%legal.notary.tariff%')] private readonly array $tariff = [])
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('legal_tariff', fn (): array => $this->tariff),
        ];
    }
}
