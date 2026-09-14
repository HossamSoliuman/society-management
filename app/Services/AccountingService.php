<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Society;

/**
 * Derives the accounting statements (Trial Balance, P&L, Balance Sheet, cash-flow,
 * balance summaries) shown across the Accounting module. The statement figures mirror
 * the design PNGs; the Trial Balance is computed live from the seeded chart of accounts.
 */
class AccountingService
{
    /**
     * Donut + legend for the dashboard "Account Balance Summary" card.
     *
     * @return array<string, mixed>
     */
    public function accountBalanceSummary(): array
    {
        return [
            'center_value' => '&#8377; 14,85,320.50',
            'center_label' => 'Total Balance',
            'segments' => [
                ['label' => 'Assets', 'amount' => '&#8377; 19,25,250', 'value' => 1925250, 'color' => '#10B981'],
                ['label' => 'Liabilities', 'amount' => '&#8377; 4,60,150', 'value' => 460150, 'color' => '#EF4444'],
                ['label' => 'Income', 'amount' => '&#8377; 9,32,450', 'value' => 932450, 'color' => '#8B5CF6'],
                ['label' => 'Expenses', 'amount' => '&#8377; 6,48,120', 'value' => 648120, 'color' => '#F97316'],
            ],
        ];
    }

    /**
     * Income vs Expense series for the dashboard cash-flow area chart (01–30 May).
     *
     * @return array{labels: array<int, string>, income: array<int, float>, expense: array<int, float>, max: float}
     */
    public function cashFlowSeries(): array
    {
        $income = [40000, 55000, 48000, 72000, 65000, 88000, 95000, 120000, 105000, 132000, 118000, 145000];
        $expense = [30000, 42000, 38000, 55000, 48000, 62000, 70000, 85000, 78000, 92000, 88000, 105000];
        $labels = ['01', '03', '06', '09', '12', '15', '18', '21', '24', '27', '29', '30'];

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense,
            'max' => 250000,
        ];
    }

    /**
     * Donut for the Transactions "Transaction Summary" rail card.
     *
     * @return array<string, mixed>
     */
    public function transactionSummary(): array
    {
        return [
            'center_value' => '&#8377; 17,30,570',
            'center_label' => 'Total',
            'segments' => [
                ['label' => 'Receipts', 'amount' => '&#8377; 9,32,450', 'pct' => '53.85%', 'value' => 932450, 'color' => '#10B981'],
                ['label' => 'Payments', 'amount' => '&#8377; 7,98,120', 'pct' => '46.15%', 'value' => 798120, 'color' => '#EF4444'],
            ],
        ];
    }

    /**
     * Live Trial Balance computed from detail accounts. Debit balances (Assets/Expenses)
     * sit in the debit column, credit balances (Liabilities/Income/Equity) in the credit.
     *
     * @return array{rows: array<int, array{code: string, name: string, debit: float, credit: float}>, total_debit: float, total_credit: float}
     */
    public function trialBalance(Society $society): array
    {
        $accounts = Account::query()
            ->forSociety($society)
            ->where('type', 'detail')
            ->with('group')
            ->orderBy('code')
            ->get();

        $creditGroups = ['Liabilities', 'Income', 'Equity', 'Other Income'];

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            $balance = (float) $account->balance;
            if ($balance <= 0) {
                continue;
            }

            $isCredit = in_array($account->group?->name, $creditGroups, true);
            $rows[] = [
                'code' => $account->code,
                'name' => $account->name,
                'debit' => $isCredit ? 0.0 : $balance,
                'credit' => $isCredit ? $balance : 0.0,
            ];

            $totalDebit += $isCredit ? 0.0 : $balance;
            $totalCredit += $isCredit ? $balance : 0.0;
        }

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];
    }

    /**
     * Full Profit & Loss statement dataset (KPIs, chart series, breakdown donut,
     * insights, and the comparative statement rows) matching "accounting Profit & Loss.png".
     *
     * @return array<string, mixed>
     */
    public function profitAndLoss(): array
    {
        return [
            'kpis' => [
                'income' => ['value' => '9,32,450.00', 'change' => '12.5%', 'dir' => 'up'],
                'expenses' => ['value' => '6,48,120.00', 'change' => '8.3%', 'dir' => 'down'],
                'net_profit' => ['value' => '2,84,330.00', 'change' => '25.6%', 'dir' => 'up'],
                'margin' => ['value' => '30.48%', 'change' => '8.7%', 'dir' => 'up'],
            ],
            'summary' => [
                'total_income' => '9,32,450.00',
                'total_expenses' => '6,48,120.00',
                'net_profit' => '2,84,330.00',
                'margin' => '30.48%',
            ],
            'chart' => [
                'labels' => ['01', '05', '10', '15', '20', '25', '30'],
                'income' => [120000, 210000, 380000, 540000, 690000, 830000, 932450],
                'expense' => [90000, 165000, 265000, 375000, 480000, 570000, 648120],
                'profit' => [30000, 45000, 115000, 165000, 210000, 260000, 284330],
                'max' => 1000000,
            ],
            'breakdown' => [
                'center_value' => '&#8377; 9,32,450',
                'center_label' => 'Total Income',
                'income' => [
                    ['label' => 'Maintenance', 'amount' => '6,25,000', 'color' => '#10B981'],
                    ['label' => 'Other', 'amount' => '1,82,450', 'color' => '#3B82F6'],
                    ['label' => 'Interest', 'amount' => '85,000', 'color' => '#8B5CF6'],
                    ['label' => 'Amenities', 'amount' => '40,000', 'color' => '#F97316'],
                ],
                'expenses' => [
                    ['label' => 'Administrative', 'amount' => '1,85,700', 'color' => '#EF4444'],
                    ['label' => 'Maintenance', 'amount' => '2,25,500', 'color' => '#F97316'],
                    ['label' => 'Utility', 'amount' => '1,05,920', 'color' => '#8B5CF6'],
                    ['label' => 'Other', 'amount' => '1,31,000', 'color' => '#6B7280'],
                ],
            ],
            'insights' => [
                ['icon' => 'fa-arrow-trend-up', 'color' => 'green', 'text' => 'Net profit grew 25.6% compared to the previous period.'],
                ['icon' => 'fa-percent', 'color' => 'blue', 'text' => 'Profit margin improved to 30.48%, up 8.7 points.'],
                ['icon' => 'fa-triangle-exclamation', 'color' => 'orange', 'text' => 'Maintenance expenses remain the largest cost head.'],
            ],
            // [particular, this_period, previous_period, change, change_pct, dir]
            'income_rows' => [
                ['Maintenance Income', '6,25,000.00', '5,60,000.00', '65,000.00', '11.6%', 'up'],
                ['Other Income', '1,82,450.00', '1,50,200.00', '32,250.00', '21.5%', 'up'],
                ['Interest Income', '85,000.00', '78,000.00', '7,000.00', '9.0%', 'up'],
                ['Amenities Income', '40,000.00', '40,600.00', '-600.00', '1.5%', 'down'],
            ],
            'total_income' => ['9,32,450.00', '8,28,800.00', '1,03,650.00', '12.5%', 'up'],
            'expense_rows' => [
                ['Administrative Expenses', '1,85,700.00', '1,72,400.00', '13,300.00', '7.7%', 'up'],
                ['Maintenance Expenses', '2,25,500.00', '2,60,300.00', '-34,800.00', '13.4%', 'down'],
                ['Utility Expenses', '1,05,920.00', '1,18,600.00', '-12,680.00', '10.7%', 'down'],
                ['Other Expenses', '1,31,000.00', '1,55,600.00', '-24,600.00', '15.8%', 'down'],
            ],
            'total_expenses' => ['6,48,120.00', '7,06,900.00', '-58,780.00', '8.3%', 'down'],
            'net_profit_row' => ['2,84,330.00', '1,21,900.00', '1,62,430.00', '133.2%', 'up'],
        ];
    }

    /**
     * Balance Sheet dataset (stat cards, two-pane rows for both dates, insights)
     * matching "accounting balance sheet.png".
     *
     * @return array<string, mixed>
     */
    public function balanceSheet(): array
    {
        return [
            'stats' => [
                'total_assets' => '1,85,62,250.50',
                'total_liabilities' => '54,32,120.00',
                'total_equity' => '1,31,30,130.50',
                'bs_total' => '1,85,62,250.50',
            ],
            // [particular, as_on, compare_on]
            'assets' => [
                ['group' => '1. Non-Current Assets', 'rows' => [
                    ['Property, Plant & Equipment', '1,15,20,000.00', '98,50,000.00'],
                    ['Investments', '18,40,000.00', '15,20,000.00'],
                    ['Other Non-Current Assets', '6,79,950.00', '5,45,750.50'],
                ]],
                ['group' => '2. Current Assets', 'rows' => [
                    ['Cash and Cash Equivalents', '1,47,550.50', '1,05,200.00'],
                    ['Bank Balances', '13,37,770.00', '10,80,600.00'],
                    ['Receivables', '3,25,600.00', '2,68,900.00'],
                    ['Other Current Assets', '27,11,380.00', '22,45,300.00'],
                ]],
            ],
            'total_assets_row' => ['1,85,62,250.50', '1,57,15,750.50'],
            'liabilities' => [
                ['group' => '1. Liabilities', 'color' => 'red', 'rows' => [
                    ['Current Liabilities', '3,10,000.00', '2,45,600.00'],
                    ['Long Term Liabilities', '51,22,120.00', '42,64,720.00'],
                ]],
                ['group' => '2. Equity & Surplus', 'color' => 'orange', 'rows' => [
                    ['Capital Fund', '95,50,000.00', '82,30,000.00'],
                    ['Reserves & Surplus', '35,80,130.50', '29,75,430.50'],
                ]],
            ],
            'total_liab_row' => ['1,85,62,250.50', '1,57,15,750.50'],
            'insights' => [
                ['label' => 'Total Assets', 'change' => '₹28,46,500 (18.11%)', 'dir' => 'up'],
                ['label' => 'Total Liabilities', 'change' => '₹9,21,800 (20.45%)', 'dir' => 'up'],
                ['label' => 'Equity & Surplus', 'change' => '₹19,24,700 (17.17%)', 'dir' => 'up'],
            ],
        ];
    }
}
