<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaystackWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || blank($secretKey)) {
            return response('Payment webhook is not configured.', 503);
        }

        $expectedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);
        $providedSignature = $request->header('x-paystack-signature', '');

        if (! is_string($providedSignature) || ! hash_equals($expectedSignature, $providedSignature)) {
            return response('Invalid signature.', 401);
        }

        if ($request->string('event')->toString() !== 'charge.success') {
            return response('Webhook received.');
        }

        $reference = $request->string('data.reference')->toString();

        if (filled($reference)) {
            ProcessPaystackWebhook::dispatch($reference);
        }

        return response('Webhook received.');
    }
}
