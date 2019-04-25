<?php

namespace App\Http\Controllers;

use App\Http\Models\Recurring;
use App\Http\Models\Transaction;
use Illuminate\Http\Request;
use Validator;

class TransactionController extends Controller {
    public function action_create(Request $request) {
        $input = $request->except('_token');

//        title: formData.title,
//        description: formData.description,
//        amount: formData.amount,
//        type: formData.type

//        recurring_type: formData.recurring_type,
//        ends_at: formData.ends_at

        if(isset($input['end_at'])) {
            $validator = Validator::make($input, [
                'title' => 'required',
                'amount' => 'required',
                'type' => 'required',
                'recurring_type' => 'required',
                'start_at' => 'required',
                'end_at' => 'required'
            ]);
        } else {
            $validator = Validator::make($input, [
                'title' => 'required',
                'amount' => 'required',
                'type' => 'required'
            ]);
        }

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            if(!isset($input['occurred_at'])) {
                $input['occurred_at'] = isset($input['start_at']) ? $input['start_at'] : date('Y-m-d H:i:s');
            }

            if(isset($input['end_at'])) {
                $recurring = Recurring::create([
                    'recurring_type' => $input['recurring_type'],
                    'start_at' => $input['start_at'],
                    'end_at' => $input['end_at']
                ]);

                $input['recurring_id'] = $recurring->id;

                $created_occurences = $this->create_occurences($recurring, $input);

                unset($input['recurring_type'], $input['end_at']);

                // todo: Create the function that will create all of the occurences
                $transaction = Transaction::create($input);
                $response = [
                    'status' => true,
                    'data' => [
                        'recurring' => $recurring,
                        'transaction' => $transaction,
                        'occurences' => $created_occurences
                    ]
                ];
            } else {
                $transaction = Transaction::create($input);
                $response = [
                    'status' => true,
                    'data' => [
                        'transaction' => $transaction
                    ]
                ];
            }

            return response(json_encode($response));
        }
    }

    public function create_occurences($recurring, $transaction) {
        $occurence_timestamps = $this->create_occurence_timestamps($recurring['recurring_type'], $recurring->start_at, $recurring->end_at);
        $occurences_created = [];

        foreach($occurence_timestamps as $timestamp) {
            $transaction = Transaction::create([
                'recurring_id' => $recurring->id,
                'title' => $transaction['title'],
                'description' => $transaction['description'],
                'amount' => $transaction['amount'],
                'type' => $transaction['type'],
                'occurred_at' => $timestamp
            ]);

            array_push($occurences_created, $transaction->toArray());
        }

        return $occurences_created;
    }

    public function test_occurences($type, $start_at, $end_at) {
        $occurences = $this->create_occurence_timestamps($type, $start_at, $end_at);
        print_r($occurences);
    }

    public function create_occurence_timestamps($type, $start_at, $end_at) {
        $datetimestamps = [$start_at];
        $tracking_datetimestamp = $start_at;
        $datetime_object = new \DateTime($start_at);

        while($tracking_datetimestamp < $end_at) {

            switch($type) {
                case 'weekly':
                    $datetime_object->add(new \DateInterval('P7D'));
                break;
                case 'monthly':
                    $datetime_object->add(new \DateInterval('P1M'));
                break;
                case 'yearly':
                    $datetime_object->add(new \DateInterval('P1Y'));
                break;
            }

            $formatted_date = $datetime_object->format('Y-m-d');
            $tracking_datetimestamp = $formatted_date;
            if($formatted_date <= $end_at) {
                array_push($datetimestamps, $formatted_date);
            }
        }

        return $datetimestamps;
    }

    public function action_remove($id) {
        $transaction = Transaction::find($id);

        if($transaction->recurring_id) {
            $recurring_transactions = Transaction::where('recurring_id', $transaction->recurring_id)->get();
            $deleted_ids = [];

            foreach($recurring_transactions as $transaction) {
                array_push($deleted_ids, $transaction->id);
                $transaction->delete();
            }

            $return_ids = $deleted_ids;
        } else {
            $transaction->delete();
            $return_ids = [$id];
        }

        return response(json_encode([
            'status' => true,
            'data' => [
                'transaction_ids' => $return_ids
            ]
        ]));
    }

    public function single($id) {
        $transaction = Transaction::find($id);
        return response(json_encode($transaction));
    }

    public function view($start, $end) {
        $end = $end .' 23:59:59';
        $transactions = Transaction::with('recurring')->whereRaw('occurred_at > ? AND occurred_at < ?', [$start, $end])->orderBy('occurred_at', 'desc')->get();

        return response(json_encode($transactions));
    }
}