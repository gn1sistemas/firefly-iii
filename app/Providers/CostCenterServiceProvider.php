<?php
/**
 * CostCenterServiceProvider.php
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

namespace FireflyIII\Providers;

use FireflyIII\Repositories\CostCenter\CostCenterRepository;
use FireflyIII\Repositories\CostCenter\CostCenterRepositoryInterface;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 * Class CostCenterServiceProvider.
 */
class CostCenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CostCenterRepositoryInterface::class,
            function (Application $app) {
                /** @var CostCenterRepository $repository */
                $repository = app(CostCenterRepository::class);
                if ($app->auth->check()) {
                    $repository->setUser(auth()->user());                    
                }

                return $repository;
            }
        );
    }
}
