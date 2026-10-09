<?php

namespace Base\Legal\Lawyer\Controller\Client;

use Base\Legal\Lawyer\Payment\FeePayment;
use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Office\Repository\Booking\AppointmentTypeRepository;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The fees: how they are set, the fee agreement, how they are paid (RIN
 * art. 11.1, 11.2, 11.5) - the lawyers' page of what it costs.
 */
class FeesController extends AbstractController
{
    #[Route('/honoraires', name: 'legal_fees', methods: ['GET'])]
    public function fees(OfficeRepository $offices, LawyerRecord $record, FeePayment $payment, MemberRepository $members, AppointmentTypeRepository $types): Response
    {
        // What a first meeting costs, as the appointment types print it.
        $priced = [];
        foreach ($members->findBookable() as $member) {
            foreach ($types->findForMember($member) as $type) {
                if (null !== $type->getPrice()) {
                    $priced[$type->getId()] = $type;
                }
            }
        }

        return $this->render('@Legal/lawyer/fees.html.twig', [
            'office' => $offices->findMain(),
            'record' => $record,
            'payment' => $payment,
            'priced' => array_values($priced),
        ]);
    }
}
