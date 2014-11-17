<?php
namespace Lycee\Importer\Lycee;

class Area extends Card {

    public function toDbData() {
        $data = parent::toDbData();
        $data['type'] = self::AREA;
        return $data;
    }
}
?>
