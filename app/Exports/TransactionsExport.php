<?php

namespace FireflyIII\Exports;

use FireflyIII\Helpers\Collector\TransactionCollector;
use FireflyIII\Transaction;
use FireflyIII\models\Account;

use Maatwebsite\Excel\Concerns\FromCollection;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionsExport implements FromView
{
    //use Exportable;

    public function __construct(TransactionCollector $colletor, Account $account)
    {
        $this->colletor = $colletor;
        $this->account = $account;

    }

    public function view(): View
    {
        return view('exports.transactions', [
            'transactions' => $this->colletor->getTransactions(),
            'account' => $this->account,
        ]);
    }
}

