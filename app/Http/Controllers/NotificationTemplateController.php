<?php

namespace App\Http\Controllers;

use App\NotificationTemplate;
use Illuminate\Http\Request;
use App\Utils\ModuleUtil;

class NotificationTemplateController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('send_notification')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $general_notifications = NotificationTemplate::generalNotifications();

        $general_notifications = $this->__getTemplateDetails($general_notifications);

        $customer_notifications = NotificationTemplate::customerNotifications();

        $module_customer_notifications = $this->moduleUtil->getModuleData('notification_list', ['notification_for' => 'customer']);

        if (!empty($module_customer_notifications)) {
            foreach ($module_customer_notifications as $module_customer_notification) {
                $customer_notifications = array_merge($customer_notifications, $module_customer_notification);
            }
        }

        $customer_notifications = $this->__getTemplateDetails($customer_notifications);

        $supplier_notifications = NotificationTemplate::supplierNotifications();

        $module_supplier_notifications = $this->moduleUtil->getModuleData('notification_list', ['notification_for' => 'supplier']);

        if (!empty($module_supplier_notifications)) {
            foreach ($module_supplier_notifications as $module_supplier_notification) {
                $supplier_notifications = array_merge($supplier_notifications, $module_supplier_notification);
            }
        }

        $supplier_notifications = $this->__getTemplateDetails($supplier_notifications);

        return view('notification_template.index')
                ->with(compact('customer_notifications', 'supplier_notifications', 'general_notifications'));
    }

    private function __getTemplateDetails($notifications)
    {
        $business_id = request()->session()->get('user.business_id');
        foreach ($notifications as $key => $value) {
            $notification_template = NotificationTemplate::getTemplate($business_id, $key);
            $notifications[$key]['subject'] = $notification_template['subject'];
            $notifications[$key]['email_body'] = $notification_template['email_body'];
            $notifications[$key]['sms_body'] = $notification_template['sms_body'];
            $notifications[$key]['whatsapp_text'] = $notification_template['whatsapp_text'];
            $notifications[$key]['auto_send'] = $notification_template['auto_send'];
            $notifications[$key]['auto_send_sms'] = $notification_template['auto_send_sms'];
            $notifications[$key]['auto_send_wa_notif'] = $notification_template['auto_send_wa_notif'];
            $notifications[$key]['cc'] = $notification_template['cc'];
            $notifications[$key]['bcc'] = $notification_template['bcc'];
        }
        return $notifications;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('send_notification')) {
            abort(403, 'Unauthorized action.');
        }

        $template_data = $request->input('template_data');
        $business_id = request()->session()->get('user.business_id');

        foreach ($template_data as $key => $value) {
            NotificationTemplate::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'template_for' => $key
                ],
                [
                    'subject' => $value['subject'],
                    'email_body' => $value['email_body'],
                    'sms_body' => $value['sms_body'],
                    'whatsapp_text' => $value['whatsapp_text'],
                    'auto_send' => !empty($value['auto_send']) ? 1 : 0,
                    'auto_send_sms' => !empty($value['auto_send_sms']) ? 1 : 0,
                    'auto_send_wa_notif' => !empty($value['auto_send_wa_notif']) ? 1 : 0,
                    'cc' => $value['cc'],
                    'bcc' => $value['bcc'],
                ]
            );
        }

        return redirect()->back();
    }
}
