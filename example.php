<?php 
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 
include("BitrixMigration.php");
$arResult = Array (
    "0" => Array (
        "IBLOCK_TYPE_ID" => "requests",
        "LID" => "ru",
        "NAME" => "Заявки",
        "SECTION_NAME" => "",
        "ELEMENT_NAME" => "",
        "ID" => "requests",
        "SECTIONS" => "Y",
        "EDIT_FILE_BEFORE" => "",
        "EDIT_FILE_AFTER" => "",
        "IN_RSS" => "N",
        "SORT" => "500",
        "IBLOCKS" => Array (
            "0" => Array (
                "ID" => "3",
                "IBLOCK_TYPE_ID" => "requests",
                "CODE" => "students_requests",
                "NAME" => "Заявки студентов",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "LIST_PAGE_URL" => "#SITE_DIR#/requests/index.php?ID=#IBLOCK_ID#",
                "DETAIL_PAGE_URL" => "#SITE_DIR#/request.php?ID=#ELEMENT_ID#",
                "SECTION_PAGE_URL" => "#SITE_DIR#/requests/list.php?SECTION_ID=#SECTION_ID#",
                "INDEX_ELEMENT" => "Y",
                "INDEX_SECTION" => "Y",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "SECTION_CHOOSER" => "L",
                "LIST_MODE" => "",
                "RIGHTS_MODE" => "S",
                "VERSION" => "2",
                "EDIT_FILE_BEFORE" => "",
                "EDIT_FILE_AFTER" => "",
                "SECTIONS_NAME" => "Разделы",
                "SECTION_NAME" => "Раздел",
                "ELEMENTS_NAME" => "Элементы",
                "ELEMENT_NAME" => "Элемент",
                "SITE_ID" => Array (
                    "0" => "s1",
                ),
                "PROPS" => Array (
                    "TYPE" => Array (
                        "NAME" => "Укажите вид справки",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "TYPE",
                        "PROPERTY_TYPE" => "L",
                        "MULTIPLE" => "N",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "N",
                        "USER_TYPE" => "",
                        "VALUES" => Array (
                            "0" => Array (
                                "VALUE" => "Заявление на допуск к экзамену",
                                "DEF" => "N",
                                "SORT" => "100",
                                "EXTERNAL_ID" => "ADMISSION_TO_EXAMINATION",
                            ),
                            "1" => Array (
                                "VALUE" => "Справка (выписка из приказа)",
                                "DEF" => "N",
                                "SORT" => "200",
                                "EXTERNAL_ID" => "OUT_OF_ORDER",
                            ),
                            "2" => Array (
                                "VALUE" => "Справка (о подтверждении факта обучения))",
                                "DEF" => "N",
                                "SORT" => "300",
                                "EXTERNAL_ID" => "PROOF_OF_LEARNING",
                            ),
                        ),
                    ),
                    "STATUS" => Array (
                        "NAME" => "Статус",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "STATUS",
                        "PROPERTY_TYPE" => "L",
                        "MULTIPLE" => "N",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "N",
                        "USER_TYPE" => "",
                        "VALUES" => Array (
                            "0" => Array (
                                "VALUE" => "Новый запрос",
                                "DEF" => "N",
                                "SORT" => "100",
                                "EXTERNAL_ID" => "NEW_REQUEST",
                            ),
                            "1" => Array (
                                "VALUE" => "Зарегистрирован",
                                "DEF" => "N",
                                "SORT" => "200",
                                "EXTERNAL_ID" => "REGISTERED",
                            ),
                            "2" => Array (
                                "VALUE" => "В обработке",
                                "DEF" => "N",
                                "SORT" => "300",
                                "EXTERNAL_ID" => "DEALING",
                            ),
                            "3" => Array (
                                "VALUE" => "Отказано (недостатчно данных)",
                                "DEF" => "N",
                                "SORT" => "400",
                                "EXTERNAL_ID" => "DENIED_NOT_ENOUGH_DATA",
                            ),
                            "4" => Array (
                                "VALUE" => "Обработано",
                                "DEF" => "N",
                                "SORT" => "500",
                                "EXTERNAL_ID" => "PROCESSED",
                            ),
                        ),
                    ),
                    "USER_ID" => Array (
                        "NAME" => "Привязка к пользователю",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "USER_ID",
                        "PROPERTY_TYPE" => "S",
                        "MULTIPLE" => "N",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "N",
                        "USER_TYPE" => "UserID",
                    ),
                    "AGREEMENT" => Array (
                        "NAME" => "Обработка персональных данных",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "AGREEMENT",
                        "PROPERTY_TYPE" => "L",
                        "MULTIPLE" => "Y",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "Y",
                        "USER_TYPE" => "",
                        "VALUES" => Array (
                            "0" => Array (
                                "VALUE" => "Даю согласие на передачу и обработку персональных данных",
                                "DEF" => "N",
                                "SORT" => "500",
                                "EXTERNAL_ID" => "YES_I_DO",
                            ),
                        ),
                    ),
                    "OTHER_FIELDS_VALUES" => Array (
                        "NAME" => "Значение дополнительных полей",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "OTHER_FIELDS_VALUES",
                        "PROPERTY_TYPE" => "S",
                        "MULTIPLE" => "N",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "N",
                        "USER_TYPE" => "",
                    ),
                    "WS_CODE" => Array (
                        "NAME" => "Код заявки",
                        "ACTIVE" => "Y",
                        "SORT" => "500",
                        "CODE" => "WS_CODE",
                        "PROPERTY_TYPE" => "S",
                        "MULTIPLE" => "N",
                        "FILE_TYPE" => "",
                        "MULTIPLE_CNT" => "5",
                        "LINK_IBLOCK_ID" => "0",
                        "IS_REQUIRED" => "N",
                        "USER_TYPE" => "",
                    ),
                ),
            ),
        ),
    ),
    "1" => Array (
        "IBLOCK_TYPE_ID" => "services",
        "LID" => "ru",
        "NAME" => "Сервисы",
        "SECTION_NAME" => "",
        "ELEMENT_NAME" => "",
        "ID" => "services",
        "SECTIONS" => "Y",
        "EDIT_FILE_BEFORE" => "",
        "EDIT_FILE_AFTER" => "",
        "IN_RSS" => "N",
        "SORT" => "500",
        "IBLOCKS" => Array (
        ),
    ),
);
$bm = new BitrixMigration();
$bm->uploadArray($arResult);
?>