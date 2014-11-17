<?php
namespace Lycee\Importer;
/**
 * Class for importing files from http://lycee-tcg.com/
 *
 * S_L - which filter menu should be open by default
 *      1: sets
 * page_out - How many results to display per page
 * page_list - Style to show results.
 *      1: thumbnail view
 *      2: list view (with all needed details except image)
 * 
 **/
use Zend\Dom\Query;
use Illuminate\Contracts\Cache\Repository as Cache;

class Lycee {

    public $baseUrl = 'http://lycee-tcg.com/card_list';
    public $convertToUtf8 = true;
    public $websiteVersionTag = "website-version-1";

    /**
     * \Lycee\Zend\CacheHelper
     * 
     * @var mixed
     * @access protected
     */
    protected $cache;
    protected $amysql;
    protected $_serviceManager;
    protected $_currentCards;
    protected $_raritiesAndSets = array ();
    protected $_sets;

    public $setsTableName = 'lycdb_sets';
    public $cardsTableName = 'lycdb_cards';
    public $cardsConnectSetsTableName = 'lycdb_cards_sets_connect';

    public function __construct(\AMysql $amysql, Cache $cache)
    {
        $this->amysql = $amysql;
        $this->cache = $cashe;
    }

    public function import() {
        $sets = $this->getSets();
        $amysql = $this->amysql;

        $stmt = $amysql->prepare("SELECT * FROM $this->cardsTableName");
        $stmt->execute();
        $currentCards = $stmt->fetchAllAssoc('cid');
        $this->_currentCards = $currentCards;

        $setDatas = array ();
        foreach ($sets as $set) {
            $setData = array ();
            $setData['name_jp'] = $set['name'];
            $setData['ext_id'] = $set['extId'];
            $setDatas[] = $setData;
        }
        try {
            $this->_sets = $setDatas;
            $stmt = $amysql->newStatement();
            $stmt->insertReplaceOnDuplicateKeyUpdate('INSERT', $this->setsTableName, $setDatas);
            $stmt->execute();
        }
        catch (\AMysql_Exception $e) {
            trigger_error($e);
        }
        $met = ini_get('max_execution_time');
        $this->importAllCards();
        echo "Done importing.";
    }

    public function importSetsByArrayOfSets($sets) {
        foreach ($sets as $set) {
            try {
                set_time_limit($met);
                $this->importSetByArray($set);
            }
            catch (\AMysql_Exception $e) {
                trigger_error($e);
            }
            catch (\Exception $e) {
                trigger_error($e);
            }
        }
    }

    public function importAllCards() {
        $arr = array ();
        $arr['page_out'] = 50000;
        $arr['path'] = 'index.cgi';
        $arr['qs'] = array ();
        $stats = $this->importCardsByArray($arr);
        printf("Imported all cards.<br>\nFound: %d<br>\nalternate: %d<br>\ninserted: %d<br>\nupdated %s<br>\n<br>\n",
            $stats['cardCount'], $stats['alternateCount'], $stats['insertedCount'], $stats['updatedCount']
        );

        $datas = array ();
        $tableName = $this->cardsConnectSetsTableName;

        $map = array ();
        foreach ($this->_sets as $set) {
            $name = $set['name_jp'];
            $map[$name] = $set['ext_id'];
        }

        foreach ($this->_raritiesAndSets as $cid => $editions) {
            foreach ($editions as $val) {
                $setName = $val['set'];
                $setExtId = isset($map[$setName]) ? $map[$setName] : 0;
                $data = array (
                    'cid' => $cid,
                    'extended_cid' => $val['extendedCid'],
                    'rarity' => $val['rarity'],
                    'set_ext_id' => $setExtId,
                    'set_name' => $val['set'],
                );
                $datas[] = $data;
            }
        }
;
        if ($datas) {
            $amysql = $this->amysql;
            $stmt = $amysql->newStatement();
            $stmt->insertReplace('INSERT IGNORE', $tableName, $datas);
            $stmt->execute();
        }
    }

