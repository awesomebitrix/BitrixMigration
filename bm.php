<?php
/**
 * Class BitrixMigration
 * Класс позволяет экспортировать структуру инфоблоков в PHP массив,
 * который можно использовать для создания новых инфоблоков.
 * @author Verbovenko Fyodor <4fyodor@gmail.com>
 * @version 1.0
 */

class BitrixMigration
{
    public $sStoreFilesDir = '/bm_files/';
    public $bImportElements = true;
    protected $sFullFilesPath;
    protected $arResult = array();
    protected $arrayName = NULL;


    protected $filter = array();
    protected $setFilter = false;

    protected $scriptName = "";

    protected $arRequiredProps =  array("CODE","NAME","ACTIVE","SORT","IS_REQUIRED","MULTIPLE","MULTIPLE_CNT","PROPERTY_TYPE","LINK_IBLOCK_ID", "USER_TYPE","FILE_TYPE");
    protected $arRequiredEnumFields =  array("VALUE","DEF","SORT","EXTERNAL_ID");

    protected $arIblockKeys = array(
        "ID","SITE_ID","CODE","NAME","ACTIVE","SORT","LIST_PAGE_URL","DETAIL_PAGE_URL","SECTION_PAGE_URL","INDEX_ELEMENT",
        "INDEX_SECTION","WORKFLOW","BIZPROC","SECTION_CHOOSER","LIST_MODE","RIGHTS_MODE","VERSION","EDIT_FILE_BEFORE",
        "EDIT_FILE_AFTER","SECTIONS_NAME","SECTION_NAME","ELEMENTS_NAME","ELEMENT_NAME","PROPS"
    );

    protected $arIblockTypeKeys = array( //"IBLOCK_TYPE_ID",
        "LID","NAME","SECTION_NAME","ELEMENT_NAME","ID","SECTIONS","EDIT_FILE_BEFORE","EDIT_FILE_AFTER","IN_RSS","SORT","IBLOCKS"
    );

    protected $arSectionKeys = array( //"IBLOCK_TYPE_ID",
        "TIMESTAMP_X","DATE_CREATE","IBLOCK_SECTION_ID","ACTIVE","GLOBAL_ACTIVE","SORT","NAME","PICTURE","LEFT_MARGIN","RIGHT_MARGIN","DEPTH_LEVEL","DESCRIPTION","DESCRIPTION_TYPE","SEARCHABLE_CONTENT","CODE","XML_ID","DETAIL_PICTURE","EXTERNAL_ID"
    );
    protected $arElementKeys = array(
        "PROPERTIES","CODE","EXTERNAL_ID","NAME","SECTION_ID","ACTIVE","DATE_ACTIVE_FROM","
                        DATE_ACTIVE_TO","SORT","PREVIEW_PICTURE","PREVIEW_TEXT","PREVIEW_TEXT_TYPE","DETAIL_PICTURE","DETAIL_TEXT",
        "DETAIL_TEXT_TYPE","DATE_CREATE","TIMESTAMP_X","TAGS"
    );


    protected $iblockIdByCode = array();

    public function __construct($scriptName)
    {
        CModule::IncludeModule("iblock");
        $this->scriptName = basename( $scriptName );
        $this->sFullFilesPath = getcwd ().$this->sStoreFilesDir;
    }


