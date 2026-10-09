<?php

namespace Base\Legal\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Base\Legal\Profession;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class LegalConfiguration extends AbstractBaseConfiguration
{
    /**
     * What a profession's defaults are where a site says nothing: who of the
     * practice reads a client's document, the consumer mediator the
     * profession set up, where its rules are published.
     */
    public const DEFAULTS = [
        'lawyer' => [
            'readers' => ['ROLE_LAWYER'],
            'mediator' => [
                'name' => 'Médiateur de la consommation de la profession d’avocat',
                'address' => '180 boulevard Haussmann, 75008 Paris',
                'email' => 'mediateur-conso@mediateur-consommation-avocat.fr',
                'url' => 'https://mediateur-consommation-avocat.fr',
            ],
            'rules_url' => 'https://www.cnb.avocat.fr/fr/reglement-interieur-national-de-la-profession-davocat-rin',
        ],
        'notary' => [
            'readers' => ['ROLE_NOTARY', 'ROLE_CLERK'],
            'mediator' => [
                'name' => 'Médiateur de la consommation du notariat',
                'address' => '60 boulevard de La Tour-Maubourg, 75007 Paris',
                'email' => null,
                'url' => 'https://mediateur-notariat.notaires.fr',
            ],
            'rules_url' => null,
        ],
        'jurist' => [
            'readers' => [],
            'mediator' => ['name' => null, 'address' => null, 'email' => null, 'url' => null],
            'rules_url' => null,
        ],
    ];

    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('profession')->values(Profession::values())->isRequired()
                    ->info('The profession the site is for: its profile is loaded, and it alone - its compliance checks, its entities and their tables, its pages, its demonstration accounts.')->end()
                ->arrayNode('roles')->addDefaultsIfNotSet()
                    ->info('The roles omnibase\'s groups give (security.yaml puts the readers under the staff\'s).')
                    ->children()
                        ->arrayNode('readers')
                            ->info('Who of the practice reads a client\'s document; the rest of the staff sees that it exists. Default: the profession\'s - a lawyer [ROLE_LAWYER], a notary [ROLE_NOTARY, ROLE_CLERK].')
                            ->beforeNormalization()->ifString()->then(static fn (string $v) => [$v])->end()
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode('staff')->defaultValue('ROLE_STAFF')->end()
                    ->end()
                ->end()
                ->arrayNode('mediator')
                    ->info('The consumer mediator whose coordinates the practice gives (/mediation, the legal notice). Default: the one the profession set up.')
                    ->children()
                        ->scalarNode('name')->defaultNull()->end()
                        ->scalarNode('address')->defaultNull()->end()
                        ->scalarNode('email')->defaultNull()->end()
                        ->scalarNode('url')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('rules_url')
                    ->info('The profession\'s rules, linked from the legal notice. Default: the profession\'s (a lawyer: the RIN on the Conseil national des barreaux\'s site).')->end()
                ->arrayNode('wording')
                    ->info('Expressions added to the built-in list the texts are checked against (comparison, disparagement, and the profession\'s own).')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('lawyer')->addDefaultsIfNotSet()
                    ->info('The lawyers\' profile.')
                    ->children()
                        ->arrayNode('domains')
                            ->info('The domain names the site answers on, checked against RIN art. 10.5 and listed in the memo for the Ordre. None: the host of the router\'s default address.')
                            ->beforeNormalization()->ifString()->then(static fn (string $v) => array_values(array_filter(array_map('trim', explode(',', $v)))))->end()
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->scalarNode('default_uri')->defaultValue('%env(default::DEFAULT_URI)%')
                            ->info('Where the domain name is read when legal.lawyer.domains is empty.')->end()
                        ->arrayNode('payment')->addDefaultsIfNotSet()
                            ->info('Paying a fee note online - fees only, never clients\' funds. Needs omnibase/marketplace: without it the link is not shown, whatever is set here.')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('route')->defaultNull()
                                    ->info('The application\'s route where a fee note is paid (the marketplace\'s checkout of a fee note).')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('notary')->addDefaultsIfNotSet()
                    ->info('The notaries\' profile.')
                    ->children()
                        ->scalarNode('email_suffix')->defaultValue('notaires.fr')
                            ->info('What a notary\'s professional e-mail address ends with (règlement professionnel, art. 16.3).')->end()
                        ->arrayNode('tariff')->addDefaultsIfNotSet()
                            ->info('Where the regulated tariff is read: the official text, the order in force, the profession\'s explanation.')
                            ->children()
                                ->scalarNode('code_url')->defaultValue('https://www.legifrance.gouv.fr/codes/texte_lc/LEGITEXT000005634379')
                                    ->info('Code de commerce, articles A444-53 and following (the notaries\' tariff).')->end()
                                ->scalarNode('order')->defaultValue('arrêté du 25 février 2026')
                                    ->info('The order fixing the tariff in force, as it is named on the page.')->end()
                                ->scalarNode('order_url')->defaultValue('https://www.legifrance.gouv.fr/eli/arrete/2026/2/25/ECOC2604872A/jo/texte')->end()
                                ->scalarNode('until')->defaultValue('2028-02-29')
                                    ->info('The last day of the order\'s period: past it, the back office asks for the new order.')->end()
                                ->scalarNode('info_url')->defaultValue('https://www.notaires.fr/fr/profession-notaire/le-tarif-du-notaire-emoluments-et-honoraires')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('jurist')->addDefaultsIfNotSet()
                    ->info('The jurists\' profile.')
                    ->children()
                        ->enumNode('authorized_status')->values([null, 'teacher', 'regulated_profession'])->defaultNull()
                            ->info('A status that lets the jurist give legal advice all the same (law of 31 December 1971): a university teacher of law (art. 57), another regulated profession within its own field (art. 59). A lawyer or a notary is not a jurist\'s status: that is another profession.')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * The configuration once the chosen profession's defaults are filled in.
     *
     * @param array<string, mixed> $config the processed tree
     *
     * @return array<string, mixed>
     */
    public static function withProfessionDefaults(array $config): array
    {
        $defaults = self::DEFAULTS[$config['profession']];

        if ([] === ($config['roles']['readers'] ?? [])) {
            $config['roles']['readers'] = $defaults['readers'];
        }
        // A mediator the site names replaces the profession's whole: never half of one with the other's address.
        if (!\array_key_exists('mediator', $config)) {
            $config['mediator'] = $defaults['mediator'];
        }
        if (!\array_key_exists('rules_url', $config)) {
            $config['rules_url'] = $defaults['rules_url'];
        }
        // The other professions' sections say nothing here: no parameter of a profile that is not the site's.
        foreach (Profession::values() as $profession) {
            if ($profession !== $config['profession']) {
                unset($config[$profession]);
            }
        }

        return $config;
    }
}