    public function importSetByArray($arr) {
        printf("Importing set %s ...<br>\n", $arr['name']);
        $stats = $this->importCardsByArray($arr);
        printf("Set: %s<br>\ncards found: %d<br>\nalternate: %d<br>\ninserted: %d<br>\nupdated %s<br>\n<br>\n",
            $arr['name'], $stats['cardCount'], $stats['alternateCount'], $stats['insertedCount'], $stats['updatedCount']
        );
    }

    public function importCardsByArray($arr) {
        $amysql = $this->amysql;
        $qs = $arr['qs'];
        $qs['page_out'] = 500;
        $qs['page_list'] = 2;
        $qs['page_sort'] = 2;
        if (isset($arr['page_out'])) {
            $qs['page_out'] = $arr['page_out'];
        }
        $options = array ();
        $options['lifetime'] = 60 * 60 * 24 * 265 * 5; // 5 years.
        $options['cache_tags'] = array ($this->websiteVersionTag);
        if ($arr['listsCards']) {
            $options['alternatecache'] = 2;
        }
        set_time_limit(500);
        if (!isset($arr['name']) && file_exists('./data/cache/lycee/all_cards.html')) {
            $html = fopen('./data/cache/lycee/all_cards.html', 'r');
        }
        else {
            set_time_limit(200);
            $html = $this->request($arr['path'], $qs, $options);
        }

        $tableArray = array ();

        $cards = array ();

        /**
         * Cards in the HTML are usually 4 tables separated by a br. 2 brs mark the end. 
         */
        if (is_resource($html)) {
            $buffer = array ();

            $started = false;

            $cardStart = '<table width="0" border="0" cellspacing="0" cellpadding="0" class="m_17">';
            $cardEnd = '</table><br />';
            while (false !== ($line = fgets($html))) {
                if (false !== strpos($line, $cardStart)) {
                    $started = true;
                }
                if ($started) {
                    $buffer[] = $line;
                }
                if ($started && false !== strpos($line, $cardEnd)) {
                    $started = false;
                    $cardHtml = join('', $buffer);
                    $buffer = array ();
                    $cardHtml = "<?xml encoding=\"utf-8\">\n<!doctype html>\n<html><head><meta charset='utf-8'></head><body>\n" .
                        '<div id="root">' . mb_convert_encoding($cardHtml, 'utf-8', array ('EUC_JP')) . '</div></body></html>';
                    $doc = new \DomDocument('1.0', 'utf-8');
                    @$doc->loadHtml($cardHtml);
                    $root = $doc->getElementById('root');
                    $children = $root->childNodes;
                    $tableArray = array ();
                    foreach ($children as $child) {
                        if ('table' == $child->tagName) {
                            $tableArray[] = $child;
                        }
                    }

                    $card = $this->getCardByTablesList2($tableArray);
                    $cards[] = $card;
                }
            }
        }
        else {
            $domQuery = new \Zend\Dom\Query($html);
            $selector = '#card_list_main div.m_15 > *';
            $selectEls = $domQuery->execute($selector);

            foreach ($selectEls as $selectEl) {
                if ('table' == $selectEl->tagName) {
                    $tableArray[] = $selectEl;
                }
                else if ('br' == $selectEl->tagName) {
                    if ($tableArray) {
                        $card = $this->getCardByTablesList2($tableArray);
                        $cards[] = $card;
                    }
                    $tableArray = array ();
                }
            }
        }
        $datas = array ();
        $baseNeededData = array (
            'cid', 'name_jp', 'ex', 'is_snow', 'is_moon', 'is_lightning', 'is_flower', 'is_sun',
            'cost_snow', 'cost_moon', 'cost_lightning', 'cost_flower', 'cost_sun', 'cost_star',
            'ability_desc_jp', 'comments_jp', 'import_errors', 'type', 'ability_cost_jp', 'ability_name_jp',
            'conversion_jp', 'basic_ability_flags', 'basic_abilities_jp', 'is_male', 'is_female', 'import_errors',
            'ap', 'dp', 'sp', 'position_flags', 'card_hash', 'lang_hash', 'import_card_hash',
        );
        $hashColumns = Model::getHashColumns();
        $langHashColumns = Model::getLangHashColumns();

        $changes = array (); // data to go into updates. Could just be a change of a hash.
        
        $cardCount = count($cards);
        $alternateCount = 0;

        $insertDatas = array ();

        foreach ($cards as $card) {
            $neededData = $baseNeededData;
            $cid = $card->getCidText();
            if (!isset($this->_raritiesAndSets[$cid])) {
                $this->_raritiesAndSets[$cid] = array ();
            }
            $ras = array (
                'extendedCid' => $card->fullCidText,
                'rarity' => $card->rarity,
                'set' => $card->getJapaneseSetName()
            );
            $this->_raritiesAndSets[$cid][] = $ras;

            if ($card->alternate) {
                $alternateCount++;
                continue;
            }
            $data = $card->toDbData();
            $data['import_card_hash'] = $data['card_hash'];

            $dataToUse = array ();

            $totallyNewCard = !isset($this->_currentCards[$cid]);
            $changed = false;
            if (!$totallyNewCard) {
                $cCard = $this->_currentCards[$cid];
                // treat the card as changed if the card_hash's do not match
                $changed = $data['card_hash'] != $cCard['card_hash'] || !$cCard['import_card_hash'];
                if ($cCard['locked']) {
                    // except if the card is locked.
                    $changed = false;
                    if ($cCard['import_card_hash'] != $data['card_hash']) {
                        // but if the locked card's import_card_hash would differ from the newly imported card data's
                        // card_hash, change the card, but only the import_card_hash.
                        $changed = true;
                        $neededData = array ('import_card_hash');
                    }
                }
                $changes[$cid] = $changed;
            }

            if ($changed || $totallyNewCard) {
                foreach ($neededData as $key) {
                    if (!array_key_exists($key, $data)) {
                        $setId = isset($arr['extId']) ? $arr['extId'] : '[not a set]';
                        trigger_error("Missing key: $key. Aborting cards. Set id: $setId", E_USER_WARNING);
                        return;
                    }
                    else {
                        $dataToUse[$key] = $data[$key];
                    }
                }
                if ($totallyNewCard) {
                    $dataToUse['insert_date'] = $amysql->expr('CURRENT_TIMESTAMP');
                    $insertDatas[] = $dataToUse;
                }
                else if ($changed) {
                    $datas[] = $dataToUse;
                }
            }
            ob_flush();
        }

        if (!$cards) {
            //trigger_error("No cards to insert or update.");
            echo "<span class=\"error\">Url: " . $this->getFullUrl($arr['path'], $qs) . " " . print_r($arr, true) . "</span>";
        }

        $insertCount = 0;
        $updatedCount = 0;

        if ($datas || $insertDatas) {
            try {
                if ($insertDatas) {
                    $stmt = $amysql->newStatement();
                    $stmt->insertReplace('INSERT IGNORE', $this->cardsTableName, $insertDatas);
                    $stmt->execute();
                    $insertCount = $affectedRows = $stmt->affectedRows;
                }
            }
            catch (\Exception $e) {
                trigger_error("Couldn't update some rows. $e");
            }

            $updateDateData = array (
                'update_date' => $amysql->expr('CURRENT_TIMESTAMP')
            );
            foreach ($datas as $data) {
                unset($data['insert_date']);
                $success = $amysql->update($this->cardsTableName, $data, 'cid = ?', $data['cid']);
                if ($amysql->affectedRows) {
                    $updatedCount++;
                    $amysql->update($this->cardsTableName, $updateDateData, 'cid = ?', $data['cid']);
                    printf("Updated card: %s<br>\n", $data['cid']);
                }
            }
        }

        $stats = array (
            'cardCount' => $cardCount,
            'alternateCount' => $alternateCount,
            'insertedCount' => $insertCount,
            'updatedCount' => $updatedCount
        );

        return $stats;
    }

