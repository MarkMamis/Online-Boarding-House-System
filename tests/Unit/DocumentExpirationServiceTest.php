<?php

namespace Tests\Unit;

use App\Services\DocumentExpirationService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DocumentExpirationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-10 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function service(): DocumentExpirationService
    {
        return new DocumentExpirationService();
    }

    public function test_more_than_60_days_is_valid(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-11-01'));

        $this->assertSame('valid', $info['status']);
        $this->assertSame('normal', $info['urgency']);
        $this->assertSame(83, $info['days_remaining']);
        $this->assertSame('Valid for 83 more days', $info['label']);
    }

    public function test_exactly_60_days_is_expiring_soon(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-10-09'));

        $this->assertSame('expiring_soon', $info['status']);
        $this->assertSame('warning_60', $info['urgency']);
        $this->assertSame(60, $info['days_remaining']);
        $this->assertSame('Expiring in 60 days', $info['label']);
    }

    public function test_30_days_is_expiring_soon(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-09-09'));

        $this->assertSame('expiring_soon', $info['status']);
        $this->assertSame('warning_30', $info['urgency']);
        $this->assertSame(30, $info['days_remaining']);
    }

    public function test_7_days_is_critical_expiring_soon(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-08-17'));

        $this->assertSame('expiring_soon', $info['status']);
        $this->assertSame('critical_7', $info['urgency']);
        $this->assertSame(7, $info['days_remaining']);
    }

    public function test_expiration_today_is_expired(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-08-10'));

        $this->assertSame('expired', $info['status']);
        $this->assertSame('expired', $info['urgency']);
        $this->assertSame(0, $info['days_remaining']);
        $this->assertSame('Expired today', $info['label']);
    }

    public function test_past_expiration_is_expired(): void
    {
        $info = $this->service()->expirationInfo(Carbon::parse('2026-08-01'));

        $this->assertSame('expired', $info['status']);
        $this->assertSame('expired', $info['urgency']);
        $this->assertSame(-9, $info['days_remaining']);
        $this->assertSame('Expired 9 days ago', $info['label']);
    }

    public function test_null_expiration_has_no_expiration_state(): void
    {
        $info = $this->service()->expirationInfo(null);

        $this->assertSame('valid', $info['status']);
        $this->assertSame('normal', $info['urgency']);
        $this->assertNull($info['days_remaining']);
        $this->assertSame('No expiration date', $info['label']);
    }

    public function test_time_of_day_does_not_affect_days_remaining(): void
    {
        // Expiration date at 23:59 vs 00:01 must yield the same day math.
        $late = Carbon::parse('2026-08-17 23:59:00');
        $early = Carbon::parse('2026-08-17 00:01:00');

        $this->assertSame(7, $this->service()->daysUntilExpiration($late));
        $this->assertSame(7, $this->service()->daysUntilExpiration($early));
    }
}
