<?php
/**
 * CostCenterFormRequest.php
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

use FireflyIII\Models\CostCenter;

/**
 * Class CostCenterFormRequest.
 */
class CostCenterFormRequest extends Request
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
     * Get information for the controller.
     *
     * @return array
     */
    public function getCostCenterData(): array
    {
        return [
            'name' => $this->string('name'),
        ];
    }

    /**
     * Rules for this request.
     *
     * @return array
     */
    public function rules(): array
    {
        $nameRule = 'required|between:1,100|uniqueObjectForUser:cost_centers,name';
        /** @var CostCenter $costCenter */
        $costCenter = $this->route()->parameter('costCenter');

        if (null !== $costCenter) {
            $nameRule = 'required|between:1,100|uniqueObjectForUser:cost_centers,name,' . $costCenter->id;
        }

        // fixed
        return [
            'name' => $nameRule,
        ];
    }
}
