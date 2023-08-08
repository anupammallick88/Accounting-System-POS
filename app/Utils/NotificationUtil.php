<?php

namespace App\Utils;

use \Notification;
use App\Business;
use App\Notifications\CustomerNotification;
use App\Notifications\RecurringInvoiceNotification;
use App\Notifications\RecurringExpenseNotification;

use App\Notifications\SupplierNotification;

use App\NotificationTemplate;
use App\Restaurant\Booking;
use App\System;
use App\Transaction;
use Config;

class NotificationUtil extends Util
{

    /**
     * Automatically send notification to customer/supplier if enabled in the template setting
     *
     * @param  int  $business_id
     * @param  string  $notification_type
     * @param  obj  $transaction
     * @param  obj  $contact
     *
     * @return void
     */
    public function autoSendNotification($business_id, $notification_type, $transaction, $contact)
    {
        $notification_template = NotificationTemplate::where('business_id', $business_id)
                ->where('template_for', $notification_type)
                ->first();

        $business = Business::findOrFail($business_id);
        $data['email_settings'] = $business->email_settings;
        $data['sms_settings'] = $business->sms_settings;
        $whatsapp_link = '';
        if (!empty($notification_template)) {
            if (!empty($notification_template->auto_send) || !empty($notification_template->auto_send_sms) || !empty($notification_template->auto_send_wa_notif) ) {
                $orig_data = [
                    'email_body' => $notification_template->email_body,
                    'sms_body' => $notification_template->sms_body,
                    'subject' => $notification_template->subject,
                    'whatsapp_text' => $notification_template->whatsapp_text,
                ];
                $tag_replaced_data = $this->replaceTags($business_id, $orig_data, $transaction);

                $data['email_body'] = $tag_replaced_data['email_body'];
                $data['sms_body'] = $tag_replaced_data['sms_body'];
                $data['whatsapp_text'] = $tag_replaced_data['whatsapp_text'];

                //Auto send email
                if (!empty($notification_template->auto_send) && !empty($contact->email)) {
                    $data['subject'] = $tag_replaced_data['subject'];
                    $data['to_email'] = $contact->email;

                    $customer_notifications = NotificationTemplate::customerNotifications();
                    $supplier_notifications = NotificationTemplate::supplierNotifications();

                    try {
                        if (array_key_exists($notification_type, $customer_notifications)) {
                            Notification::route('mail', $data['to_email'])
                                            ->notify(new CustomerNotification($data));
                        } elseif (array_key_exists($notification_type, $supplier_notifications)) {
                            Notification::route('mail', $data['to_email'])
                                            ->notify(new SupplierNotification($data));
                        }
                        $this->activityLog($transaction, 'email_notification_sent', null, [], false, $business_id);
                        
                    } catch (\Exception $e) {
                        \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                    }
                }

                //Auto send sms
                if (!empty($notification_template->auto_send_sms)) {
                    $data['mobile_number'] = $contact->mobile;
                    if (!empty($contact->mobile)) {

                        try {
                            $this->sendSms($data);

                            $this->activityLog($transaction, 'sms_notification_sent', null, [], false, $business_id);

                        } catch (\Exception $e) {
                            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                        }
                    }
                }

                if (!empty($notification_template->auto_send_wa_notif)) {
                    $data['mobile_number'] = $contact->mobile;
                    if (!empty($contact->mobile)) {
                        $whatsapp_link = $this->getWhatsappNotificationLink($data);
                    }
                }
            }
        }

        return $whatsapp_link;
    }

