<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;

class TestLoanFilters extends Command
{
    protected $signature = 'test:loan-filters';
    protected $description = 'Test loan filter queries';

    public function handle()
    {
        $this->info("====================================");
        $this->info("PRUEBA DE FILTROS DE PRÉSTAMOS");
        $this->info("====================================\n");

        // Total de préstamos
        $totalLoans = Loan::count();
        $this->info("📊 Total de préstamos en BD: $totalLoans\n");

        // PRUEBA 1: Préstamos PAGADOS
        $this->info("1️⃣  PRÉSTAMOS PAGADOS (0 cuotas pendientes):");
        $paidLoans = Loan::whereRaw(
            'loans.id IN (
                SELECT loan_id FROM loan_payments
                WHERE deleted_at IS NULL
                GROUP BY loan_id
                HAVING COUNT(*) = SUM(CASE WHEN paid = 1 THEN 1 ELSE 0 END)
            )'
        )->count();
        $this->info("   ✅ Resultado: $paidLoans préstamos completamente pagados\n");

        // PRUEBA 2: Préstamos PENDIENTES
        $this->info("2️⃣  PRÉSTAMOS PENDIENTES (con cuotas no pagadas):");
        $pendingLoans = Loan::whereRaw(
            'loans.id IN (
                SELECT DISTINCT loan_id FROM loan_payments
                WHERE paid = 0 AND deleted_at IS NULL
            )'
        )->count();
        $this->info("   ✅ Resultado: $pendingLoans préstamos con cuotas pendientes\n");

        // Verificación
        $this->info("3️⃣  VERIFICACIÓN:");
        $this->info("   Pagados: $paidLoans");
        $this->info("   Pendientes: $pendingLoans");
        $this->info("   Total: $totalLoans");
        if ($paidLoans + $pendingLoans === $totalLoans) {
            $this->line("   ✅ ¡CORRECTO!\n");
        } else {
            $this->warn("   ⚠️  Discrepancia detectada\n");
        }

        // Ejemplos de préstamos pagados
        $this->info("4️⃣  EJEMPLOS DE PRÉSTAMOS PAGADOS:");
        $examples = Loan::whereRaw(
            'loans.id IN (
                SELECT loan_id FROM loan_payments
                WHERE deleted_at IS NULL
                GROUP BY loan_id
                HAVING COUNT(*) = SUM(CASE WHEN paid = 1 THEN 1 ELSE 0 END)
            )'
        )->with('client', 'payments')->limit(3)->get();

        foreach ($examples as $loan) {
            $totalPayments = $loan->payments->count();
            $paidPayments = $loan->payments->where('paid', 1)->count();
            $this->info("   - Préstamo #{$loan->id}: {$loan->client->name} ({$paidPayments}/{$totalPayments})");
        }

        $this->info("\n5️⃣  EJEMPLOS DE PRÉSTAMOS PENDIENTES:");
        $examples = Loan::whereRaw(
            'loans.id IN (
                SELECT DISTINCT loan_id FROM loan_payments
                WHERE paid = 0 AND deleted_at IS NULL
            )'
        )->with('client', 'payments')->limit(3)->get();

        foreach ($examples as $loan) {
            $totalPayments = $loan->payments->count();
            $paidPayments = $loan->payments->where('paid', 1)->count();
            $this->info("   - Préstamo #{$loan->id}: {$loan->client->name} ({$paidPayments}/{$totalPayments})");
        }

        $this->info("\n====================================");
        $this->info("✅ PRUEBAS COMPLETADAS");
        $this->info("====================================");
    }
}
