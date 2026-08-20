<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoanLiquidationTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $client;
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->client = Client::create([
            'tipo_doc' => 'CC',
            'numero_doc' => '987654321',
            'name' => 'Maria Lopez',
            'address' => 'Carrera 45',
            'email' => 'maria@example.com',
            'phone' => '3119876543'
        ]);

        $this->type = Type::create([
            'name' => 'Préstamo Mensual',
            'minimo' => 5,
            'maximo' => 20,
            'periodicity_days' => 30,
            'num_payments' => 4
        ]);
    }

    public function test_can_get_liquidation_summary()
    {
        $loan = Loan::create([
            'client_id' => $this->client->id,
            'type_id' => $this->type->id,
            'amount' => 1000,
            'interest_percent' => 5,
            'total_to_pay' => 1200,
            'num_payments' => 4
        ]);

        for ($i = 1; $i <= 4; $i++) {
            LoanPayment::create([
                'loan_id' => $loan->id,
                'cuota' => $i,
                'due_date' => now()->addDays(30 * $i),
                'amount' => 300,
                'paid' => $i == 1 ? 1 : 0,
                'status' => $i == 1 ? 'paid' : 'pending'
            ]);
        }

        $this->actingAs($this->user);

        $response = $this->get(route('loans.liquidation.summary', $loan->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'client_name' => 'Maria Lopez',
                'total_loan_amount' => 1000.0,
                'capital_paid' => 250.0,
                'capital_remaining' => 750.0,
                'interest_percent' => 5.0,
                'current_month_interest' => 50.0,
                'total_to_pay' => 800.0,
                'paid_quotes' => 1,
                'pending_quotes' => 3
            ]);
    }

    public function test_can_execute_liquidation()
    {
        $loan = Loan::create([
            'client_id' => $this->client->id,
            'type_id' => $this->type->id,
            'amount' => 1000,
            'interest_percent' => 5,
            'total_to_pay' => 1200,
            'num_payments' => 4
        ]);

        for ($i = 1; $i <= 4; $i++) {
            LoanPayment::create([
                'loan_id' => $loan->id,
                'cuota' => $i,
                'due_date' => now()->addDays(30 * $i),
                'amount' => 300,
                'paid' => $i == 1 ? 1 : 0,
                'status' => $i == 1 ? 'paid' : 'pending'
            ]);
        }

        $this->actingAs($this->user);

        $response = $this->post(route('loans.liquidate', $loan->id), [
            'confirm_1' => '1',
            'confirm_2' => '1'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Deuda liquidada exitosamente'
            ]);

        $loan->refresh();
        $this->assertEquals(1, $loan->liquidated);
        $this->assertEquals('liquidated', $loan->state);
        $this->assertNotNull($loan->liquidation_date);

        $this->assertNotNull($loan->liquidation);
        $this->assertEquals(750, $loan->liquidation->principal_paid);
        $this->assertEquals(50, $loan->liquidation->interest_paid);
        $this->assertEquals(800, $loan->liquidation->total_paid);

        $payments = $loan->payments;
        $this->assertEquals('paid', $payments->where('cuota', 1)->first()->status);
        $this->assertEquals('cancelled', $payments->where('cuota', 2)->first()->status);
        $this->assertEquals('cancelled', $payments->where('cuota', 3)->first()->status);
        $this->assertEquals('cancelled', $payments->where('cuota', 4)->first()->status);
    }
}
