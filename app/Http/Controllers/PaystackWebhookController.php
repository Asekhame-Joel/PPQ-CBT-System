<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\ConfirmCoursePayment;
use App\Payments\PaymentVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, ConfirmCoursePayment $confirmCoursePayment): Response
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

        if (blank($reference)) {
            return response('Webhook received.');
        }

        $payment = Payment::query()->where('reference', $reference)->first();

        if (! $payment) {
            Log::warning('Paystack webhook referenced an unknown payment.', [
                'reference' => $reference,
            ]);

            return response('Webhook received.');
        }

        $verification = new PaymentVerification(
            successful: $request->string('data.status')->toString() === 'success',
            reference: $reference,
            amount: $request->integer('data.amount'),
            currency: $request->string('data.currency')->toString(),
            response: $request->all(),
        );

        try {
            $confirmCoursePayment->handleVerified($payment, $verification);
        } catch (RuntimeException $exception) {
            Log::warning('Paystack webhook payment details did not match.', [
                'payment_id' => $payment->getKey(),
                'reference' => $reference,
                'message' => $exception->getMessage(),
            ]);
        }

        return response('Webhook received.');
    }
}
