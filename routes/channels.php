<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/*
|--------------------------------------------------------------------------
| Public Channels
|--------------------------------------------------------------------------
|
| These channels are accessible to all users without authentication
|
*/

// Message Channel - Public access for real-time messaging
Broadcast::channel('message', function () {
    return true;
});

/*
|--------------------------------------------------------------------------
| Private Channels
|--------------------------------------------------------------------------
|
| These channels require user authentication
|
*/

// Example: User-specific channel
// Broadcast::channel('user.{userId}', function ($user, $userId) {
//     return (int) $user->id === (int) $userId;
// });

// Example: Admin-only channel
// Broadcast::channel('admin', function ($user) {
//     return $user->role === 'admin';
// });