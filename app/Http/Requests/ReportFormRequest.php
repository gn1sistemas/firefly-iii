<?php
/**
 * ReportFormRequest.php
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
declare(strict_types=1);

namespace FireflyIII\Http\Requests;

use Carbon\Carbon;
use Exception;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use FireflyIII\Repositories\Budget\BudgetRepositoryInterface;
use FireflyIII\Repositories\Category\CategoryRepositoryInterface;
use FireflyIII\Repositories\CostCenter\CostCenterRepositoryInterface;
use FireflyIII\Repositories\Tag\TagRepositoryInterface;
use Illuminate\Support\Collection;
use Log;

/**
 * Class ReportFormRequest.
 */
class ReportFormRequest extends Request
{
    /**
     * Verify the request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Only allow logged in users
        return auth()->check();
    }

    /**
     * Validate list of accounts.
     *
     * @return Collection
     */
    public function getAccountList(): Collection
    {
        // fixed
        /** @var AccountRepositoryInterface $repository */
        $repository = app(AccountRepositoryInterface::class);
        $set        = $this->get('accounts');
        $collection = new Collection;
        if (\is_array($set)) {
            foreach ($set as $accountId) {
                $account = $repository->findNull((int)$accountId);
                if (null !== $account) {
                    $collection->push($account);
                }
            }
        }

        return $collection;
    }

    /**
     * Validate list of budgets.
     *
     * @return Collection
     */
    public function getBudgetList(): Collection
    {
        /** @var BudgetRepositoryInterface $repository */
        $repository = app(BudgetRepositoryInterface::class);
        $set        = $this->get('budget');
        $collection = new Collection;
        if (\is_array($set)) {
            foreach ($set as $budgetId) {
                $budget = $repository->findNull((int)$budgetId);
                if (null !== $budget) {
                    $collection->push($budget);
                }
            }
        }

        return $collection;
    }

    /**
     * Validate list of categories.
     *
     * @return Collection
     */
    public function getCategoryList(): Collection
    {
        /** @var CategoryRepositoryInterface $repository */
        $repository = app(CategoryRepositoryInterface::class);
        $set        = $this->get('category');
        $collection = new Collection;
        if (\is_array($set)) {
            foreach ($set as $categoryId) {
                $category = $repository->findNull((int)$categoryId);
                if (null !== $category) {
                    $collection->push($category);
                }
            }
        }

        return $collection;
    }

    /**
     * Validate list of cost centers.
     *
     * @return Collection
     */
    public function getCostCenterList(): Collection
    {
        /** @var CostCenterRepositoryInterface $repository */
        $repository = app(CostCenterRepositoryInterface::class);
        $set        = $this->get('costCenter');
        $collection = new Collection;
        if (\is_array($set)) {
            foreach ($set as $costCenterId) {
                $costCenter = $repository->findNull((int)$costCenterId);
                if (null !== $costCenter) {
                    $collection->push($costCenter);
                }
            }
        }

        return $collection;
    }

    /**
     * Validate end date.
     *
     * @return Carbon
     *
     * @throws FireflyException
     */
    public function getEndDate(): Carbon
    {
        $date  = new Carbon;
        $range = $this->get('daterange');
        $parts = explode(' - ', (string)$range);
        if (2 === \count($parts)) {
            try {
                $date = new Carbon($parts[1]);
                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $error = sprintf('"%s" is not a valid date range: %s', $range, $e->getMessage());
                Log::error($error);
                throw new FireflyException($error);
                // @codeCoverageIgnoreEnd
            }

        }

        return $date;
    }

    /**
     * Validate list of expense accounts.
     *
     * @return Collection
     */
    public function getExpenseList(): Collection
    {
        /** @var AccountRepositoryInterface $repository */
        $repository = app(AccountRepositoryInterface::class);
        $set        = $this->get('exp_rev');
        $collection = new Collection;
        if (\is_array($set)) {
            foreach ($set as $accountId) {
                $account = $repository->findNull((int)$accountId);
                if (null !== $account) {
                    $collection->push($account);
                }
            }
        }

        return $collection;
    }

    /**
     * Validate start date.
     *
     * @return Carbon
     *
     * @throws FireflyException
     */
    public function getStartDate(): Carbon
    {
        $date  = new Carbon;
        $range = $this->get('daterange');
        $parts = explode(' - ', (string)$range);
        if (2 === \count($parts)) {
            try {
                $date = new Carbon($parts[0]);
                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $error = sprintf('"%s" is not a valid date range: %s', $range, $e->getMessage());
                Log::error($error);
                throw new FireflyException($error);
                // @codeCoverageIgnoreEnd
            }
        }

        return $date;
    }

    /**
     * Validate list of tags.
     *
     * @return Collection
     */
    public function getTagList(): Collection
    {
        /** @var TagRepositoryInterface $repository */
        $repository = app(TagRepositoryInterface::class);
        $set        = $this->get('tag');
        $collection = new Collection;
        Log::debug('Set is:', $set ?? []);
        if (\is_array($set)) {
            foreach ($set as $tagTag) {
                Log::debug(sprintf('Now searching for "%s"', $tagTag));
                $tag = $repository->findByTag($tagTag);
                if (null !== $tag) {
                    $collection->push($tag);
                    continue;
                }
                $tag = $repository->findNull((int)$tagTag);
                if (null !== $tag) {
                    $collection->push($tag);
                    continue;
                }
            }
        }

        return $collection;
    }

    /**
     * Rules for this request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'report_type' => 'in:audit,default,category,cost_center,budget,tag,account',
        ];
    }
}
