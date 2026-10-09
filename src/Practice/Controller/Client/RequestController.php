<?php

namespace Base\Legal\Practice\Controller\Client;

use Base\Office\Controller\Client\AbstractRequestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Asking for a first appointment without an account: a practice's clients
 * are invited, so someone new could not go through omnibase/office's
 * booking pages, which ask to sign in. The request becomes an appointment
 * of the office in request mode (no time yet: the practice fixes it), under
 * the name, the e-mail and the phone given; the few words are kept
 * encrypted, as every reason is.
 *
 * The form, its model and what is done with the request are
 * omnibase/office's (AbstractRequestController, AppointmentRequests); the
 * route, the page and its words are here - what the profession says of its
 * own (nothing confidential yet; a lawyer: do not name the other party) is
 * its <profession>.request.* in the `legal` domain.
 */
class RequestController extends AbstractRequestController
{
    #[Route('/rendez-vous/demande', name: 'legal_request', methods: ['GET', 'POST'], priority: 10)]
    public function request(Request $request): Response
    {
        return $this->respond($request, '@Legal/client/request.html.twig', 'legal', '@legal.request');
    }
}
