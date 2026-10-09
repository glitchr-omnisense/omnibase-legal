<?php

namespace Base\Legal\Practice\Controller\Client;

use Base\Legal\Practice\Repository\AreaRepository;
use Base\Legal\Practice\Service\Record;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The practice's public pages every profession has: the fields of practice
 * and the consumer mediator. What it costs is the profession's own page
 * (a lawyer's fees, a notary's tariff: its profile's controller).
 */
class PracticeController extends AbstractController
{
    public function __construct(private readonly OfficeRepository $offices, private readonly Record $record)
    {
    }

    #[Route('/domaines', name: 'legal_areas', methods: ['GET'])]
    public function areas(AreaRepository $areas): Response
    {
        return $this->render('@Legal/client/areas.html.twig', ['areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/domaines/{slug}', name: 'legal_area', methods: ['GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function area(string $slug, AreaRepository $areas): Response
    {
        $area = $areas->findOneActiveBySlug($slug) ?? throw $this->createNotFoundException();

        return $this->render('@Legal/client/area.html.twig', ['area' => $area, 'areas' => $areas->findActive(), 'office' => $this->offices->findMain()]);
    }

    #[Route('/mediation', name: 'legal_mediation', methods: ['GET'])]
    public function mediation(): Response
    {
        return $this->render('@Legal/client/mediation.html.twig', [
            'office' => $this->offices->findMain(),
            'record' => $this->record,
            'mediator' => $this->getParameter('legal.mediator'),
        ]);
    }
}
