<?php

namespace App\Http\Controllers;

use App\Platform;
use App\Game;
use App\Publisher;
use App\RSSFeed;
use App\GamePlatform;
use App\RSSWebsite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class RSSCrawlerController extends Controller
{
    public $keywordWithPrevWord = array();
    public $keywordWithTwoPrevWord = array();
    public $keywordWithNextWord = array();
    public $keywordWithTwoNextWord = array();
    public $keywordWithPrevNextWord = array();
    public $expire_in_days = 10;



    //
    public function crawl()
    {

        ini_set("default_charset", 'utf-8');
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes


        // Setup for saving our crawlings
        $date_of_expire = Carbon::now()->subDays($this->expire_in_days);
        $platforms = Platform::all();

        $rss_websites = RSSWebsite::all();

        foreach($rss_websites as $site) {

            // Setup which site to crawl
            $url = $site->rss_url;
            $xml = simplexml_load_string($this->getHTMLPage($url));

            // Process the crawled XML data to CSV
            foreach($xml->channel->item as $item) {

                $title 		= htmlspecialchars_decode($this->decodeXML($item->title->__toString(), $site->title));
                if($site->article_format == "guid") {
                    $url 	= htmlspecialchars(utf8_decode($item->guid->__toString()));
                } else {
                    $url 	= htmlspecialchars(utf8_decode($item->link->__toString()));
                }
                $datetime	= $item->pubDate->__toString();

                // reformat date
                $datetime = Carbon::createFromFormat($site->date_format, $datetime);
                if(isset($site->date_reformat) && $site->date_reformat == true) {
                    $datetime->addHours(2);
                }
                $datetime = $datetime->format("Y-m-d H:i:s");

                // Categories
                $categories = "";
                if(isset($item->category)) {
                    foreach($item->category as $category) {
                        $categories .= $category . ", ";
                    }
                }

                // Game title
                $game_id = $this->findGameidByNewsTitle($title);

                if($datetime > $date_of_expire) {

                    // Save the item to the CSV array
                    RSSFeed::updateOrCreate(
                        [
                            'title' => $title,
                            'url'   => $url,
                        ],
                        [
                            'site'  => $site->title,
                            'rss_website_id'  => $site->id,
                            'published_at' => $datetime,
                            'categories' => $categories,
                            'game_id' => $game_id
                        ]
                    );

                    // Add the platforms to the game if added
                    if($game_id != null && $categories != "") {

                        foreach($platforms as $platform) {
                            if(strpos(strtolower($categories), strtolower($platform['title']))) {

                                GamePlatform::updateOrCreate([
                                    'game_id' => $game_id,
                                    'platform_id' => $platform['id']
                                ]);
                            }
                        }
                    }

                } else {

                    // If we reach the expire date, kill the foreach so we prevent long loaders
                    break;
                }
            }
        }

        $setGameTitlesToRRSFeed = $this->setGametitleToRSSFeed();
        $removeDuplicates = $this->removeDuplicates(false);
        $removeOldNews = $this->removeOldNews();

        return [
            'fetchFeedItems' => 'succeed',
            'saveFeedItems' => 'succeed',
            'setGameTitlesToRSSFeedItems' => $setGameTitlesToRRSFeed,
            'removeDuplicates' => $removeDuplicates,
            'removeOldNews' => $removeOldNews,
        ];
    }

    public function removeDuplicates($view = true)
    {
        $feed_items = RSSFeed::select('*')
            ->selectRaw(' COUNT(`title`) as `occurrences`')
            ->from('rss_feeds')
            ->where([
                ['title', '!=', ''],
            ])
            ->groupBy('title')
            ->having('occurrences', '>', '1')
            ->get();

        foreach($feed_items as $record) {
            $record->forceDelete();
        }

        if($view) {
            return "removed the duplicaties";
        }
        return true;
    }

    public function removeOldNews() {

        $file = 'data/' .Carbon::now()->format("Y-m") . '.json';

        // Get all the old feed_items
        $db_items = RSSFeed::where('published_at', '<', Carbon::now()->subDay($this->expire_in_days))->get();

        if(Storage::disk('local')->exists($file)) {
            // get the current feed_items in the json file
            $file_items = json_decode(Storage::disk('local')->get($file));

            // foreach item still in the DB
            foreach($db_items as $db_item) {

                $in_file = false;

                // And foreach item in the JSON file
                foreach($file_items as $file_item) {

                    if($file_item->title == $db_item->title) {
                        $in_file = true;
                    }
                }

                // If there was no occurrence, we place t he item in the JSON file
                if ($in_file == false) {

                    // Delete in DTB
                    $db_item->delete();

                    // Now put it into the file
                    unset($db_item['id']);
                    $file_items[] = $db_item;
                }
            }
        } else {

            $file_items = array();

            foreach($db_items as $db_item) {
                // Delete in DTB
                $db_item->delete();

                // Now put it into the file
                unset($db_item['id']);
                $file_items[] = $db_item;
            }
        }

        // Save the json_items
        Storage::disk('local')->put($file, json_encode($file_items));

        // Return if wanted
        return true;
    }


    public function index()
    {
        ini_set("default_charset", 'utf-8');

        $feed_items = RSSFeed::orderBy('published_at','desc')->get();

        return view('feed.index', compact('feed_items'));
    }

    public function remove_from_index($id)
    {

        // Delete
        RSSFeed::find($id)->delete();
    }

    public function getGameTitles()
    {

        // First, be sure we have all the games (we saved) added to the RSS_Feed newsitems
        $this->setGametitleToRSSFeed();

        // Now, fetch all the newsitems so we can get the single keywords out of it
        $feed_items = RSSFeed::whereNull('game_id')->orderBy('published_at','desc')->get();
        $keywords = $this->getSingleKeywordsFromRSSFeeds($feed_items);
        $probable_game_titles = [];

        // loop trough each key (like, "2018") and get all the titles regarding it
        foreach($keywords as $keyword => $value){

            // Empty arrays
            $keywordWithTwoPrevWord = [];
            $keywordWithTwoNextWord = [];
            $keywordWithPrevNextWord = [];

            // Retrieve all newsitems with the keyword in the title
            $news_items = RSSFeed::whereNull('game_id')->whereRaw('(LOWER (title) LIKE "% '.strtolower($keyword).' %" OR LOWER (title) LIKE "'.strtolower($keyword).' %")')->get();

            // retrieve previous word
            $keywordWithPrevWord = $this->getPreviousWord($keyword, $news_items);

            // Loop trough the 'last-word + key' options
            foreach($keywordWithPrevWord as $keywithprev) {

                // retrieve previous word (3 words in total)
                $tmp_result = $this->getPreviousWord($keywithprev['snippet'], $news_items);

                // Make key_values easier to read.
                foreach($tmp_result as $key => $result) {
                    $keywordWithTwoPrevWord[$key] = $tmp_result[$key];
                }
            }

            // retrieve next word
            $keywordWithNextWord = $this->getNextWord($keyword, $news_items);

            // Loop trough the 'key + next-word' options
            foreach($keywordWithNextWord as $keywithnext) {

                // retrieve previous word (3 words in total)
                $tmp_result = $this->getNextWord($keywithnext['snippet'], $news_items);

                // Make key_values easier to read.
                foreach($tmp_result as $key => $result) {
                    $keywordWithTwoNextWord[$key] = $tmp_result[$key];
                }
            }

            // Loop trough the 'prev-word + key + next-word' options
            foreach($keywordWithPrevWord as $keywithprev) {

                // retrieve previous word (3 words in total)
                $tmp_result = $this->getNextWord($keywithprev['snippet'], $news_items);

                // Make key_values easier to read.
                foreach($tmp_result as $key => $result) {
                    $keywordWithPrevNextWord[$key] = $tmp_result[$key];
                }
            }


            $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($keywordWithPrevWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($keywordWithTwoPrevWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($keywordWithNextWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($keywordWithTwoNextWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($keywordWithPrevNextWord);
        }

        $resetKeywords = [];
        foreach($keywords as $key => $value) {
            $resetKeywords[$key] = [
                'snippet' => $key,
                'occurrences' => $value
            ];
        }

        $probable_game_titles[] = $this->stripKeywordIfOneOccurrence($resetKeywords);

        // Loop so we can undo depth-1 of array
        $game_titles = [];
        foreach($probable_game_titles as $game) {
            foreach($game as $key => $value) {
                $game_titles[$key] = $value;
            }
        }

//        dd($game_titles);

        return view('feed.gametitles', compact('game_titles'));
    }

    function getPreviousWord($keyword, $news_items)
    {
        $tmp = [];

        foreach($news_items as $news_item) {

            if(empty($news_item['title']) || empty($keyword)) {
                break;
            }

            // forget camel casing
            $news_item['title'] = strtolower($news_item['title']);
            $keyword = strtolower($keyword);

            // If the keyword is in the newsitem, continue
            if (strpos( $news_item['title'], $keyword) !== false) {

                // Split by keyword and space before
                $titles_snippet_by_keyword = explode(' ' . $keyword, $news_item['title']);

                // If the explode returns the whole title, the keyword is at the beginning of the string
                if($titles_snippet_by_keyword[0] == $news_item['title']) {// Maak een key
                    $tmp_key = str_replace(' ', '_', $keyword);

                    $tmp[$tmp_key] = [
                        'snippet' => $keyword,
                        'occurrences' => 99,
                    ];
                }

                $i = 0;

                // omdat er meerdere keren een keyword in 1 titel kan voorkomen, doen we een foreach erop
                foreach ($titles_snippet_by_keyword as $title_snippet) {

                    // Zolang we de laatste niet gebruiken zijn we goed
                    if ($i < count($titles_snippet_by_keyword) - 1) {

                        // vind het laatste woord in de snippet, zodat we "thelast word" hebben
                        $word_before = explode(' ', $title_snippet);
                        $word_before = $word_before[ count($word_before) - 1 ];

                        // Make snippet and key
                        $snippet = $word_before . ' ' . $keyword;
                        $key = str_replace(' ', '_', $snippet);

                        if ( ! in_array($snippet, array_column($tmp, 'snippet'))) { // search value in the array
                            $tmp[$key] = [
                                'snippet' => $snippet,
                                'occurrences' => 1,
                            ];
                        }
                        else {
                            $tmp[$key]['occurrences'] += 1;
                        }
                    }
                    $i++;
                }
            }

        }

        return $tmp;
    }

    function getNextWord($keyword, $news_items)
    {
        $tmp = [];

        foreach($news_items as $news_item) {

            if(empty($news_item['title']) || empty($keyword)) {
                break;
            }

            // forget camel casing
            $news_item['title'] = strtolower($news_item['title']);
            $keyword = strtolower($keyword);

            // If the keyword is in the newsitem, continue
            if (strpos( $news_item['title'], $keyword) !== false) {
                // Split by the keyword
                $titles_snippet_by_keyword = explode($keyword . ' ', $news_item['title']);

                unset($titles_snippet_by_keyword[0]);

                // omdat er meerdere keren een keyword in 1 titel kan voorkomen, doen we een foreach erop
                foreach ($titles_snippet_by_keyword as $title_snippet) {

                    // Vind het eerste woord na de explode op een spatie
                    // Keyword is buitengesloten van de explode dus is op positie 0 het woord dat volgt op t keyword
                    $word_after = explode(' ', $title_snippet)[0];

                    // Make snippet and key
                    $snippet = $keyword . ' ' . $word_after;
                    $key = str_replace(' ', '_', $snippet);

                    // Add or count up to array
                    $tmp[$key] = [
                        'snippet' => $snippet,
                        'occurrences' => (isset($tmp[$key]['occurrences'])) ? $tmp[$key]['occurrences'] + 1 : 1,
                    ];
                }
            }
        }

        return $tmp;
    }

    function stripKeywordIfOneOccurrence($array)
    {
        foreach($array as $key => $item) {
            if ($item['occurrences'] == 1 or $item['occurrences'] == 99) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    function stripIfEmptyArray($array)
    {
        foreach($array as $key => $value){
            if(empty($value)) {
                unset($array[$key]);
            }
        }

        $array = array_map('array_values', $array);

        return $array;
    }

    function getSingleKeywordsFromRSSFeeds($data)
    {

        // Get all the keywords that we already have, so we remove it from here
        $wordlist = [];
        $removeWords1 = array_map('strtolower', Publisher::pluck('title')->toArray() );
        $removeWords2 = array_map('strtolower', Game::pluck('title')->toArray() );
        $removeWords3 = array_map('strtolower', Platform::pluck('title')->toArray() );
        $removeWords4 = ['de', 'het', 'een', 'van', 'naar', 'voor', 'achter', 'op', 'onder', 'in', 'uit', 'met', 'zonder', 'nu', 'later', 'is', 'en', 'of', '-'];
        $removeWords5 = ['trailer', 'nieuwe', 'nieuw', 'jaar', 'maand', 'week'];
        $removeWords6 = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

        $removeWordsRaw = array_merge($removeWords1, $removeWords2, $removeWords3, $removeWords4, $removeWords5, $removeWords6);

        foreach($removeWordsRaw as $removable) {

            if (strpos($removable, " ") !== false) {
                $b = explode(" ", $removable);

                foreach($b as $c) {
                    $removeWords[] = strtolower($c);
                }
            }
            $removeWords[] = strtolower($removable);
        }

        // Now show me some magic. Run trough each word and count!
        foreach ($data as $row) {

            // Remove some special characters
            $row['title'] = $this->removeSpecialCharacters($row['title']);

            // get each word out of the title
            $title_as_array = explode(' ', $row['title']);

            // loop trough each word and count
            foreach ($title_as_array as $word) {
                $word = strtolower($word);
                if (!array_key_exists($word, $wordlist)) {
                    $wordlist[$word] = 1;
                } else {
                    $wordlist[$word] += 1;
                }
            }
        }

        arsort($wordlist);

        foreach($wordlist as $key => $value) {
            if ($value == 1) {
                unset($wordlist[$key]);
                continue;
            }

            if (in_array($key, $removeWords, true)) {
                unset($wordlist[$key]);
                continue;
            }
        }

        return $wordlist;
    }

    /**
     * getHTMLPage($url) - Get the html from the requested page
     * @param $url
     *
     * @return bool|string
     */
    private function getHTMLPage($url)
    {
        $opts = array(
            'http' => array (
                'method' => 'GET',
                'header' => "
                    User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36; \r\n
			        "
            ),
            // TODO fix the verification of openSSL
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        // Open the stream
        $context = stream_context_create($opts);

        // Get the page
        $page = file_get_contents($url, false, $context);

        // Return the value
        return $page;
    }

    /**
     * decodeXML($string, $bool) - If bool is true, return the utf8_decoded string
     * @param $string
     * @param $bool
     *
     * @return string
     */
    private function decodeXML($string, $bool)
    {

        $string = str_replace('ï¿½','é', $string);

        if($bool === true) {
            return utf8_decode($string);
        }

        return $string;
    }

    private function findGameidByNewsTitle($newstitle = '')
    {

        // get game list
        $game_titles = Game::all();

        foreach ($game_titles as $game) {

            // Remove some special characters
            $newstitle = $this->removeSpecialCharacters($newstitle);
            $game['title'] = $this->removeSpecialCharacters($game['title']);

            if (strpos(strtolower($newstitle), strtolower($game['title'])) !== false) {
                return $game['id'];
            }

            // Aliases for games
            if(isset($game['aliases']) && $game['aliases'] != null) {

                $aliases = rtrim($game['aliases'], ',');
                $aliases = explode(',', $aliases);

                foreach($aliases as $alias) {

                    if (strpos(strtolower($newstitle), strtolower($alias)) !== false) {
                        return $game['id'];
                    }
                }
            }
        }

        return null;
    }

    public function suggestGameTitle()
    {
        $rss_feed = RSSFeed::where([
            ['published_at', '>=', Carbon::now()->subHours(48)],
            ['game_id', '=', null]
        ])->get();

        // Get the keywords
        $file = 'data/RSSitems/keywords.json';
        if (Storage::disk('local')->exists($file)) {
            $file_items = json_decode(Storage::disk('local')->get($file), true);
        }

        $suggestions = [];

        // Loop trough the feed_items
        foreach($rss_feed as $item){

            // get each word out of the title
            $title_as_array = explode(' ', $this->removeSpecialCharacters($item->title));

            // loop trough each word
            foreach ($title_as_array as $key => $word) {

                $word = strtolower($word);

                if ($word === "") {
                    continue;
                }

                if(array_key_exists($word, $file_items['items'])) {
                    if ($file_items['items'][ $word ]['in_game'] == 0 && $file_items['items'][ $word ]['other'] > 0) {
                        unset($title_as_array[ $key ]);
                    }
                }
            }

            if($title_as_array != [0 => ""]) {
                $suggestions[$item->id]["title"] = $item->title;
                $suggestions[$item->id]["words"] = $title_as_array;
            }
        }

        return view('backend.feed.suggest', compact('suggestions'));
    }

    private function setGametitleToRSSFeed()
    {

        $rss_feed = RSSFeed::whereNull('game_id')->get();

        foreach($rss_feed as $item) {

            // Find the game title by the RSSFeed news_item
            $item['game_id'] = $this->findGameidByNewsTitle($item['title']);

            // Update the item
            $item->update(array('game_id' => $item['game_id']));
        }

        return true;
    }

    private function removeSpecialCharacters($string)
    {
        $string = preg_replace("/&#?[a-z0-9]{2,8};/i","",$string);
        $string = str_replace(':', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('[', '', $string);
        $string = str_replace(']', '', $string);
        $string = str_replace('-', '', $string);
        $string = str_replace('!', '', $string);
        $string = str_replace('?', '', $string);
        $string = str_replace('  ', ' ', $string);
        $string = str_replace('   ', ' ', $string);

        return $string;
    }
}
