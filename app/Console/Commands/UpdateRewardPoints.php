<?php

namespace App\Console\Commands;

use App\Business;

use App\Transaction;
use App\Utils\NotificationUtil;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateRewardPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:updateRewardPoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks reward points expiry and updates customer reward points';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil, NotificationUtil $notificationUtil)
    {
        parent::__construct();

        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
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

            $businesses = Business::get();

            DB::beginTransaction();
            foreach ($businesses as $business) {
                if ($business->enable_rp != 1 || empty($business->rp_expiry_period)) {
                    continue;
                }

                $transaction_date_to_be_expired = \Carbon::now();
                if ($business->rp_expiry_type == 'month') {
                    $transaction_date_to_be_expired = $transaction_date_to_be_expired->subMonths($business->rp_expiry_period);
                } elseif ($business->rp_expiry_type == 'year') {
                    $transaction_date_to_be_expired = $transaction_date_to_be_expired->subYears($business->rp_expiry_period);
                }

                $transactions = Transaction::where('business_id', $business->id)
                                        ->where('type', 'sell')
                                        ->where('status', 'final')
                                        ->whereDate('transaction_date', '<=', $transaction_date_to_be_expired->format('Y-m-d'))
                                        ->whereNotNull('rp_earned')
                                        ->with(['contact'])
                                        ->select(
                                            DB::raw('SUM(COALESCE(rp_earned, 0)) as total_rp_expired'),
                                            'contact_id'
                                        )->groupBy('contact_id')
                                        ->get();

                foreach ($transactions as $transaction) {
                    if (!empty($transaction->total_rp_expired) && $transaction->contact->total_rp_used < $transaction->total_rp_expired) {
                        $contact = $transaction->contact;

                        $diff = $transaction->total_rp_expired - $contact->total_rp_used;

                        $contact->total_rp -= $diff;
                        $contact->total_rp_expired = $transaction->total_rp_expired;
                        $contact->save();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            die($e->getMessage());
        }
    }
}
