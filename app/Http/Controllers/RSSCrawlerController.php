<?php

namespace App\Http\Controllers;

use App\Game;
use App\Publisher;
use App\RSSFeed;
use Carbon\Carbon;
use DateTime;

class RSSCrawlerController extends Controller
{
    public $keywordWithPrevWord = array();
    public $keywordWithTwoPrevWord = array();
    public $keywordWithNextWord = array();
    public $keywordWithTwoNextWord = array();
    public $keywordWithPrevNextWord = array();


    // List of all RSS feeds we want to know the info from
    private $rss_from_sites = [
        ['4gamers',         'http://www.4gamers.be/rss', false, "link", "D, d M Y H:i:s O", false],
        ['dailynintendo',   'https://www.dailynintendo.nl/feed/', false, "link", "D, d M Y H:i:s O", true], // +2 uur rekenen
        ['evilgamerz',      'http://www.evilgamerz.com/nieuws/evilgamerz.xml', false, "link", "D, d M Y H:i:s O", false],
        // Fokzine
        ['gamed',           'http://www.gamed.nl/rss', false, "link", "D, d M Y H:i:s O", false],

        ['gameliner',       'http://feeds.feedburner.com/gameliner/SuOy', true, "link", "D, d M Y H:i:s O", false],
        ['gamequarter',     'http://www.gamequarter.be/rss/nieuws.xml', true, "link", "D, d M Y H:i:s O", false],
        ['gamereactor',     'https://www.gamereactor.nl/rss/rss.php?texttype=4', false, "link", "D, d M Y H:i:s O", false],
        ['gamesnetnl',      'http://feedproxy.google.com/gamersnet/KbfX', false, "guid", "D, d M Y H:i:s O", false],
        ['gamingnation',    'http://www.gamingnation.nl/feed/', false, "guid", "D, d M Y H:i:s O", true],

        ['igamernl',        'http://feeds.feedburner.com/insidegamer/content?format=xml', false, "link", "Y-m-d\TH:i:sZ", false], //x
        ['ignnl',           'http://nl.ign.com/feed.xml', false, "link", "D, d M Y H:i:s O", false],
        ['powerunlimited',  'https://www.pu.nl/feeds/all/', false, "link", "D, d M Y H:i:s O", false],
        ['telegraaf',       'https://www.telegraaf.nl/tech/games/rss', false, "link", "D, d M Y H:i:s O", false],

        ['thatsgaming',     'http://thatsgaming.nl/feed/', false, "link", "D, d M Y H:i:s O", false],
//        ['tweakers',        'http://feeds.feedburner.com/tweakers/mixed', false, "link", "D, d M Y H:i:s O", false],
        ['xboxworldnl',     'http://www.xboxworld.nl/artikelen/rss/', false, "link", "D, d M Y H:i:s O", false],
        ['xgn',             'https://www.xgn.nl/rss', false, "link", "Y-m-d\TH:i:s O", false],

        ['gameparty',       'http://www.gameparty.net/feed/', false, "link", "D, d M Y H:i:s O", false],
    ];
    //
    public function crawl()
    {

        ini_set("default_charset", 'utf-8');

        // Setup for saving our crawlings
        $date_of_expire = Carbon::now()->subDays(10);

        foreach($this->rss_from_sites as $site) {

            // Setup which site to crawl
            $url = $site[1];
            $xml = simplexml_load_string($this->getHTMLPage($url));

            // Process the crawled XML data to CSV
            foreach($xml->channel->item as $item) {

                $title 		= htmlspecialchars($this->decodeXML($item->title->__toString(), $site[2]));
                if($site[3] == "guid") {
                    $url 	= htmlspecialchars(utf8_decode($item->guid->__toString()));
                } else {
                    $url 	= htmlspecialchars(utf8_decode($item->link->__toString()));
                }
                $datetime	= $item->pubDate->__toString();

                // reformat Date
                $datetime = DateTime::createFromFormat($site[4], $datetime);
                if(isset($site[5]) && $site[5] == true) {
                    $datetime->modify('+2 hours');
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
                    RSSFeed::updateOrCreate([
                        'site'  => $site[0],
                        'title' => $title,
                        'url'   => $url,
                        'published_at' => $datetime,
                        'categories' => $categories,
                        'game_id' => $game_id
                        ]);
                } else {

                    // If we reach the expire date, kill the foreach so we prevent long loaders
                    break;
                }
            }
        }

        dd('saved.');
    }

    public function index()
    {
        ini_set("default_charset", 'utf-8');

        $feed_items = RSSFeed::orderBy('published_at','desc')->get();

        return view('feed.index', compact('feed_items'));
    }

