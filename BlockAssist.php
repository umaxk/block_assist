<?php
/**
 * Working with IBlock and HLoad blocks Bitrix API
 * @author umaxk <umaxk@tutanota.com>
 * @copyright 2019 umaxk
 * To work with the assistant, attach a file or add it to the auto-loader.
 * use BlockAssist;
 */
CModule::IncludeModule('main');
CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

trait BlockAssist {
    /**
     * Receive data from the information block
     * @param int $ib Iblock number
     * @param string[] $fields field of the Iblock
     * @param string[] $param advanced setting
     * @param string[] $sort sort options
     * @param string $active Active entries 'Y'|'N'
     * @return string[] data on request
     */
    function getIB ($ib, $fields, $param = [], $sort = [], $active = 'Y') {
        $data = [];
        $standart_param = [
            'IBLOCK_ID' => (int)$ib,
            'ACTIVE'    => (string)$active
        ];

        if (count($param)>0) 
            foreach ($param as $key => $val) 
                $standart_param[$key] = $val;

        $list = CIBlockElement::GetList(
            (array)$sort,
            (array)$standart_param,
            false,
            false,
            (array)$fields
        );

        while($ob = $list->Fetch()) 
            $data[] = $ob;

        return $data;
    }

    /**
     * Entry in the Iblock
     * @param int $ib Iblock number
     * @param string[] $fields field of the Iblock
     * @param string $name title of the record
     * @param string $active Active entries 'Y'|'N'
     * @return int|bool number or failure
     */
    function addIB($ib, $fields, $name, $active = 'Y') {
        $el = new CIBlockElement;

        return $el->Add([
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID'         => (int)$ib,
            'PROPERTY_VALUES'   => (array)$fields,
            'NAME'              => (string)$name,
            'ACTIVE'            => (string)$active,
            'XML_ID'            => ' ', // if necessary, you can remove it
        ]);
    }
    /**
     * Update an entry in the Iblock
     * @param int $ib Iblock number
     * @param string[] $fields field of the Iblock
     * @param string $name title of the record
     * @param string $active Active entries 'Y'|'N'
     * @param string[] $paramsCastom advanced setting
     * @return bool execution result
     */
    function updateIB(int $id, $fields, string $name = 'Item', string $active = 'Y', $paramsCastom = []):bool {
        $el = new CIBlockElement;

        $param = array_merge(
            [
                "IBLOCK_SECTION" => false,
                "NAME"           => (string)$name,
                "ACTIVE"         =>  $active,
            ], 
            (array)$paramsCastom
        );

        if( $el->Update((int)$id, (array)$param)){
            foreach ($fields as $code => $value) 
                CIBlockElement::SetPropertyValueCode((int)$id, $code, $value);
            return true;
        }

        return false;
    }
    /**
     * Removing an element of the IBlock
     * @param int $id record number to delete
     * @return bool execution result
     */
    function removeIB($id) {
        return CIBlockElement::Delete((int)$id);
    }
    /**
     * Getting data from the HLblock
     * @param int $ib HLblock number
     * @param string[] $select filter
     * @param string[] $filter filter for fields
     * @return [] date
     */
    function getHL (int $id, array $filter = [], array $select = ['*']):array {
        $data = [];
        $hlblock = HL\HighloadBlockTable::getById((int)$id)->fetch(); 
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $rsData = $entity_data_class::getList([
           'select' => (array)$select,
           'filter' => (array)$filter
        ]);

        while($el = $rsData->fetch()) 
            $data[] = $el;

        return $data;
    }
    /**
     * The entry in the HLblock
     * @param int $ib HLblock number
     * @param string[] $fields fields for record
     * @return int|bool number or false
     */
    function addHL($ib, $fields) {
        $hlblock = HL\HighloadBlockTable::getById((int)$ib)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $result = $entity_data_class::add((array)$fields);

        return $result->getId();
    }
    /**
     * Updates an entry in the HLblock
     * @param int $hl HLblock number
     * @param int $id number entry
     * @param string[] $fields fields for update
     * @return int|bool number or false
     */
    function updateHL ($hl, $id, $fields) {
        if (count($fields) === 0 ) return false;

        $HLblock = HL\HighloadBlockTable::getById((int)$hl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($HLblock);
        $entityDataClass = $entity->getDataClass();
        $result = $entityDataClass::update((int)$id,(array)$fields);

        return $result->getId();
    }
    /**
     * Removes an entry from the HLblock
     * @param int $id number entry
     * @param int $hl HLblock number
     * @return bool execution result
     */
    function removeHL($id, $hl) {
        $HLblock = HL\HighloadBlockTable::getById((int)$hl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($HLblock);
        $entityDataClass = $entity->getDataClass();
        
        return $entityDataClass::delete((int)$id);
    }
}


