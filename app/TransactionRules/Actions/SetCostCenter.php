<?php
/**
 * SetCostCenter.php
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

namespace FireflyIII\TransactionRules\Actions;

use FireflyIII\Factory\CostCenterFactory;
use FireflyIII\Models\RuleAction;
use FireflyIII\Models\Transaction;
use FireflyIII\Models\TransactionJournal;
use Log;

/**
 * Class SetCostCenter.
 */
class SetCostCenter implements ActionInterface
{
    /** @var RuleAction The rule action */
    private $action;

    /**
     * TriggerInterface constructor.
     *
     * @param RuleAction $action
     */
    public function __construct(RuleAction $action)
    {
        $this->action = $action;
    }

    /**
     * Set costCenter X
     *
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function act(TransactionJournal $journal): bool
    {
        $name = $this->action->action_value;

        /** @var CostCenterFactory $factory */
        $factory = app(CostCenterFactory::class);
        $factory->setUser($journal->user);
        $costCenter = $factory->findOrCreate(null, $name);
        if (null === $costCenter) {
            Log::error(sprintf('Action SetCostCenter did not fire because "%s" did not result in a valid costCenter.', $name));

            return true;
        }
        $journal->costCenters()->detach();
        // set costCenter on transactions:
        /** @var Transaction $transaction */
        foreach ($journal->transactions as $transaction) {
            $transaction->costCenters()->sync([$costCenter->id]);
        }
        $journal->touch();


        $journal->touch();
        Log::debug(sprintf('RuleAction SetCostCenter set the costCenter of journal #%d to costCenter #%d ("%s").', $journal->id, $costCenter->id, $costCenter->name));

        return true;
    }
}
