<?php

namespace Base\Legal\Lawyer\Twig;

use Base\Legal\Lawyer\Entity\Attorney;
use Base\Legal\Lawyer\Payment\FeePayment;
use Base\Legal\Lawyer\Repository\AttorneyRepository;
use Base\Office\Entity\Member;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The lawyers' own in templates, beside the practice's (legal_record(),
 * legal_areas(), legal_mediator()...): legal_attorney(member) (bar, oath,
 * certified specialisations, dominant fields) and legal_payment() (the
 * hook for paying fees online).
 */
class LawyerExtension extends AbstractExtension
{
    public function __construct(private readonly AttorneyRepository $attorneys, private readonly FeePayment $payment)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('legal_attorney', fn (?Member $member): ?Attorney => $this->attorneys->findOneByMember($member)),
            new TwigFunction('legal_payment', fn (): FeePayment => $this->payment),
        ];
    }
}
