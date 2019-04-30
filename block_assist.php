<?php
/**
 * Работа с IB и HL блоками битрикса
 * @author umaxk <umaxk@tutanota.com>
 * Для работы с помощником подключить файл или добавьте его в автолоадер.
 * use block_assist;
 */
CModule::IncludeModule('main');
CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

trait block_assist {
    /**
     * Получение данных из инфоблока
     * @param int $ib  Номер инфоблока
     * @param array $fields поля инфоблока
     * @param array $param доп параметры
     * @param array $sort параметры параметры сортировки
     * @return array данные по запросу
     */
    function getIB ($ib, $fields, $param = [], $sort = [], $active='Y') {
        $data = [];
        $standart_param = [
            'IBLOCK_ID' => (int)$ib,
            'ACTIVE'    => (string)$active
        ];

        if (count($param) > 0) {
            foreach ($param as $key => $val) $standart_param[$key] = $val;
        }

        $res = CIBlockElement::GetList(
            (array)$sort,
            (array)$standart_param,
            false,
            false,
            (array)$fields
        );

        while($ob = $res->Fetch()) $data[] = $ob;

        return $data;
    }
    /**
     * Запись в инфоблок
     * @param int $ib Номер инфоблока
     * @param array $fields поля
     * @param string $name нейм записи
     * @return mixed номер записи или неудача
     */
    function addIB($ib, $fields, $name) {
        $el = new CIBlockElement;

        $arLoadProductArray = [
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"         => (int)$ib,
            "PROPERTY_VALUES"   => (array)$fields,
            "NAME"              => (string)$name,
            "ACTIVE"            => "Y",
            "XML_ID"            => " ",
        ];

        return $el->Add($arLoadProductArray);
    }
    /**
     * Обновление записи в инфоблоке
     * @param int $id номер записи
     * @param array $fields Поля
     * @param string $name название записи 
     * @param array $params_castom параметры задаваемые пользователем дополнительно
     * @return bool результат выполнения
     */
    function updateIB($id, $fields, $name = 'Элемент', $params_castom = []) {
        if((int)$id > 0){
            $el = new CIBlockElement;
            $param = [
                "IBLOCK_SECTION" => false,
                "NAME"           => (string)$name,
                "ACTIVE"         => "Y",
            ];
            $param = array_merge($param, (array)$params_castom);

            if( $el->Update((int)$id, (array)$param)){
                foreach ($fields as $code => $value) 
                    CIBlockElement::SetPropertyValueCode((int)$id, $code, $value);
                return true;
            }
        }

        return false;
    }
    /**
     * Удаление элемента инфоблока
     * @param int $id номер записи для удаления
     * @return bool результат выполнения
     */
    function removeIB($id) {
    	return CIBlockElement::Delete((int)$id);
    }
    /**
     * Получение данных из хайлоад блока
     * @param int $ib номер инфоблока
     * @param array $select фильтр 
     * @param array $filter фильтрация по полям
     * @return mixed данные из запроса
     */
    function getHL ($id, $filter = [], $select = ['*']) {
        $data = false;
        $hlblock = HL\HighloadBlockTable::getById((int)$id)->fetch(); 
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $rsData = $entity_data_class::getList([
           'select' => (array)$select,
           'filter' => (array)$filter
        ]);

        while($el = $rsData->fetch()) $data[] = $el;

        return $data;
    }
    /**
     * Запись в HL блок
     * @param int$ib Номер HL блока
     * @param array $fields поля
     * @return mixed номер записи или неудача
     */
    function addHL($ib, $fields) {
        $hlblock = HL\HighloadBlockTable::getById((int)$ib)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $result = $entity_data_class::add((array)$fields);

        return $result->getId();
    }
    /**
     * Обновляет запись в HL блоке
     * @param int $hl номер HL блока
     * @param int $id номер записи
     * @return mixed номер записи или неудача
     */
    function updateHL ($hl, $id, $fields) {
        if (count($fields) === 0 ) return false;
        $hlblock = HL\HighloadBlockTable::getById((int)$hl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $result = $entity_data_class::update((int)$id,(array)$fields);

        return $result->getId();
    }
    /**
     * Удаляет запись из HL блока
     * @param int $id номер записи
     * @param int $hl номер HL блока
     * @return bool результат работы
     */
    function removeHL($id, $hl) {
        $hlblock = HL\HighloadBlockTable::getById((int)$hl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        
        return $entity_data_class::delete((int)$id);
    }
}