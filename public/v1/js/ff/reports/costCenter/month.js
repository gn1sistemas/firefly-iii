/*
 * month.js
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

/** global: costCenterIncomeUri, costCenterExpenseUri, accountIncomeUri, accountExpenseUri, mainUri */

$(function () {
    "use strict";
    drawChart();

    $('#cost-centers-in-pie-chart-checked').on('change', function () {
        redrawPieChart(costCenterIncomeUri, 'cost-centers-in-pie-chart');
    });

    $('#cost-centers-out-pie-chart-checked').on('change', function () {
        redrawPieChart(costCenterExpenseUri, 'cost-centers-out-pie-chart');
    });

    $('#accounts-in-pie-chart-checked').on('change', function () {
        redrawPieChart(accountIncomeUri, 'accounts-in-pie-chart');
    });

    $('#accounts-out-pie-chart-checked').on('change', function () {
        redrawPieChart(accountExpenseUri, 'accounts-out-pie-chart');
    });

});


function drawChart() {
    "use strict";

    // month view:
    doubleYChart(mainUri, 'in-out-chart');

    // draw pie chart of income, depending on "show other transactions too":
    redrawPieChart(costCenterIncomeUri, 'cost-centers-in-pie-chart');
    redrawPieChart(costCenterExpenseUri, 'cost-centers-out-pie-chart');
    redrawPieChart(accountIncomeUri, 'accounts-in-pie-chart');
    redrawPieChart(accountExpenseUri, 'accounts-out-pie-chart');

}

function redrawPieChart(uri, container) {
    "use strict";
    var checkbox = $('#' + container + '-checked');

    var others = '0';
    // check if box is checked:
    if (checkbox.prop('checked')) {
        others = '1';
    }
    uri = uri.replace('OTHERS', others);

    pieChart(uri, container);

}
