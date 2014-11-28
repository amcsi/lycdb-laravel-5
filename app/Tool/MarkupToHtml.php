<?php

namespace Lycee\Tool;

class MarkupToHtml
{

    /**
     * @var array
     */
    private $elements;
    private $imgBase;
    /**
     * @var BasicAbility
     */
    private $basicAbility;

    function __construct(array $elements, $imgBase, BasicAbility $basicAbility)
    {
        $this->elements = $elements;
        $this->imgBase = $imgBase;
        $this->basicAbility = $basicAbility;
    }

    public function convert($string)
    {
        // first let's escape any HTML in the source for safety
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');

        $imgBase = $this->imgBase;
        foreach ($this->elements as $element) {
            $elementName = $element['name'];
            $string = str_replace(
                "[$elementName]",
                $this->getImgTag("$imgBase/$elementName.gif", "[$elementName]"),
                $string
            );
        }
        $string = str_replace('[0]', $this->getImgTag("$imgBase/0.gif", '[0]'), $string);
        $string = str_replace('[tap]', $this->getImgTag("$imgBase/tap.gif", '[tap]'), $string);
        $string = str_replace('[on]', $this->getImgTag("$imgBase/spot-on.gif", '[on]'), $string);
        $string = str_replace('[off]', $this->getImgTag("$imgBase/spot-off.gif", '[off]'), $string);
        $string = preg_replace("@\[target\](.*?)\[/target\]@", '<span class="target">\1</span>', $string);
        $string = preg_replace("@\[cost\](.*?)\[/cost\]@", '<span class="cost">\1</span>', $string);
        $string = preg_replace("@\[color=(\w+)\](.*?)\[/color\]@", '<span style="color: \1;">\2</span>', $string);
        $string = preg_replace_callback("@%b\(([^\)]*)\)@", [$this, 'basicAbilityCallback'], $string);
        $string = nl2br($string, false);
        return $string;
    }

    private function getImgTag($src, $alt)
    {
        return "<img src=\"$src\" alt=\"$alt\">";
    }

    /**
     * @param array $matches
     * @return string
     */
    private function basicAbilityCallback(array $matches)
    {
        return $this->basicAbility->normalizeAndTranslate($matches[1]);
    }

}
