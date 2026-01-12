<?php

namespace App\Http\Controllers;

use App\Repositories\EmailQueueRepository;
use App\Services\EmailQueueService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailQueueController extends Controller
{
    protected EmailQueueRepository $emailQueueRepository;
    protected EmailQueueService $emailQueueService;

    public function __construct(EmailQueueRepository $emailQueueRepository, EmailQueueService $emailQueueService)
    {
        $this->emailQueueRepository = $emailQueueRepository;
        $this->emailQueueService = $emailQueueService;
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
            $queueItem = $this->emailQueueService->queueEmail([
                'domain_uuid' => null,
                'hostname'    => $request->getHost(),
                'to'          => $emailRecipient,
                'from'        => $emailFromAddress,
                'subject'     => 'Test Message (Queued)',
                'body'        => $emailBody,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message Queued Successfully',
                'uuid'    => $queueItem->email_queue_uuid,
                'recipient' => $emailRecipient,
                'settings' => [
                    'from_address' => $emailFromAddress,
                    'smtp_host' => config('mail.mailers.smtp.host'),
                    'smtp_port' => config('mail.mailers.smtp.port'),
                    'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Queueing Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
