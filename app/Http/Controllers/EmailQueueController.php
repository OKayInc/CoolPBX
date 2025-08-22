<?php

namespace App\Http\Controllers;

use App\Repositories\EmailQueueRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailQueueController extends Controller
{
    protected EmailQueueRepository $emailQueueRepository;

    public function __construct(EmailQueueRepository $emailQueueRepository)
    {
        $this->emailQueueRepository = $emailQueueRepository;
    }
    public function index(): View
    {
        return view("pages.emailqueue.index");
    }

    public function edit(string $uuid): View
    {
        $emailQueue = $this->emailQueueRepository->findByUuid($uuid);
        $emailQueueUuid = $emailQueue->email_queue_uuid;

        return view("pages.emailqueue.form", compact('emailQueueUuid'));
    }

    public function testEmail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email'
        ]);

        try {
            $emailRecipient = $request->input('to');

            $emailBody = "<b>Test Message</b><br /><br />\n";
            $emailBody .= "This message is a test of the SMTP settings configured within your PBX.<br />\n";
            $emailBody .= "If you received this message, your current SMTP settings are valid.<br /><br />\n";

            $emailFromAddress = config('mail.from.address');
            $emailFromName = config('mail.from.name');

            Mail::html($emailBody, function ($message) use ($emailRecipient, $emailFromAddress, $emailFromName) {
                $message->to($emailRecipient)
                    ->subject('Test Message')
                    ->from($emailFromAddress, $emailFromName);
            });

            return response()->json([
                'success' => true,
                'message' => 'Message Sent Successfully',
                'recipient' => $emailRecipient,
                'settings' => [
                    'from_address' => $emailFromAddress,
                    'from_name' => $emailFromName,
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'smtp_port' => config('mail.mailers.smtp.port'),
                    'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
