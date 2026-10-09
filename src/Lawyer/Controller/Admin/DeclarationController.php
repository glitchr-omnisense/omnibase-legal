<?php

namespace Base\Legal\Lawyer\Controller\Admin;

use Base\Legal\Lawyer\Repository\AttorneyRepository;
use Base\Legal\Lawyer\Service\Domains;
use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Legal\Practice\Repository\AreaRepository;
use Base\Legal\Practice\Controller\Admin\AdminScreenTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The memo of what is declared to the conseil de l'Ordre, filled with what
 * the site says today: its domain names (RIN art. 10.5), each lawyer's
 * specialisations, qualifications and dominant fields as they are shown
 * (art. 10.2: their terms are sent to the Ordre), the fields of practice
 * the firm presents (art. 10.3: any publicity is communicated). To print
 * or to copy into the letter - the bundle sends nothing itself.
 */
#[IsGranted('ROLE_ADMIN')]
class DeclarationController extends AbstractController
{
    use AdminScreenTrait;

    #[Route('/admin/legal/declaration', name: 'legal_admin_declaration', methods: ['GET'], defaults: ['_nest' => true])]
    public function index(LawyerRecord $record, Domains $domains, AttorneyRepository $attorneys, AreaRepository $areas): Response
    {
        return $this->page('@Legal/lawyer/admin/declaration.html.twig', [
            'record' => $record,
            'verdicts' => $domains->verdicts(),
            'attorneys' => $attorneys->findVisible(),
            'areas' => $areas->findActive(),
        ]);
    }
}