    public function start()
    {
        $this->init();
        if ($_REQUEST['ajax_call']=="Y") $this->ajax();
        if ($_REQUEST['save']=="Y") $this->saveFile();
        $this->bImportElements = false;
        $this->getIbStructure();

        if (empty($this->arResult) ) die('Инфоблоки не найдены.');
        ?>
        <html>
        <head>
            <title>Импорт/Экспорт Инфоблоков 1c-Bitrix</title>
            <style>
                .disabled {color: #ddd;}
                label, input[type=button] {cursor: pointer;}
                li{list-style: none}

            </style>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
            <script>
                var currentScriptName = '<?php echo $this->scriptName?>';
                $(document).ready(function () {


                    var resultMessage = function(text,color){
                        $("#save-results").remove();
                        var resDiv = $("<div>").attr("id","save-results").html(text).css({'color':color,'font-weight':'bold'});
                        $(".iblocks-lists").after(resDiv);
                    }

                    $("#iblocks-list").find("input").change(function () {
                        sendRequest();

                    });

                    $("input[name=save]").click(function () {
                        if ( $("#result_textarea").text().length<=0 ) {
                            resultMessage('Выберите один из инфоблоков.','red');
                            return false;
                        }
                        var context$ = $(this);
                        if (!$(this).hasClass("disabled")) {
                            $(this).addClass("disabled");
                            $("#save-results").remove();
                            var sFileName = $("input[name=file-name]").val();
                            var sScript = $("#result_textarea").val();

                            $(".iblocks-lists").after("<div id='loader'>Загружается...</div>")

                            $.post( currentScriptName,{save:"Y",file:sFileName, script: sScript}, function( data ) {
                                 //console.log(data);

                                 if (data=="True") {
                                     sFileName += '.php';
                                     var fileLink = $("<a>").attr("href",sFileName).text("Запустить его.");
                                     var resText = $("<span>").text("Файл сохранен.").append(fileLink);
                                     resultMessage(resText,'green');

                                 }else {
                                     resultMessage("Возникли проблемы при сохранение файла. Возможные причины: <br />" +
                                         "- Имя файла может состоять из букв латинского алфавита и цифр, <br />" +
                                         "- Файл с таким именем уже существует, <br />" +
                                         "- Возможно у скрипта не хватает прав на запись в эту папку.",'red');
                                 }

                                 $("#loader").remove();
                                 context$.removeClass("disabled");
                             });
                        }

                        return false;
                    });

                    $("#unselect_all").click(function () {
                        $(".iblocks-lists").find("input[type=checkbox]").prop('checked',false);
                        setTimeout(function(){
                            sendRequest();
                        },100)

                    });

                    $("#select_all").click(function () {
                        $(".iblocks-lists").find("input[type=checkbox]").prop('checked', true);
                        setTimeout(function(){
                            sendRequest();
                        },100)
                    });


                    var sendRequest = function () {
                        $("#do-something").text("");
                        $("#result_textarea").text("");
                        $(".iblocks-lists").after("<div id='loader'>Загружается...</div>")
                        var formInputs = $("#iblocks-list").serialize();
                        formInputs +="&ajax_call=Y";
                        $.post(currentScriptName, formInputs, function (data) {
                            $("#result_textarea").text(data);
                            $("#loader").remove();
                        });
                    }

                });

            </script>
        </head>
        <body>
        <input type="button" id="unselect_all" name="unselect_all" value="Снять для всех">
        <input type="button" id="select_all" name="select_all" value="Установить для всех">
        <div class="iblocks-lists">
            <form name="iblocks-list" id="iblocks-list">
                <ul>
                    <? foreach ($this->arResult as $arIblockType): ?>
                        <? foreach ($arIblockType['IBLOCKS'] as $arIblock): ?>
                            <li>
                                <input class="iblocks" id="iblock<?= $arIblock['ID'] ?>" type="checkbox" name="iblocks[]"
                                       value="<?= $arIblock['ID'] ?>">
                                <label for="iblock<?= $arIblock['ID'] ?>">
                                    <?= $arIblock['NAME'] ?>
                                </label>
                            </li>
                        <? endforeach; ?>
                    <? endforeach; ?>
                </ul>

                <hr />
                <input class="iblocks" id="items" type="checkbox" name="items" value="Y">
                <label for="items">
                    Выгружать разделы и элементы
                </label>


            </form>

        </div>
        <input type="text" name="file-name" value="import">.php
        <input type="button" name="save" value="Сохранить в файл"> или можете сделать copy&paste кода:
        <span id="do-something"><br /><br />Выберите один из инфоблоков.</span>
        <textarea id="result_textarea" style="width:100%; height: 50%; position: relative; bottom: 0;"></textarea>
        </body>
        </html>
        <?
    }

    public function uploadArray( $arResult )
    {
        foreach($arResult as $arType){

            $this->addType( $arType );
            foreach($arType['IBLOCKS'] as $arIblock){
                $arIblock['IBLOCK_TYPE_ID'] = $arType['ID'];
                $iNewIblockId = $this->addIblock( $arIblock );
                foreach($arIblock['PROPS'] as $arProperty){
                    $arProperty['IBLOCK_ID'] = $iNewIblockId;
                    $this->addProperty( $arProperty );
                }
                if ( count($arIblock['SECTIONS'])>0 ){
                    $this->uploadSections( $arIblock, $iNewIblockId );
                }
            }
        }
        echo "<br /><b>Загружен массив:</b>";
        echo '<pre>';
        print_r($arResult);
        echo '</pre>';
    }
    protected function init()
    {
        $this->arrayName = 'arResult';

    }

    protected function ajax()
    {
        if (isset($_REQUEST['iblocks'][0])){
            $this->filter = $_REQUEST;
            $this->bImportElements = $_REQUEST['items']==="Y";
            if (count($this->filter)>0) $this->setFilter = true;
            $this->getIbStructure();
            print_r($this->generateFileContent());
        }
        exit();
    }

    protected function saveFile()
    {
        $fileName = preg_replace("/\W/i", "",$_REQUEST['file']);
        $scriptString = $_REQUEST['script'];
        $ext = '.php';
        if ( !file_exists($fileName.$ext) && $fileName===$_REQUEST['file']){
            $fileName = $fileName.$ext;
            $res = file_put_contents($fileName,$scriptString);
            if ($res>0)
                echo 'True';
            else
                echo 'False';
        }else {
            echo 'False';
        }

        exit();
    }

    /*
     * Загрузка разделов и элементов
     */
    protected function uploadSections( $arIblock, $iIblockId )
    {
        foreach($arIblock['SECTIONS'] as $arSection){
            if ($arSection['IS_ROOT']=="Y" && isset($arSection['ELEMENTS'][0])){
                foreach($arSection['ELEMENTS'] as $arElement){
                    $arElement["IBLOCK_SECTION_ID"] = false;
                    $arElement["IBLOCK_ID"] = $iIblockId;
                    $this->addElement( $arElement );
                }

            }else {
                $arSection["IBLOCK_ID"] = $iIblockId;
                $arSection["IBLOCK_TYPE_ID"] = $arIblock['IBLOCK_TYPE_ID'];
                $arSection["IBLOCK_SECTION_ID"] = false;

                $this->addSection( $arSection);

            }

        }
    }

    //Рекурсивная функция для добавления дерева разделов и элементов
    protected function addSection( $arSection )
    {
        //TODO: Сделать рефакторинг этого метода

        $iNewSectionId = 0; //TODO : Видимо эта переменная видна в рекурсивном методе
        $iIblockId = 0;
        $sIblockTypeId = $arSection['IBLOCK_TYPE_ID'];
        if ( strlen($arSection['CODE'])>0){
            $arFilter = Array('IBLOCK_ID'=>$arSection['IBLOCK_ID'], 'CODE'=>$arSection['CODE'],'IBLOCK_TYPE_ID'=>$sIblockTypeId);
            $db_list = CIBlockSection::GetList(Array(), $arFilter, true);
            if($arRes = $db_list->GetNext()){
                $iNewSectionId = $arRes['ID'];//такой раздел уже есть
                $iIblockId = $arRes['IBLOCK_ID'];
            }
        }

        if ($iNewSectionId==0){
            $bs = new CIBlockSection;
            if (is_array($arSection['PICTURE']))
                $arSection['PICTURE'] = $this->prepareFile($arSection['PICTURE']);
            if ($iNewSectionId = $bs->Add($arSection)) {
                echo '<br /><b>Новый раздел ' . $iNewSectionId . ' добавлен.</b>';
                $iIblockId = $arSection['IBLOCK_ID'];
            }
            unset($bs);
        }

        if ( intval($iNewSectionId) > 0  && intval($iIblockId)>0 ) {
            //добавляем элементы раздела
            if (isset($arSection['ELEMENTS'][0])) {
                foreach ($arSection['ELEMENTS'] as $arElement) {
                    $arElement["IBLOCK_SECTION_ID"] = $iNewSectionId;
                    $arElement["IBLOCK_ID"] = $iIblockId;
                    $this->addElement($arElement);
                }
            }
            //добавляем подразделы
            if (isset($arSection['SECTIONS'][0])) {
                foreach ($arSection['SECTIONS'] as $arSection) {
                    $arSection["IBLOCK_SECTION_ID"] = $iNewSectionId;
                    $arSection["IBLOCK_ID"] = $iIblockId;
                    $arSection['IBLOCK_TYPE_ID'] = $sIblockTypeId;
                    $this->addSection($arSection);
                }
            }
        }
        return $iNewSectionId;
    }

    protected function addElement( $arElement )
    {
        if (is_array($arElement['DETAIL_PICTURE']))
            $arSection['DETAIL_PICTURE'] = $this->prepareFile( $arElement['DETAIL_PICTURE'] );
        if (is_array($arElement['PREVIEW_PICTURE']))
            $arSection['PREVIEW_PICTURE'] = $this->prepareFile( $arElement['PREVIEW_PICTURE'] );

        if (strlen($arElement['CODE'])>0 ){
            $arSelect = Array("ID");
            $arFilter = Array("IBLOCK_ID"=>$arElement['IBLOCK_ID'], "CODE"=>$arElement['CODE']);
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
            if($ob = $res->GetNextElement()){
                $arFields = $ob->GetFields();
                $el = new CIblockElement();
                if ($el->Update($arFields['ID'],$arElement))
                    echo 'Элемент '.$arElement['CODE'].' обновлен.';
                else
                    echo $el->LAST_ERROR;

                unset( $el );
                return $arFields['ID'];
            }
        }

        $el = new CIblockElement();
        if ($iNewId = $el->Add( $arElement ))
            echo '<br /><b>Новый элемент '.$iNewId.' добавлен.</b>';
        else
            echo $el->LAST_ERROR;

        unset( $el );
        return $iNewId;
    }

    protected function addType( $arType )
    {
        $db_iblock_type = CIBlockType::GetList(false, array("=ID"=>$arType["ID"]));
        if($ar_iblock_type = $db_iblock_type->Fetch()){
            echo '<br /><b>Тип инфоблока '.$arType["ID"].' уже есть.</b>';
        }else {
            $obBlocktype = new CIBlockType;
            $res = $obBlocktype->Add($arType);
            echo '<br /><b>Новый тип инфоблока '.$arType["ID"].' добавлен.</b>';
        }
    }

    protected function addIblock($arIblock)
    {
        $res = CIBlock::GetList(
            Array(),
            Array(
                'TYPE'=>$arIblock['IBLOCK_TYPE_ID'],
                "CODE"=>$arIblock['CODE']
            ), true
        );

        if($arFindedIblock = $res->Fetch() ){

            $this->iblockIdByCode[$arFindedIblock['CODE']] = $arFindedIblock['ID'];
            echo 'Инфоблока '.$arIblock["CODE"].' уже есть.';
            return $arFindedIblock['ID'];
        }else {
            echo 'Инфоблок не найден.';
            $ib = new CIBlock;
            $iNewIblockId = false;
            if ($iNewIblockId = $ib->Add($arIblock)) {
                $this->iblockIdByCode[$arIblock['CODE']] = $iNewIblockId;
                echo '<br /><b>Инфоблок успешно добавлен.</b>';
            }else {
                echo $ib->LAST_ERROR;
            }
            unset($ib);
            return $iNewIblockId;

        }
    }

    protected function addProperty( $arProperty )
    {
        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$arProperty['IBLOCK_ID'],"CODE"=>$arProperty["CODE"]));

