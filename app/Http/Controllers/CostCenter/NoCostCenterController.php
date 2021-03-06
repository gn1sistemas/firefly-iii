<?php
/**
 * NoCostCenterController.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
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

declare(strict_types=1);

namespace FireflyIII\Http\Controllers\CostCenter;


use Carbon\Carbon;
use FireflyIII\Helpers\Collector\TransactionCollectorInterface;
use FireflyIII\Helpers\Filter\InternalTransferFilter;
use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\TransactionType;
use FireflyIII\Repositories\Journal\JournalRepositoryInterface;
use FireflyIII\Support\Http\Controllers\PeriodOverview;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Log;

/**
 *
 * Class NoCostCenterController
 */
class NoCostCenterController extends Controller
{
    use PeriodOverview;
    /** @var JournalRepositoryInterface Journals and transactions overview */
    private $journalRepos;

    /**
     * CostCenterController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(
            function ($request, $next) {
                app('view')->share('title', (string)trans('firefly.cost_centers'));
                app('view')->share('mainTitleIcon', 'fa-bar-chart');
                $this->journalRepos = app(JournalRepositoryInterface::class);

                return $next($request);
            }
        );
    }

    /**
     * Show transactions without a cost center.
     *
     * @param Request     $request
     * @param Carbon|null $start
     * @param Carbon|null $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, Carbon $start = null, Carbon $end = null)
    {
        Log::debug('Start of noCostCenter()');
        /** @var Carbon $start */
        $start = $start ?? session('start');
        /** @var Carbon $end */
        $end      = $end ?? session('end');
        $page     = (int)$request->get('page');
        $pageSize = (int)app('preferences')->get('listPageSize', 50)->data;
        $subTitle = trans(
            'firefly.without_cost_center_between',
            ['start' => $start->formatLocalized($this->monthAndDayFormat), 'end' => $end->formatLocalized($this->monthAndDayFormat)]
        );
        $periods  = $this->getNoCostCenterPeriodOverview($start);

        Log::debug(sprintf('Start for noCostCenter() is %s', $start->format('Y-m-d')));
        Log::debug(sprintf('End for noCostCenter() is %s', $end->format('Y-m-d')));

        /** @var TransactionCollectorInterface $collector */
        $collector = app(TransactionCollectorInterface::class);
        $collector->setAllAssetAccounts()->setRange($start, $end)->setLimit($pageSize)->setPage($page)->withoutCostCenter()->withOpposingAccount()
                  ->setTypes([TransactionType::WITHDRAWAL, TransactionType::DEPOSIT, TransactionType::TRANSFER]);
        $collector->removeFilter(InternalTransferFilter::class);
        $transactions = $collector->getPaginatedTransactions();
        $transactions->setPath(route('cost-centers.no-cost-center'));

        return view('cost-centers.no-cost-center', compact('transactions', 'subTitle', 'periods', 'start', 'end'));
    }


    /**
     * Show all transactions without a cost-center.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAll(Request $request)
    {
        // default values:
        $start    = null;
        $end      = null;
        $periods  = new Collection;
        $page     = (int)$request->get('page');
        $pageSize = (int)app('preferences')->get('listPageSize', 50)->data;
        Log::debug('Start of noCostCenter()');
        $subTitle = (string)trans('firefly.all_journals_without_cost_center');
        $first    = $this->journalRepos->firstNull();
        $start    = null === $first ? new Carbon : $first->date;
        $end      = new Carbon;
        Log::debug(sprintf('Start for noCostCenter() is %s', $start->format('Y-m-d')));
        Log::debug(sprintf('End for noCostCenter() is %s', $end->format('Y-m-d')));

        /** @var TransactionCollectorInterface $collector */
        $collector = app(TransactionCollectorInterface::class);
        $collector->setAllAssetAccounts()->setRange($start, $end)->setLimit($pageSize)->setPage($page)->withoutCostCenter()->withOpposingAccount()
                  ->setTypes([TransactionType::WITHDRAWAL, TransactionType::DEPOSIT, TransactionType::TRANSFER]);
        $collector->removeFilter(InternalTransferFilter::class);
        $transactions = $collector->getPaginatedTransactions();
        $transactions->setPath(route('cost-centers.no-cost-center.all'));

        return view('cost-centers.no-cost-center', compact('transactions', 'subTitle', 'periods', 'start', 'end'));
    }
}
