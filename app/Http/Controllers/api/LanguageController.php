<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    /**
     * Get translations for a specific language
     */
    public function getTranslations($lang)
    {
        $allowedLanguages = ['en', 'bn'];
        
        if (!in_array($lang, $allowedLanguages)) {
            return response()->json(['error' => 'Language not supported'], 400);
        }

        $langPath = lang_path("{$lang}.json");
        
        if (!File::exists($langPath)) {
            Log::warning("Language file not found: {$langPath}");
            return response()->json(['error' => 'Language file not found'], 404);
        }

        try {
            $translations = json_decode(File::get($langPath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Invalid JSON in language file: {$langPath}");
                return response()->json(['error' => 'Invalid language file'], 500);
            }

            return response()->json($translations);
        } catch (\Exception $e) {
            Log::error("Error loading language file: {$e->getMessage()}");
            return response()->json(['error' => 'Failed to load translations'], 500);
        }
    }

    /**
     * Save user's language preference
     */
    public function savePreference(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,bn',
        ]);

        $user = $request->user();
        
        if ($user) {
            // Save to user preferences (if you have a user_preferences table or similar)
            // For now, we'll just return success
            // You can extend this to save to database if needed
            
            // Example: Save to user settings
            // $user->update(['language' => $request->language]);
            
            // Or save to a separate preferences table
            // UserPreference::updateOrCreate(
            //     ['user_id' => $user->id],
            //     ['language' => $request->language]
            // );
        }

        // Set session language
        session(['app_language' => $request->language]);

        return response()->json([
            'message' => 'Language preference saved',
            'language' => $request->language,
        ]);
    }

    /**
     * Get user's language preference
     */
    public function getPreference(Request $request)
    {
        $user = $request->user();
        $language = 'en'; // Default

        if ($user) {
            // Get from user preferences if stored in database
            // $language = $user->language ?? session('app_language', 'en');
            
            // For now, get from session
            $language = session('app_language', 'en');
        } else {
            $language = session('app_language', 'en');
        }

        return response()->json([
            'language' => $language,
        ]);
    }
}
