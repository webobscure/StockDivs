<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_can_login_and_read_portfolio_summary(): void
    {
        $this->seed();

        $login = $this->postJson('/api/login', [
            'email' => 'demo@stockdivs.test',
            'password' => 'password123',
        ]);

        $login->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'setting'],
            ]);

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/portfolio/summary')
            ->assertOk()
            ->assertJsonPath('data.base_currency', 'USD')
            ->assertJsonStructure([
                'data' => [
                    'total_invested',
                    'total_current_value',
                    'total_profit',
                    'total_profit_percent',
                    'daily_change',
                    'asset_count',
                    'positions',
                    'allocation',
                    'currencies',
                ],
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dividends/upcoming')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['ticker', 'amount', 'held_quantity', 'expected_amount'],
                ],
            ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/stocks/search?query='.urlencode('Сбер'))
            ->assertOk()
            ->assertJsonPath('data.0.ticker', 'SBER');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/settings', [
                'base_currency' => 'USD',
                'language' => 'de',
                'theme' => 'dark',
            ])
            ->assertOk()
            ->assertJsonPath('data.language', 'de')
            ->assertJsonPath('data.theme', 'dark');
    }
}
