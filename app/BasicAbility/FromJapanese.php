<?php

namespace Lycee\BasicAbility;

/**
 * Lycee\BasicAbility/FromJapanese
 */
class FromJapanese
{

    /**
     * @var array
     */
    private $basicAbilities;
    /**
     * @var array
     */
    private $jpEnMap;
    /**
     * @var array
     */
    private $enToIndexMap;

    function __construct(array $basicAbilities, array $jpEnMap)
    {
        $this->basicAbilities = $basicAbilities;
        $this->jpEnMap = $jpEnMap;
    }

    /**
     * Returns the basic ability index by the Japanese name
     *
     * @param string $jp
     * @return int
     */
    public function toEnIndex($jp)
    {
        $en = $this->toEn($jp);

        return $this->getEnToIndexMap()[$en];
    }

    /**
     * Returns the basic ability English name by the Japanese name
     *
     * @param $jp
     * @return string
     */
    public function toEn($jp)
    {
        $enToIndexMap = $this->getEnToIndexMap();

        $en = $this->jpEnMap[$jp];

        return $en;
    }

    /**
     * @return array
     */
    private function getEnToIndexMap()
    {
        if (!$this->enToIndexMap) {
            $this->enToIndexMap = array_flip($this->basicAbilities);
        }

        return $this->enToIndexMap;
    }
}