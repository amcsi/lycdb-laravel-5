<?php

namespace Lycee\Tool;

use Symfony\Component\Translation\TranslatorInterface;

class BasicAbility
{

    /**
     * @var TranslatorInterface
     */
    private $_;
    /**
     * @var array
     */
    private $basicAbilities;
    private $basicAbilitiesNormalizeMap;

    function __construct(TranslatorInterface $_, array $basicAbilities)
    {
        $this->_ = $_;
        $this->basicAbilities = $basicAbilities;
    }

    /**
     * @param string $basicAbilityString
     * @return string
     */
    public function normalize($basicAbilityString)
    {
        $map = $this->getBasicAbilitiesNormalizeMap();
        $stripped = $this->simplifyString($basicAbilityString);

        if (!isset($map[$stripped])) {
            throw new \InvalidArgumentException(
                "Basic ability not found: $basicAbilityString"
            );
        }

        $key = $map[$stripped];

        $ret = $this->basicAbilities[$key];

        return $ret;
    }

    /**
     * @param string $basicAbilityString
     * @return string
     */
    public function normalizeAndTranslate($basicAbilityString)
    {
        $normalized = $this->normalize($basicAbilityString);

        $ret = $this->_->trans($normalized);

        return $ret;
    }

    /**
     * @return array    Mapping lowercased strings without spaces to
     *                  basic ability keys.
     */
    private function getBasicAbilitiesNormalizeMap()
    {
        if (!$this->basicAbilitiesNormalizeMap) {
            $basicAbilities = $this->basicAbilities;
            $map = [];
            foreach ($basicAbilities as $key => $name) {
                // remove spaces and lowercase all
                $stripped = $this->simplifyString($name);
                $map[$stripped] = $key;
            }
            $this->basicAbilitiesNormalizeMap = $map;
        }

        return $this->basicAbilitiesNormalizeMap;
    }

    private function simplifyString($string)
    {
        // strip all special characters
        $stripped = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        $stripped = strtolower($stripped);

        return $stripped;
    }
}
