<?php

namespace Base\Legal\Lawyer\Compliance;

use Base\Legal\Lawyer\Payment\FeePayment;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * Paying online is for fees only (RIN art. 11.5); clients' funds go to the
 * CARPA (art. 6.2). Off: nothing to check. On without omnibase/marketplace
 * or without a route: the link is not shown, and the back office says why.
 * On and available: a reminder of what the payment page may take.
 */
final class PaymentScopeCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly FeePayment $payment)
    {
    }

    public function check(): ComplianceResult
    {
        if (!$this->payment->isEnabled()) {
            return ComplianceResult::ok('lawyer.compliance.payment', 'legal');
        }
        if (!$this->payment->isAvailable()) {
            return new ComplianceResult('lawyer.compliance.payment', ComplianceResult::WARNING, $this->payment->isInstalled() ? 'lawyer.compliance.payment_route' : 'lawyer.compliance.payment_missing', 'legal');
        }

        return new ComplianceResult('lawyer.compliance.payment', ComplianceResult::OK, 'lawyer.compliance.payment_scope', 'legal');
    }
}
