<?php

namespace Base\Legal\Tests\Lawyer;

use Base\Legal\Lawyer\Compliance\PaymentScopeCheck;
use Base\Legal\Lawyer\Payment\FeePayment;
use Base\Office\Compliance\ComplianceResult;
use Base\Legal\Tests\NeedsOffice;
use PHPUnit\Framework\TestCase;

/** The hook for paying fees online: shown only when the marketplace is installed, the option on and a route named. */
final class FeePaymentTest extends TestCase
{
    use NeedsOffice;

    public function testOffByDefault(): void
    {
        $payment = new FeePayment();

        self::assertFalse($payment->isAvailable());
        self::assertNull($payment->getRoute());
        self::assertTrue((new PaymentScopeCheck($payment))->check()->isOk());
    }

    public function testOnWithoutTheMarketplaceTheLinkIsNotShown(): void
    {
        $payment = new FeePayment(true, 'app_fee_note', false);

        self::assertFalse($payment->isAvailable());
        $result = (new PaymentScopeCheck($payment))->check();
        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertSame('lawyer.compliance.payment_missing', $result->advice);
    }

    public function testOnWithoutARouteNeither(): void
    {
        $result = (new PaymentScopeCheck(new FeePayment(true, null, true)))->check();

        self::assertSame('lawyer.compliance.payment_route', $result->advice);
    }

    public function testInstalledEnabledAndRoutedItIsAvailableWithItsReminder(): void
    {
        $payment = new FeePayment(true, 'app_fee_note', true);

        self::assertTrue($payment->isAvailable());
        self::assertSame('app_fee_note', $payment->getRoute());
        $result = (new PaymentScopeCheck($payment))->check();
        self::assertTrue($result->isOk());
        self::assertSame('lawyer.compliance.payment_scope', $result->advice, 'fees only: the reminder stays');
    }

    public function testTheMarketplaceIsLookedForByItsClass(): void
    {
        self::assertSame(class_exists(FeePayment::MARKETPLACE), (new FeePayment(true, 'x'))->isInstalled());
    }
}
