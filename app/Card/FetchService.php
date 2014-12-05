<?php

namespace Lycee\Card;

use Illuminate\Http\Request;
use Lycee\Config\Elements;

/**
 * Lycee\Card/FetchService
 */
class FetchService
{
    /**
     * @var Eloquent
     */
    private $eloquent;
    /**
     * @var Elements
     */
    private $elements;

    /**
     * @param Eloquent $eloquent
     * @param Elements $elements
     */
    public function __construct(Eloquent $eloquent, Elements $elements)
    {
        $eloquent->setElements($elements);
        $this->eloquent = $eloquent;
        $this->elements = $elements;
    }

    /**
     * @param array $requestVars
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getByRequest(array $requestVars)
    {
        $mergeKeys = ['cid', 'name', 'cost_type', 'element_type', 'ex', 'text', 'page'];
        $options = array_intersect_key(array_flip($mergeKeys), $requestVars);

        $map = [
            'card_type' => 'type',
            'ex_operator' => 'ex_equality',
        ];

        foreach ($map as $src => $dst) {
            if (array_key_exists($src, $requestVars)) {
                $options[$dst] = $requestVars[$src];
            }
        }

        $page = isset($data['page']) ? max(1, $data['page']) : 1;

        $elements = $this->elements;
        $options['cost'] = array ();
        $options['element'] = array ();
        foreach ($elements as $enum => $element) {
            $key = $element['key'];
            if (array_key_exists("cost_$key", $requestVars)) {
                $options['cost'][$enum] = $requestVars["cost_$key"];
            }
            if ('star' !== $key && array_key_exists("element_$key", $requestVars)) {
                $options['element'][$key] = (bool) $requestVars["element_$key"];
            }
        }
        $results = $this->eloquent->getByOptions($options);

        return $results;
    }
} 