    /**
     * Replaces tags from notification body with original value
     *
     * @param  text  $body
     * @param  int  $booking_id
     *
     * @return array
     */
    public function replaceBookingTags($business_id, $data, $booking_id)
    {
        $business = Business::findOrFail($business_id);
        $booking = Booking::where('business_id', $business_id)
                    ->with(['customer', 'table', 'correspondent', 'waiter', 'location', 'business'])
                    ->findOrFail($booking_id);
        foreach ($data as $key => $value) {
            //Replace contact name
            if (strpos($value, '{contact_name}') !== false) {
                $contact_name = $booking->customer->name;

                $data[$key] = str_replace('{contact_name}', $contact_name, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_1}') !== false) {
                $contact_custom_field_1 = $booking->customer->custom_field1 ?? '';
                $data[$key] = str_replace('{contact_custom_field_1}', $contact_custom_field_1, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_2}') !== false) {
                $contact_custom_field_2 = $booking->customer->custom_field2 ?? '';
                $data[$key] = str_replace('{contact_custom_field_2}', $contact_custom_field_2, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_3}') !== false) {
                $contact_custom_field_3 = $booking->customer->custom_field3 ?? '';
                $data[$key] = str_replace('{contact_custom_field_3}', $contact_custom_field_3, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_4}') !== false) {
                $contact_custom_field_4 = $booking->customer->custom_field4 ?? '';
                $data[$key] = str_replace('{contact_custom_field_4}', $contact_custom_field_4, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_5}') !== false) {
                $contact_custom_field_5 = $booking->customer->custom_field5 ?? '';
                $data[$key] = str_replace('{contact_custom_field_5}', $contact_custom_field_5, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_6}') !== false) {
                $contact_custom_field_6 = $booking->customer->custom_field6 ?? '';
                $data[$key] = str_replace('{contact_custom_field_6}', $contact_custom_field_6, $data[$key]);
            }

            if (strpos($value, '{contact_custom_field_7}') !== false) {
                $contact_custom_field_7 = $booking->customer->custom_field7 ?? '';
                $data[$key] = str_replace('{contact_custom_field_7}', $contact_custom_field_7, $data[$key]);
            }
            if (strpos($value, '{contact_custom_field_8}') !== false) {
                $contact_custom_field_8 = $booking->customer->custom_field8 ?? '';
                $data[$key] = str_replace('{contact_custom_field_8}', $contact_custom_field_8, $data[$key]);
            }
            if (strpos($value, '{contact_custom_field_9}') !== false) {
                $contact_custom_field_9 = $booking->customer->custom_field9 ?? '';
                $data[$key] = str_replace('{contact_custom_field_9}', $contact_custom_field_9, $data[$key]);
            }
            if (strpos($value, '{contact_custom_field_10}') !== false) {
                $contact_custom_field_10 = $booking->customer->custom_field10 ?? '';
                $data[$key] = str_replace('{contact_custom_field_10}', $contact_custom_field_10, $data[$key]);
            }

            //Replace table
            if (strpos($value, '{table}') !== false) {
                $table = !empty($booking->table->name) ?  $booking->table->name : '';

                $data[$key] = str_replace('{table}', $table, $data[$key]);
            }

            //Replace start_time
            if (strpos($value, '{start_time}') !== false) {
                $start_time = $this->format_date($booking->booking_start, true);

                $data[$key] = str_replace('{start_time}', $start_time, $data[$key]);
            }

            //Replace end_time
            if (strpos($value, '{end_time}') !== false) {
                $end_time = $this->format_date($booking->booking_end, true);

                $data[$key] = str_replace('{end_time}', $end_time, $data[$key]);
            }
            //Replace location
            if (strpos($value, '{location}') !== false) {
                $location = $booking->location->name;

                $data[$key] = str_replace('{location}', $location, $data[$key]);
            }

            if (strpos($value, '{location_name}') !== false) {
                $location = $booking->location->name;

                $data[$key] = str_replace('{location_name}', $location, $data[$key]);
            }

            if (strpos($value, '{location_address}') !== false) {
                $location_address = $booking->location->location_address;

                $data[$key] = str_replace('{location_address}', $location_address, $data[$key]);
            }

            if (strpos($value, '{location_email}') !== false) {
                $location_email = $booking->location->email;

                $data[$key] = str_replace('{location_email}', $location_email, $data[$key]);
            }

            if (strpos($value, '{location_phone}') !== false) {
                $location_phone = $booking->location->mobile;

                $data[$key] = str_replace('{location_phone}', $location_phone, $data[$key]);
            }

            if (strpos($value, '{location_custom_field_1}') !== false) {
                $location_custom_field_1 = $booking->location->custom_field1;

                $data[$key] = str_replace('{location_custom_field_1}', $location_custom_field_1, $data[$key]);
            }

            if (strpos($value, '{location_custom_field_2}') !== false) {
                $location_custom_field_2 = $booking->location->custom_field2;

                $data[$key] = str_replace('{location_custom_field_2}', $location_custom_field_2, $data[$key]);
            }

            if (strpos($value, '{location_custom_field_3}') !== false) {
                $location_custom_field_3 = $booking->location->custom_field3;

                $data[$key] = str_replace('{location_custom_field_3}', $location_custom_field_3, $data[$key]);
            }

            if (strpos($value, '{location_custom_field_4}') !== false) {
                $location_custom_field_4 = $booking->location->custom_field4;

                $data[$key] = str_replace('{location_custom_field_4}', $location_custom_field_4, $data[$key]);
            }

            //Replace service_staff
            if (strpos($value, '{service_staff}') !== false) {
                $service_staff = !empty($booking->waiter) ? $booking->waiter->user_full_name : '';

                $data[$key] = str_replace('{service_staff}', $service_staff, $data[$key]);
            }

            //Replace service_staff
            if (strpos($value, '{correspondent}') !== false) {
                $correspondent = !empty($booking->correspondent) ? $booking->correspondent->user_full_name : '';

                $data[$key] = str_replace('{correspondent}', $correspondent, $data[$key]);
            }

            //Replace business_name
            if (strpos($value, '{business_name}') !== false) {
                $business_name = $business->name;
                $data[$key] = str_replace('{business_name}', $business_name, $data[$key]);
            }

            //Replace business_logo
            if (strpos($value, '{business_logo}') !== false) {
                $logo_name = $business->logo;
                $business_logo = !empty($logo_name) ? '<img src="' . url('storage/business_logos/' . $logo_name) . '" alt="Business Logo" >' : '';

                $data[$key] = str_replace('{business_logo}', $business_logo, $data[$key]);
            }
        }
        return $data;
    }

