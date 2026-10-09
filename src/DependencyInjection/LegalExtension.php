<?php

namespace Base\Legal\DependencyInjection;

use Base\Bundle\AbstractBaseExtension;
use Base\Legal\Profession;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class LegalExtension extends AbstractBaseExtension implements PrependExtensionInterface
{
    public function getConfiguration(array $config, ContainerBuilder $container): LegalConfiguration
    {
        return new LegalConfiguration();
    }

    /**
     * The profession as the configuration files write it, the last one that
     * sets it winning: prepend() needs it before anything is processed.
     *
     * @param list<array<string, mixed>> $configs legal's configurations, unprocessed
     */
    public static function profession(array $configs): ?Profession
    {
        $profession = null;
        foreach ($configs as $config) {
            if (\is_string($config['profession'] ?? null)) {
                $profession = Profession::tryFrom($config['profession']) ?? $profession;
            }
        }

        return $profession;
    }

    /**
     * What the chosen profession sets before the other bundles read their
     * configuration:
     *
     *   - the practice's entities (the fields of practice) and the
     *     profile's own (a lawyer's Attorney), mapped for it alone - a site
     *     has no table of a profession that is not its own;
     *   - omnibase/office's pages in the profession's words: the contact
     *     form's warning (nothing confidential here: the client's space is
     *     for that), the menu of the client's space, where the e-mails send
     *     the client.
     */
    public function prepend(ContainerBuilder $container): void
    {
        $profession = self::profession($container->getExtensionConfig('legal'));
        if (null === $profession) {
            return;     // load() says what is missing
        }
        $src = \dirname(__DIR__);
        $mappings = [];

        if ($profession->hasPractice() && $container->hasExtension('office')) {
            $container->prependExtensionConfig('office', [
                'templates' => ['space_nav' => '@Legal/client/space/_nav.html.twig'],
                'contact' => ['notice' => '@legal.'.$profession->value.'.contact.notice'],
                'booking' => ['space_route' => 'legal_space'],
            ]);
            $mappings['LegalPractice'] = ['dir' => $src.'/Practice/Entity', 'prefix' => 'Base\\Legal\\Practice\\Entity'];
        }
        if (is_dir($src.'/'.$profession->folder().'/Entity')) {
            $mappings['Legal'.$profession->folder()] = ['dir' => $src.'/'.$profession->folder().'/Entity', 'prefix' => 'Base\\Legal\\'.$profession->folder().'\\Entity'];
        }
        if ([] !== $mappings && $container->hasExtension('doctrine')) {
            foreach ($mappings as $name => $mapping) {
                $mappings[$name] = $mapping + ['is_bundle' => false, 'type' => 'attribute', 'alias' => $name];
            }
            $container->prependExtensionConfig('doctrine', ['orm' => ['mappings' => $mappings]]);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new LegalConfiguration();
        $config = LegalConfiguration::withProfessionDefaults((new Processor())->processConfiguration($configuration, $configs));
        $profession = Profession::from($config['profession']);

        $bundles = $container->hasParameter('kernel.bundles') ? (array) $container->getParameter('kernel.bundles') : [];
        $practice = $profession->hasPractice();
        if ($practice && !isset($bundles['OfficeBundle'])) {
            throw new \LogicException(\sprintf('legal.profession "%s" keeps a practice on omnibase/office: register Base\\Office\\OfficeBundle (config/bundles.php) before Base\\Legal\\LegalBundle.', $profession->value));
        }

        // Flat parameters: legal.profession, legal.roles.readers, legal.mediator, legal.lawyer.domains...
        $this->setConfiguration($container, $config, $configuration->getTreeBuilder()->buildTree()->getName());
        $container->setParameter('legal.practice', $practice);

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');
    }
}
