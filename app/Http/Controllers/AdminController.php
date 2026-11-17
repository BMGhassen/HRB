<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NewUserNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\DatabaseNotification;

class AdminController extends Controller
{
    //
    public function dashboard(){
        return view('admin.dashboard');
    }
    
    public function notification()
    {
        $admin = User::where('role', 'admin')->first();
    if ($admin) {
        $unreadNotifications = $admin->unreadNotifications;
        $unreadNotificationsCount = $unreadNotifications->count();

        return response()->json([
            'unreadNotifications' => $unreadNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);
    } else {
        return response()->json([
            'unreadNotifications' => [],
            'unreadNotificationsCount' => 0,
        ]);
    }
    }

    public function markAsRead($id)
    {
        // Find the notification by its ID
        $notification = DatabaseNotification::find($id);

        // Check if the notification exists
        if ($notification) {
            $notification->markAsRead();
            return redirect()->back()->with('success', 'Notification marked as read');
        }

        // If the notification doesn't exist
        return redirect()->back()->with('error', 'Notification not found');
    }
    
    
    public function UploadImg(Request $request)
    {
        try{
   //   dd('2132132132');
        $request->validate([
            'import_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validating for image files
        ]);

       // dd($request->import_csv);
       //read csv file and skip data
       $file =  $request->file('import_img');

    //   $path = public_path('assets/img');
     $path ='build/assets/img';
       // dd($path);
       // Create the directory if it doesn't exist
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Move the uploaded image to the assets folder
        $file->move($path, $file->getClientOriginalName());

        // Return a success response
        return back()->with('success', 'Image uploaded successfully.');
        }catch (\Exception $e) {
            \Log::error('Upload Error: ' . $e->getMessage());
        return back()->with('error', 'Failed to upload image.');
    }
    }
}
