<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NewsAppResourceCollection;
use App\Models\PinArticle;

class NewsAppController extends Controller
{
    public static function search(Request $request)
    {
        try {

            $minutes = 30; // For example, 30 minutes
            // Convert minutes to seconds
            $seconds = $minutes * 60;
            $slugLimited = substr(\Str::slug(json_encode($request->all())), 0, 20);

            // cache results for 30 mins for better perfomance
            return \Cache::remember($slugLimited, $seconds, function () use ($request) {
                return \GuardianApi::search($request->search, $request->input('page', 1));
            });
        } catch (\Exception $error) {
            \Log::error($error);
            return response()->json([
                'error' => $error->getMessage(),
            ]);
        }
    }

    public static function getPinnedArticles(Request $request)
    {
        $userCookieKey = \Str::slug(config('app.name') . '_user_session');        
        $userSession = $request->cookie( $userCookieKey );       
        if(!$userSession) {
            $userSession = \Str::random(10);
            $minutes = 60;            
            return response()->json()->withCookie(cookie($userCookieKey, $userSession, $minutes));            
        }

        $articles = PinArticle::where('user_session', $userSession)->get();

        $results = collect($articles)->map(function ($result) {

            // sample response
            /*
                {
                    "user_session": "abcdef",
                    "article_id": "sport/live/2024/feb/26/india-v-england-fourth-test-day-four-live",
                    "section_id": "sport",
                    "section_name": "Sport",
                    "date": "2024-02-26T06:03:37Z",
                    "title": "India v England: fourth Test, day four– live",
                    "url": "https://www.theguardian.com/sport/live/2024/feb/26/india-v-england-fourth-test-day-four-live",
                }
            */
            return [
                'id' => $result['id'],
                'article_id' => $result['article_id'],
                'date' => $result['date'], // The publication date (formatted as DD/MM/YYYY).
                'title' => $result['title'],
                'url' => $result['url'],
                'sectionId' => $result['section_id'],
                'sectionName' => $result['section_name'],
            ];
        })->groupBy('sectionName');

        return [
            'results' => $results,
        ];
    }

    public static function pinArticle(Request $request)
    {
        $userCookieKey = \Str::slug(config('app.name') . '_user_session');        
        $userSession = $request->cookie( $userCookieKey );

        $minutes = 60;            
        if(!$userSession) {
            $userSession = \Str::random(10);            
        }

        $article = PinArticle::firstOrCreate([
            'user_session' => $userSession,
            'article_id' => $request->article_id,
        ]);

        $article->title = $request->title;
        $article->url = $request->url;
        $article->date = $request->date;
        $article->section_id = $request->section_id;
        $article->section_name = $request->section_name;
        $article->save();

        return response()->json([
            'success' => true,
            'message' => 'Article pinned',
            'article' => $article,
            'session' => $userSession,
        ])->withCookie(cookie($userCookieKey, $userSession, $minutes));
    }

    public static function unPinArticle(Request $request, $id)
    {
        $userCookieKey = \Str::slug(config('app.name') . '_user_session');        
        $userSession = $request->cookie( $userCookieKey );

        $minutes = 60;            
        if(!$userSession) {
            $userSession = \Str::random(10);            
        }

        $article = PinArticle::where('user_session', $userSession)->find($id);

        if(!$article->delete()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unpin',
                'session' => $userSession,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Article un-pinned',            
            'session' => $userSession,
        ])->withCookie(cookie($userCookieKey, $userSession, $minutes));
    }
}