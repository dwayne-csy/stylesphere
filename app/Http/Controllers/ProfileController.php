<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
    
        $request->validate([
            'name' => 'required|string|max:255', // Added name validation
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'age' => 'nullable|integer|min:1',
            'sex' => 'nullable|in:Male,Female,Other',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
    
        // Handle Image Upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $user->image = $imagePath;
        }
    
        // Update user data
        $user->name = $request->name;
        $user->age = $request->age;
        $user->sex = $request->sex;
        $user->contact_number = $request->contact_number;
        $user->address = $request->address;
        
        $user->save();
    
        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}