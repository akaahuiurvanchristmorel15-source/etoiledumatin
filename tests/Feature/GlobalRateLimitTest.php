<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GlobalRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeTime();
    }

    public function test_an_ip_exceeding_the_per_second_limit_is_blocked(): void
    {
        $this->sendRequests(AppServiceProvider::MAX_REQUESTS_PER_SECOND, '203.0.113.10')
            ->each(fn (int $status) => $this->assertSame(200, $status));

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get(route('seo.robots'))
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertSee('Trop de requêtes');
    }

    public function test_the_limit_resets_after_one_second(): void
    {
        $this->sendRequests(AppServiceProvider::MAX_REQUESTS_PER_SECOND + 1, '203.0.113.10');

        $this->travel(2)->seconds();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get(route('seo.robots'))->assertOk();
    }

    public function test_a_flooding_ip_does_not_block_other_visitors(): void
    {
        $this->sendRequests(AppServiceProvider::MAX_REQUESTS_PER_SECOND + 1, '203.0.113.10');

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])->get(route('seo.robots'))->assertOk();
    }

    public function test_paystack_webhook_is_never_throttled(): void
    {
        foreach (range(1, AppServiceProvider::MAX_REQUESTS_PER_SECOND + 5) as $attempt) {
            $status = $this->withServerVariables(['REMOTE_ADDR' => '52.31.139.75'])
                ->postJson(route('paystack.webhook'), [])
                ->getStatusCode();

            $this->assertNotSame(429, $status);
        }
    }

    /**
     * @return Collection<int, int>
     */
    private function sendRequests(int $count, string $ip): Collection
    {
        return collect(range(1, $count))->map(fn () => $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('seo.robots'))
            ->getStatusCode());
    }
}