    public function getCardByTablesList2(array $tableArray) {
        /**
         * Card id, type, name, elements, ex
         **/
        $firstCells = $tableArray[0]->getElementsByTagName('td');
        $cidText = trim(strip_tags($firstCells->item(0)->textContent));
        $cardTypeText = trim($firstCells->item(1)->textContent, " \t\n\r\0\x0B　");
        $name = trim($firstCells->item(2)->textContent);
        $pattern = '@<img src="([^\"]*)"@';
        $elementArr = $this->countElementsByDomElement($firstCells->item(3));
        $exText = trim($firstCells->item(4)->textContent);
        $foundEx = preg_match('@\d+@', $exText, $matches2);

        if ('ＩTEM' == $cardTypeText) {
            $cardTypeText = 'ITEM';
        }
        $card = Card::newCardByTypeText($cardTypeText, $cidText);

        $isChar = $card instanceof Char;
        $card->setCidText($cidText);
        $card->setJpName($name);
        $card->setElementByJapaneseArray($elementArr);
        if ($foundEx) {
            $ex = $matches2[0];
            $card->ex = (int) $ex;
        }
        else {
            $card->addError("Couldn't find card's ex");
        }

        /**
         * Card cost, position, ap, dp, sp, gender, rarity
         **/
        $secondCells = $tableArray[1]->getElementsByTagName('td');
        $costElementArr = $this->countElementsByDomElement($secondCells->item(0));
        $card->setCostByJapaneseArray($costElementArr);
        if ($isChar) {
            $positionImgs = $secondCells->item(1)->getElementsByTagName('img');
            $flags = 0;
            for ($i = 0; $i < 6; $i++) {
                $img = $positionImgs->item($i);
                $hasPosition = false !== strpos($img->getAttribute('src'), 'b.gif');
                if ($hasPosition) {
                    $flags |= (1 << $i);
                }
            }
            if ($flags) {
                $card->setSpotFlags($flags);
            }
            $pattern = '@\d+@';
            $ap = 0;
            $dp = 0;
            $sp = 0;
            $suc = preg_match($pattern, $secondCells->item(2 + 6)->textContent, $matches);
            if ($suc) {
                $ap = $matches[0];
            }
            else {
                $card->addError("Could not find AP");
            }

            $suc = preg_match($pattern, $secondCells->item(3 + 6)->textContent, $matches);
            if ($suc) {
                $dp = $matches[0];
            }
            else {
                $card->addError("Could not find DP");
            }

            $suc = preg_match($pattern, $secondCells->item(4 + 6)->textContent, $matches);
            if ($suc) {
                $sp = $matches[0];
            }
            else {
                $card->addError("Could not find SP");
            }

            $gender = str_replace('性別　', '', $secondCells->item(5 + 6)->textContent);
            $card->setGenderByText($gender);
            $card->setStat(Char::STAT_AP, $ap);
            $card->setStat(Char::STAT_DP, $dp);
            $card->setStat(Char::STAT_SP, $sp);
        }
        $rarity = trim(str_replace('ﾚｱﾘﾃｨ　', '', $secondCells->item(6 + 6)->textContent));
        $card->rarity = $rarity;

        $toMarkupOptions = array (
            'card' => $card
        );

        if ($isChar) {
            $thirdRows = $tableArray[2]->getElementsByTagName('tr');

            /**
             * These are the orders in abilities should be listed for a character.
             * Something is wrong if the order appears to be messed up.
             **/
            $typeConversion = 0;
            $typeBasicAbility = 1;
            $typeSpecialAbility = 2;

            $nextIsSpecialAbilityText = false;

            foreach ($thirdRows as $abilityRow) {
                $tds = $abilityRow->getElementsByTagName('td');
                $td1 = $tds->item(0);
                $td1Html = $this->getInnerHtml($td1);
                if (!$nextIsSpecialAbilityText) {
                    $td2 = $tds->item(1);
                    $td2Html = $this->getInnerHtml($td2);
                }
                /**
                 * If on last row we marked the next row being the ability text, then this is the ability text :)
                 */
                if ($nextIsSpecialAbilityText) {
                    $nextIsSpecialAbilityText = false;

                    $specialAbilityText = trim($td1Html);
                    $pattern = '@(?:<br>)*(※.*)$@i';
                    $hasComments = preg_match($pattern, $specialAbilityText, $matches);
                    if ($hasComments) {
                        $comments = $matches[1];
                        $comments = $this->toLycdbMarkup($comments, $toMarkupOptions);
                        $card->setComment(Card::LANG_JP, $comments);
                        $specialAbilityText = preg_replace($pattern, '', $specialAbilityText);
                    }
                    $specialAbilityText = $this->toLycdbMarkup($specialAbilityText, $toMarkupOptions);
                    $card->setMainAbilityText($specialAbilityText);

                    continue;
                }
                if (2 == $td1->getAttribute('rowspan')) {

                    $card->setSpecialAbilityName($td1->textContent);
                    $japaneseCostArray = $this->countElementsByDomElement($td2);
                    $costText = trim(strip_tags($td2->textContent));
                    $cost = new Cost;
                    $costText = $this->toLycdbMarkup($costText, $toMarkupOptions);
                    $cost->text = $costText;
                    $cost->fillByLyceeArray($japaneseCostArray);
                    $card->setSpecialAbilityCost($cost);

                    $nextIsSpecialAbilityText = true;

                    continue;
                }
                else {
                    $this->addConversionOrAbilityToCard($card, $td1Html, $td2Html);
                }
            }
        }
        else {
            $thirdCells = $tableArray[2]->getElementsByTagName('td');
            $text = $this->toLycdbMarkup($this->getInnerHtml($thirdCells->item(2)), $toMarkupOptions);
            $card->setMainAbilityText($text);
        }

        $fourthCells = $tableArray[3]->getElementsByTagName('td');

        $setName = trim($fourthCells->item(3)->textContent);
        $card->setName = $setName;
        $isErrata = false !== strpos($this->getInnerHtml($fourthCells->item(5)), 'errata_icon');
        $card->isErrata = $isErrata;

        return $card;
    }

