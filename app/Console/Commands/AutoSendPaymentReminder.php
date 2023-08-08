<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Utils\NotificationUtil;
use App\Business;
use App\Transaction;
use \Notification;
use App\Notifications\CustomerNotification;
use App\NotificationTemplate;

class AutoSendPaymentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:autoSendPaymentReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends payment reminder to customers with overdue sells if auto send is enabled in notification template for payment reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationUtil $notificationUtil)
    {
        parent::__construct();

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
            
            $templates = NotificationTemplate::where('template_for', 'payment_reminder')
                                        ->where( function($q) {
                                            $q->where('auto_send', 1)
                                            ->orWhere('auto_send_sms', 1)
                                            ->orWhere('auto_send_wa_notif', 1);
                                        })
                                        ->get();
                                        

            foreach ($templates as $template) {

                $business = Business::with(['currency'])->where('id', $template->business_id)->first();

                $data = [
                    'subject' => $template->subject ?? '',
                    'sms_body' => $template->sms_body ?? '',
                    'whatsapp_text' => $template->whatsapp_text ?? '',
                    'email_body' => $template->email_body ?? '',
                    'template_for' => 'payment_reminder',
                    'cc' => $template->cc ?? '',
                    'bcc' => $template->bcc ?? '',
                    'auto_send' => !empty($template->auto_send) ? 1 : 0,
                    'auto_send_sms' => !empty($template->auto_send_sms) ? 1 : 0,
                    'auto_send_wa_notif' => !empty($template->auto_send_wa_notif)
                     ? 1 : 0
                ];

                $orig_data = [
                    'email_body' => $data['email_body'],
                    'sms_body' => $data['sms_body'],
                    'subject' => $data['subject'],
                    'whatsapp_text' => $data['whatsapp_text']
                ];

                if (!empty($data['auto_send']) || !empty($data['auto_send_sms'])) {
                    $overdue_sells = Transaction::where('transactions.business_id', $business->id)
                                    ->where('transactions.type', 'sell')
                                    ->where('transactions.status', 'final')
                                    ->leftjoin('activity_log as a', function($join){
                                        $join->on('a.subject_id', '=', 'transactions.id')
                                            ->where('subject_type', 'App\Transaction')
                                            ->where('description', 'payment_reminder');
                                    })
                                    ->whereNull('a.id')
                                    ->with(['contact', 'payment_lines'])
                                    ->select('transactions.*')
                                    ->groupBy('transactions.id')
                                    ->OverDue()
                                    ->get();
                    
                    foreach ($overdue_sells as $sell) {
                        $tag_replaced_data = $this->notificationUtil->replaceTags($business, $orig_data, $sell);

                        $data['email_body'] = $tag_replaced_data['email_body'];
                        $data['sms_body'] = $tag_replaced_data['sms_body'];
                        $data['subject'] = $tag_replaced_data['subject'];
                        $data['whatsapp_text'] = $tag_replaced_data['whatsapp_text'];

                        $data['email_settings'] = $business->email_settings ?? [];
                        $data['sms_settings'] = $business->sms_settings ?? [];

                        //send email notification
                        if (!empty($data['auto_send']) && !empty($sell->contact->email)) {
                            try {
                                Notification::route('mail', [$sell->contact->email])
                                    ->notify(new CustomerNotification($data));

                                $this->notificationUtil->activityLog($sell, 'payment_reminder', null, ['email' => $sell->contact->email, 'is_automatic' => true], false);
                            } catch (\Exception $e) {
                                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                            }
                            
                        }

                        //send sms notification
                        if (!empty($data['auto_send_sms']) && !empty($sell->contact->mobile)) {
                            try {
                                $this->notificationUtil->sendSms($data);

                                $this->notificationUtil->activityLog($sell, 'payment_reminder', null, ['mobile' => $sell->contact->mobile, 'is_automatic' => true], false);
                            } catch (\Exception $e) {
                                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                            }
                        }

                        //TODO:: whatsapp notification to be implemented
                    }

                }
            }
            
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            die($e->getMessage());
        }
    }
}
