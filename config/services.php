<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Base\Legal\Practice\ProfileInterface;
use Base\Legal\Practice\Service\Record;
use Base\Legal\Profession;
use Base\Legal\Routing\ProfileRouteLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
 * Three parts, each a folder of src/ loaded on its own condition:
 *
 *   Practice/     what a firm and an office share, on omnibase/office: when
 *                 the profession keeps a practice (a lawyer, a notary);
 *   <Profession>/ the chosen profession's profile (Lawyer/, Notary/,
 *                 Jurist/) - and no other: a folder that is not loaded
 *                 gives the container nothing, not even a class to look at;
 *   the rest      the route loader.
 *
 * Every service of the profile carries the tag legal.profile, with its
 * profession: `debug:container --tag=legal.profile` lists what the profile
 * brought. Its compliance checks are tagged by omnibase/office's
 * autoconfiguration (ComplianceCheckInterface), its audience resolver too.
 * In each part the back office is loaded when omnibase/admin is installed,
 * the demonstration accounts when glitchr/omnibase has the demo environment.
 */
return function (ContainerConfigurator $configurator, ContainerBuilder $container) {
    $src = dirname(__DIR__).'/src';
    $profession = Profession::from($container->getParameter('legal.profession'));
    $admin = class_exists('Base\\Admin\\Controller\\AbstractCrudController');
    $demo = interface_exists('Base\\Demo\\DemoAccountProviderInterface');

    $services = $configurator->services();
    $services->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->public(false);

    // The routes of the practice and of the profile: `resource: .`, `type: legal` in the application's routes.
    $services->set(ProfileRouteLoader::class)
        ->arg('$profession', '%legal.profession%')
        ->arg('$practice', '%legal.practice%')
        ->arg('$admin', $admin)
        ->tag('routing.loader');

    // ── The practice ──────────────────────────────────────────────
    if ($container->getParameter('legal.practice')) {
        $practice = $src.'/Practice';
        $services->load('Base\\Legal\\Practice\\', $practice.'/')
            ->exclude([
                $practice.'/Entity/',
                $practice.'/Guard/',
                $practice.'/Controller/',
                $practice.'/Admin/',
            ]);
        $services->load('Base\\Legal\\Practice\\Controller\\Client\\', $practice.'/Controller/Client/')
            ->tag('controller.service_arguments');
        if ($admin) {
            $services->load('Base\\Legal\\Practice\\Controller\\Admin\\', $practice.'/Controller/Admin/')
                ->tag('controller.service_arguments');
            $services->load('Base\\Legal\\Practice\\Admin\\', $practice.'/Admin/');
        }
    }

    // ── The profile ───────────────────────────────────────────────
    $folder = $src.'/'.$profession->folder();
    $namespace = 'Base\\Legal\\'.$profession->folder().'\\';
    $tag = ['profession' => $profession->value];

    $services->load($namespace, $folder.'/')
        ->exclude([
            $folder.'/Entity/',
            $folder.'/Enum/',
            $folder.'/Guard/',
            $folder.'/Controller/',
            $folder.'/Admin/',
            $folder.'/Demo/',
            // What reads another bundle's contract: loaded below, when that bundle is there.
            $folder.'/Consulting/',
        ])
        ->tag('legal.profile', $tag);

    if (is_dir($folder.'/Controller/Client')) {
        $services->load($namespace.'Controller\\Client\\', $folder.'/Controller/Client/')
            ->tag('controller.service_arguments')
            ->tag('legal.profile', $tag);
    }
    if ($admin && is_dir($folder.'/Controller/Admin')) {
        $services->load($namespace.'Controller\\Admin\\', $folder.'/Controller/Admin/')
            ->tag('controller.service_arguments')
            ->tag('legal.profile', $tag);
    }
    if ($demo && is_dir($folder.'/Demo')) {
        $services->load($namespace.'Demo\\', $folder.'/Demo/')
            ->tag('legal.profile', $tag);
    }
    // A jurist's offerings are omnibase/consulting's: the guard reads them through that bundle's rule.
    if (is_dir($folder.'/Consulting') && interface_exists('Base\\Consulting\\Offering\\OfferingRuleInterface')) {
        $services->load($namespace.'Consulting\\', $folder.'/Consulting/')
            ->tag('legal.profile', $tag);
    }

    // What the practice asks of the profession: its profile and its record, under the names the practice knows.
    if ($container->getParameter('legal.practice')) {
        $services->alias(ProfileInterface::class, $namespace.$profession->folder().'Profile');
        $services->alias(Record::class, $namespace.'Service\\'.$profession->folder().'Record');
    }
};
