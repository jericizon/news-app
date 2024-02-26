<?php

namespace App\Library;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;

class GuardianApi
{
    protected const GUARDIAN_ENDPOINT = 'https://content.guardianapis.com';
    
    private function _validateKey()
    {
        if (empty(config('services.guardian.key'))) {
            throw new Exception("Api key required");        
        }
    }

    public static function search($search, $page = 1)
    {                
        $response = Http::get( self::GUARDIAN_ENDPOINT . "/search", [
            'api-key' => config('services.guardian.key') ?? null,
            'q' => $search ?? null,
            'page' => $page,
        ]);

        if(!$response->successful()) {
            throw new $response;
        }
        
        $response = $response->json()['response'];

        // sample response
        /*
            "status" => "ok",
            "userTier" => "developer",
            "total" => 170518,
            "startIndex" => 1,
            "pageSize" => 10,
            "currentPage" => 1,
            "pages" => 17052,
            "orderBy" => "relevance",
            "results" => []
         */

        $results = collect($response['results'])->map(function ($result) {
            // sample response
            /*
                {
                    "id": "sport/live/2024/feb/26/india-v-england-fourth-test-day-four-live",
                    "type": "liveblog",
                    "sectionId": "sport",
                    "sectionName": "Sport",
                    "webPublicationDate": "2024-02-26T06:03:37Z",
                    "webTitle": "India v England: fourth Test, day four– live",
                    "webUrl": "https://www.theguardian.com/sport/live/2024/feb/26/india-v-england-fourth-test-day-four-live",
                    "apiUrl": "https://content.guardianapis.com/sport/live/2024/feb/26/india-v-england-fourth-test-day-four-live",
                    "isHosted": false,
                    "pillarId": "pillar/sport",
                    "pillarName": "Sport"
                }
            */
            return [
                'id' => $result['id'],
                'date' => Carbon::parse($result['webPublicationDate'])->format('d/m/Y'), // The publication date (formatted as DD/MM/YYYY).
                'title' => $result['webTitle'],
                'url' => $result['webUrl'],
                'sectionId' => $result['sectionId'],
                'sectionName' => $result['sectionName'],
            ];
        })->groupBy('sectionName');

        return [
            'total' => $response['total'],
            'startIndex' => $response['startIndex'],
            'pageSize' => $response['pageSize'],
            'currentPage' => $response['currentPage'],
            'pages' => $response['pages'],
            'orderBy' => $response['orderBy'],
            'results' => $results,
        ];
    }    
}
