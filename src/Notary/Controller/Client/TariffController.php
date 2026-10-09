<?php

namespace Base\Legal\Notary\Controller\Client;

use Base\Legal\Notary\Service\NotaryRecord;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The tariff - the notaries' page of what it costs: regulated, so the page
 * explains it and links to the official text, with the office's discounts
 * and its fees for what the tariff does not cover.
 */
class TariffController extends AbstractController
{
    #[Route('/tarifs', name: 'legal_tariff', methods: ['GET'])]
    public function tariff(OfficeRepository $offices, NotaryRecord $record): Response
    {
        return $this->render('@Legal/notary/tariff.html.twig', [
            'office' => $offices->findMain(),
            'record' => $record,
            'tariff' => $this->getParameter('legal.notary.tariff'),
            'mediator' => $this->getParameter('legal.mediator'),
        ]);
    }
}
