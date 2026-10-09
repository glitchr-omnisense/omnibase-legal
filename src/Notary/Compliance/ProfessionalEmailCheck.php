<?php

namespace Base\Legal\Notary\Compliance;

use Base\Legal\Notary\Guard\EmailDomain;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\OfficeRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The office's e-mail addresses end with notaires.fr (règlement
 * professionnel du notariat, art. 16.3): the address each office shows to
 * the public, and the one the site writes to clients from.
 */
final class ProfessionalEmailCheck implements ComplianceCheckInterface
{
    public function __construct(
        private readonly OfficeRepository $offices,
        #[Autowire('%office.sender%')] private readonly ?string $sender = null,
        #[Autowire('%legal.notary.email_suffix%')] private readonly string $suffix = 'notaires.fr',
    ) {
    }

    public function check(): ComplianceResult
    {
        $wrong = [];
        $any = false;
        foreach ($this->offices->findOrdered() as $office) {
            if (null === $office->getEmail()) {
                continue;
            }
            $any = true;
            if (!EmailDomain::isNotarial($office->getEmail(), $this->suffix)) {
                $wrong[] = $office->getEmail();
            }
        }
        if ([] !== $wrong) {
            return new ComplianceResult('notary.compliance.email', ComplianceResult::MISSING, 'notary.compliance.email_advice', 'legal', ['addresses' => implode(', ', array_unique($wrong)), 'suffix' => $this->suffix]);
        }
        if (!$any) {
            return new ComplianceResult('notary.compliance.email', ComplianceResult::MISSING, 'notary.compliance.email_none', 'legal', ['suffix' => $this->suffix]);
        }
        if (null !== $this->sender && '' !== trim($this->sender) && !EmailDomain::isNotarial($this->sender, $this->suffix)) {
            return new ComplianceResult('notary.compliance.email', ComplianceResult::WARNING, 'notary.compliance.email_sender', 'legal', ['addresses' => $this->sender, 'suffix' => $this->suffix]);
        }

        return ComplianceResult::ok('notary.compliance.email', 'legal');
    }
}
