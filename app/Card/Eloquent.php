<?php

namespace Lycee\Card;


use Illuminate\Database\Eloquent\Model;
use Lycee\Config\Elements;
use Illuminate\Database\Eloquent\Builder;

class Eloquent extends Model {

    const TYPE_CHAR = 0;
    const TYPE_AREA = 1;
    const TYPE_ITEM = 2;
    const TYPE_EVENT = 3;

    protected $table = 'cards';
    /**
     * @var array
     */
    private $elements;

    /**
     * @return Elements
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Several methods require the Elements for being able to
     * make queries.
     *
     * @param Elements $elements
     */
    public function setElements(Elements $elements)
    {
        $this->elements = $elements;
    }

    public function getbyOptions($options = array ())
    {
        $elements = $this->getElements();
        $perPage = min(50, max(10, isset($options['perPage']) ? (int) $options['perPage'] : 50));
        $page = (int) (isset($options['page']) ? $options['page'] : 1);
        $offset = ($page - 1) * $perPage;
        $limit = " LIMIT $perPage ";
        $offset = " OFFSET $offset ";
        $wheres = array ();
        $binds = array ();

        /** @var Builder $builder */
        $builder = $this->newQuery();

        if (isset($options['cid']) && strlen($options['cid'])) {
            $this->queryWhereCid($builder, $options['cid']);
        }

        if (!empty($options['name'])) {
            $this->contains('name_jp', $options['name']);
        }

        if (isset($options['type']) && -2 < ($type = $options['type'])) {
            /**
             * Not a character
             */
            if (-1 == $type) {
                $this->where('type', '!=', self::TYPE_CHAR);
            }
            else {
                $this->where('type', '=', $type);
            }
        }
        if (!empty($options['cost'])) {
            $costType = isset($options['cost_type']) ? $options['cost_type'] : 0;
            // payable by
            if (1 == $costType) {
                $this->costsPayableBy($options['cost']);
            }
            // exact cost
            else if (2 == $costType) {
                $this->costsExactly($options['cost']);
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
                $builder->where('ex', $op, $options['ex']);
            }
        }

        $this->resolveElementTypeByOptions($builder, $options);

        $this->resolveText($builder, $options);

        //$builder->paginate($perPage);
        $builder->limit($perPage);

        $result = $builder->get();

        /*

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $this->cardsTableName $where $limit $offset";
        $stmt = $this->amysql->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAllAssoc();
        $this->foundRows = $this->amysql->foundRows();
        $totalPages = (int) $this->foundRows / $perPage;
        $this->pageCount = $totalPages;

        if (!empty($options['template'])) {
            $this->amendWithSetData($result);
            $positionImgs = array (
            );
            $elements = $this->elements;
            foreach ($result as &$row) {
                $setStringRows = array ();
                foreach ($row['setInfo'] as $val) {
                    $setStringRows[] = sprintf('%s (%s)', $val['set_name'], $val['rarity']);
                }
                $row['sets_string'] = join("\n", $setStringRows);
                if (2 < $count = count($setStringRows)) {
                    $row['sets_string_short'] = $setStringRows[0] . "\n" . '(' . ($count - 1) . " more ...)";
                }
                else {
                    $row['sets_string_short'] = $row['sets_string'];
                }
                $displayCost = '';
                $displayElements = '';
                foreach ($elements as $element) {
                    $elementKey = $element['key'];
                    if ($row["cost_$elementKey"]) {
                        $displayCost .= str_repeat("[$elementKey]", $row["cost_$elementKey"]);
                    }
                    if (!empty($row["is_$elementKey"])) {
                        $displayElements .= "[$elementKey]";
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
                $basicAbilitiesJp = $row['basic_abilities_jp'] ?
                    explode("\n", $row['basic_abilities_jp']) :
                    array ();
                $displayBasicAbilitiesJp = array ();
                foreach ($basicAbilitiesJp as $bab) {
                    $split = explode (' | ', $bab, 2);
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

        */

        return $result;
    }

    /**
     * @param Builder $builder
     * @param string $cid
     */
    private function queryWhereCid(Builder $builder, $cid)
    {
        // strip string of unneeded characters which in turn makes the string
        // safe for mysql queries.
        $stripped = preg_replace('@[^A-Z0-9]@', '', strtoupper($cid));
        $match = preg_match("@([A-Z]*)([0-9]*)@", $stripped, $matches);
        if ($match) {
            $hasLetters = strlen($matches[1]);
            $hasNumbers = strlen($matches[2]);
            if ($hasNumbers) {
                $number = sprintf("%04d", $matches[2]);
                if ($hasLetters) {
                    $val = sprintf('%s-%s', $matches[1], $number);
                    $builder->where('cid', '=', $val);
                }
                else {
                    $builder->where('cid', 'LIKE', "%$number");
                }
            }
            else if ($hasLetters) {
                $letter = $matches[1];
                $builder->where('cid', 'LIKE', "$letter%");
            }
        }
    }

    public function scopeContains(Builder $query, $column, $value)
    {
        $escapedValue = str_replace(['%', '_'], ['\%', '\_'], $value);
        $query->where($column, 'LIKE', '%' . $escapedValue . '%');
    }

    public function scopeOrContains(Builder $query, $column, $value)
    {
        $escapedValue = str_replace(['%', '_'], ['\%', '\_'], $value);
        $query->orWhere($column, 'LIKE', '%' . $escapedValue . '%');
    }

    public function scopeCostsExactly(Builder $query, array $costs)
    {
        foreach ($this->elements as $key => $element) {
            $costAmount = isset($costs[$key]) ? $costs[$key] : 0;
            $query->where("cost_$element", '=', $costAmount);
        }
    }

    public function scopeCostsPayableBy(Builder $query, array $costs)
    {
        $total = 0;
        $starWheres = [];
        foreach ($this->elements as $key => $element) {
            $costAmount = isset($costs[$key]) ? $costs[$key] : 0;
            $total += $costAmount;
            $starWheres[] = "cost_$element";
            if ($element !== 'star') {
                $query->where("cost_$element", '<=', $costAmount);
            }
        }
        if ($starWheres) {
            $columns = join (' + ', $starWheres);
            $query->where($columns, '<=', $total);
        }
    }

    private function getElementsFlipped()
    {
        if (!$this->elementsFlipped) {
            $this->elementsFlipped = array_flip($this->elements);
        }

        return $this->getElementsFlipped();
    }

    private function resolveElementTypeByOptions(Builder $builder, $options)
    {
        $elements = $this->elements;
        if (isset($options['element'])) {
            $elementType = isset($options['element_type']) ? $options['element_type'] : 1;
            // has
            if (1 == $elementType) {
                foreach ($elements as $enum => $element) {
                    $elementKey = $element['key'];
                    if ('star' === $elementKey) {
                        continue;
                    }
                    if (!empty($options['element'][$enum])) {
                        $builder->where("is_$elementKey", '!=', 0);
                    }
                }
            }
            // is
            else if (2 == $elementType) {
                foreach ($elements as $key => $element) {
                    if ('star' === $element) {
                        continue;
                    }
                    $op = !empty($options['element'][$key]) ? '!=' : '=';
                    $builder->where("is_$element", $op, 0);
                }
            }
        }
    }

    /**
     * @param Builder $builder
     * @param string $text
     */
    private function resolveText(Builder $builder, $options)
    {
        if (!empty($options['text'])) {
            $text = $options['text'];
            $builder->contains('ability_desc_jp', $text);
            $builder->orContains('ability_desc_en', $text);
            $builder->orContains('comments_jp', $text);
            $builder->orContains('comments_en', $text);
        }
    }

} 