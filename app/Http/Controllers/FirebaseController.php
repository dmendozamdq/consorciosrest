<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

public function postToken(Request $request)
{
    $user = Auth::guard('api')->user();

    if ($request->has('device_token')) {
        $user->device_token = $request->input('device_token');
        $user->save();
    }
}

public function sendAll(Request $request)
{
    $recipients = User::whereNotNull('fcm_token')
        ->pluck('fcm_token')->toArray();

    fcm()
        ->to($recipients)
        ->notification([
            'title' => $request->input('title'),
            'body' => $request->input('body')
        ])
        ->send();

    $notification = 'Notificación enviada a todos los usuarios (Android).';
    return back()->with(compact('notification'));
}