    public function toLycdbMarkup($html, $options = array ()) {
        $pattern = '@<img [^>]*alt="([^"]*)"[^>]*>@';
        $html = preg_replace_callback($pattern, array ($this, 'imageReplaceCallback'), $html);
        $html = preg_replace('@<span class="red">([^<]*?)</span>@', '[target]\1[/target]', $html);
        $html = preg_replace('@<span class="blue_c">([^<]*?)</span>@', '[color=blue]\1[/color]', $html);
        $html = str_replace('<br>', "\n", $html);
        if (false !== strpos($html, '<')) {
            $msg = sprintf("There still is HTML in the marked up result: %s", $html);
            if (isset($options['card'])) {
                $options['card']->addError($msg);
            }
            else {
                trigger_error($msg);
            }
        }
        return $html;
    }

    public function imageReplaceCallback($matches) {
        $alt = $matches[1];
        $elementMap = Lycee::getJapaneseElementMap();
        if ('t' == $alt || 'T' == $alt) {
            return '[tap]';
        }
        else if ('0' === $alt) {
            return '[0]';
        }
        else if (isset($elementMap[$alt])) {
            switch ($elementMap[$alt]) {
            case Lycee::SNOW:
                return '[snow]';
            case Lycee::MOON:
                return '[moon]';
            case Lycee::FLOWER:
                return '[flower]';
            case Lycee::LIGHTNING:
                return '[lightning]';
            case Lycee::SUN:
                return '[sun]';
            case Lycee::STAR:
                return '[star]';
            }
        }
        else {
            trigger_error("Couldn't convert to lycdb markup: `$alt`");
            return "[?]";
        }
    }