    public function recurringInvoiceNotification($user, $invoice)
    {
        $user->notify(new RecurringInvoiceNotification($invoice));
    }

    public function recurringExpenseNotification($user, $expense)
    {
        $user->notify(new RecurringExpenseNotification($expense));
    }

    public function configureEmail($notificationInfo, $check_superadmin = true)
    {
        $email_settings = $notificationInfo['email_settings'];

        $is_superadmin_settings_allowed = System::getProperty('allow_email_settings_to_businesses');

        //Check if prefered email setting is superadmin email settings
        if (!empty($is_superadmin_settings_allowed) && !empty($email_settings['use_superadmin_settings']) && $check_superadmin) {
            $email_settings['mail_driver'] = config('mail.driver');
            $email_settings['mail_host'] = config('mail.host');
            $email_settings['mail_port'] = config('mail.port');
            $email_settings['mail_username'] = config('mail.username');
            $email_settings['mail_password'] = config('mail.password');
            $email_settings['mail_encryption'] = config('mail.encryption');
            $email_settings['mail_from_address'] = config('mail.from.address');
        }

        $mail_driver = !empty($email_settings['mail_driver']) ? $email_settings['mail_driver'] : 'smtp';
        Config::set('mail.driver', $mail_driver);
        Config::set('mail.host', $email_settings['mail_host']);
        Config::set('mail.port', $email_settings['mail_port']);
        Config::set('mail.username', $email_settings['mail_username']);
        Config::set('mail.password', $email_settings['mail_password']);
        Config::set('mail.encryption', $email_settings['mail_encryption']);

        Config::set('mail.from.address', $email_settings['mail_from_address']);
        Config::set('mail.from.name', $email_settings['mail_from_name']);
    }
}
