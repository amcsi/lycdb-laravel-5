<?php
namespace Lycee\Importer\Lycee;

class Model {
    protected $_amysql;
    protected $_sm;

    public $setsTableName = 'lycdb_sets';
    public $cardsTableName = 'lycdb_cards';

    public $foundRows;
    public $pageCount;

    public function __construct() {
    }

    public function get($options = array ()) {
        $amysql = $this->amysql;

        $perPage = max(10, isset($options['perPage']) ? (int) $options['perPage'] : 50);
        $page = (int) (isset($options['page']) ? $options['page'] : 1);
        $offset = ($page - 1) * $perPage;

        $limit = " LIMIT $perPage ";
        $offset = " OFFSET $offset ";
        $wheres = array ();
        $binds = array ();

        if (isset($options['cid']) && strlen($options['cid'])) {
            $where = '0';
            $stripped = preg_replace('@[^A-Z0-9]@', '', strtoupper($options['cid']));
            $match = preg_match("@([A-Z]*)([0-9]*)@", $stripped, $matches);
            if ($match) {
                $hasLetters = strlen($matches[1]);
                $hasNumbers = strlen($matches[2]);
                if ($hasNumbers) {
                    $number = sprintf("%04d", $matches[2]);
                    if ($hasLetters) {
                        $where = "cid = :cid";
                        $binds['cid'] = sprintf('%s-%s', $matches[1], $number);
                    }
                    else {
                        $where = "cid LIKE '%$number'";
                    }
                }
                else if ($hasLetters) {
                    $letter = $matches[1];
                    $where = "cid LIKE '$letter%'";
                }
            }
            $wheres[] = $where;
        }

        if (!empty($options['name'])) {
            $expr = $amysql->expr(\AMysql_Expr::ESCAPE_LIKE, $options['name']);
            $wheres[] = 'name_jp LIKE :name OR name_en LIKE :name';
            $binds['name'] = $expr;
        }

        if (isset($options['type']) && -2 < ($type = $options['type'])) {
            /**
             * Not a character 
             */
            if (-1 == $type) {
                $wheres[] = "type != :type";
                $binds['type'] = Card::CHAR;
            }
            else {
                $wheres[] = "type = :type";
                $binds['type'] = $type;
            }
        }
        $elements = array ('snow', 'moon', 'flower', 'lightning', 'sun', 'star');
        if (!empty($options['cost'])) {
            $costType = isset($options['cost_type']) ? $options['cost_type'] : 0;
            // payable by
            if (1 == $costType) {
                $total = 0;
                $starWheres = array ();
                foreach ($elements as $key => $element) {
                    $costAmount = isset($options['cost'][$key]) ? $options['cost'][$key] : 0;
                    $total += $costAmount;
                    $starWheres[] = "cost_$element";
                    if ($key != Lycee::STAR) {
                        $wheres[] = "cost_$element <= :cost_$element";
                        $binds["cost_$element"] = $costAmount;
                    }
                    else {
                        $wheres[] = join (' + ', $starWheres) . ' <= :cost_total';
                        $binds['cost_total'] = $total;
                    }
                }
            }
            // exact cost
            else if (2 == $costType) {
                foreach ($elements as $key => $element) {
                    $costAmount = isset($options['cost'][$key]) ? $options['cost'][$key] : 0;
                    $wheres[] = "cost_$element = :cost_$element";
                    $binds["cost_$element"] = $costAmount;
                }
            }
        }

        if (isset($options['ex'], $options['ex_equality'])) {
            $eq = $options['ex_equality'];
            if (!$options['ex'] && 0 < $options['ex_equality']) {
            }
            else if (\Lycee\Config::MAX_EX_VALUE <= $options['ex'] && $options['ex_equality'] < 0) {
            }
            else {
                $op = '=';
                if ($eq < 0) {
                    $op = '<=';
                }
                else if (0 < $eq) {
                    $op = '>=';
                }
                $wheres[] = "ex $op :ex";
                $binds['ex'] = $options['ex'];
            }
        }

        if (isset($options['element'])) {
            $elementType = isset($options['element_type']) ? $options['element_type'] : 1;
            // has
            if (1 == $elementType) {
                foreach ($elements as $key => $element) {
                    if (Lycee::STAR == $key) {
                        continue;
                    }
                    if (!empty($options['element'][$key])) {
                        $wheres[] = "is_$element != 0";
                    }
                }
            }
            // is
            else if (2 == $elementType) {
                foreach ($elements as $key => $element) {
                    if (Lycee::STAR == $key) {
                        continue;
                    }
                    $op = !empty($options['element'][$key]) ? '!=' : '=';
                    $wheres[] = "is_$element $op 0";
                }
            }
        }

        if (!empty($options['text'])) {
            $wheres[] = 'ability_desc_jp LIKE :text OR ability_desc_en LIKE :name
                OR comments_jp LIKE :text OR comments_en LIKE :text
            ';
            $expr = $amysql->expr(\AMysql_Expr::ESCAPE_LIKE, $options['text']);
            $binds['text'] = $expr;
        }

        $where = $wheres ? ' WHERE ' . join (' AND ', $wheres) : '';

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $this->cardsTableName $where $limit $offset";
        $stmt = $this->amysql->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAllAssoc();
        $this->foundRows = $this->amysql->foundRows();
        $totalPages = (int) $this->foundRows / $perPage;
        $this->pageCount = $totalPages;

        if (!empty($options['template'])) {
            $positionImgs = array (
            );
            $elements = array ('snow', 'moon', 'flower', 'lightning', 'sun', 'star');
            foreach ($result as &$row) {
                $displayCost = '';
                $displayElements = '';
                foreach ($elements as $element) {
                    if ($row["cost_$element"]) {
                        $displayCost .= str_repeat("[$element]", $row["cost_$element"]);
                    }
                    if (!empty($row["is_$element"])) {
                        $displayElements .= "[$element]";
                    }
                }
                if (!$displayElements) {
                    $displayElements = '[star]';
                }
                $row['cost_markup'] = $displayCost;
                $row['elements_markup'] = $displayElements;
                $row['position_markup'] = '';
                if (Card::CHAR == $row['type']) {
                    $pm = '';
                    $pm .= Char::AL_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $pm .= Char::AC_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $pm .= Char::AR_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $pm .= "\n";
                    $pm .= Char::DL_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $pm .= Char::DC_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $pm .= Char::DR_FLAG & $row['position_flags'] ? '[on]' : '[off]';
                    $row['position_markup'] = $pm;
                }
                $tt = '';
                switch ($row['type']) {
                    case Card::CHAR:
                        $tt = 'character';
                        break;
                    case Card::AREA:
                        $tt = 'area';
                        break;
                    case Card::EVENT:
                        $tt = 'event';
                        break;
                    case Card::ITEM:
                        $tt = 'item';
                        break;
                    default:
                        $tt = 'unknown';
                        break;
                }
                $row['type_text'] = $tt;
                $row['default_image_external'] = str_replace('-', '_', strtolower($row['cid'])) . '_l.jpg';
                $row['sets_string'] = 'Coming soon...';
                $basicAbilitiesJp = explode("\n", $row['basic_abilities_jp']);
                $displayBasicAbilitiesJp = array ();
                foreach ($basicAbilitiesJp as $bab) {
                    $split = explode (' ', $bab, 2);
                    $babName = $split[0];
                    $displayBab = sprintf("<span class=\"basicAbility\">%s</span>", htmlspecialchars($babName));
                    if (isset($split[1])) {
                        $displayBab .= sprintf(" <span class=\"costText\">%s</span>", htmlspecialchars($split[1]));
                    }
                    $displayBasicAbilitiesJp[] = $displayBab;
                }
                $row['display_basic_abilities_jp_markup'] = $displayBasicAbilitiesJp;
            }
        }
        return $result;
    }

    public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceManager) {
        $this->_sm = $serviceManager;
    }

    public function getAMysql() {
        if (!$this->_amysql) {
            $this->_amysql = $this->_sm->get('amysql');
        }
        return $this->_amysql;
    }

    public function __get($key) {
        switch ($key) {
        case 'amysql':
            return $this->getAMysql();
        default:
            throw new Exception ("Bad property name: $key");
        }
    }
    
}
?>