    /**
     * addConversionOrAbilityToCard 
     * 
     * @param mixed $card       Character object
     * @param mixed $td1Html    Contining the name of the ability or conversion
     * @param mixed $td2Html    Containing the cost
     * @access public
     * @return void
     */
    public function addConversionOrAbilityToCard(Char $card, $td1Html, $td2Html) {
        $basicAbilityMap = $card->getJapaneseBasicAbilityMap();
        $openingBracketTypes = "［\[";
        if ('コンバージョン' == $td1Html || 'コンバ−ジョン' == $td1Html) {
            $card->conversion = $td2Html;
        }
        else if (isset($basicAbilityMap[$td1Html])) {
            $this->addBasicAbilityToCard($card, $td1Html, $td2Html);
        }
        else if (preg_match("@\].*[$openingBracketTypes]@u", $td1Html)) {
            // official lycee website bug remedy
            $split = preg_split("@\][^$openingBracketTypes]*[$openingBracketTypes]@u", $td1Html);
            $count = count($split);
            $split[$count - 1] .= ":" . $td2Html; // haxx
            foreach ($split as $content) {
                $abilityAndCost = preg_split('@:|：@u', $content);
                if (!isset($abilityAndCost[1])) {
                    $abilityAndCost[1] = '';
                }
                $td1Html = $abilityAndCost[0];
                $td2Html = $abilityAndCost[1];
                $this->addConversionOrAbilityToCard($card, $td1Html, $td2Html);
            }
        }
        else {
            $msg = "Couldn't map to a registered basic ability: `$td1Html`";
            $card->addError($msg);
        }
    }

