<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Transaction;
use App\User;
use App\Utils\TransactionUtil;
use App\Utils\NotificationUtil;
use Illuminate\Support\Facades\DB;

class RecurringExpense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:generateRecurringExpense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates recurring expenses if enabled';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, NotificationUtil $notificationUtil)
    {
        parent::__construct();

        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
            $transactions = Transaction::where('is_recurring', 1)
                                ->where('type', 'expense')
                                ->whereNull('recur_stopped_on')
                                ->whereNotNull('recur_interval')
                                ->whereNotNull('recur_interval_type')
                                ->with(['recurring_invoices', 'business'])
                                ->get();

            foreach ($transactions as $transaction) {
                date_default_timezone_set($transaction->business->time_zone);
                //inner try-catch block open
                try { 

                    //Check if no. of generated invoices exceed limit
                    $no_of_recurring_invoice_generated = count($transaction->recurring_invoices);

                    if (!empty($transaction->recur_repetitions) && $no_of_recurring_invoice_generated >= $transaction->recur_repetitions) {
                        continue;
                    }

                    //Check if generate interval is today
                    $last_generated = $no_of_recurring_invoice_generated > 0 ? $transaction->recurring_invoices->max('transaction_date') : $transaction->transaction_date;

                    if (!empty($last_generated)) {
                        $last_generated_string = \Carbon::parse($last_generated)->format('Y-m-d');
                        $last_generated = \Carbon::parse($last_generated_string);
                        $today = \Carbon::parse(\Carbon::now()->format('Y-m-d'));
                        $diff_from_today = 0;
                        if ($transaction->recur_interval_type == 'days') {
                            $diff_from_today = $last_generated->diffInDays($today);
                        } elseif ($transaction->recur_interval_type == 'months') {

                            //check repeat on date and set last generated date part to reapeat on date
                            if (!empty($transaction->subscription_repeat_on)) {
                                $last_generated_string = $last_generated->format('Y-m');
                                $last_generated = \Carbon::parse($last_generated_string . '-' . $transaction->subscription_repeat_on);
                            }
                            $diff_from_today = $last_generated->diffInMonths($today);
                        } elseif ($transaction->recur_interval_type == 'years') {
                            $diff_from_today = $last_generated->diffInYears($today);
                        }
                        
                        //if last generated is today or less than today then continue
                        if ($diff_from_today == 0) {
                            continue;
                        }

                        //If difference from today is not multiple of recur_interval then continue
                        if ($diff_from_today % $transaction->recur_interval != 0) {
                            continue;
                        }
                    }

                    DB::beginTransaction();
                    //Create new recurring expense
                    $recurring_expense = $this->transactionUtil->createRecurringExpense($transaction);


                    //Save database notification
                    $created_by = User::find($transaction->created_by);
                    $this->notificationUtil->recurringExpenseNotification($created_by, $recurring_expense);

                    //if admin is different
                    if ($created_by->id != $transaction->business->owner_id) {
                        $admin = User::find($transaction->business->owner_id);
                        $this->notificationUtil->recurringExpenseNotification($admin, $recurring_expense);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                }
                //inner try-catch block close
            }

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            die($e->getMessage());
        }
    }
}