        if($prop_fields = $properties->GetNext()){
            echo '<br />Свойство '.$arProperty["CODE"].' уже есть.';
        }else {
            $ibp = new CIBlockProperty;
            if ($PropID = $ibp->Add($arProperty)){
                echo '<br /><b>Свойство '.$arProperty["CODE"].' добавлено.</b>';
            }
            unset( $ibp );
        }
    }

    protected function generateFileContent()
    {
        $arrayString = self::arrayToString( $this->arResult ,$this->arrayName,'    ');
        $content = "<?php \n";
        $content .= "require(\$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); \n";
        $content .= "include(\"bm.php\");\n";
        $content .= $arrayString;
        $content .= "\$bm = new BitrixMigration(__FILE__);\n";
        $content .= "\$bm->sStoreFilesDir = '/bm_files/';\n";
        $content .= "\$bm->uploadArray(\$arResult);\n";
        $content .= "?>";
        return $content;
    }


    protected function getIbStructure()
    {
        CModule::IncludeModule("iblock");
        $db_iblock_type = CIBlockType::GetList(array(),array()); //"id"=>"help"
        $arResult = array();
        while($ar_iblock_type = $db_iblock_type->Fetch()){

            if($arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG)){
                $sTypeId = $arIBType['IBLOCK_TYPE_ID'];
                $arIBType = array_intersect_key($arIBType, array_flip( $this->arIblockTypeKeys ) );
                $arIBType['LANG']=Array(
                    $arIBType['LID']=>Array(
                        'NAME'=>$arIBType['NAME'],
                        'SECTION_NAME'=>$arIBType['SECTION_NAME'],
                        'ELEMENT_NAME'=>$arIBType['ELEMENT_NAME']
                    )
                );
                $arIBType['IBLOCKS'] = $this->getIBlocksArray( $sTypeId );
                if ( isset($arIBType['IBLOCKS'][0]))
                    $arResult[] =  $arIBType;
            }
        }
        $this->arResult = $arResult;

    }

    protected function getIBlocksArray($type)
    {
        $arResult = array();
        $res = CIBlock::GetList(
            Array(),
            Array(
                'TYPE'=>$type,
        ), true);

        while($ar_res = $res->Fetch()){

            $iIBlockID = $ar_res['ID'];
            if ($this->filterItem($ar_res['ID'],'iblocks')){

                $ar_res = array_intersect_key($ar_res, array_flip( $this->arIblockKeys ) );
                $ar_res['SITE_ID']  = array(SITE_ID);
                $ar_res['PROPS'] = $this->getIBlocksProperties( $iIBlockID );

                if ($this->bImportElements){
                    $ar_res['SECTIONS'] = $this->getSections( $iIBlockID );
                }
                //myPrintR($ar_res,__FILE__,__LINE__ );

                $arResult[] = $ar_res;
            }
        }
        return $arResult;
    }
    protected function filterItem($id,$type)
    {
        if (!$this->setFilter) return true;

        return  in_array($id,$this->filter[$type]);
    }

    protected function getIBlocksProperties($iblockId)
    {
        $arResult = array();
        $properties = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$iblockId));

        while ($prop_fields = $properties->GetNext()){

            foreach ($prop_fields as $sFieldName=>$sValue){
                if ( !in_array( $sFieldName, $this->arRequiredProps) ){
                    unset( $prop_fields[ $sFieldName ]);
                }
            }


            if ( $prop_fields["PROPERTY_TYPE"] == "L" ){

                $prop_fields['VALUES'] = Array();
                $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockId, "CODE"=>$prop_fields['CODE']));
                while($enum_fields = $property_enums->GetNext()){


                    foreach ($enum_fields as $sEnumFieldName=>$sValue){
                        if ( !in_array( $sEnumFieldName, $this->arRequiredEnumFields) ){
                            unset( $enum_fields[ $sEnumFieldName ]);
                        }
                    }
                    $prop_fields['VALUES'][] = $enum_fields;
                }
            }

            $arResult[$prop_fields['CODE']] = $prop_fields;

        }
        return $arResult;
    }


    protected function getSections( $iIblockId, $iParentSectionId = false )
    {
        $arSections = array();

        //добавляем корневой раздел если есть элементы в корне
        if (!$iParentSectionId){
            $arRootElements = $this->getElements( $iIblockId, false );
            if (count($arRootElements)>0){
                $arSections[] = array(
                    "IS_ROOT" => "Y",
                    "ELEMENTS"=>$arRootElements,
                );
            }
        }
        $db_list = CIBlockSection::GetList(Array(), Array('IBLOCK_ID'=>$iIblockId, "SECTION_ID"=>$iParentSectionId), false);
        while($arSection = $db_list->GetNext()){
            //фильтруем параметры
            $iSectionId = $arSection['ID'];

            if ( empty($arSection['CODE']) ) $arSection['CODE']= 'OLD_ID_'.$arSection['ID'];

            $arSection = array_intersect_key($arSection, array_flip( $this->arSectionKeys ) );

            if (intval($arSection['PICTURE'])>0){
                $arSection['PICTURE'] = $this->getFile( intval($arSection['PICTURE']) );
            }

            //подключаем подразделы
            if ($iSectionId>0){
                $arSubSections = $this->getSections( $iIblockId, $iSectionId );
                if ( count($arSubSections)>0 ) {
                    $arSection['SECTIONS'] = $arSubSections;
                }
            }
            $arSection['ELEMENTS'] = $this->getElements( $iIblockId, $iSectionId );

            $arSections[] = $arSection;
        }
        $arSections[] = $arSection;
        return $arSections; //$this->arrayToString($arSections,'arElements');
    }

    protected function getElements( $iIblockId, $iSectionId  )
    {
        $arElements = array();
        $arSelect = Array("ID","CODE","EXTERNAL_ID","NAME","IBLOCK_ID","IBLOCK_SECTION_ID","ACTIVE","DATE_ACTIVE_FROM","
                        DATE_ACTIVE_TO","SORT","PREVIEW_PICTURE","PREVIEW_TEXT","PREVIEW_TEXT_TYPE","DETAIL_PICTURE","DETAIL_TEXT",
                        "DETAIL_TEXT_TYPE","SEARCHABLE_CONTENT","DATE_CREATE","CREATED_BY","TIMESTAMP_X","TAGS");
        $arFilter = Array("IBLOCK_ID"=>$iIblockId, "SECTION_ID"=>$iSectionId );
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();

            if ( empty($arFields['CODE']) ) $arFields['CODE']= 'OLD_ID_'.$arFields['ID'];

            $this->prepareElementFields( $arFields );

            $arProperties = $ob->GetProperties();
            foreach($arProperties as $sPropertyCode => $arProperty){

                if ( empty($arProperty['VALUE']) ) continue;
                //фильтруем поля
                $arProperty = array_intersect_key($arProperty, array_flip( array("PROPERTY_TYPE","VALUE") ) );
                if ($arProperty["PROPERTY_TYPE"]=="F"){
                    $arProperty['FILE'] = $this->getFile( $arProperty['VALUE'] );
                }
                $arFields["PROPERTIES"][ $sPropertyCode ] = $arProperty;
            }

            $arFields = array_intersect_key($arFields, array_flip( $this->arElementKeys ) );
            $arElements[] = $arFields;
        }
        return $arElements;
    }

    protected function prepareElementFields(&$arFields)
    {
        foreach($arFields as $key=>$value){
            switch($key){
                case "DETAIL_PICTURE":
                case "PREVIEW_PICTURE":
                    $arFields[$key] = $this->getFile( intval($arFields[$key]) );
                break;
                case "DETAIL_TEXT":
                case "PREVIEW_TEXT":

                    $arFields[$key] = htmlspecialchars($arFields["~".$key], ENT_QUOTES);;
                break;

            }
        }
    }

    protected function getFile( $iFileId )
    {
        if (!$iFileId) return "";
        $arFile = CFile::GetFileArray( $iFileId );

        $arPathInfo = pathinfo( $_SERVER['DOCUMENT_ROOT'].$arFile['SRC'] );
        $sFileName = $arFile['ID'].'.'.$arPathInfo['extension'];

        if (!file_exists( $this->sFullFilesPath )) {
            mkdir($this->sFullFilesPath, 0775, true);
        }
        if (!file_exists( $this->sFullFilesPath.'/'.$sFileName)) {
            if (copy( $_SERVER['DOCUMENT_ROOT'].$arFile['SRC'],$this->sFullFilesPath.'/'.$sFileName))
                $arFile['NEW_SRC'] =  $sFileName;
        }else {
            $arFile['NEW_SRC'] =  $sFileName;
        }
        return $arFile;
    }

    /*
     *  "ID" => "17",
        "TIMESTAMP_X" => "13.01.2015 21:09:47",
        "MODULE_ID" => "iblock",
        "HEIGHT" => "1080",
        "WIDTH" => "1920",
        "FILE_SIZE" => "3299943",
        "CONTENT_TYPE" => "image/jpeg",
        "SUBDIR" => "iblock/95d",
        "FILE_NAME" => "conflating_by_mbaldelli-d82slpr.jpg",
        "ORIGINAL_NAME" => "conflating_by_mbaldelli-d82slpr.jpg",
        "DESCRIPTION" => "",
        "HANDLER_ID" => "",
        "~src" => "",
        "SRC" => "/upload/iblock/95d/conflating_by_mbaldelli-d82slpr.jpg",
        "NEW_SRC" => "17.jpg",
     * */
    protected function prepareFile( $arFile )
    {
        if (!file_exists( $this->sFullFilesPath.'/'.$arFile['NEW_SRC'] )) {
            return CFile::MakeFileArray($this->sFullFilesPath.'/'.$arFile['NEW_SRC']);

        }
        return false;
    }

    protected static function arrayToString($array,$arrayName, $indent='    ')
    {
        if ($indent=='    ')
            $resultText = "\$".$arrayName." = Array (\n";

        foreach ($array as $key=>$value){
            if (is_array($value)){
                $resultText .= $indent."'".$key."' => Array (\n";
                $resultText .= self::arrayToString($value,'',$indent.'    ');
                $resultText .= $indent."),\n";
            }else {
                $resultText.= $indent."'".$key."' => '".$value."',\n";
            }
        }

        if ($indent=='    ')
            $resultText .= ");\n";

        return $resultText;
    }

}
?>
