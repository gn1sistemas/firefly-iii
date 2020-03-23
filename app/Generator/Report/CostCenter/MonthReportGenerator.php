<?php
/**
 * MonthReportGenerator.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
/** @noinspection MultipleReturnStatementsInspection */
/** @noinspection PhpUndefinedMethodInspection */
declare(strict_types=1);

namespace FireflyIII\Generator\Report\CostCenter;

use Carbon\Carbon;
use FireflyIII\Generator\Report\ReportGeneratorInterface;
use FireflyIII\Generator\Report\Support;
use FireflyIII\Helpers\Collector\TransactionCollectorInterface;
use FireflyIII\Helpers\Filter\NegativeAmountFilter;
use FireflyIII\Helpers\Filter\OpposingAccountFilter;
use FireflyIII\Helpers\Filter\PositiveAmountFilter;
use FireflyIII\Helpers\Filter\TransferFilter;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionType;
use Illuminate\Support\Collection;
use Log;
use Throwable;

/**
 * Class MonthReportGenerator.
 *
 * @codeCoverageIgnore
 */
class MonthReportGenerator extends Support implements ReportGeneratorInterface
{
    /** @var Collection The included accounts */
    private $accounts;
    /** @var Collection The included costCenters */
    private $costCenters;
    /** @var Carbon The end date */
    private $end;
    /** @var Collection The expenses */
    private $expenses;
    /** @var Collection The income in the report. */
    private $income;
    /** @var Carbon The start date. */
    private $start;

    /**
     * MonthReportGenerator constructor.
     */
    public function __construct()
    {
        $this->income   = new Collection;
        $this->expenses = new Collection;
    }

    /**
     * Generates the report.
     *
     * @return string
     */
    public function generate(): string
    {
        $accountIds         = implode(',', $this->accounts->pluck('id')->toArray());
        $costCenterIds      = implode(',', $this->costCenters->pluck('id')->toArray());
        $reportType         = 'cost_center';
        $expenses           = $this->getExpenses();
        $income             = $this->getIncome();
        $accountSummary     = $this->getObjectSummary($this->summarizeByAccount($expenses), $this->summarizeByAccount($income));
        $costCenterSummary  = $this->getObjectSummary($this->summarizeByCostCenter($expenses), $this->summarizeByCostCenter($income));
        $averageExpenses    = $this->getAverages($expenses, SORT_ASC);
        $averageIncome      = $this->getAverages($income, SORT_DESC);
        $topExpenses        = $this->getTopExpenses();
        $topIncome          = $this->getTopIncome();

        // render!
        try {
            return view(
                'reports.costCenter.month', compact(
                                            'accountIds', 'costCenterIds', 'topIncome', 'reportType', 'accountSummary', 'costCenterSummary', 'averageExpenses',
                                            'averageIncome', 'topExpenses'
                                        )
            )
                ->with('start', $this->start)->with('end', $this->end)
                ->with('costCenters', $this->costCenters)
                ->with('accounts', $this->accounts)
                ->render();
        } catch (Throwable $e) {
            Log::error(sprintf('Cannot render reports.costCenter.month: %s', $e->getMessage()));
            $result = 'Could not render report view.' . sprintf('Cannot render reports.costCenter.month: %s', $e->getMessage());
        }

        return $result;
    }

    /**
     * Set the involved accounts.
     *
     * @param Collection $accounts
     *
     * @return ReportGeneratorInterface
     */
    public function setAccounts(Collection $accounts): ReportGeneratorInterface
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Empty budget setter.
     *
     * @param Collection $budgets
     *
     * @return ReportGeneratorInterface
     */
    public function setBudgets(Collection $budgets): ReportGeneratorInterface
    {
        return $this;
    }

    /**
     * Set the categories involved in this report.
     *
     * @param Collection $costCenters
     *
     * @return ReportGeneratorInterface
     */
    public function setCategories(Collection $categories): ReportGeneratorInterface
    {
        return $this;
    }

    /**
     * Set the cost centers involved in this report.
     *
     * @param Collection $costCenters
     *
     * @return ReportGeneratorInterface
     */
    public function setCostCenters(Collection $costCenters): ReportGeneratorInterface
    {
        $this->costCenters = $costCenters;

        return $this;
    }

    /**
     * Set the end date for this report.
     *
     * @param Carbon $date
     *
     * @return ReportGeneratorInterface
     */
    public function setEndDate(Carbon $date): ReportGeneratorInterface
    {
        $this->end = $date;

        return $this;
    }

    /**
     * Set the expenses involved in this report.
     *
     * @param Collection $expense
     *
     * @return ReportGeneratorInterface
     */
    public function setExpense(Collection $expense): ReportGeneratorInterface
    {
        return $this;
    }

    /**
     * Set the start date for this report.
     *
     * @param Carbon $date
     *
     * @return ReportGeneratorInterface
     */
    public function setStartDate(Carbon $date): ReportGeneratorInterface
    {
        $this->start = $date;

        return $this;
    }

    /**
     * Unused tag setter.
     *
     * @param Collection $tags
     *
     * @return ReportGeneratorInterface
     */
    public function setTags(Collection $tags): ReportGeneratorInterface
    {
        return $this;
    }

    /**
     * Get the expenses for this report.
     *
     * @return Collection
     */
    protected function getExpenses(): Collection
    {
        if ($this->expenses->count() > 0) {
            Log::debug('Return previous set of expenses.');

            return $this->expenses;
        }

        /** @var TransactionCollectorInterface $collector */
        $collector = app(TransactionCollectorInterface::class);
        $collector->setAccounts($this->accounts)->setRange($this->start, $this->end)
                  ->setTypes([TransactionType::WITHDRAWAL, TransactionType::TRANSFER])
                  ->setCostCenters($this->costCenters)->withOpposingAccount();
        $collector->removeFilter(TransferFilter::class);

        $collector->addFilter(OpposingAccountFilter::class);
        $collector->addFilter(PositiveAmountFilter::class);

        $transactions   = $collector->getTransactions();
        $this->expenses = $transactions;

        return $transactions;
    }

    /**
     * Get the income for this report.
     *
     * @return Collection
     */
    protected function getIncome(): Collection
    {
        if ($this->income->count() > 0) {
            return $this->income;
        }

        /** @var TransactionCollectorInterface $collector */
        $collector = app(TransactionCollectorInterface::class);
        $collector->setAccounts($this->accounts)->setRange($this->start, $this->end)
                  ->setTypes([TransactionType::DEPOSIT, TransactionType::TRANSFER])
                  ->setCostCenters($this->costCenters)->withOpposingAccount();

        $collector->addFilter(OpposingAccountFilter::class);
        $collector->addFilter(NegativeAmountFilter::class);

        $transactions = $collector->getTransactions();
        $this->income = $transactions;

        return $transactions;
    }

    /**
     * Summarize the cost center.
     *
     * @param Collection $collection
     *
     * @return array
     */
    private function summarizeByCostCenter(Collection $collection): array
    {
        $result = [];
        /** @var Transaction $transaction */
        foreach ($collection as $transaction) {
            $jrnlCostCenterId      = (int)$transaction->transaction_journal_cost_center_id;
            $transCostCenterId     = (int)$transaction->transaction_cost_center_id;
            $costCenterId          = max($jrnlCostCenterId, $transCostCenterId);
            $result[$costCenterId] = $result[$costCenterId] ?? '0';
            $result[$costCenterId] = bcadd($transaction->transaction_amount, $result[$costCenterId]);
        }

        return $result;
    }
}
