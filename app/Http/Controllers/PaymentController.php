<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Payments;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;

use Validator, Exception;


class PaymentController extends Controller
{
    //
    // display a form for payment
    public function initiate()
    {
        return view('paytm');
    }

    public function pay(Request $request)
    {

        $amount = 1500; //Amount to be paid

         $validator = Validator::make($request->all() , [
            'mobile' => 'digits_between:6,13',
            'email' => 'required|max:255',
            ]);

        if($validator->fails()) {

            $errors = implode(',',$validator->messages()->all());

            return back()->with('messages', $errors);
        }

        $userData = [
            'name' => $request->name, // Name of user
            'mobile' => $request->mobile, //Mobile number of user
            'email' => $request->email, //Email of user
            'fee' => $amount,
            'order_id' => $request->mobile . "_" . rand(1, 1000) //Order id
        ];

        $paytmuser = Payments::create($userData); // creates a new database record

        $payment = PaytmWallet::with('receive');

        $payment->prepare([
            'order' => $userData['order_id'],
            'user' => $paytmuser->id,
            'mobile_number' => $userData['mobile'],
            'email' => $userData['email'], // your user email address
            'amount' => $amount, // amount will be paid in INR.
            'callback_url' => route('status') // callback URL
        ]);
        return $payment->receive();  // initiate a new payment
    }

    public function paymentCallback()
    {

        try {

            $transaction = PaytmWallet::with('receive');

            $response = $transaction->response();

            $order_id = $transaction->getOrderId(); // return a order id

            $transaction->getTransactionId(); // return a transaction id

            // update the db data as per result from api call
            if ($transaction->isSuccessful()) {

                Payments::where('order_id', $order_id)->update(['status' => 1, 'transaction_id' => $transaction->getTransactionId()]);

                return redirect(route('initiate.payment'))->with('message', "Your payment is successfull.");
         
            } else if ($transaction->isFailed()) {

                Payments::where('order_id', $order_id)->update(['status' => 0, 'transaction_id' => $transaction->getTransactionId()]);

                return redirect(route('initiate.payment'))->with('message', "Your payment is failed.");
           
            } else if ($transaction->isOpen()) {

                Payments::where('order_id', $order_id)->update(['status' => 2, 'transaction_id' => $transaction->getTransactionId()]);

                return redirect(route('initiate.payment'))->with('message', "Your payment is processing.");
            }
            $transaction->getResponseMessage(); //Get Response Message If Available

            // $transaction->getOrderId(); // Get order id

        } catch (Exception $e) {

            return back()->with('flash_error', $e->getMessage());
        }
    }
}
