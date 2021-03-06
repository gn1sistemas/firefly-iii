<?php
/**
 * ReportController.php
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
/** @noinspection CallableParameterUseCaseInTypeContextInspection */
declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use Carbon\Carbon;
use FireflyIII\Generator\Report\ReportGeneratorFactory;
use FireflyIII\Helpers\Report\ReportHelperInterface;
use FireflyIII\Http\Requests\ReportFormRequest;
use FireflyIII\Models\AccountType;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Support\Http\Controllers\RenderPartialViews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Log;

/**
 * Class ReportController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReportController extends Controller
{
    use RenderPartialViews;

    /** @var ReportHelperInterface Helper interface. */
    protected $helper;

    /** @var BudgetRepositoryInterface The budget repository */
    private $repository;

    /**
     * ReportController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(
            function ($request, $next) {
                app('view')->share('title', (string)trans('firefly.reports'));
                app('view')->share('mainTitleIcon', 'fa-line-chart');
                app('view')->share('subTitleIcon', 'fa-calendar');
                $this->helper     = app(ReportHelperInterface::class);
                $this->repository = app(BudgetRepositoryInterface::class);

                return $next($request);
            }
        );
    }
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Show account report.
     *
     * @param Collection $accounts
     * @param Collection $expense
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function accountReport(Collection $accounts, Collection $expense, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }

        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle', trans(
                          'firefly.report_account',
                          ['start' => $start->formatLocalized($this->monthFormat), 'end' => $end->formatLocalized($this->monthFormat)]
                      )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Account', $start, $end);
        $generator->setAccounts($accounts);
        $generator->setExpense($expense);

        return $generator->generate();
    }

    /**
     * Show audit report.
     *
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function auditReport(Collection $accounts, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }
        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_audit',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Audit', $start, $end);
        $generator->setAccounts($accounts);

        return $generator->generate();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Show budget report.
     *
     * @param Collection $accounts
     * @param Collection $budgets
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function budgetReport(Collection $accounts, Collection $budgets, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }
        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_budget',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Budget', $start, $end);
        $generator->setAccounts($accounts);
        $generator->setBudgets($budgets);

        return $generator->generate();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Show category report.
     *
     * @param Collection $accounts
     * @param Collection $categories
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function categoryReport(Collection $accounts, Collection $categories, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }
        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_category',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Category', $start, $end);
        $generator->setAccounts($accounts);
        $generator->setCategories($categories);

        return $generator->generate();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Show cost centers report.
     *
     * @param Collection $accounts
     * @param Collection $costCenters
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function costCenterReport(Collection $accounts, Collection $costCenters, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }
        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_cost_center',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('CostCenter', $start, $end);
        $generator->setAccounts($accounts);
        $generator->setCostCenters($costCenters);

        return $generator->generate();
    }

    /**
     * Show default report.
     *
     * @param Collection $accounts
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function defaultReport(Collection $accounts, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date'));
        }

        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_default',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Standard', $start, $end);
        $generator->setAccounts($accounts);

        return $generator->generate();
    }

    /**
     * Show index.
     *
     * @param AccountRepositoryInterface $repository
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(AccountRepositoryInterface $repository)
    {
        /** @var Carbon $start */
        $start            = clone session('first');
        $months           = $this->helper->listOfMonths($start);
        $customFiscalYear = app('preferences')->get('customFiscalYear', 0)->data;
        $accounts         = $repository->getAccountsByType([AccountType::DEFAULT, AccountType::ASSET]);
        $accountList      = implode(',', $accounts->pluck('id')->toArray());
        $this->repository->cleanupBudgets();

        return view('reports.index', compact('months', 'accounts', 'start', 'accountList', 'customFiscalYear'));
    }

    /**
     * Show options for reports.
     *
     * @param string $reportType
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function options(string $reportType)
    {
        switch ($reportType) {
            default:
                $result = $this->noReportOptions();
                break;
            case 'category':
                $result = $this->categoryReportOptions();
                break;
            case 'budget':
                $result = $this->budgetReportOptions();
                break;
            case 'tag':
                $result = $this->tagReportOptions();
                break;
            case 'account':
                $result = $this->accountReportOptions();
                break;
            case 'cost_center':
                $result = $this->costCenterReportOptions();
                break;
        }

        return response()->json(['html' => $result]);
    }

    /**
     * Process the submit of report.
     *
     * @param ReportFormRequest $request
     *
     * @return RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function postIndex(ReportFormRequest $request)
    {
        // report type:
        $reportType     = $request->get('report_type');
        $start          = $request->getStartDate()->format('Ymd');
        $end            = $request->getEndDate()->format('Ymd');
        $accounts       = implode(',', $request->getAccountList()->pluck('id')->toArray());
        $categories     = implode(',', $request->getCategoryList()->pluck('id')->toArray());
        $costCenters    = implode(',', $request->getCostCenterList()->pluck('id')->toArray());
        $budgets        = implode(',', $request->getBudgetList()->pluck('id')->toArray());
        $tags           = implode(',', $request->getTagList()->pluck('id')->toArray());
        $expense        = implode(',', $request->getExpenseList()->pluck('id')->toArray());
        $uri            = route('reports.index');

        if (0 === $request->getAccountList()->count()) {
            Log::debug('Account count is zero');
            session()->flash('error', (string)trans('firefly.select_at_least_one_account'));

            return redirect(route('reports.index'));
        }

        if ('category' === $reportType && 0 === $request->getCategoryList()->count()) {
            session()->flash('error', (string)trans('firefly.select_at_least_one_category'));

            return redirect(route('reports.index'));
        }

        if ('cost_center' === $reportType && 0 === $request->getCostCenterList()->count()) {
            session()->flash('error', (string) trans('firefly.select_at_least_one_cost_center'));

            return redirect(route('reports.index'));
        }

        if ('budget' === $reportType && 0 === $request->getBudgetList()->count()) {
            session()->flash('error', (string)trans('firefly.select_at_least_one_budget'));

            return redirect(route('reports.index'));
        }

        if ('tag' === $reportType && 0 === $request->getTagList()->count()) {
            session()->flash('error', (string)trans('firefly.select_at_least_one_tag'));

            return redirect(route('reports.index'));
        }

        if ('account' === $reportType && 0 === $request->getExpenseList()->count()) {
            session()->flash('error', (string)trans('firefly.select_at_least_one_expense'));

            return redirect(route('reports.index'));
        }

        if ($request->getEndDate() < $request->getStartDate()) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date'));
        }

        switch ($reportType) {
            case 'category':
                $uri = route('reports.report.category', [$accounts, $categories, $start, $end]);
                break;
            case 'cost_center':
                $uri = route('reports.report.costCenter', [$accounts, $costCenters, $start, $end]);
                break;
            case 'default':
                $uri = route('reports.report.default', [$accounts, $start, $end]);
                break;
            case 'audit':
                $uri = route('reports.report.audit', [$accounts, $start, $end]);
                break;
            case 'budget':
                $uri = route('reports.report.budget', [$accounts, $budgets, $start, $end]);
                break;
            case 'tag':
                $uri = route('reports.report.tag', [$accounts, $tags, $start, $end]);
                break;
            case 'account':
                $uri = route('reports.report.account', [$accounts, $expense, $start, $end]);
                break;
        }
        
        return redirect($uri);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Get a tag report.
     *
     * @param Collection $accounts
     * @param Collection $tags
     * @param Carbon     $start
     * @param Carbon     $end
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function tagReport(Collection $accounts, Collection $tags, Carbon $start, Carbon $end)
    {
        if ($end < $start) {
            return view('error')->with('message', (string)trans('firefly.end_after_start_date')); // @codeCoverageIgnore
        }
        $this->repository->cleanupBudgets();

        app('view')->share(
            'subTitle',
            trans(
                'firefly.report_tag',
                [
                    'start' => $start->formatLocalized($this->monthFormat),
                    'end'   => $end->formatLocalized($this->monthFormat),
                ]
            )
        );

        $generator = ReportGeneratorFactory::reportGenerator('Tag', $start, $end);
        $generator->setAccounts($accounts);
        $generator->setTags($tags);

        return $generator->generate();
    }


}
