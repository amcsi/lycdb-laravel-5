<?php
namespace Lycee\Importer\Lycee;

class Event extends Card {

    public function toDbData() {
        $data = parent::toDbData();
        $data['type'] = self::EVENT;
        return $data;
    }
}
?>