    public function getInnerHtml(\DomElement $element) {
        $innerHTML = ""; 
        $children = $element->childNodes; 
        $doc = $element->ownerDocument;
        foreach ($children as $child) 
        { 
            $innerHTML .= $doc->saveHTML($child);
        } 
        return $innerHTML; 
    }

    public function addBasicAbilityToCard($card, $japaneseBasicAbility, $costHtml) {
        $toMarkupOptions = array (
            'card' => $card
        );
        $basicAbilityMap = $card->getJapaneseBasicAbilityMap();
        $basicAbilityEnumVal = $basicAbilityMap[$japaneseBasicAbility];
        if ($card->basicAbilityHasCost($basicAbilityEnumVal)) {
            $cost = new Cost;
            if (Char::BOOST == $basicAbilityEnumVal) {
                $costText = $costHtml;
            }
            else {
                $japaneseCostArray = $this->countElementsByHtml($costHtml);
                $cost->fillByLyceeArray($japaneseCostArray);
                $costText = trim(strip_tags($costHtml));
            }
            $costText = $this->toLycdbMarkup($costText, $toMarkupOptions);
            $cost->setText($costText);
        }
        else {
            // no cost
            $cost = false;
        }
        $card->setBasicAbility($basicAbilityEnumVal, true, $cost);
    }

    /**
     * Counts the amount of elements in a dom element by its images.
     * The japanese element names are used as the keys.
     * Also, 0 means free and T means tap.
     * 
     * @param mixed $html 
     * @access public
     * @return void
     */
    public function countElementsByDomElement(\DomElement $el) {
        $elementArr = array ();
        $elementImgEls = $el->getElementsByTagName('img');
        foreach ($elementImgEls as $el) {
            $alt = $el->getAttribute('alt');
            if (!isset($elementArr[$alt])) {
                $elementArr[$alt] = 1;
            }
            else {
                $elementArr[$alt]++;
            }
        }
        return $elementArr;
    }

