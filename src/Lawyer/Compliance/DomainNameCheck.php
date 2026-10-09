<?php

namespace Base\Legal\Lawyer\Compliance;

use Base\Legal\Lawyer\Guard\DomainName;
use Base\Legal\Lawyer\Service\Domains;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * The domain name carries the lawyer's name or the firm's denomination,
 * and evokes generically neither the title, nor a field of law, nor an
 * activity of the profession (RIN art. 10.5).
 */
final class DomainNameCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Domains $domains)
    {
    }

    public function check(): ComplianceResult
    {
        $verdicts = $this->domains->verdicts();
        if ([] === $verdicts) {
            return new ComplianceResult('lawyer.compliance.domain', ComplianceResult::WARNING, 'lawyer.compliance.domain_none', 'legal');
        }
        $by = [];
        foreach ($verdicts as $domain => $verdict) {
            $by[$verdict][] = $domain;
        }
        if (isset($by[DomainName::GENERIC])) {
            return new ComplianceResult('lawyer.compliance.domain', ComplianceResult::MISSING, 'lawyer.compliance.domain_generic', 'legal', ['domains' => implode(', ', $by[DomainName::GENERIC])]);
        }
        foreach ([DomainName::MIXED => 'lawyer.compliance.domain_mixed', DomainName::UNNAMED => 'lawyer.compliance.domain_unnamed', DomainName::LOCAL => 'lawyer.compliance.domain_local'] as $verdict => $advice) {
            if (isset($by[$verdict])) {
                return new ComplianceResult('lawyer.compliance.domain', ComplianceResult::WARNING, $advice, 'legal', ['domains' => implode(', ', $by[$verdict])]);
            }
        }

        return ComplianceResult::ok('lawyer.compliance.domain', 'legal');
    }
}