    public function getGameTitles() {

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


            $probable_game_titles[] = $this->stripKeywordIfOneOccurence($keywordWithPrevWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurence($keywordWithTwoPrevWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurence($keywordWithNextWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurence($keywordWithTwoNextWord);
            $probable_game_titles[] = $this->stripKeywordIfOneOccurence($keywordWithPrevNextWord);
        }

        $resetKeywords = [];
        foreach($keywords as $key => $value) {
            $resetKeywords[$key] = [
                'snippet' => $key,
                'occurences' => $value
            ];
        }

        $probable_game_titles[] = $this->stripKeywordIfOneOccurence($resetKeywords);

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

    function getPreviousWord($keyword, $news_items) {
        $tmp = [];

        foreach($news_items as $news_item) {

            if(empty($news_item['title']) || empty($keyword)) {
                break;
            }

            // forget camel casing
            $news_item['title'] = strtolower($news_item['title']);

            // If the keyword is in the newsitem, continue
            if (strpos( strtolower($news_item['title']), strtolower($keyword)) !== false) {

                $titles_snippet_by_keyword = explode(' ' . $keyword, $news_item['title']);

                // als de string aan het begin zit, skip
                if($titles_snippet_by_keyword[0] == $news_item['title']) {// Maak een key
                    $tmp_key = str_replace(' ', '_', $keyword);
                    $tmp_key = strtolower( $tmp_key);

                    $tmp[$tmp_key] = [
                        'snippet' => $keyword,
                        'occurences' => 99];
                }

                $i = 0;

                // omdat er meerdere keren een keyword in 1 titel kan voorkomen, doen we een foreach erop
                foreach ($titles_snippet_by_keyword as $title_snippet) {

                    // Zolang we de laatste niet gebruiken zijn we goed
                    if ($i < count($titles_snippet_by_keyword) - 1) {

                        // vind het laatste woord in de snippet, zodat we "thelast word" hebben
                        $word_before = explode(' ', $title_snippet);
                        $word_before = $word_before[ count($word_before) - 1 ];

                        // Maak een key
                        $tmp_key = str_replace(' ', '_', $keyword);
                        $tmp_key = strtolower($word_before . '_' . $tmp_key);

                        if ( ! in_array($word_before . ' ' . $keyword, array_column($tmp, 'snippet'))) { // search value in the array
                            $tmp[$tmp_key] = [
                                'snippet' => $word_before . ' ' . $keyword,
                                'occurences' => 1
                            ];
                        }
                        else {
                            $tmp[$tmp_key]['occurences'] += 1;
                        }
                    }
                    $i++;
                }
            }

        }

        return $tmp;
    }

    function getNextWord($keyword, $news_items) {
        $tmp = [];


        foreach($news_items as $news_item) {

            if(empty($news_item['title']) || empty($keyword)) {
                break;
            }

            // forget camel casing
            $news_item['title'] = strtolower($news_item['title']);
            $keyword = strtolower($keyword);
//            if($keyword == 'sonic') {
//                dd($news_items);
//            }

            // If the keyword is in the newsitem, continue
            if (strpos( $news_item['title'], $keyword) !== false) {
                // Split by the keyword
                $titles_snippet_by_keyword = explode($keyword . ' ', $news_item['title']);

                unset($titles_snippet_by_keyword[0]);

                // omdat er meerdere keren een keyword in 1 titel kan voorkomen, doen we een foreach erop
                foreach ($titles_snippet_by_keyword as $title_snippet) {

                    // vind het laatste woord in de snippet, zodat we "thelast word" hebben
                    $word_before = explode(' ', $title_snippet);
                    $word_before = $word_before[0];

                    // Maak een key
                    $tmp_key = str_replace(' ', '_', $keyword);
                    $tmp_key = $tmp_key . '_' . $word_before;

                    if ( ! in_array($keyword . ' ' . $word_before, array_column($tmp, 'snippet'))) { // search value in the array
                        $tmp[$tmp_key] = [
                            'snippet' => $keyword . ' ' . $word_before,
                            'occurences' => 1
                        ];
                    }
                    else {
                        $tmp[$tmp_key]['occurences'] += 1;
                    }
                }
            }

        }

        return $tmp;
    }

    function stripKeywordIfOneOccurence($array) {
        foreach($array as $key => $item) {
            if ($item['occurences'] == 1 or $item['occurences'] == 99) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    function stripIfEmptyArray($array) {
        foreach($array as $key => $value){
            if(empty($value)) {
                unset($array[$key]);
            }
        }

        $array = array_map('array_values', $array);

        return $array;
    }

    function getSingleKeywordsFromRSSFeeds($data) {

        // Get all the keywords that we already have, so we remove it from here
        $wordlist = [];
        $removeWords1 = array_map('strtolower', Publisher::pluck('title')->toArray() );
        $removeWords2 = array_map('strtolower', Game::pluck('title')->toArray() );
        $removeWords3 = ['de', 'het', 'een', 'van', 'naar', 'voor', 'achter', 'op', 'onder', 'in', 'uit', 'met', 'zonder', 'nu', 'later', 'is', 'en', 'of', '-'];
        $removeWords4 = ['trailer', 'nieuwe', 'nieuw', 'jaar', 'maand', 'week'];

        $removeWordsRaw = array_merge($removeWords1, $removeWords2, $removeWords3, $removeWords4);

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
    private function getHTMLPage($url) {
        $opts = array(
            'http' => array (
                'method' => 'GET',
                'header' => "
                    User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36; \r\n
			        "
            )
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
    private function decodeXML($string, $bool) {

        $string = str_replace('ï¿½','é', $string);

        if($bool === true) {
            return utf8_decode($string);
        }

        return $string;
    }

    private function findGameidByNewsTitle($newstitle = '') {

        // get game list
        $game_titles = Game::all();

        foreach ($game_titles as $game) {

            if (strpos(strtolower($newstitle), strtolower($game['title'])) !== false) {
                return $game['id'];
                break;
            }

            // TODO: aliases for games
//            if(isset($value['aliases'])) {
//                foreach($value['aliases'] as $alias) {
//                    if (strpos(strtolower($newstitle), strtolower($alias)) !== false) {
//                        return $value['title'];
//                        break;
//                    }
//                }
//            }
        }

        return null;
    }

    private function setGametitleToRSSFeed() {

        $rss_feed = RSSFeed::whereNull('game_id')->get();

        foreach($rss_feed as $item) {

            // Find the game title by the RSSFeed news_item
            $item['game_id'] = $this->findGameidByNewsTitle($item['title']);

            // Update the item
            $item->update(array('game_id' => $item['game_id']));
        }

    }
}