    /**
     * Counts the amount of elements in a partial html by its images
     * 
     * @param mixed $html 
     * @access public
     * @return void
     */
    public function countElementsByHtml($html) {
        $elementArr = array ();
        $success = preg_match_all('@<img[^>]*alt="([^"]*)"[^>]*>@', $html, $matches);
        foreach ($matches[1] as $alt) {
            if (!isset($elementArr[$alt])) {
                $elementArr[$alt] = 1;
            }
            else {
                $elementArr[$alt]++;
            }
        }
        return $elementArr;

    }

    public function getSets() {
        $key = 'indexHtml';
        $indexHtml = $this->getIndexHtmlWithSetsOpen();
        $domQuery = new \Zend\Dom\Query();
        $domQuery->setDocumentHtml($indexHtml, 'utf-8');
        $setEls = $domQuery->execute('#card_list_main div.m_14a div.m_14b_y div.m_14e a');
        $sets = array ();
        foreach ($setEls as $el) {
            $set['page'] = str_replace("\r", '', $el->getAttribute('href'));
            $text = trim($el->textContent);
            $pattern = '@^(.*)（(\d+)）$@'; // note the japanese parentheses characters
            preg_match($pattern, $text, $matches);
            $set['name'] = $matches[1];
            $set['count'] = $matches[2];
            $parsedUrl = parse_url($set['page']);
            parse_str($parsedUrl['query'], $qs);
            $set['path'] = $parsedUrl['path'];
            $set['qs'] = $qs;
            $set['listsCards'] = false;
            $setExtId = null;
            foreach ($qs as $key => $val) {
                if (preg_match('@^S_L_(\d+)$@', $key, $matches)) {
                    $set['extId'] = $matches[1];
                }
                if (preg_match('@^S_L_number@', $key, $matches)) {
                    $set['listsCards'] = true;
                }
            }
            $sets[] = $set;
        }
        return $sets;
    }

    /**
     * Requests the index page. This is where we should find the card list.
     * Should be cached per day.
     * 
     * @access public
     * @return void
     */
    public function getIndexHtmlWithSetsOpen() {
        $page = 'index.cgi';
        $params = array (
            'S_L' => 1
        );
        $options = array (
            'alternate_cache' => 3
        );
        $indexHtml = $this->request($page, $params, $options);
        return $indexHtml;
    }

    public function request($url, $params = array (), $options = array ()) {
        $fullUrl = "$this->baseUrl/$url";
        return $this->requestFullUrl($fullUrl, $params, $options);
    }

    public function getFullUrl($url, $params = array ()) {
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    public function requestFullUrl($url, $params = array (), $options = array ()) {
        $cache = $this->cache;

        $useCache = !isset($options['use_cache']) || empty($options['use_cache']); // use cache by default.
        if (!$useCache || !($result = $cache->getCachedResult($url, $params, $options))) {
            $args = (array) $params;
            $result = $this->_requestFullUrl($url, $args, $options);
            if ($result) {
                $cache->cacheResult($result, $url, $params, $options);
            }
        }
        $convertToUtf8 = $this->convertToUtf8;
        if (isset($options['convertToUtf8'])) {
            $convertToUtf8 = $options['convertToUtf8'];
        }
        if ($convertToUtf8) {
            $this->convertHtmlDocToUtf8($result);
        }
        return $result;
        
    }

    public function convertHtmlDocToUtf8($result) {
        $result = mb_convert_encoding($result, 'utf-8', array ('EUC_JP'));
        $result = str_replace('charset=EUC-JP', 'charset=UTF-8', $result);
        return $result;
    }

    protected function _requestFullUrl($url, $params = array (), $options = array ()) {
        $url = $this->getFullUrl($url, $params);
		$result = file_get_contents($url);
		return $result;
    }
}
