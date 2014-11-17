<?php
namespace Lycee\Importer\Lycee;

class Item extends Card {

    public function toDbData() {
        $data = parent::toDbData();
        $data['type'] = self::ITEM;
        return $data;
    }
}
?>
