<?php

namespace Base\Legal\Lawyer\Compliance;

use Base\Legal\Lawyer\Service\LawyerRecord;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * « L'avocat informe son client, dès sa saisine, des modalités de
 * détermination des honoraires » (RIN art. 11.1) and « conclut par écrit
 * avec son client une convention d'honoraires » (art. 11.2). The fees page
 * always says the second; the first is the firm's own words, typed in the
 * settings: how its fees are set.
 */
final class FeesInformationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly LawyerRecord $record)
    {
    }

    public function check(): ComplianceResult
    {
        return null !== $this->record->getFees()
            ? ComplianceResult::ok('lawyer.compliance.fees', 'legal')
            : new ComplianceResult('lawyer.compliance.fees', ComplianceResult::MISSING, 'lawyer.compliance.fees_advice', 'legal');
    }
}
